<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Admin\Traits\Service\UserServiceTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;

class UsersController extends AbstractVueController
{
    use AuthUserTrait;
    use UserServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('users-list');
        $this->setComponent($component);
    }
}
