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
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Entity\EmployeeMembership;
use OrangeHRM\Pim\Api\Model\EmployeeMembershipModel;
use OrangeHRM\Pim\Dto\EmployeeMembershipSearchFilterParams;
use OrangeHRM\Pim\Service\EmployeeMembershipService;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class EmployeeMembershipAPI extends Endpoint implements CrudEndpoint
{
    use AuthUserTrait;
    use EmployeeServiceTrait;

    public const PARAMETER_MEMBERSHIP_ID = 'membershipId';
    public const PARAMETER_SUBSCRIPTION_FEE = 'subscriptionFee';
    public const PARAMETER_SUBSCRIPTION_PAID_BY = 'subscriptionPaidBy';
    public const PARAMETER_SUBSCRIPTION_CURRENCY = 'currencyTypeId';
    public const PARAMETER_SUBSCRIPTION_COMMENCE_DATE = 'subscriptionCommenceDate';
    public const PARAMETER_SUBSCRIPTION_RENEWAL_DATE = 'subscriptionRenewalDate';

    /**
     * @var null|EmployeeMembershipService
     */
    protected ?EmployeeMembershipService $employeeMembershipService = null;

    /**
     * @return EmployeeMembershipService
     */
    public function getEmployeeMembershipService(): EmployeeMembershipService
    {
        if (is_null($this->employeeMembershipService)) {
            $this->employeeMembershipService = new EmployeeMembershipService();
        }
        return $this->employeeMembershipService;
    }

    /**
     * @OA\Get(
     *     path="/api/v2/pim/employees/{empNumber}/memberships/{id}",
     *     tags={"PIM/Employee Membership"},
     *     summary="Get an Employee's Membership",
     *     operationId="get-an-employees-membership",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeMembershipModel"
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
        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_ID
        );
        $employeeMembership = $this->getEmployeeMembershipService()
            ->getEmployeeMembershipDao()
            ->getEmployeeMembershipById($empNumber, $id);
        $this->throwRecordNotFoundExceptionIfNotExist($employeeMembership, EmployeeMembership::class);

        return new EndpointResourceResult(
            EmployeeMembershipModel::class,
            $employeeMembership,
            new ParameterBag([CommonParams::PARAMETER_EMP_NUMBER => $empNumber])
        );
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
                CommonParams::PARAMETER_ID,
                new Rule(Rules::POSITIVE)
            ),
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v2/pim/employees/{empNumber}/memberships",
     *     tags={"PIM/Employee Membership"},
     *     summary="List an Employee's Memberships",
     *     operationId="list-an-employees-memberships",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum=EmployeeMembershipSearchFilterParams::ALLOWED_SORT_FIELDS)
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/sortOrder"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *     @OA\Parameter(ref="#/components/parameters/offset"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeDependentModel"
     *             ),
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="empNumber", type="integer")
     *             )
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     */
    public function getAll(): EndpointCollectionResult
    {
        $limit = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_QUERY, 'limit', 50);
        $offset = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_QUERY, 'offset', 0);

        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );

        $experiences = $this->getHedwigeExperiences($this->getAuthUser()->getUserHedwigeToken());

        if (!is_array($experiences)) {
            throw new \RuntimeException('Invalid data format received for experiences.');
        }


        $employeeMemberships = array_map(function ($experience) use ($experiences) {
            $employeeMembership = new EmployeeMembership();
        
            // Données spécifiques à chaque expérience
            $employeeMembership->setTitle($experience['title'] ?? null);
            $employeeMembership->setDescription($experience['description'] ?? null);
            $employeeMembership->setYear($experience['period'] ?? null); // "period" utilisé comme "year"
        
            // Données globales
            $employeeMembership->setProfessionalExperience($experiences['professionalExperience'] ?? null);
            $employeeMembership->setSpecificProfessionalExperience($experiences['specificProfessionalExperience'] ?? null);
        
            return $employeeMembership;
        }, $experiences['skills'] ?? []); // Itère sur "skills"

        $employeeMembershipModels = array_map(
            fn($membership) => $membership instanceof EmployeeMembershipModel 
                ? $membership 
                : new EmployeeMembershipModel($membership),
            $employeeMemberships
        );
        $normalizedResults = array_map(fn($model) => $model->normalize(), $employeeMembershipModels);

        $result = new EndpointCollectionResult(
            EmployeeMembershipModel::class,
            $employeeMembershipModels, // Normalized data
            new ParameterBag([
                'limit' => $limit,
                'offset' => $offset,
                'total' => count($normalizedResults),
            ])
        );

        return $result;
    }


    protected function getHedwigeExperiences(string $token) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/user/skills", [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            $data = json_decode($response->getBody(), true);

            return $data;
        } catch (\Exceptionon $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            ...$this->getSortingAndPaginationParamsRules(EmployeeMembershipSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v2/pim/employees/{empNumber}/memberships",
     *     tags={"PIM/Employee Membership"},
     *     summary="Add a Membership to an Employee",
     *     operationId="add-a-membership-to-an-employee",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", maxLength=100),
     *             @OA\Property(property="description", type="string", maxLength=300),
     *             @OA\Property(property="year", type="integer", format="int32"),
     *             required={"title", "year"}
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeMembershipModel"
     *             ),
     *             @OA\Property(property="empNumber", type="integer")
     *         )
     *     )
     * )

     * @inheritDoc
     */
    public function create(): EndpointResourceResult
    {
        try {
            $empNumber = $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_ATTRIBUTE,
                CommonParams::PARAMETER_EMP_NUMBER
            );

            $title = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'title');

            $year = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'year');

            $description = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'description');

            $skillData = (object) [
                'title' => $title,
                'period' => $year,
                'description' => $description,
            ];
            $skillDataJson = json_encode($skillData);
            $postExperience = $this->postHedwigeExperience($this->getAuthUser()->getUserHedwigeToken(), $skillDataJson);
            $employeeMembership = new EmployeeMembership();
            $employeeMembership->setTitle($skillData->title ?? null);
            $employeeMembership->setYear($skillData->year ?? null);
            $employeeMembership->setDescription($skillData->description ?? null);
            $employeeMembership->setProfessionalExperience($professionalExperience ?? null);
            $employeeMembership->setSpecificProfessionalExperience($specificProfessionalExperience ?? null);

            return new EndpointResourceResult(
                EmployeeMembershipModel::class,
                $employeeMembership,
                new ParameterBag(['status' => 'success'])
            );
        } catch (\Exception $e) {
            return new EndpointResourceResult(
                EmployeeMembershipModel::class,
                ['message' => 'Erreur : ' . $e->getMessage()],
                new ParameterBag([
                    'status' => 'error'
                ])
            );
        }
    }

    protected function postHedwigeExperience(string $token, string $data): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
    
        try {
            // Décoder les données pour vérifier qu'elles sont valides JSON
            $decodedData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data provided: ' . json_last_error_msg());
            }

            $response = $client->request('POST', "{$clientBaseUrl}/user/skill", [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $decodedData,
            ]);
    
            $responseData = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode response JSON: ' . json_last_error_msg());
            }
    
            return $responseData['certificates'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            ...$this->getCommonBodyValidationRules('POST'),
        );
    }

    /**
     * @return ParamRule[]
     */
    private function getCommonBodyValidationRules(string $httpMethod): array
    {
        if ($httpMethod === 'POST') {
            return [
                new ParamRule(
                    'title',
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, 100])
                ),
                new ParamRule(
                    'description',
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, 300])
                ),
                new ParamRule(
                    'year',
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, 100])
                ),
                // $this->getValidationDecorator()->notRequiredParamRule(
                //     new ParamRule(
                //         'year',
                //         new Rule(Rules::POSITIVE), // Doit être un entier positif
                //         new Rule(Rules::LENGTH, [4, 4]) // Longueur de 4 caractères
                //     )
                // ),
            ];
        }
        if ($httpMethod === 'PUT') {
            return [
                $this->getValidationDecorator()->notRequiredParamRule(
                    new ParamRule(
                        'professionalExperience',
                        new Rule(Rules::STRING_TYPE),
                        new Rule(Rules::LENGTH, [null, 100])
                    )
                ),
                $this->getValidationDecorator()->notRequiredParamRule(
                    new ParamRule(
                        'specificProfessionalExperience',
                        new Rule(Rules::STRING_TYPE),
                        new Rule(Rules::LENGTH, [null, 100])
                    )
                ),
            ];
        }
        return [];
        // return [
        //     $this->getValidationDecorator()->notRequiredParamRule(
        //         new ParamRule(
        //             self::PARAMETER_SUBSCRIPTION_PAID_BY,
        //             new Rule(Rules::STRING_TYPE),
        //             new Rule(Rules::IN, [[EmployeeMembership::COMPANY,EmployeeMembership::INDIVIDUAL]]),
        //         ),
        //     ),
        //     $this->getValidationDecorator()->notRequiredParamRule(
        //         new ParamRule(
        //             self::PARAMETER_SUBSCRIPTION_CURRENCY,
        //             new Rule(Rules::CURRENCY),
        //         ),
        //     ),
        //     $this->getValidationDecorator()->notRequiredParamRule(
        //         new ParamRule(
        //             self::PARAMETER_SUBSCRIPTION_COMMENCE_DATE,
        //             new Rule(Rules::API_DATE),
        //         ),
        //     ),
        // ];
    }

    /**
     * @OA\Put(
     *     path="/api/v2/pim/employees/{empNumber}/memberships/{id}",
     *     tags={"PIM/Employee Membership"},
     *     summary="Update an Employee's Membership",
     *     operationId="update-an-employees-membership",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="membershipId", type="integer"),
     *             @OA\Property(property="subscriptionFee", type="number"),
     *             @OA\Property(property="subscriptionPaidBy", type="string"),
     *             @OA\Property(property="currencyTypeId", type="string"),
     *             @OA\Property(property="subscriptionCommenceDate", type="string", format="date"),
     *             @OA\Property(property="subscriptionRenewalDate", type="string", format="date"),
     *             required={"membershipId"}
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeMembershipModel"
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
        try {
            $empNumber = $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_ATTRIBUTE,
                CommonParams::PARAMETER_EMP_NUMBER
            );

            $professionalExperience = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'professionalExperience');

            $specificProfessionalExperience = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_BODY, 'specificProfessionalExperience');

            $skillData = (object) [
                'professionalExperience' => $professionalExperience,
                'specificProfessionalExperience' => $specificProfessionalExperience,
            ];
            $skillDataJson = json_encode($skillData);
            $putExperience = $this->putHedwigeExperience($this->getAuthUser()->getUserHedwigeToken(), $skillDataJson);
            $employeeMembership = new EmployeeMembership();
            $employeeMembership->setTitle($skillData->title ?? null);
            $employeeMembership->setYear($skillData->year ?? null);
            $employeeMembership->setDescription($skillData->description ?? null);
            $employeeMembership->setProfessionalExperience($professionalExperience ?? null);
            $employeeMembership->setSpecificProfessionalExperience($specificProfessionalExperience ?? null);

            // $employeeMembership = new EmployeeMembership();
            // $employeeMembership->setTitle($skillData->title);
            // $employeeMembership->setYear($skillData->year);
            // $employeeMembership->setDescription($skillData->description);

            return new EndpointResourceResult(
                EmployeeMembershipModel::class,
                $employeeMembership,
                new ParameterBag(['message' => 'Update successful', 'response' => $employeeMembership]),
                new ParameterBag(['status' => 'success'])
            );
        } catch (\Exception $e) {
            return new EndpointResourceResult(
                EmployeeMembershipModel::class,
                ['message' => 'Erreur : ' . $e->getMessage()],
                new ParameterBag([
                    'status' => 'error'
                ])
            );
        }
        // $employeeMembership = $this->saveEmployeeMembership();
        // return new EndpointResourceResult(
        //     EmployeeMembershipModel::class,
        //     $employeeMembership,
        //     new ParameterBag([CommonParams::PARAMETER_EMP_NUMBER => $employeeMembership->getEmployee()->getEmpNumber()])
        // );
    }

    protected function putHedwigeExperience(string $token, string $data): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
    
        try {
            // Décoder les données pour vérifier qu'elles sont valides JSON
            $decodedData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data provided: ' . json_last_error_msg());
            }

            $response = $client->request('PUT', "{$clientBaseUrl}/user/professional-experiences", [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $decodedData,
            ]);
    
            $responseData = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode response JSON: ' . json_last_error_msg());
            }
    
            return $responseData['certificates'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            // new ParamRule(
            //     CommonParams::PARAMETER_ID,
            //     new Rule(Rules::POSITIVE)
            // ),
            ...$this->getCommonBodyValidationRules('PUT'),
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/pim/employees/{empNumber}/memberships",
     *     tags={"PIM/Employee Membership"},
     *     summary="Delete an Employee's Memberships",
     *     operationId="delete-an-employees-memberships",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(ref="#/components/requestBodies/DeleteRequestBody"),
     *     @OA\Response(response="200", ref="#/components/responses/DeleteResponse")
     * )
     *
     * @inheritDoc
     */
    public function delete(): EndpointResourceResult
    {
        try {
            $empNumber = $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_ATTRIBUTE,
                CommonParams::PARAMETER_EMP_NUMBER
            );

            $decodedData = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, 'ids');
            foreach ($decodedData as $item) {
                $title = $item['title'] ?? null;
                if (!$title) {
                    throw new \InvalidArgumentException('Title is missing in request data');
                }
            }
            // $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
            // if (!$employee instanceof Employee) {
            //     throw new \Exception('L\'employé n\'a pas pu être trouvé ou n\'est pas valide.');
            // }
            // $this->throwRecordNotFoundExceptionIfNotExist($employee, Employee::class);

            $skillData = (object) [
                'title' => $title,
            ];
            $skillDataJson = json_encode($skillData);

            $deleteResponse = $this->deleteHedwigeExperience($this->getAuthUser()->getUserHedwigeToken(), $skillDataJson);

            if (isset($deleteResponse['response'])) {
                return new EndpointResourceResult(
                    EmployeeMembershipModel::class,
                    ['message' => 'Compétence supprimée avec succès.'],
                    new ParameterBag(['status' => 'success'])
                );
            } elseif ($deleteResponse['status'] === 'error') {
                throw new \Exception('Échec de la suppression : ' . ($deleteResponse['message'] ?? 'Erreur inconnue.'));
            } else {
                throw new \Exception('Réponse inattendue lors de la suppression.');
            }
        } catch (\Exception $e) {
            return new EndpointResourceResult(
                EmployeeMembershipModel::class,
                ['message' => 'Erreur : ' . $e->getMessage()],
                new ParameterBag(['status' => 'error'])
            );
        }
        // return new EndpointResourceResult(
        //     ArrayModel::class,
        //     $ids,
        //     new ParameterBag(
        //         [
        //             CommonParams::PARAMETER_EMP_NUMBER => $empNumber,
        //         ]
        //     )
        // );
    }

    protected function deleteHedwigeExperience(string $token, string $data): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
    
        try {
            $decodedData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data provided: ' . json_last_error_msg());
            }
            $response = $client->request('DELETE', "{$clientBaseUrl}/user/skill", [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $decodedData,
            ]);
    
            $responseData = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode response JSON: ' . json_last_error_msg());
            }
    
            return ['response' => ['message' => 'Suppression effectuée.']];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_EMP_NUMBER,
                new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
            ),
            new ParamRule(
                CommonParams::PARAMETER_IDS,
                new Rule(Rules::ARRAY_TYPE)
            ),
        );
    }

    /**
     * @return EmployeeMembership
     */
    private function saveEmployeeMembership(): EmployeeMembership
    {
        $id = $this->getRequestParams()->getInt(RequestParams::PARAM_TYPE_ATTRIBUTE, CommonParams::PARAMETER_ID);
        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );
        $membershipId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_MEMBERSHIP_ID
        );
        $paidBy = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_SUBSCRIPTION_PAID_BY
        );
        $currency = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_SUBSCRIPTION_CURRENCY
        );

        $fee = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_SUBSCRIPTION_FEE
        );
        $commenceDate = $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_SUBSCRIPTION_COMMENCE_DATE
        );
        $renewalDate = $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_SUBSCRIPTION_RENEWAL_DATE
        );
        if ($id) {
            $employeeMembership = $this->getEmployeeMembershipService()->getEmployeeMembershipDao()->getEmployeeMembershipById(
                $empNumber,
                $id
            );
            $this->throwRecordNotFoundExceptionIfNotExist($employeeMembership, EmployeeMembership::class);
        } else {
            $employeeMembership = new EmployeeMembership();
            $employeeMembership->getDecorator()->setEmployeeByEmpNumber($empNumber);
        }

        $employeeMembership->getDecorator()->setMembershipByMembershipId($membershipId);
        $employeeMembership->setSubscriptionPaidBy($paidBy);
        $employeeMembership->setSubscriptionCurrency($currency);
        $employeeMembership->setSubscriptionFee($fee);
        $employeeMembership->setSubscriptionCommenceDate($commenceDate);
        $employeeMembership->setSubscriptionRenewalDate($renewalDate);

        return $this->getEmployeeMembershipService()
            ->getEmployeeMembershipDao()
            ->saveEmployeeMembership($employeeMembership);
    }
}
