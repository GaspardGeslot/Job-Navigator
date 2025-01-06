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

use GuzzleHttp\Client;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\ResourceEndpoint;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\EmployeeAttachment;
use OrangeHRM\Core\Dto\Base64Attachment;
use OrangeHRM\Entity\UserRole;
use OrangeHRM\Pim\Api\Model\EmployeePersonalDetailModel;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;
use OrangeHRM\Pim\Service\EmployeeAttachmentService;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class EmployeePersonalDetailAPI extends Endpoint implements ResourceEndpoint
{
    use AuthUserTrait;
    use UserRoleManagerTrait;
    use EmployeeServiceTrait;
    use ConfigServiceTrait;

    public const PARAMETER_THEME = 'theme';
    public const PARAMETER_EMP_NUMBER = 'empNumber';
    public const PARAMETER_FIRST_NAME = 'firstName';
    public const PARAMETER_MIDDLE_NAME = 'middleName';
    public const PARAMETER_LAST_NAME = 'lastName';
    public const PARAMETER_NEED = 'need';
    public const PARAMETER_ATTACHMENT = 'attachment';
    public const PARAMETER_ATTACHMENT_METHOD = 'attachmentMethod';
    public const PARAMETER_ATTACHMENT_ID = 'attachmentId';
    public const PARAMETER_LICENSE = 'drivingLicense';
    public const PARAMETER_MOTIVATION = 'motivation';
    public const PARAMETER_SALARY = 'salary';
    public const PARAMETER_STUDY_LEVEL = 'studyLevel';
    public const PARAMETER_COURSE_START = 'courseStart';
    public const PARAMETER_COMPANY_NAME = 'companyName';
    public const PARAMETER_COMPANY_SIRET = 'companySiret';
    public const PARAMETER_COMPANY_WEBSITE = 'companyWebsite';
    public const PARAMETER_COMPANY_PRESENTATION = 'companyPresentation';
    public const PARAMETER_COMPANY_WORKFORCE = 'companyWorkforce';
    public const PARAMETER_COMPANY_NAF_CODE = 'companyNafCode';
    public const PARAMETER_EMPLOYEE_ID = 'employeeId';
    public const PARAMETER_OTHER_ID = 'otherId';
    public const PARAMETER_DRIVING_LICENSE_NO = 'drivingLicenseNo';
    public const PARAMETER_DRIVING_LICENSE_EXPIRED_DATE = 'drivingLicenseExpiredDate';
    public const PARAMETER_GENDER = 'gender';
    public const PARAMETER_MARTIAL_STATUS = 'maritalStatus';
    public const PARAMETER_BIRTHDAY = 'birthday';
    public const PARAMETER_NATIONALITY_ID = 'nationalityId';

    // Deprecated Fields
    public const PARAMETER_NICKNAME = 'nickname';
    public const PARAMETER_SMOKER = 'smoker';
    public const PARAMETER_MILITARY_SERVICE = 'militaryService';

    // Country Specific
    public const PARAMETER_SSN_NUMBER = 'ssnNumber';
    public const PARAMETER_SIN_NUMBER = 'sinNumber';

    public const PARAM_RULE_ATTACHMENT_FILE_NAME_MAX_LENGTH = 100;
    public const PARAM_RULE_FIRST_NAME_MAX_LENGTH = 30;
    public const PARAM_RULE_MIDDLE_NAME_MAX_LENGTH = 30;
    public const PARAM_RULE_LAST_NAME_MAX_LENGTH = 30;
    public const PARAM_RULE_EMPLOYEE_ID_MAX_LENGTH = 50;
    public const PARAM_RULE_NEED_MAX_LENGTH = 100;
    public const PARAM_RULE_STUDY_LEVEL_MAX_LENGTH = 100;
    public const PARAM_RULE_COMPANY_WORKFORCE_MAX_LENGTH = 100;
    public const PARAM_RULE_COMPANY_NAME_MAX_LENGTH = 100;
    public const PARAM_RULE_COMPANY_SIRET_MAX_LENGTH = 100;
    public const PARAM_RULE_COMPANY_WEBSITE_MAX_LENGTH = 300;
    public const PARAM_RULE_COMPANY_PRESENTATION_MAX_LENGTH = 1000;
    public const PARAM_RULE_COMPANY_NAF_CODE_MAX_LENGTH = 100;
    public const PARAM_RULE_COURSE_START_MAX_LENGTH = 100;
    public const PARAM_RULE_OTHER_ID_MAX_LENGTH = 100;
    public const PARAM_RULE_DRIVING_LICENSE_NO_MAX_LENGTH = 100;
    public const PARAM_RULE_MARTIAL_STATUS_MAX_LENGTH = 20;
    public const PARAM_RULE_NICKNAME_MAX_LENGTH = 100;
    public const PARAM_RULE_MILITARY_SERVICE_MAX_LENGTH = 100;
    public const PARAM_RULE_SSN_NUMBER_MAX_LENGTH = 100;
    public const PARAM_RULE_SIN_NUMBER_MAX_LENGTH = 100;

    /**
     * @var EmployeeAttachmentService|null
     */
    protected ?EmployeeAttachmentService $employeeAttachmentService = null;

    /**
     * @return EmployeeAttachmentService
     */
    public function getEmployeeAttachmentService(): EmployeeAttachmentService
    {
        if (!$this->employeeAttachmentService instanceof EmployeeAttachmentService) {
            $this->employeeAttachmentService = new EmployeeAttachmentService();
        }
        return $this->employeeAttachmentService;
    }

    /**
     * @OA\Get(
     *     path="/api/v2/pim/employees/{empNumber}/personal-details",
     *     tags={"PIM/Employee Personal Details"},
     *     summary="Get an Employee's Personal Details",
     *     operationId="get-an-employees-personal-details",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeePersonalDetailModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResourceResult
    {
        $empNumber = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_EMP_NUMBER);
        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        $profile = $this->getHedwigeProfile($this->getAuthUser()->getUserHedwigeToken());
        
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        if ($this->getAuthUser()->getIsCandidate())
            $employee->setProfileInfo($profile);
        else $employee->setCompany($profile);
        
        $userRoles = $this->getUserRoleManager()->getUserRolesForAuthUser();
        $userRoleNames = array_map(fn (UserRole $userRole) => $userRole->getName(), $userRoles);
        $employee->setOtherId(json_encode($userRoleNames));
        return new EndpointResourceResult(EmployeePersonalDetailModel::class, $employee);
    }

    /**
     * @param string $token
     * @return mixed
     */
    protected function getHedwigeProfile(string $token) : mixed
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $profileType = $this->getAuthUser()->getIsCandidate() ? "user" : "company";
            $response = $client->request('GET', "{$clientBaseUrl}/{$profileType}/info", [
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
     * @OA\Put(
     *     path="/api/v2/pim/employees/{empNumber}/personal-details",
     *     tags={"PIM/Employee Personal Details"},
     *     summary="Update an Employee's Personal Details",
     *     operationId="update-an-employees-personal-details",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="lastName", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_LAST_NAME_MAX_LENGTH),
     *             @OA\Property(property="firstName", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_FIRST_NAME_MAX_LENGTH),
     *             @OA\Property(property="middleName", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_MIDDLE_NAME_MAX_LENGTH),
     *             @OA\Property(property="employeeId", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_EMPLOYEE_ID_MAX_LENGTH),
     *             @OA\Property(property="otherId", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_OTHER_ID_MAX_LENGTH),
     *             @OA\Property(property="drivingLicenseNo", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_DRIVING_LICENSE_NO_MAX_LENGTH),
     *             @OA\Property(property="drivingLicenseExpiredDate", type="string", format="date"),
     *             @OA\Property(property="ssnNumber", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_SSN_NUMBER_MAX_LENGTH),
     *             @OA\Property(property="sinNumber", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_SIN_NUMBER_MAX_LENGTH),
     *             @OA\Property(property="need", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_NEED_MAX_LENGTH),
     *             @OA\Property(property="studyLevel", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_STUDY_LEVEL_MAX_LENGTH),
     *             @OA\Property(property="courseStart", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_COURSE_START_MAX_LENGTH),
     *             @OA\Property(property="gender", type="integer"),
     *             @OA\Property(property="maritalStatus", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_MARTIAL_STATUS_MAX_LENGTH),
     *             @OA\Property(property="birthday", type="string", format="date"),
     *             @OA\Property(property="nationalityId", type="integer"),
     *             @OA\Property(property="nickname", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_NICKNAME_MAX_LENGTH),
     *             @OA\Property(property="smoker", type="boolean"),
     *             @OA\Property(property="militaryService", type="string", maxLength=OrangeHRM\Pim\Api\EmployeePersonalDetailAPI::PARAM_RULE_MILITARY_SERVICE_MAX_LENGTH),
     *             required={"lastName", "firstName"},
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeDependentModel"
     *             ),
     *             @OA\Property(property="empNumber", type="integer")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResourceResult
    {
        // error_log('find motivation' . $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_MOTIVATION));
        $empNumber = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_EMP_NUMBER);
        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        if ($this->getAuthUser()->getIsCandidate()) {
            $employee->setFirstName(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_FIRST_NAME)
            );
            $employee->setMiddleName(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_MIDDLE_NAME)
            );
            $employee->setLastName(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_LAST_NAME)
            );
            $employee->setNeed(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NEED)
            );
            $employee->setDrivingLicense(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_LICENSE)
            );
            $employee->setMotivation(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_MOTIVATION)
            );
            $employee->setSalary(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_SALARY)
            );
            $employee->setStudyLevel(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_STUDY_LEVEL)
            );
            $employee->setCourseStart(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COURSE_START)
            );

        } else {
            $employee->setCompanySiret($employee->getEmployeeId());
            $employee->setCompanyWorkforce(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_WORKFORCE)
            );
            $employee->setCompanyName(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_NAME)
            );
            $employee->setFirstName(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_NAME)
            );
            $employee->setCompanySiret(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_SIRET)
            );
            $employee->setCompanyPresentation(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_PRESENTATION)
            );
            $employee->setCompanyWebsite(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_WEBSITE)
            );
            $employee->setCompanyNafCode(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_COMPANY_NAF_CODE)
            );
        }
        /*$employee->setEmployeeId(
            $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_EMPLOYEE_ID)
        );*/
        $employee->setOtherId(
            $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_OTHER_ID)
        );
        /*$employee->setDrivingLicenseNo(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_DRIVING_LICENSE_NO
            )
        );
        $employee->setDrivingLicenseExpiredDate(
            $this->getRequestParams()->getDateTimeOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_DRIVING_LICENSE_EXPIRED_DATE
            )
        );*/
        $employee->setGender(
            $this->getRequestParams()->getStringOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_GENDER)
        );
        /*$employee->setMaritalStatus(
            $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_MARTIAL_STATUS)
        );*/
        $employee->setBirthday(
            $this->getRequestParams()->getDateTimeOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_BIRTHDAY)
        );
        /*$employee->getDecorator()->setNationality(
            $this->getRequestParams()->getIntOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NATIONALITY_ID)
        );

        $showDeprecatedFields = $this->getConfigService()->showPimDeprecatedFields();
        $showSsn = $this->getConfigService()->showPimSSN();
        $showSin = $this->getConfigService()->showPimSIN();

        // Deprecated Fields
        if ($showDeprecatedFields) {
            $employee->setNickName(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_NICKNAME)
            );
            $employee->getDecorator()->setSmoker(
                $this->getRequestParams()->getBooleanOrNull(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_SMOKER)
            );
            $employee->setMilitaryService(
                $this->getRequestParams()->getStringOrNull(
                    RequestParams::PARAM_TYPE_BODY,
                    self::PARAMETER_MILITARY_SERVICE
                )
            );
        }

        // Country Specific
        if ($showSsn) {
            $employee->setSsnNumber(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_SSN_NUMBER)
            );
        }
        if ($showSin) {
            $employee->setSinNumber(
                $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_SIN_NUMBER)
            );
        }*/

        $this->getEmployeeService()->updateEmployeePersonalDetails($employee);
        if ($this->getAuthUser()->getIsCandidate()) {
            $profileId = $this->updateHedwigeProfile($this->getAuthUser()->getUserHedwigeToken(), $employee);
            $employee->setProfileId($profileId);
        } else {
            $this->updateHedwigeCompany($this->getAuthUser()->getUserHedwigeToken(), $employee);    
        }
            
        $attachmentMethod = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ATTACHMENT_METHOD);
        $attachmentId = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_BODY, self::PARAMETER_ATTACHMENT_ID);
        if ($attachmentMethod && $attachmentMethod !== 'keepCurrent') {
            if ($attachmentMethod === 'deleteCurrent') {
                $this->getEmployeeAttachmentService()->deleteEmployeeAttachments(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'), 'personal', [$attachmentId]);
                $this->removeHedwigeAttachment($this->getAuthUser()->getUserHedwigeToken());
                if($this->getAuthUser()->getIsCandidate())
                    $employee->setResume(-1);
                else $employee->setAttachment(-1);
            } else {
                $base64Attachment = $this->getRequestParams()->getAttachmentOrNull(
                    RequestParams::PARAM_TYPE_BODY,
                    self::PARAMETER_ATTACHMENT
                );
                if (!is_null($base64Attachment))
                {
                    if ($attachmentId && $attachmentId != -1)
                        $this->getEmployeeAttachmentService()->deleteEmployeeAttachments(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'), 'personal', [$attachmentId]);
                    $employeeAttachment = new EmployeeAttachment();
                    $employeeAttachment->getDecorator()->setEmployeeByEmpNumber(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'));
                    $employeeAttachment->setScreen("personal");
                    $this->setAttachmentAttributesForCreateAndGetId($employeeAttachment, $base64Attachment, getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'), 'personal');
                    $newEmployeeAttachment = $this->getEmployeeAttachmentService()->saveEmployeeAttachment($employeeAttachment);
                    $attachmentId = $newEmployeeAttachment->getAttachId();
                    $this->updateHedwigeAttachment($this->getAuthUser()->getUserHedwigeToken(), $attachmentId);
                }
                if($this->getAuthUser()->getIsCandidate())
                    $employee->setResume($attachmentId);
                else $employee->setAttachment($attachmentId);
            }
        } else {
            if($this->getAuthUser()->getIsCandidate())
                $employee->setResume($attachmentId);
            else $employee->setAttachment($attachmentId);
        }
        return new EndpointResourceResult(EmployeePersonalDetailModel::class, $employee);
    }

    /**
     * @param string $token
     * @param Employee $employee
     * @return int
     */
    protected function updateHedwigeProfile(string $token, Employee $employee) : int
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $data = [
            'firstName' => $employee->getFirstName(),
            'need' => $employee->getNeed(),
            'drivingLicenses' => $employee->getDrivingLicense() !== null && $employee->getDrivingLicense() !== '' ? json_decode($employee->getDrivingLicense(), true) : [],
            'motivation' => $employee->getMotivation(),
            'salaryExpectation' => $employee->getSalary(),
            'studyLevel' => $employee->getStudyLevel(),
            'lastName' => $employee->getLastName(),
            'civility' => $employee->getGender(),
            'courseStart' => $employee->getCourseStart(),
            'birthDate' => $employee->getBirthday() != null ? $employee->getBirthday()->format('Y-m-d') : null,
        ];

        try {
            $response = $client->request('PUT', "{$clientBaseUrl}/user/info", [
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
     */
    protected function updateHedwigeCompany(string $token, Employee $employee) : void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        $data = [
            'name' => $employee->getCompanyName(),
            'nafCode' => $employee->getCompanyNafCode(),
            'workforce' => $employee->getCompanyWorkforce(),
            'website' => $employee->getCompanyWebsite(),
            'presentation' => $employee->getCompanyPresentation(),
        ];

        try {
            $client->request('PUT', "{$clientBaseUrl}/company/info", [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data)
            ]);
        } catch (\Exceptionon $e) {
        }
    }

    /**
     * @param string $token
     * @param int $attachmentId
     */
    protected function updateHedwigeAttachment(string $token, int $attachmentId) : void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = $this->getAuthUser()->getIsCandidate() ? "{$clientBaseUrl}/user/resume?resumeId={$attachmentId}" : "{$clientBaseUrl}/company/attachment?attachmentId={$attachmentId}";
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exceptionon $e) {
        }
    }

    /**
     * @param string $token
     */
    protected function removeHedwigeAttachment(string $token) : void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = $this->getAuthUser()->getIsCandidate() ? "{$clientBaseUrl}/user/resume" : "{$clientBaseUrl}/company/attachment";
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exceptionon $e) {
        }
    }


    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        $showDeprecatedFields = $this->getConfigService()->showPimDeprecatedFields();
        $showSsn = $this->getConfigService()->showPimSSN();
        $showSin = $this->getConfigService()->showPimSIN();
        $paramRules = [
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
                    self::PARAMETER_FIRST_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_FIRST_NAME_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MIDDLE_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_MIDDLE_NAME_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_LAST_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_LAST_NAME_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_NEED,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_NEED_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ATTACHMENT_METHOD,
                    new Rule(Rules::STRING_TYPE),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ATTACHMENT_ID,
                    new Rule(Rules::INT_TYPE),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    'drivingLicense',
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, 100]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    'motivation',
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, 2000]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    'salary',
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, 100]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_STUDY_LEVEL,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_STUDY_LEVEL_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COURSE_START,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COURSE_START_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_ATTACHMENT,
                    new Rule(
                        Rules::BASE_64_ATTACHMENT,
                        [null, null, self::PARAM_RULE_ATTACHMENT_FILE_NAME_MAX_LENGTH]
                    )
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMPANY_WORKFORCE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMPANY_WORKFORCE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMPANY_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMPANY_NAME_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMPANY_SIRET,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMPANY_SIRET_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMPANY_PRESENTATION,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMPANY_PRESENTATION_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMPANY_WEBSITE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMPANY_WEBSITE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMPANY_NAF_CODE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_COMPANY_NAF_CODE_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_EMPLOYEE_ID,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_EMPLOYEE_ID_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_OTHER_ID,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_OTHER_ID_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_DRIVING_LICENSE_NO,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_DRIVING_LICENSE_NO_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_DRIVING_LICENSE_EXPIRED_DATE,
                    new Rule(Rules::API_DATE),
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_GENDER,
                    new Rule(Rules::IN, [[Employee::GENDER_MALE, Employee::GENDER_FEMALE]])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MARTIAL_STATUS,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_MARTIAL_STATUS_MAX_LENGTH]),
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_BIRTHDAY,
                    new Rule(Rules::API_DATE),
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_NATIONALITY_ID,
                    new Rule(Rules::POSITIVE),
                )
            ),
        ];

        if ($showDeprecatedFields) {
            $paramRules[] = $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_NICKNAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_NICKNAME_MAX_LENGTH]),
                ),
                true
            );
            $paramRules[] = $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_SMOKER,
                    new Rule(Rules::BOOL_TYPE),
                ),
                true
            );
            $paramRules[] = $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MILITARY_SERVICE,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_MILITARY_SERVICE_MAX_LENGTH]),
                ),
                true
            );
        }

        if ($showSsn) {
            $paramRules[] = $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_SSN_NUMBER,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_SSN_NUMBER_MAX_LENGTH]),
                ),
                true
            );
        }

        if ($showSin) {
            $paramRules[] = $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_SIN_NUMBER,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAM_RULE_SIN_NUMBER_MAX_LENGTH]),
                ),
                true
            );
        }
        return new ParamRuleCollection(...$paramRules);
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

    /**
     * @param EmployeeAttachment $employeeAttachment
     * @param Base64Attachment $base64Attachment
     * @return EmployeeAttachment
     */
    private function setAttachmentAttributesForCreateAndGetId(
        EmployeeAttachment $employeeAttachment,
        Base64Attachment $base64Attachment,
        int $empNumber,
        string $screen
    ): EmployeeAttachment {

        $size = (int)$base64Attachment->getSize();
    
        if (!is_int($size)) {
            throw new \Exception('Attachment size must be an integer');
        }
        if ($base64Attachment->getSize() > 10485760) {
            throw new Exception('File size exceeds the limit of 10 MB.');
        }

        $content = $base64Attachment->getContent();
        if (is_string($content)) {
            $employeeAttachment->setAttachment($content);
        } else {
            throw new \Exception('Attachment content must be a valid binary string');
        }
    
        $employeeAttachment->setFilename($base64Attachment->getFilename());
        $employeeAttachment->setSize($size);
        $employeeAttachment->setFileType($base64Attachment->getFileType());
    
        $employeeAttachment->setAttachedBy($empNumber);
        $employeeAttachment->setAttachedByName($screen);
    
        return $employeeAttachment;
    }
}
