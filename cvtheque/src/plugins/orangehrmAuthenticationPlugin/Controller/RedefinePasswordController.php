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


    public function preRender(Request $request): void
    {
        $component = new Component('auth-redefine-password');

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
}
