<?php
namespace OrangeHRM\Authentication\Controller;

use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Authentication\Traits\CsrfTokenManagerTrait;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Authorization\Service\HomePageService;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\EventDispatcherTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\OpenidAuthentication\Traits\Service\SocialMediaAuthenticationServiceTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use OrangeHRM\Authentication\Service\ResetPasswordService;

class RedefinePasswordController extends AbstractVueController implements PublicControllerInterface
{
    use AuthUserTrait;
    use EventDispatcherTrait;
    use ThemeServiceTrait;
    use CsrfTokenManagerTrait;
    use SocialMediaAuthenticationServiceTrait;

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

    protected ?ResetPasswordService $resetPasswordService = null;

    /**
     * @return ResetPasswordService
     */
    public function getResetPasswordService(): ResetPasswordService
    {
        if (!$this->resetPasswordService instanceof ResetPasswordService) {
            $this->resetPasswordService = new ResetPasswordService();
        }
        return $this->resetPasswordService;
    }

    public function preRender(Request $request): void
    {
        $component = new Component('auth-redefine-password');
        $email = $request->query->get('email');

        $component->addProp(new Prop('email', Prop::TYPE_STRING, $email));

        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }
    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();

        if (!$token) {
            /** @var UrlGenerator $urlGenerator */
            $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
            $redirectUrl = $urlGenerator->generate('subdomain_auth_login', [],UrlGenerator::ABSOLUTE_URL);
            return new RedirectResponse($redirectUrl);
        }

        return parent::handle($request);
    }

    public function redefine(Request $request)
    {
        $theme = $request->attributes->get('theme');
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $data = json_decode($request->getContent(), true);

        try {
            $this->redefinePasswordHedwige($token, $data);
            $this->getResetPasswordService()->redefinePassword($data['email'], $data['newPassword'], $theme);
            $this->getAuthUser()->setHasToRedefinedPassword(false);

            $homePagePath = $this->getHomePageService()->getHomePagePath();
            return new Response(json_encode([
                'error' => false,
                'message' => 'Password redefined successfully',
                'redirectUrl' => '/' . $homePagePath
            ]), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_OK);
        }
    }

    public function redefinePasswordHedwige(string $token, array $data): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/user/password/redefine";
        $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data)
        ]);
    }
}
