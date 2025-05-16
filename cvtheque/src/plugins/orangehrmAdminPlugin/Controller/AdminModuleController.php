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

namespace OrangeHRM\Admin\Controller;

use Exception;
use OrangeHRM\Core\Controller\AbstractModuleController;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;

class AdminModuleController extends AbstractModuleController
{
    /**
     * @return RedirectResponse
     * @throws Exception
     */
    public function handle(Request $request): RedirectResponse
    {
        $defaultPath = '';
        switch ($request->attributes->get('theme')) {
            case 'olecio':
                $defaultPath = 'admin/viewLeads';
                break;
            case 'constructys':
                $defaultPath = 'admin/viewCandidates';
                break;
            default:
                $defaultPath = $this->getHomePageService()->getAdminModuleDefaultPath();
        }
        return $this->redirect($request->attributes->get('theme') . "/" . $defaultPath);
    }
}
