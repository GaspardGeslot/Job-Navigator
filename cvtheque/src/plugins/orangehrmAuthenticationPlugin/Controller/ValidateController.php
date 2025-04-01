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

class ValidateController extends AbstractController implements PublicControllerInterface
{
    use AuthUserTrait;
    use ServiceContainerTrait;
    use CsrfTokenManagerTrait;
    use SessionHandlingTrait;

    public const PARAMETER_USERNAME = 'username';
    public const PARAMETER_PASSWORD = 'password';

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
        $username = $request->request->get(self::PARAMETER_USERNAME, '');
        $password = $request->request->get(self::PARAMETER_PASSWORD, '');
        $theme = $request->attributes->get('theme');
        $role = $request->attributes->get('role');
        $role = $role !== null && $role === 'admin' ? 'Admin' : 'ESS';
        $credentials = new UserCredential($username, $password, $role);

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        if ($role === 'Admin') {
            $loginUrl = $urlGenerator->generate('auth_login_admin', ['theme' => $theme], UrlGenerator::ABSOLUTE_URL);
        } else {
            $loginUrl = $urlGenerator->generate('auth_login', ['theme' => $theme], UrlGenerator::ABSOLUTE_URL);
        }

        try {
            $token = $request->request->get('_token');
            if (!$this->getCsrfTokenManager()->isValid('login', $token)) {
                throw AuthenticationException::invalidCsrfToken();
            }
            error_log("Controller : \nUsername : " . $username . "\nPassword : " . $password . "\nTheme : " . $theme . "\nRole : " . $role);
            /** @var AuthProviderChain $authProviderChain */
            $authProviderChain = $this->getContainer()->get(Services::AUTH_PROVIDER_CHAIN);
            $token = $authProviderChain->authenticate(new AuthParams($credentials, null, $theme));
            $success = !is_null($token);

            if (!$success) {
                throw AuthenticationException::invalidCredentials();
            }
            $this->getAuthUser()->setIsAuthenticated($success);
            $this->getAuthUser()->setIsAdmin($role === 'Admin');
            $this->getAuthUser()->setIsCandidate($role !== 'Admin');
            $this->getAuthUser()->setUserHedwigeToken($token);
            $this->getLoginService()->addLogin($credentials);
        } catch (AuthenticationException $e) {
            error_log('Info error when authenticating : ' . $e->getTraceAsString());
            $this->getAuthUser()->addFlash(AuthUser::FLASH_LOGIN_ERROR, $e->normalize());
            if ($e instanceof RedirectableException) {
                return new RedirectResponse($e->getRedirectUrl());
            }
            return new RedirectResponse($loginUrl);
        } catch (Throwable $e) {
            error_log('Unexpected error when authenticating : ' . $e->getTraceAsString());
            $this->getAuthUser()->addFlash(
                AuthUser::FLASH_LOGIN_ERROR,
                [
                    'error' => AuthenticationException::UNEXPECT_ERROR,
                    'message' => "Une erreur inattendue s'est produite. Veuillez contacter votre conseiller Constructys.",
                ]
            );
            return new RedirectResponse($loginUrl);
        }

        $redirectUrl = $this->handleSessionTimeoutRedirect($theme);
        if ($redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        $homePagePath = $this->getHomePageService()->getHomePagePath();
        return $this->redirect($theme . "/" . $homePagePath);
    }
}
