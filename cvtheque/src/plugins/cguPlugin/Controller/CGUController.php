<?php
namespace CGU\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\Request;

class CGUController extends AbstractVueController implements PublicControllerInterface
{

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $this->setComponent(new Component('view-cgu'));
        $this->setTemplate('no_header.html.twig');
    }
}