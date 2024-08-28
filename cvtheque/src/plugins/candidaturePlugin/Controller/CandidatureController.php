<?php
namespace Candidature\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\Request;

class CandidatureController extends AbstractVueController implements PublicControllerInterface
{
    use ThemeServiceTrait;
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
        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }
}
