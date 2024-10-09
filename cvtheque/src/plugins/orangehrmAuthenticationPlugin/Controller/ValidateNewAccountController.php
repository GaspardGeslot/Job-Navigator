<?php
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

class ValidateNewAccountController extends AbstractController implements PublicControllerInterface
{
    use AuthUserTrait;
    use ServiceContainerTrait;
    use CsrfTokenManagerTrait;
    use SessionHandlingTrait;

    public const PARAMETER_EMAIL = 'email';
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
        $email = $request->request->get(self::PARAMETER_EMAIL, '');
        $password = $request->request->get(self::PARAMETER_PASSWORD, '');
        $credentials = new UserCredential($email, $password);

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        $createAccountUrl = $urlGenerator->generate('auth_create_account', [], UrlGenerator::ABSOLUTE_URL);

        try {
            $token = $request->request->get('_token');
            if (!$this->getCsrfTokenManager()->isValid('login', $token)) {
                throw AuthenticationException::invalidCsrfToken();
            }

            /** @var AuthProviderChain $authProviderChain */
            $authProviderChain = $this->getContainer()->get(Services::AUTH_PROVIDER_CHAIN);
            
            $token = $authProviderChain->signIn(new AuthParams($credentials));
            $success = !is_null($token);

            if (!$success)
                throw AuthenticationException::invalidCredentials();
            $this->getAuthUser()->setIsAuthenticated($success);
            $this->getAuthUser()->setUserHedwigeToken($token);
            $this->getLoginService()->addLogin($credentials);
        } catch (AuthenticationException $e) {
            $this->getAuthUser()->addFlash(AuthUser::FLASH_LOGIN_ERROR, $e->normalize());
            if ($e instanceof RedirectableException) {
                return new RedirectResponse($e->getRedirectUrl());
            }
            return new RedirectResponse($createAccountUrl);
        } catch (Throwable $e) {
            $this->getAuthUser()->addFlash(
                AuthUser::FLASH_LOGIN_ERROR,
                [
                    'error' => AuthenticationException::UNEXPECT_ERROR,
                    'message' => 'Unexpected error occurred',
                ]
            );
            return new RedirectResponse($createAccountUrl);
        }
        $redirectUrl = $this->handleSessionTimeoutRedirect();
        if ($redirectUrl) {
            return new RedirectResponse($redirectUrl);
        }

        $homePagePath = $this->getHomePageService()->getHomePagePath();
        return $this->redirect($homePagePath);
    }
}
