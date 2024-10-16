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

namespace OrangeHRM\CorporateDirectory\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\Employee;

/**
 * @OA\Schema(
 *     schema="CorporateDirectory-EmployeeDirectoryModel",
 *     type="object",
 *     @OA\Property(property="companyId", type="integer"),
 *     @OA\Property(property="companyName", type="string"),
 *     @OA\Property(property="companyLogo", type="string"),
 *     @OA\Property(property="companyLocation", type="string"),
 *     @OA\Property(property="companyPhoneNumberContact", type="string"),
 *     @OA\Property(property="companyEmailContact", type="string"),
 *     @OA\Property(property="companyMatchingJobTitle", type="string"),
 *     @OA\Property(property="jobTitle", type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="isDeleted", type="boolean")
 *     ),
 *     @OA\Property(property="subunit", type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="location", type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 */
class EmployeeDirectoryModel implements Normalizable
{
    use ModelTrait;

    /**
     * @param Employee $employee
     */
    public function __construct(Employee $employee)
    {
        $this->setEntity($employee);
        $this->setFilters(
            [
                'companyId',
                'companyName',
                'companyLogo',
                'companyLocation',
                'companyPhoneNumberContact',
                'companyEmailContact',
                'companyMatchingJobTitle',
                /*['getEmployeeTerminationRecord', 'getId'],
                ['getJobTitle', 'getId'],
                ['getJobTitle', 'getJobTitleName'],
                ['getJobTitle', 'isDeleted'],
                ['getSubDivision', 'getId'],
                ['getSubDivision', 'getName'],
                ['getDecorator', 'getLocation', 'getId'],
                ['getDecorator', 'getLocation', 'getName'],*/
            ]
        );
        $this->setAttributeNames(
            [
                'companyId',
                'companyName',
                'companyLogo',
                'companyLocation',
                'companyPhoneNumberContact',
                'companyEmailContact',
                'companyMatchingJobTitle',
                /*['jobTitle', 'id'],
                ['jobTitle', 'title'],
                ['jobTitle', 'isDeleted'],
                ['subunit', 'id'],
                ['subunit', 'name'],
                ['location', 'id'],
                ['location', 'name'],*/
            ]
        );
    }
}
