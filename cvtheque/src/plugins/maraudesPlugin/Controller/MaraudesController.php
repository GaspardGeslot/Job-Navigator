<?php
namespace Maraudes\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Framework\Http\Request;

class MaraudesController extends AbstractVueController implements PublicControllerInterface
{
    use ThemeServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('maraudes-home');
        
        $component->addProp(
            new Prop('maraudes-logo-src', Prop::TYPE_STRING, $request->getBasePath() . '/images/cvtheque_logo.png')
        );
        
        $component->addProp(
            new Prop('maraudes-banner-src', Prop::TYPE_STRING, $this->getThemeService()->getLoginBannerURL($request))
        );
        
        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }
}

