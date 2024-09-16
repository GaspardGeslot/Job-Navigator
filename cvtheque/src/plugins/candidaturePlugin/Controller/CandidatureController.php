<?php
namespace Candidature\Controller;

use GuzzleHttp\Client;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Authorization\Service\HomePageService;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\Request;

class CandidatureController extends AbstractVueController implements PublicControllerInterface
{
    use AuthUserTrait;
    use ThemeServiceTrait;

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
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('new-candidature');
        $component->addProp(
            new Prop('candidature-logo-src', Prop::TYPE_STRING, $request->getBasePath() . '/images/cvtheque_logo.png')
        );
        $component->addProp(
            new Prop('candidature-banner-src', Prop::TYPE_STRING, $this->getThemeService()->getLoginBannerURL($request))
        );
        $component->addProp(
            new Prop('options', Prop::TYPE_OBJECT, $this->getCandidatureOptions())
        );
        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }

    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        if ($this->getAuthUser()->isAuthenticated()) {
            $homePagePath = $this->getHomePageService()->getHomePagePath();
            return $this->redirect($homePagePath);
        }
        return parent::handle($request);
    }

    /**
     * @return array|object
     */
    public function getCandidatureOptions(): array|object
    {
        $client = new Client();
        $clientId = getenv('HEDWIGE_CLIENT_ID');
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/options", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody());
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }
}