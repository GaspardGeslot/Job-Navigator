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

namespace OrangeHRM\Recruitment\Controller;

use GuzzleHttp\Client;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\Controller\VueComponentPermissionTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Recruitment\Service\CandidateService;
use OrangeHRM\Recruitment\Traits\Service\CandidateServiceTrait;
use OrangeHRM\Recruitment\Service\RecruitmentAttachmentService;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class ViewCompanyController extends AbstractVueController
{
    use VueComponentPermissionTrait;
    use CandidateServiceTrait;
    use ConfigServiceTrait;
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {

        $companyId = $request->attributes->has('companyId') ? $request->attributes->getInt('companyId') : $request->attributes->getInt('id');
        $matchingId = $request->attributes->has('matchingId') ? $request->attributes->getInt('matchingId') : null;
        $component = new Component('view-company-profile');
        $component->addProp(new Prop('updatable', Prop::TYPE_BOOLEAN, false));
        $component->addProp(new Prop('company-id', Prop::TYPE_NUMBER, $companyId));
        $component->addProp(new Prop('matching-id', Prop::TYPE_NUMBER, $matchingId));
        
        $this->setComponent($component);
    }
}