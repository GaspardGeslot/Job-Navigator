<?php

namespace OrangeHRM\Authentication\Controller;

use GuzzleHttp\Exception\GuzzleException;
use OrangeHRM\Authentication\Auth\AuthProviderChain;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Authentication\Controller\Traits\SessionHandlingTrait;
use OrangeHRM\Authentication\Dto\AuthParams;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Authentication\Exception\AuthenticationException;
use OrangeHRM\Authentication\Exception\UserAlreadyEnrolledException;
use OrangeHRM\Authentication\Service\AuthenticationService;
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

class ValidateOlecioController extends AbstractController implements PublicControllerInterface
{
    use AuthUserTrait;
    use ServiceContainerTrait;
    use CsrfTokenManagerTrait;
    use SessionHandlingTrait;

    public const PARAMETER_EMAIL = 'email';
    public const PARAMETER_PASSWORD = 'password';
    public const PARAMETER_ORIGIN = 'origin';
    public const OAUTH_SOURCE_OLECIO = 'Olecio';

    protected ?LoginService $loginService = null;
    protected ?HomePageService $homePageService = null;
    protected ?AuthenticationService $authenticationService = null;

    public function getHomePageService(): HomePageService
    {
        if (!$this->homePageService instanceof HomePageService) {
            $this->homePageService = new HomePageService();
        }
        return $this->homePageService;
    }

    public function getLoginService(): LoginService
    {
        if (is_null($this->loginService)) {
            $this->loginService = new LoginService();
        }
        return $this->loginService;
    }

    public function getAuthenticationService(): AuthenticationService
    {
        if (is_null($this->authenticationService)) {
            $this->authenticationService = new AuthenticationService();
        }
        return $this->authenticationService;
    }

