<?php

namespace OrangeHRM\Authentication\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;

class OlecioAuthCallbackController extends AbstractVueController implements PublicControllerInterface
{
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('auth-olecio-callback');
        $component->addProp(new Prop('token', Prop::TYPE_STRING, $request->query->get('token', '')));
        $component->addProp(new Prop('user-id', Prop::TYPE_STRING, $request->query->get('user_id', '')));
        $component->addProp(new Prop('email', Prop::TYPE_STRING, $request->query->get('email', '')));

        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }
}
