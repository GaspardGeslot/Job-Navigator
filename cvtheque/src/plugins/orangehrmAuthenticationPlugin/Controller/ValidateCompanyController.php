<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\Authentication\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OrangeHRM\Authentication\Auth\AuthProviderChain;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Authentication\Controller\Traits\SessionHandlingTrait;
use OrangeHRM\Authentication\Dto\AuthParams;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Authentication\Exception\AuthenticationException;
use OrangeHRM\Authentication\Service\LoginService;
use OrangeHRM\Authentication\Traits\CsrfTokenManagerTrait;
use OrangeHRM\Core\Authorization\Service\HomePageService;
use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Exception\RedirectableException;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use Throwable;

class ValidateCompanyController extends AbstractController implements PublicControllerInterface
{
    use AuthUserTrait;
    use ServiceContainerTrait;
    use CsrfTokenManagerTrait;
    use SessionHandlingTrait;

    public const PARAMETER_SIRET = 'siret';
    public const PARAMETER_ADHERENT_CODE = 'adherentCode';

    /**
     * @var null|LoginService
     */
    protected ?LoginService $loginService = null;

    /**
     * @var HomePageService|null
     */
    protected ?HomePageService $homePageService = null;

    /**
     * @return HomePageService
     */
    public function getHomePageService(): HomePageService
    {
        if (!$this->homePageService instanceof HomePageService) {
            $this->homePageService = new HomePageService();
        }
        return $this->homePageService;
    }

    /**
     * @return LoginService
     */
    public function getLoginService(): LoginService
    {
        if (is_null($this->loginService)) {
            $this->loginService = new LoginService();
        }
        return $this->loginService;
    }

    public function handle(Request $request): RedirectResponse
    {
        $siret = $request->request->get(self::PARAMETER_SIRET, '');
        $adherentCode = $request->request->get(self::PARAMETER_ADHERENT_CODE, '');

        try{
            /** @var UrlGenerator $urlGenerator */
            $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
            $loginUrl = $urlGenerator->generate('auth_login_company', [], UrlGenerator::ABSOLUTE_URL);
            
            $clientToken = $this->retrieveClientToken();    
            $isAuthorizedByClient = $this->checkIsAuthorizedByClient($siret, $adherentCode, $clientToken);
            if (!$isAuthorizedByClient) {
                $this->getAuthUser()->addFlash(
                    AuthUser::FLASH_LOGIN_ERROR, 
                    [
                        'error' => AuthenticationException::UNEXPECT_ERROR,
                        'message' => "Votre entreprise n'est pas identifiée comme adhérente à Constructys. Veuillez contacter votre conseiller Constructys.",
                    ]
                );
                return new RedirectResponse($loginUrl);
            }

            $credentials = new UserCredential($siret, $adherentCode, 'Interviewer');

            $token = $request->request->get('_token');
            if (!$this->getCsrfTokenManager()->isValid('login', $token)) {
                throw AuthenticationException::invalidCsrfToken();
            }

            /** @var AuthProviderChain $authProviderChain */
            $authProviderChain = $this->getContainer()->get(Services::AUTH_PROVIDER_CHAIN);
            $token = $authProviderChain->authenticateCompany(new AuthParams($credentials));
            $success = !is_null($token);

            if (!$success) {
                throw AuthenticationException::invalidCredentials();
            }
            $this->getAuthUser()->setIsAuthenticated($success);
            $this->getAuthUser()->setIsCandidate(false);
            $this->getAuthUser()->setUserHedwigeToken($token);
            $this->getLoginService()->addLogin($credentials);
        } catch (ClientException $e) {
            $this->getAuthUser()->addFlash(AuthUser::FLASH_LOGIN_ERROR, 
            [
                'error' => AuthenticationException::UNEXPECT_ERROR,
                'message' => $e->getResponse()->getBody()->getContents(),
            ]);
            return new RedirectResponse($loginUrl);
        } catch (AuthenticationException $e) {
            $this->getAuthUser()->addFlash(AuthUser::FLASH_LOGIN_ERROR, $e->normalize());
            if ($e instanceof RedirectableException) {
                return new RedirectResponse($e->getRedirectUrl());
            }
            return new RedirectResponse($loginUrl);
        } catch (Throwable | ServerException $e) {
            $this->getAuthUser()->addFlash(
                AuthUser::FLASH_LOGIN_ERROR,
                [
                    'error' => AuthenticationException::UNEXPECT_ERROR,
                    'message' => "Une erreur inattendue s'est produite. Veuillez contacter votre conseiller Constructys.",
                ]
            );
            return new RedirectResponse($loginUrl);
        }

        $redirectUrl = $this->handleSessionTimeoutRedirect();
        if ($redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        $homePagePath = $this->getHomePageService()->getHomePagePath();
        return $this->redirect($homePagePath);
    }

    private function retrieveClientToken(): string
    {
        $client = new Client();
        $clientToken = getenv('CONSTUCTYS_CLIENT_TOKEN');
        $clientBaseUrl = getenv('CONSTUCTYS_URL');

        try {
            $response = $client->request('POST', "{$clientBaseUrl}/api/token", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody(), true)['token'];
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }

    private function checkIsAuthorizedByClient(string $siret, string $adherentCode, string $token): bool
    {
        $client = new Client();
        $clientBaseUrl = getenv('CONSTUCTYS_URL');

        $response = $client->request('POST', "{$clientBaseUrl}/api/checkAdhStatus", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'siret' => $siret,
                'sentrep' => (int) $adherentCode,
            ]
        ]);

        return filter_var($response->getBody()->getContents(), FILTER_VALIDATE_BOOLEAN);
    }
}