    public function handle(Request $request): RedirectResponse
    {
        $email = $request->request->get(self::PARAMETER_EMAIL, '');
        $password = $request->request->get(self::PARAMETER_PASSWORD, '');
        // origin n'influence que la page de retour en cas d'erreur (login | createAccount)
        $origin = $request->request->get(self::PARAMETER_ORIGIN, 'login');
        // rétrocompatibilité avec l'ancien champ role
        if ($origin !== 'login' && $origin !== 'createAccount') {
            $roleParam = $request->request->get('role', 'candidate');
            $origin = $roleParam === 'admin' ? 'login' : 'createAccount';
        }
        $theme = $request->attributes->get('theme');
        $useSubdomain = $request->attributes->get('_use_subdomain', false);

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        $originUrl = $this->resolveOriginUrl($urlGenerator, $origin, $useSubdomain, $theme);

        try {
            $token = $request->request->get('_token');
            if (!$this->getCsrfTokenManager()->isValid('login', $token)) {
                throw AuthenticationException::invalidCsrfToken();
            }

            if ($email === '' || $password === '') {
                $this->flashError(
                    AuthenticationException::UNEXPECT_ERROR,
                    "Authentification échouée : informations utilisateur incomplètes."
                );
                return new RedirectResponse($originUrl);
            }

            // Existence locale sans filtre de rôle : même comportement depuis LoginAdmin et CreateAccount
            $lookupCredentials = new UserCredential($email, $password, null, self::OAUTH_SOURCE_OLECIO);
            $existsLocal = $this->getAuthenticationService()->hasCredentials($lookupCredentials, $theme);

            try {
                $existsHedwige = $this->getAuthenticationService()->existsHedwigeEmail($email, $theme);
            } catch (GuzzleException | Throwable $e) {
                error_log('Olecio OAuth exists email check failed: ' . $e->getMessage());
                $this->flashError(
                    AuthenticationException::UNEXPECT_ERROR,
                    "Une erreur s'est produite lors de la vérification de l'email."
                );
                return new RedirectResponse($originUrl);
            }

            if ($existsLocal !== $existsHedwige) {
                $this->flashError(
                    AuthenticationException::UNEXPECT_ERROR,
                    'Compte incohérent. Veuillez contacter le support.'
                );
                return new RedirectResponse($originUrl);
            }

            /** @var AuthProviderChain $authProviderChain */
            $authProviderChain = $this->getContainer()->get(Services::AUTH_PROVIDER_CHAIN);
            $isExistingUser = $existsLocal && $existsHedwige;
            $hedwigeToken = null;

            if ($isExistingUser) {
                $result = $authProviderChain->authenticate(new AuthParams($lookupCredentials, null, $theme));
                $success = !is_null($result) && !is_null($result['token']);
                if (!$success) {
                    $this->flashError(
                        AuthenticationException::INVALID_CREDENTIALS,
                        'Cet email est déjà utilisé avec un autre compte.'
                    );
                    return new RedirectResponse($originUrl);
                }
                $hedwigeToken = $result['token'];
                $credentialsForLog = $lookupCredentials;
                $sessionIsAdmin = $this->getAuthUser()->getUserRoleName() === 'Admin';
                $this->getAuthUser()->setIsAdmin($sessionIsAdmin);
            } else {
                // Création toujours en ESS ; Admin uniquement via promotion manuelle en BDD
                $createCredentials = new UserCredential($email, $password, 'ESS', self::OAUTH_SOURCE_OLECIO);
                $hedwigeToken = $authProviderChain->signIn(new AuthParams($createCredentials, null, $theme));
                $success = !is_null($hedwigeToken);
                if (!$success) {
                    throw AuthenticationException::invalidCredentials();
                }
                $sessionIsAdmin = false;
                $credentialsForLog = $createCredentials;
            }

            $this->getAuthUser()->setUserHedwigeToken($hedwigeToken);
            $this->getAuthUser()->setIsAuthenticated(true);
            $this->getAuthUser()->setIsCandidate(!$sessionIsAdmin);
            $this->getLoginService()->addLogin($credentialsForLog);

            $redirectUrl = $this->handleSessionTimeoutRedirect($theme);
            if ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            }

            if (!$isExistingUser) {
                return $this->redirect('/' . $theme . '/pim/viewMyDetails');
            }

            $homePagePath = $this->getHomePageService()->getHomePagePath();
            return $this->redirect('/' . $homePagePath);
        } catch (UserAlreadyEnrolledException $e) {
            $this->flashError(
                AuthenticationException::USER_ALREADY_SIGNED_IN,
                'Compte déjà créé, veuillez vous connecter.'
            );
            return new RedirectResponse($originUrl);
        } catch (AuthenticationException $e) {
            error_log('Olecio OAuth auth error: ' . $e->getMessage());
            $this->getAuthUser()->addFlash(AuthUser::FLASH_LOGIN_ERROR, $e->normalize());
            if ($e instanceof RedirectableException) {
                return new RedirectResponse($e->getRedirectUrl());
            }
            return new RedirectResponse($originUrl);
        } catch (Throwable $e) {
            error_log('Olecio OAuth unexpected error: ' . $e->getTraceAsString());
            $this->flashError(
                AuthenticationException::UNEXPECT_ERROR,
                "Une erreur inattendue s'est produite. Veuillez contacter le support."
            );
            return new RedirectResponse($originUrl);
        }
    }

    private function resolveOriginUrl(
        UrlGenerator $urlGenerator,
        string $origin,
        bool $useSubdomain,
        ?string $theme
    ): string {
        if ($origin === 'login') {
            return $useSubdomain
                ? $urlGenerator->generate('subdomain_auth_login_admin', [], UrlGenerator::ABSOLUTE_URL)
                : $urlGenerator->generate('auth_login_admin', ['theme' => $theme], UrlGenerator::ABSOLUTE_URL);
        }

        return $useSubdomain
            ? $urlGenerator->generate('subdomain_auth_create_account', [], UrlGenerator::ABSOLUTE_URL)
            : $urlGenerator->generate('auth_create_account', ['theme' => $theme], UrlGenerator::ABSOLUTE_URL);
    }

    private function flashError(string $error, string $message): void
    {
        $this->getAuthUser()->addFlash(
            AuthUser::FLASH_LOGIN_ERROR,
            [
                'error' => $error,
                'message' => $message,
            ]
        );
    }
}
