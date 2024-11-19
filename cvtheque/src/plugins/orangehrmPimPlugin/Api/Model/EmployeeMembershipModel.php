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

namespace OrangeHRM\Pim\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\EmployeeMembership;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeeMembershipModel",
 *     type="object",
 *     @OA\Property(property="title", type="string", maxLength=100),
 *     @OA\Property(property="description", type="string", maxLength=300),
 *     @OA\Property(property="year", type="integer", format="int32")
 * )
 */
class EmployeeMembershipModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param EmployeeMembership $employeeMembership
     */
    public function __construct(EmployeeMembership $employeeMembership)
    {
        $this->setEntity($employeeMembership);
        $this->setFilters(
            [
                'title',
                'description',
                'year',
            ]
        );
        $this->setAttributeNames(
            [
                'title',
                'description',
                'year',
            ]
        );
    }
}
