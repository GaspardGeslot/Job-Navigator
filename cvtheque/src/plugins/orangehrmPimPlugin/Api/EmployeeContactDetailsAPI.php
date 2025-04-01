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

namespace OrangeHRM\Pim\Api;

use Exception;
use GuzzleHttp\Client;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\Exception\BadRequestException;
use OrangeHRM\Core\Api\V2\Exception\RecordNotFoundException;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\UserRole;
use OrangeHRM\Pim\Api\Model\EmployeeContactDetailsModel;
use OrangeHRM\Pim\Service\EmployeeService;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class EmployeeContactDetailsAPI extends Endpoint implements CrudEndpoint
{
    use AuthUserTrait;
    use UserRoleManagerTrait;
    use EmployeeServiceTrait;

    public const PARAMETER_THEME = 'theme';
    public const PARAMETER_EMP_NUMBER = 'empNumber';
    public const PARAMETER_STREET_1 = 'street1';
    public const PARAMETER_STREET_2 = 'street2';
    public const PARAMETER_CITY = 'city';
    public const PARAMETER_PROVINCE = 'province';
    public const PARAMETER_ZIP_CODE = 'zipCode';
    public const PARAMETER_MOBILITY = 'mobility';
    public const PARAMETER_COUNTRY = 'country';
    public const PARAMETER_COUNTRY_CODE = 'countryCode';
    public const PARAMETER_HOME_TELEPHONE = 'homeTelephone';
    public const PARAMETER_WORK_TELEPHONE = 'workTelephone';
    public const PARAMETER_ALLOW_CONTACT_VIA_EMAIL = 'allowContactViaEmail';
    public const PARAMETER_ALLOW_CONTACT_VIA_PHONE = 'allowContactViaPhone';
    public const PARAMETER_MOBILE = 'mobile';
    public const PARAMETER_WORK_EMAIL = 'workEmail';
    public const PARAMETER_OTHER_EMAIL = 'otherEmail';

    public const PARAM_RULE_STREET_1_MAX_LENGTH = 100;
    public const PARAM_RULE_STREET_2_MAX_LENGTH = 100;
    public const PARAM_RULE_CITY_MAX_LENGTH = 100;
    public const PARAM_RULE_PROVINCE_MAX_LENGTH = 100;
    public const PARAM_RULE_MOBILITY_MAX_LENGTH = 300;
    public const PARAM_RULE_ZIP_CODE_MAX_LENGTH = 20;
    public const PARAM_RULE_COUNTRY_MAX_LENGTH = 100;
    public const PARAM_RULE_COUNTRY_CODE_MAX_LENGTH = 100;
    public const PARAM_RULE_HOME_TELEPHONE_MAX_LENGTH = 25;
    public const PARAM_RULE_WORK_TELEPHONE_MAX_LENGTH = 25;
    public const PARAM_RULE_MOBILE_MAX_LENGTH = 25;
    public const PARAM_RULE_WORK_EMAIL_MAX_LENGTH = EmployeeService::WORK_EMAIL_MAX_LENGTH;
    public const PARAM_RULE_OTHER_EMAIL_MAX_LENGTH = 50;

    /**
     * @OA\Get(
     *     path="/api/v2/pim/employee/{empNumber}/contact-details",
     *     tags={"PIM/Employee Contact Details"},
     *     summary="Get an Employee's Contact Details",
     *     operationId="get-an-employees-contact-details",
     *     description="This endpoint allows you to retrieve an employee's contact details.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeContactDetailsModel"
     *             ),
     *             @OA\Property(property="meta", type="object", additionalProperties=false)
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @return EndpointResourceResult
     * @throws Exception
     */
    public function getOne(): EndpointResourceResult
    {
        $empNumber = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_EMP_NUMBER);
        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        $contact = $this->getHedwigeContactDetails($this->getAuthUser()->getUserHedwigeToken());
        
        if ($this->getAuthUser()->getIsCandidate())
            $employee->setProfileContact($contact);
        else $employee->setCompanyContact($contact);
        
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        $userRoles = $this->getUserRoleManager()->getUserRolesForAuthUser();
        $userRoleNames = array_map(fn (UserRole $userRole) => $userRole->getName(), $userRoles);
        $employee->setOtherId(json_encode($userRoleNames));

        return new EndpointResourceResult(EmployeeContactDetailsModel::class, $employee);
    }

    /**
     * @param string $token
     * @return mixed
     */
    protected function getHedwigeContactDetails(string $token) : mixed
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $profileType = $this->getAuthUser()->getIsCandidate() ? "user" : "company";
            $response = $client->request('GET', "{$clientBaseUrl}/{$profileType}/contact", [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function getAll(): EndpointCollectionResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @OA\Put(
     *     path="/api/v2/pim/employee/{empNumber}/contact-details",
     *     tags={"PIM/Employee Contact Details"},
     *     summary="Update an Employee's Contact Details",
     *     operationId="update-an-employees-contact-details",
     *     description="This endpoint allows you to update an employee's contact details. Note that the work email field should be updated in order to send out notifications regarding Leaves.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="street1",
     *                 description="Specify the employee's street address",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_STREET_1_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="street2",
     *                 description="Specify additional details of the street address",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_STREET_2_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 description="Specify the employee's city",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_CITY_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="province",
     *                 description="Specify the employee's state/province",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_PROVINCE_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="zipCode",
     *                 description="Specify the employee's zipcode",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_ZIP_CODE_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="country",
     *                 description="Specify the employee's country name",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_COUNTRY_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="countryCode",
     *                 description="Specify the employee's country code",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_COUNTRY_CODE_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="homeTelephone",
     *                 description="Specify the employee's home telephone number",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_HOME_TELEPHONE_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="workTelephone",
     *                 description="Specify the employee's work telephone number",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_WORK_TELEPHONE_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="mobile",
     *                 description="Specify the employee's mobile phone number",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_MOBILE_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="workEmail",
     *                 description="Specify the employee's work email",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_WORK_EMAIL_MAX_LENGTH
     *             ),
     *             @OA\Property(
     *                 property="otherEmail",
     *                 description="Specify the employee's other email",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeContactDetailsAPI::PARAM_RULE_OTHER_EMAIL_MAX_LENGTH
     *             ),
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeContactDetailsModel"
     *             ),
     *             @OA\Property(property="meta", type="object", additionalProperties=false)
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     * @throws Exception
     */
    public function update(): EndpointResourceResult
    {
        $employee = $this->saveContactDetails();
        if ($this->getAuthUser()->getIsCandidate())
            $profileId = $this->updateHedwigeContact($this->getAuthUser()->getUserHedwigeToken(), $employee);
        else $profileId = $this->updateHedwigeCompanyContact($this->getAuthUser()->getUserHedwigeToken(), $employee);
        $employee->setProfileId($profileId);
        return new EndpointResourceResult(EmployeeContactDetailsModel::class, $employee);
    }

    /**
     * @param string $token
     * @param Employee $employee
     * @return int
     */
    protected function updateHedwigeContact(string $token, Employee $employee) : int
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        $data = [
            'contactEmail' => $employee->getOtherEmail(),
            'phoneNumber' => $employee->getMobile(),
            'mobility' => $employee->getMobility(),
            'address' => [
                'street' => $employee->getStreet1(),
                'city' => $employee->getCity(),
                'state' => $employee->getProvince(),
                'postalCode' => $employee->getZipcode(),
                'country' => $employee->getCountry(),
            ],
        ];

        try {
            $response = $client->request('PUT', "{$clientBaseUrl}/user/contact", [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data)
            ]);
            $responseBody = $response->getBody()->getContents();
            return (int) trim($responseBody);
        } catch (\Exceptionon $e) {
            return -1;
        }
    }

    /**
     * @param string $token
     * @param Employee $employee
     * @return int
     */
    protected function updateHedwigeCompanyContact(string $token, Employee $employee) : int
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        $data = [
            'contactEmail' => $employee->getOtherEmail(),
            'phoneNumber' => $employee->getMobile(),
            'allowContactViaEmail' => $employee->getCompanyAllowContactViaEmail(),
            'allowContactViaPhone' => $employee->getCompanyAllowContactViaPhone(),
            'address' => [
                'street' => $employee->getStreet1(),
                'city' => $employee->getCity(),
                'state' => $employee->getProvince(),
                'postalCode' => $employee->getZipcode(),
                'country' => $employee->getCountry(),
            ],
        ];

        try {
            $response = $client->request('PUT', "{$clientBaseUrl}/company/contact", [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data)
            ]);
            $responseBody = $response->getBody()->getContents();
            return (int) trim($responseBody);
        } catch (\Exceptionon $e) {
            return -1;
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_STREET_1,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_STREET_1_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_STREET_2,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_STREET_2_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_CITY,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_CITY_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_PROVINCE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_PROVINCE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ZIP_CODE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_ZIP_CODE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MOBILITY,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_MOBILITY_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COUNTRY,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COUNTRY_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COUNTRY_CODE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COUNTRY_CODE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_HOME_TELEPHONE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::PHONE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_HOME_TELEPHONE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_WORK_TELEPHONE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::PHONE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_WORK_TELEPHONE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MOBILE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::PHONE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_MOBILE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_WORK_EMAIL,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_WORK_EMAIL_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_OTHER_EMAIL,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_OTHER_EMAIL_MAX_LENGTH]),
                    new Rule(Rules::EMAIL),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ALLOW_CONTACT_VIA_EMAIL,
                    new Rule(Rules::BOOL_TYPE),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ALLOW_CONTACT_VIA_PHONE,
                    new Rule(Rules::BOOL_TYPE),
                ),
                true
            ),
        );
    }

    /**
     * @param string $email
     * @param string $currentEmailGetter
     * @return bool
     */
    public function isUniqueEmail(string $email, string $currentEmailGetter): bool
    {
        if (in_array($email, [null,''])) {
            return true;
        }

        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_EMP_NUMBER
        );
        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        if (!$employee instanceof Employee) {
            return false;
        }

        return $this->getEmployeeService()->isUniqueEmail($email, $employee->$currentEmailGetter());
    }

    /**
     * @return Employee
     * @throws RecordNotFoundException|BadRequestException
     */
    public function saveContactDetails(): Employee
    {
        $empNumber = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_EMP_NUMBER
        );
        $street1 = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_STREET_1);
        $street2 = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_STREET_2);
        $city = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_CITY);
        $province = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_PROVINCE
        );
        $zipcode = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ZIP_CODE);
        $country = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COUNTRY);
        $homeTelephone = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_HOME_TELEPHONE
        );
        $workTelephone = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_WORK_TELEPHONE
        );
        $mobile = $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_MOBILE);
        $workEmail = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_WORK_EMAIL
        );
        $otherEmail = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_OTHER_EMAIL
        );
        $allowContactViaEmail = $this->getRequestParams()->getBoolean(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_ALLOW_CONTACT_VIA_EMAIL
        );
        $allowContactViaPhone = $this->getRequestParams()->getBoolean(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_ALLOW_CONTACT_VIA_PHONE
        );
        $mobility = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_MOBILITY
        );

        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        $employee->setStreet1($street1);
        $employee->setStreet2($street2);
        $employee->setCity($city);
        $employee->setProvince($province);
        $employee->setZipcode($zipcode);
        $employee->setCountry($country);
        $employee->setWorkTelephone($workTelephone);
        $employee->setHomeTelephone($homeTelephone);
        $employee->setMobile($mobile);
        $employee->setWorkEmail($workEmail);
        $employee->setOtherEmail($otherEmail);
        $employee->setCompanyAllowContactViaEmail($allowContactViaEmail);
        $employee->setCompanyAllowContactViaPhone($allowContactViaPhone);
        $employee->setMobility($mobility);
        return $this->getEmployeeService()->saveEmployee($employee);
    }

    /**
     * @inheritDoc
     */
    public function delete(): EndpointResourceResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}
