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
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\ResourceEndpoint;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Pim\Api\Model\EmployeeJobDetailModel;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class EmployeeJobDetailAPI extends Endpoint implements ResourceEndpoint
{
    use AuthUserTrait;
    use EmployeeServiceTrait;
    use DateTimeHelperTrait;

    public const PARAMETER_THEME = 'theme';
    public const PARAMETER_JOINED_DATE = 'joinedDate';
    public const PARAMETER_JOB_TITLE_ID = 'jobTitleId';
    public const PARAMETER_EMP_STATUS_ID = 'empStatusId';
    public const PARAMETER_JOB_CATEGORY_ID = 'jobCategoryId';
    public const PARAMETER_SUBUNIT_ID = 'subunitId';
    public const PARAMETER_LOCATION_ID = 'locationId';

    /**
     * @OA\Get(
     *     path="/api/v2/pim/employees/{empNumber}/job-details",
     *     tags={"PIM/Employee Job Details"},
     *     summary="Get an Employee's Job Details",
     *     operationId="get-an-employees-job-details",
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
     *                 ref="#/components/schemas/Pim-EmployeeJobDetailModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResourceResult
    {
        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );
        $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        $employee->setJobs($this->getHedwigeJobs($this->getAuthUser()->getUserHedwigeToken()));
        $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);
        return new EndpointResourceResult(EmployeeJobDetailModel::class, $employee);
    }

    /**
     * @param string $token
     * @return string
     */
    protected function getHedwigeJobs(string $token) : string
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/user/jobs", [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_encode(json_decode($response->getBody(), true)['sectors'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
                CommonParams::PARAMETER_EMP_NUMBER,
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
     *     path="/api/v2/pim/employees/{empNumber}/job-details",
     *     tags={"PIM/Employee Job Details"},
     *     summary="Update an Employee's Job Details",
     *     operationId="update-an-employees-job-details",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="joinedDate", type="string", format="date"),
     *             @OA\Property(property="jobTitleId", type="integer"),
     *             @OA\Property(property="empStatusId", type="integer"),
     *             @OA\Property(property="jobCategoryId", type="integer"),
     *             @OA\Property(property="subunitId", type="integer"),
     *             @OA\Property(property="locationId", type="integer")
     *             @OA\Property(property="jobs", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeJobDetailModel"
     *             ),
     *             @OA\Property(property="empNumber", type="integer")
     *         )
     *     )
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResourceResult
    {

    $empNumber = $this->getRequestParams()->getInt(
        RequestParams::PARAM_TYPE_ATTRIBUTE,
        CommonParams::PARAMETER_EMP_NUMBER
    );
    $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
    $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

    $jobs = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, 'jobs');

    if (!is_array($jobs)) {
        throw new \InvalidArgumentException("Le paramètre `jobs` doit être un tableau.");
    }

    $jobs = array_map(function ($job) {
        return html_entity_decode($job, ENT_QUOTES, 'UTF-8');
    }, $jobs);

    $employee->setJobs(json_encode($jobs, JSON_UNESCAPED_UNICODE));

    $sendJobs = $employee->getJobs();
    $client = new Client();
    $clientBaseUrl = getenv('HEDWIGE_URL');

    $token = $this->getAuthUser()->getUserHedwigeToken();
    
    try {
        $response = $client->request('PUT', "{$clientBaseUrl}/user/jobs", [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $sendJobs
        ]);
        $responseContent = $response->getBody()->getContents();

        $employee->setJobs($sendJobs);

        return new EndpointResourceResult(EmployeeJobDetailModel::class, $employee);
    } catch (\Exception $e) {
        error_log("Erreur lors de la requête PUT Hedwige : " . $e->getMessage());

        throw $e;
    }

    return new EndpointResourceResult(EmployeeJobDetailModel::class, $employee);

    }
        // $empNumber = $this->getRequestParams()->getInt(
        //     RequestParams::PARAM_TYPE_ATTRIBUTE,
        //     CommonParams::PARAMETER_EMP_NUMBER
        // );
        // error_log("Received empNumber: " . $empNumber);
        // echo'<empNumber>';
        // var_dump($empNumber);
        // print_r($empNumber);
        // echo'</empNumber>';
        // $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
        // $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

        // $jobs = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, 'jobs');

        // error_log("Received jobs: " . json_encode($jobs));

        // if (!is_array($jobs) || empty($jobs)) {
        //     throw new \Exception("Invalid 'jobs' data: expected a non-empty array.");
        // }
        
        // $employee->setJobs(json_encode($jobs));
        // error_log("Set jobs successfully");
        
        // echo'<jobs>';
        // print_r($employee->getJobs());
        // echo'</jobs>';

        // $joinedDate = $this->getRequestParams()->getDateTimeOrNull(
        //     RequestParams::PARAM_TYPE_BODY,
        //     self::PARAMETER_JOINED_DATE
        // );
        // $jobTitleId = $this->getRequestParams()->getIntOrNull(
        //     RequestParams::PARAM_TYPE_BODY,
        //     self::PARAMETER_JOB_TITLE_ID
        // );
        // $empStatusId = $this->getRequestParams()->getIntOrNull(
        //     RequestParams::PARAM_TYPE_BODY,
        //     self::PARAMETER_EMP_STATUS_ID
        // );
        // $jobCategoryId = $this->getRequestParams()->getIntOrNull(
        //     RequestParams::PARAM_TYPE_BODY,
        //     self::PARAMETER_JOB_CATEGORY_ID
        // );
        // $subunitId = $this->getRequestParams()->getIntOrNull(
        //     RequestParams::PARAM_TYPE_BODY,
        //     self::PARAMETER_SUBUNIT_ID
        // );
        // $locationId = $this->getRequestParams()->getIntOrNull(
        //     RequestParams::PARAM_TYPE_BODY,
        //     self::PARAMETER_LOCATION_ID
        // );

        // $currentJoinedDate = $employee->getJoinedDate();
        // $employee->setJoinedDate($joinedDate);
        // $employee->getDecorator()->setJobTitleById($jobTitleId);
        // $employee->getDecorator()->setEmpStatusById($empStatusId);
        // $employee->getDecorator()->setJobCategoryById($jobCategoryId);
        // $employee->getDecorator()->setSubunitById($subunitId);
        // $employee->getDecorator()->setLocationById($locationId);

        // $this->getEmployeeService()->updateEmployeeJobDetails($employee);
        // echo'ICI';
        // var_dump([
        //     'empNumber' => $empNumber,
        //     'joinedDate' => $joinedDate,
        //     'jobTitleId' => $jobTitleId,
        //     'empStatusId' => $empStatusId,
        // ]);
        // echo'ICI';

        // if (!$this->getDateTimeHelper()->isDatesEqual($currentJoinedDate, $employee->getJoinedDate(), true)) {
        //     $this->getEmployeeService()->dispatchJoinedDateChangedEvent($employee, $currentJoinedDate);
        // }

        // return new EndpointResourceResult(EmployeeJobDetailModel::class, $employee);
    // }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        $rules = new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
            new ParamRule(
                'jobs',
                new Rule(Rules::REQUIRED),
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_JOINED_DATE,
                    new Rule(Rules::API_DATE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_JOB_TITLE_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_EMP_STATUS_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_JOB_CATEGORY_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_SUBUNIT_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_LOCATION_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
        );

        return $rules;
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