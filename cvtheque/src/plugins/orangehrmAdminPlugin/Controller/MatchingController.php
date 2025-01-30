<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;

class MatchingController extends AbstractVueController
{

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('matching-list');
        $this->setComponent($component);
    }
}
