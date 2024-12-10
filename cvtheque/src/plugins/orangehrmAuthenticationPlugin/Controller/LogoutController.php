<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\Authentication\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Core\Utility\Base64Url;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Session\Session;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class LogoutController extends AbstractController
{
    public const RESET_PASSWORD_TOKEN_RANDOM_BYTES_LENGTH = 16;
    use ServiceContainerTrait;
    use AuthUserTrait;

    public function handle(): RedirectResponse
    {
        $isCandidate = $this->getAuthUser()->getIsCandidate();
        /** @var Session $session */
        $session = $this->getContainer()->get(Services::SESSION);
        $session->invalidate();
        return $this->redirect($isCandidate ? "auth/login" : "auth/company/login");
    }
}
