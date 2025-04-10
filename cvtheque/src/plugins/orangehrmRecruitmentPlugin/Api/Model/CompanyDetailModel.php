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

namespace OrangeHRM\Recruitment\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Entity\Employee;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeePersonalDetailModel",
 *     type="object",
 *     @OA\Property(property="empNumber", type="integer"),
 *     @OA\Property(property="profileId", type="integer"),
 *     @OA\Property(property="studyLevel", type="string"),
 *     @OA\Property(property="courseStart", type="string"),
 *     @OA\Property(property="companyName", type="string"),
 *     @OA\Property(property="companySiret", type="string"),
 *     @OA\Property(property="companyWorkforce", type="string"),
 *     @OA\Property(property="companyNafCode", type="string"),
 *     @OA\Property(property="need", type="string"),
 *     @OA\Property(property="lastName", type="string"),
 *     @OA\Property(property="firstName", type="string"),
 *     @OA\Property(property="middleName", type="string"),
 *     @OA\Property(property="employeeId", type="string"),
 *     @OA\Property(property="otherId", type="string"),
 *     @OA\Property(property="resume", type="int"),
 *     @OA\Property(property="roles", type="string"),
 *     @OA\Property(property="drivingLicenseNo", type="string"),
 *     @OA\Property(property="drivingLicenseExpiredDate", type="string", format="date"),
 *     @OA\Property(property="gender", type="string"),
 *     @OA\Property(property="maritalStatus", type="string"),
 *     @OA\Property(property="birthday", type="string", format="date"),
 *     @OA\Property(property="terminationId", type="integer"),
 *     @OA\Property(property="nationality", type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="ssnNumber", type="string", nullable=true),
 *     @OA\Property(property="sinNumber", type="string", nullable=true),
 *     @OA\Property(property="nickname", type="string", nullable=true),
 *     @OA\Property(property="smoker", type="integer", nullable=true),
 *     @OA\Property(property="militaryService", type="string", nullable=true),
 * )
 */
class CompanyDetailModel implements Normalizable
{
    use ModelTrait;
    use ConfigServiceTrait;

    /**
     * @param Employee $employee
     */
    public function __construct(Employee $employee)
    {
        $this->setEntity($employee);
        $filter = [
            'attachment',
            'candidatureStatus',
            'city',
            'companyName',
            'companyEmailContact',
            'companyPhoneNumberContact',
            'companyPresentation',
            'companySiret',
            'companyWebsite',
            'companyWorkforce',
            'companyNafCode',
            'jobs',
            'province',
            'street1',
            'zipcode',
        ];

        $attributeNames = [
            'attachment',
            'candidatureStatus',
            'city',
            'companyName',
            'companyEmailContact',
            'companyPhoneNumberContact',
            'companyPresentation',
            'companySiret',
            'companyWebsite',
            'companyWorkforce',
            'companyNafCode',
            'jobs',
            'province',
            'street1',
            'zipcode',
        ];

        $this->setFilters($filter);
        $this->setAttributeNames($attributeNames);
    }
}
