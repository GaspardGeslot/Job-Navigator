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

namespace OrangeHRM\Recruitment\Api;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Exception\BadRequestException;
use OrangeHRM\Core\Api\V2\Exception\RecordNotFoundException;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use OrangeHRM\Core\Traits\Service\DateTimeHelperTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Entity\Candidate;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\CandidateHistory;
use OrangeHRM\Entity\CandidateVacancy;
use OrangeHRM\Entity\Vacancy;
use OrangeHRM\Entity\WorkflowStateMachine;
use OrangeHRM\ORM\Exception\TransactionException;
use OrangeHRM\Recruitment\Api\Model\CandidateDetailedModel;
use OrangeHRM\Recruitment\Api\Model\CompanyDetailModel;
use OrangeHRM\Recruitment\Api\Model\CandidateListModel;
use OrangeHRM\Recruitment\Api\Model\CandidateModel;
use OrangeHRM\Pim\Api\Model\EmployeePersonalDetailModel;
use OrangeHRM\Recruitment\Dto\CandidateSearchFilterParams;
use OrangeHRM\Recruitment\Service\CandidateService;
use OrangeHRM\Recruitment\Traits\Service\CandidateServiceTrait;
use OrangeHRM\Recruitment\Traits\Service\RecruitmentAttachmentServiceTrait;
use OrangeHRM\Recruitment\Traits\Service\VacancyServiceTrait;

class CompanyAPI extends Endpoint implements CrudEndpoint
{
    use CandidateServiceTrait;
    use VacancyServiceTrait;
    use RecruitmentAttachmentServiceTrait;
    use EntityManagerHelperTrait;
    use DateTimeHelperTrait;
    use AuthUserTrait;
    use UserRoleManagerTrait;

    public const FILTER_MATCHING_ID = 'matchingId';
    public const FILTER_JOB_TITLE_ID = 'jobTitleId';

    public const MODEL_DEFAULT = 'default';
    public const MODEL_CANDIDATE_LIST = 'list';
    public const MODEL_CANDIDATE_DETAILED = 'detailed';

    public const MODEL_MAP = [
        self::MODEL_DEFAULT => CandidateModel::class,
        self::MODEL_CANDIDATE_LIST => CandidateListModel::class,
        self::MODEL_CANDIDATE_DETAILED => CandidateDetailedModel::class,
    ];

    public const PARAMETER_RULE_NAME_MAX_LENGTH = 30;
    public const PARAMETER_RULE_KEYWORDS_MAX_LENGTH = 250;
    public const PARAMETER_RULE_COMMENT_MAX_LENGTH = 250;
    public const PARAMETER_THEME = 'theme';

    /**
     * @OA\Get(
     *     path="/api/v2/recruitment/candidates",
     *     tags={"Recruitment/Candidates"},
     *     summary="List All Candidates",
     *     operationId="list-all-candidates",
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum=CandidateSearchFilterParams::ALLOWED_SORT_FIELDS)
     *     ),
     *     @OA\Parameter(
     *         name="candidateId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
    *       @OA\Parameter(
    *         name="allLeads",
    *         in="query",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
     *     @OA\Parameter(
     *         name="jobTitleId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vacancyId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hiringManagerId",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={
     *             "APPLICATION INITIATED",
     *             "SHORTLISTED",
     *             "REJECTED",
     *             "INTERVIEW SCHEDULED",
     *             "INTERVIEW PASSED",
     *             "INTERVIEW FAILED",
     *             "JOB OFFERED",
     *             "OFFER DECLINED",
     *             "HIRED"
     *         })
     *     ),
     *     @OA\Parameter(
     *         name="fromDate",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="toDate",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="methodOfApplication",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="candidateName",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={
     *                 OrangeHRM\Recruitment\Api\CandidateAPI::MODEL_DEFAULT,
     *                 OrangeHRM\Recruitment\Api\CandidateAPI::MODEL_CANDIDATE_LIST,
     *                 OrangeHRM\Recruitment\Api\CandidateAPI::MODEL_CANDIDATE_DETAILED
     *             }
     *         )
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
     *                 type="array",
     *                 @OA\Items(oneOf={
     *                     @OA\Schema(ref="#/components/schemas/Recruitment-CandidateModel"),
     *                     @OA\Schema(ref="#/components/schemas/Recruitment-CandidateListModel"),
     *                     @OA\Schema(ref="#/components/schemas/Recruitment-CandidateDetailedModel")
     *                 })
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     * @inheritDoc
     * @throws BadRequestException
     */
    public function getAll(): EndpointResult
    {
        $candidateSearchFilterParamHolder = new CandidateSearchFilterParams();

        $candidateId = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_CANDIDATE_ID
        );

        if (!is_null($candidateId)) {
            $candidateSearchFilterParamHolder->setCandidateIds([$candidateId]);
        } else {
            $accessibleCandidateIds = $this->getUserRoleManager()->getAccessibleEntityIds(Candidate::class);
            $candidateSearchFilterParamHolder->setCandidateIds($accessibleCandidateIds);
        }

        $this->setSortingAndPaginationParams($candidateSearchFilterParamHolder);
        $this->validateDateFilterParams();
        $candidateSearchFilterParamHolder->setJobTitleId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_JOB_TITLE_ID
            )
        );
        $candidateSearchFilterParamHolder->setCandidateId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_CANDIDATE_ID
            )
        );
        $candidateSearchFilterParamHolder->setVacancyId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_VACANCY_ID
            )
        );
        $candidateSearchFilterParamHolder->setHiringManagerId(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_HIRING_MANAGER_ID
            )
        );
        $candidateSearchFilterParamHolder->setStatus($this->getStatusFilterParam());
        $candidateSearchFilterParamHolder->setKeywords(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_KEYWORDS
            )
        );
        $candidateSearchFilterParamHolder->setFromDate(
            $this->getFromDateFilterParam()
        );
        $candidateSearchFilterParamHolder->setToDate(
            $this->getToDateFilterParam()
        );
        $candidateSearchFilterParamHolder->setMethodOfApplication(
            $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_METHOD_OF_APPLICATION
            )
        );
        $candidateSearchFilterParamHolder->setCandidateName(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_QUERY,
                self::FILTER_CANDIDATE_NAME
            )
        );
        /*$candidates = $this->getCandidateService()->getCandidateDao()->getCandidatesList(
            $candidateSearchFilterParamHolder
        );*/

        $matchingId = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_MATCHING_ID
        );

        $allLeads = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_ALL_LEADS
        );

        $jobSector = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_JOB_SECTOR
        );
        
        $professionalExperienceFilter = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_PROFESSIONAL_EXPERIENCE
        );
        
        $jobTitleFilter = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_JOB_TITLE
        );
        $needFilter = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_NEED
        );
        $studyLevelFilter = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_STUDY_LEVEL
        );
        $courseStartFilter = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_COURSE_START
        );

        $statusJob = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_STATUS_JOB
        );

        // error_log($statusJob);

        $leads = $this->getLeads($this->getAuthUser()->getUserHedwigeToken(), $matchingId, $allLeads, $jobTitleFilter, $needFilter, $studyLevelFilter, $courseStartFilter, $professionalExperienceFilter, $statusJob);
        $candidates = array();
        foreach ($leads as $lead) {
            $candidate = new Candidate();
            $candidate->setLeadInfo($lead);
            array_push($candidates, $candidate);
        }

        $count = count($leads); //$this->getCandidateService()->getCandidateDao()->getCandidatesCount($candidateSearchFilterParamHolder);

        return new EndpointCollectionResult(
            CandidateDetailedModel::class,
            $candidates,
            new ParameterBag([CommonParams::PARAMETER_TOTAL => $count])
        );
    }

    protected function getLeads(string $token, ?int $matchingId = null, ?string $allLeads = null, ?string $jobTitleFilter = '', ?string $needFilter = '', ?string $studyLevelFilter = '', ?string $courseStartFilter = '', ?string $professionalExperienceFilter = '', ?string $statusJob = '') : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = $allLeads ? "{$clientBaseUrl}/client/leads?" : "{$clientBaseUrl}/company/leads?";
            if ($jobTitleFilter !== '') {
                $url .= 'job=' . urlencode($jobTitleFilter) . '&';
            }
            if ($needFilter !== '') {
                $url .= 'need=' . urlencode($needFilter) . '&';
            }
            if ($studyLevelFilter !== '') {
                $url .= 'studyLevel=' . urlencode($studyLevelFilter) . '&';
            }
            if ($courseStartFilter !== '') {
                $url .= 'courseStart=' . urlencode($courseStartFilter) . '&';
            }
            if ($professionalExperienceFilter !== '') {
                $url .= 'professionalExperience=' . urlencode($professionalExperienceFilter) . '&';
            }
            if ($matchingId && $matchingId != ''){
                $url .= 'matchingId=' . urlencode($matchingId) . '&';
            }
            if ($statusJob != ''){
                $url .= 'status=' . urlencode($statusJob) . '&';
            }
                
            $response = $client->request('GET', $url, [
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
     * @return string|null
     */
    private function getStatusFilterParam(): ?string
    {
        $state = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_STATUS
        );
        return $state ? CandidateService::STATUS_MAP[$state] : null;
    }

    /**
     * @return DateTime|null
     */
    private function getFromDateFilterParam(): ?DateTime
    {
        return $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_FROM_DATE
        );
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    private function validateDateFilterParams()
    {
        $fromDate = $this->getFromDateFilterParam();
        $toDate = $this->getToDateFilterParam();

        if (($fromDate && $toDate) && $fromDate >= $toDate) {
            throw $this->getBadRequestException('From Date Should Be Earlier Than To Date');
        }
    }

    /**
     * @return DateTime|null
     */
    private function getToDateFilterParam(): ?DateTime
    {
        return $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_TO_DATE
        );
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        $model = $this->getRequestParams()->getString(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_MODEL,
            self::MODEL_DEFAULT,
        );
        return self::MODEL_MAP[$model];
    }

    /**
     * @return ParamRule
     */
    protected function getModelClassParamRule(): ParamRule
    {
        return $this->getValidationDecorator()->notRequiredParamRule(
            new ParamRule(
                self::FILTER_MODEL,
                new Rule(Rules::IN, [array_keys(self::MODEL_MAP)])
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_CANDIDATE_ID,
                    new Rule(Rules::POSITIVE),
                    new Rule(Rules::IN_ACCESSIBLE_ENTITY_ID, [Candidate::class])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_MATCHING_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_ALL_LEADS,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_JOB_SECTOR,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_PROFESSIONAL_EXPERIENCE,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_JOB_TITLE,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_NEED,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_STUDY_LEVEL,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_COURSE_START,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_STATUS_JOB,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_JOB_TITLE_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_VACANCY_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_HIRING_MANAGER_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_STATUS,
                    new Rule(Rules::IN, [array_keys(CandidateService::STATUS_MAP)])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_KEYWORDS,
                    new Rule(Rules::STRING_TYPE)
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_FROM_DATE,
                    new Rule(Rules::API_DATE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_TO_DATE,
                    new Rule(Rules::API_DATE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_METHOD_OF_APPLICATION,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_CANDIDATE_NAME,
                    new Rule(Rules::STRING_TYPE)
                ),
                true
            ),
            $this->getModelClassParamRule(),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_THEME,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            ...$this->getSortingAndPaginationParamsRules(CandidateSearchFilterParams::ALLOWED_SORT_FIELDS)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v2/recruitment/candidates",
     *     tags={"Recruitment/Candidates"},
     *     summary="Create a Candidate",
     *     operationId="create-a-candidate",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="firstName", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_NAME_MAX_LENGTH),
     *             @OA\Property(property="middleName", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_NAME_MAX_LENGTH),
     *             @OA\Property(property="lastName", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_NAME_MAX_LENGTH),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="contactNumber", type="string"),
     *             @OA\Property(property="vacancyId", type="integer"),
     *             @OA\Property(property="keywords", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_KEYWORDS_MAX_LENGTH),
     *             @OA\Property(property="comment", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_COMMENT_MAX_LENGTH),
     *             @OA\Property(property="dateOfApplication", type="string", format="date"),
     *             @OA\Property(property="consentToKeepData", type="boolean"),
     *             required={"firstName", "lastName", "email"}
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Recruitment-CandidateModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     * @inheritDoc
     * @throws TransactionException
     */
    public function create(): EndpointResult
    {
        $this->beginTransaction();
        try {
            $candidate = new Candidate();
            $this->setCandidate($candidate);
            $candidate = $this->getCandidateService()->getCandidateDao()->saveCandidate($candidate);
            $lastInsertedCandidateId = $candidate->getId();

            $candidateHistory = new CandidateHistory();
            $this->setCommonCandidateHistoryAttributes(
                $candidateHistory,
                $lastInsertedCandidateId,
                CandidateService::RECRUITMENT_CANDIDATE_ACTION_ADD
            );
            $this->getCandidateService()->getCandidateDao()->saveCandidateHistory($candidateHistory);

            $vacancyId = $this->getRequestParams()->getIntOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_VACANCY_ID
            );

            if (!is_null($vacancyId)) {
                $this->attachVacancy($lastInsertedCandidateId, $vacancyId);
            }

            $this->commitTransaction();
            $candidate = $this->getCandidateService()->getCandidateDao()->getCandidateById($lastInsertedCandidateId);
            return new EndpointResourceResult($this->getModelClass(), $candidate);
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }
    }

    /**
     * @param Candidate $candidate
     */
    private function setCandidate(Candidate $candidate): void
    {
        $candidate->setFirstName(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_FIRST_NAME
            )
        );
        $candidate->setMiddleName(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_MIDDLE_NAME
            ),
        );
        $candidate->setLastName(
            $this->getRequestParams()->getString(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_LAST_NAME
            )
        );
        $candidate->setEmail(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_EMAIL
            )
        );
        $candidate->setContactNumber(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_CONTACT_NUMBER
            )
        );
        $candidate->setKeywords(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_KEYWORDS
            )
        );
        $candidate->setComment(
            $this->getRequestParams()->getStringOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_COMMENT
            )
        );
        $applicationDate = $this->getRequestParams()->getDateTimeOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_DATE_OF_APPLICATION,
        );
        $candidate->setDateOfApplication(
            $applicationDate instanceof DateTime ?
                $applicationDate :
                $this->getDateTimeHelper()->getNow()
        );
        $candidate->setModeOfApplication(Candidate::MODE_OF_APPLICATION_MANUAL);
        $candidate->setConsentToKeepData(
            $this->getRequestParams()->getBooleanOrNull(
                RequestParams::PARAM_TYPE_BODY,
                self::PARAMETER_CONSENT_TO_KEEP_DATA,
                false
            )
        );
        $candidate->getDecorator()->setAddedPersonById(
            !is_null($candidate->getAddedPerson()) ? $candidate->getAddedPerson()->getEmpNumber() :
                $this->getAuthUser()->getEmpNumber()
        );
    }

    /**
     * @param CandidateVacancy $candidateVacancy
     * @param int $candidateId
     * @param string $status
     */
    private function setCandidateVacancy(CandidateVacancy $candidateVacancy, int $candidateId, string $status): void
    {
        $vacancyId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_VACANCY_ID
        );
        $candidateVacancy->getDecorator()->setCandidateById($candidateId);
        $candidateVacancy->getDecorator()->setVacancyById($vacancyId);
        $candidateVacancy->setStatus($status);
        $candidateVacancy->setAppliedDate($this->getDateTimeHelper()->getNow());
    }

    /**
     * @param CandidateHistory $candidateHistory
     * @param int $candidateId
     * @param int $action
     */
    private function setCommonCandidateHistoryAttributes(
        CandidateHistory $candidateHistory,
        int $candidateId,
        int $action
    ): void {
        $candidateHistory->getDecorator()->setCandidateById($candidateId);
        $candidateHistory->setAction($action);
        $candidateHistory->getDecorator()->setPerformedBy($this->getAuthUser()->getEmpNumber());
        $candidateHistory->setPerformedDate($this->getDateTimeHelper()->getNow());
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            $this->getModelClassParamRule(),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_THEME,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            ...$this->getCommonBodyValidationRules()
        );
    }

    /**
     * @return array
     */
    private function getCommonBodyValidationRules(): array
    {
        return [
            new ParamRule(
                self::PARAMETER_FIRST_NAME,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_NAME_MAX_LENGTH])
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MIDDLE_NAME,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_NAME_MAX_LENGTH])
                ),
                true
            ),
            new ParamRule(
                self::PARAMETER_LAST_NAME,
                new Rule(Rules::STRING_TYPE),
                new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_NAME_MAX_LENGTH])
            ),
            new ParamRule(
                self::PARAMETER_EMAIL,
                new Rule(Rules::EMAIL)
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_CONTACT_NUMBER,
                    new Rule(Rules::PHONE)
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_VACANCY_ID,
                    new Rule(Rules::ENTITY_ID_EXISTS, [Vacancy::class])
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_KEYWORDS,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_KEYWORDS_MAX_LENGTH])
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_COMMENT,
                    new Rule(Rules::STRING_TYPE),
                    new Rule(Rules::LENGTH, [null, self::PARAMETER_RULE_COMMENT_MAX_LENGTH])
                ),
                true
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_DATE_OF_APPLICATION,
                    new Rule(Rules::API_DATE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_CONSENT_TO_KEEP_DATA,
                    new Rule(Rules::BOOL_TYPE)
                )
            ),
        ];
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/recruitment/candidates",
     *     tags={"Recruitment/Candidates"},
     *     summary="Delete Candidates",
     *     operationId="delete-candidates",
     *     @OA\RequestBody(ref="#/components/requestBodies/DeleteRequestBody"),
     *     @OA\Response(response="200", ref="#/components/responses/DeleteResponse"),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     * @inheritDoc
     */
    public function delete(): EndpointResult
    {
        $ids = $this->getCandidateService()->getCandidateDao()->getExistingCandidateIds(
            $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, CommonParams::PARAMETER_IDS)
        );
        $this->throwRecordNotFoundExceptionIfEmptyIds($ids);
        $this->getCandidateService()->getCandidateDao()->deleteCandidates($ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_IDS,
                new Rule(Rules::ARRAY_TYPE)
            ),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v2/recruitment/companies/{id}",
     *     tags={"Recruitment/Company"},
     *     summary="Get a Company",
     *     operationId="get-a-company",
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
     *                 @OA\Schema(ref="#/components/schemas/Recruitment-CandidateModel")
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_ID
        );

        $matchingId = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_QUERY,
            self::FILTER_MATCHING_ID
        );

        $companyDto = $this->getCompany($this->getAuthUser()->getUserHedwigeToken(), $id, $matchingId);
        $company = new Employee();
        $company->setCompanyDetails($companyDto);
        return new EndpointResourceResult(CompanyDetailModel::class, $company);
    }

    protected function getCompany(string $token, int $companyId, ?int $matchingId) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = $matchingId ? "{$clientBaseUrl}/company/{$companyId}/matching/{$matchingId}" : "{$clientBaseUrl}/company/{$companyId}";
            $response = $client->request('GET', $url, [
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
                CommonParams::PARAMETER_ID,
                new Rule(Rules::POSITIVE)
                //new Rule(Rules::IN_ACCESSIBLE_ENTITY_ID, [Candidate::class])
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::FILTER_MATCHING_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            )
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v2/recruitment/candidates/{id}",
     *     tags={"Recruitment/Candidates"},
     *     summary="Update a Candidate",
     *     operationId="update-a-candidate",
     *     @OA\PathParameter(
     *         name="id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="firstName", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_NAME_MAX_LENGTH),
     *             @OA\Property(property="middleName", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_NAME_MAX_LENGTH),
     *             @OA\Property(property="lastName", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_NAME_MAX_LENGTH),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="contactNumber", type="string"),
     *             @OA\Property(property="vacancyId", type="integer"),
     *             @OA\Property(property="keywords", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_KEYWORDS_MAX_LENGTH),
     *             @OA\Property(property="comment", type="string", maxLength=OrangeHRM\Recruitment\Api\CandidateAPI::PARAMETER_RULE_COMMENT_MAX_LENGTH),
     *             @OA\Property(property="dateOfApplication", type="string", format="date"),
     *             @OA\Property(property="consentToKeepData", type="boolean"),
     *             @OA\Parameter(
     *                 name="model",
     *                 in="query",
     *                 required=false,
     *                 @OA\Schema(
     *                     type="string",
     *                     enum={
     *                         OrangeHRM\Recruitment\Api\CandidateAPI::MODEL_DEFAULT,
     *                         OrangeHRM\Recruitment\Api\CandidateAPI::MODEL_CANDIDATE_LIST,
     *                         OrangeHRM\Recruitment\Api\CandidateAPI::MODEL_CANDIDATE_DETAILED
     *                     }
     *                 )
     *             ),
     *             required={"firstName", "lastName", "email"}
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Recruitment-CandidateModel"
     *             ),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     * @inheritDoc
     * @throws TransactionException
     */
    public function update(): EndpointResult
    {
        $this->beginTransaction();
        try {
            $candidateId = $this->getRequestParams()->getInt(
                RequestParams::PARAM_TYPE_ATTRIBUTE,
                CommonParams::PARAMETER_ID
            );

            $candidate = $this->getCandidateService()->getCandidateDao()->getCandidateById($candidateId);
            $this->throwRecordNotFoundExceptionIfNotExist($candidate, Candidate::class);
            $this->setCandidate($candidate);
            $this->getCandidateService()->getCandidateDao()->saveCandidate($candidate);

            $candidateVacancy = $this->getCandidateService()
                ->getCandidateDao()
                ->getCandidateVacancyByCandidateId($candidateId);
            $this->performCandidateVacancyUpdateCriteria($candidateId, $candidateVacancy);

            $this->commitTransaction();
            return new EndpointResourceResult($this->getModelClass(), $candidate);
        } catch (RecordNotFoundException $e) {
            $this->rollBackTransaction();
            throw $e;
        } catch (Exception $e) {
            $this->rollBackTransaction();
            throw new TransactionException($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_ID,
                new Rule(Rules::POSITIVE),
                new Rule(Rules::IN_ACCESSIBLE_ENTITY_ID, [Candidate::class])
            ),
            $this->getModelClassParamRule(),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_THEME,
                    new Rule(Rules::STRING_TYPE)
                )
            ),
            ...$this->getCommonBodyValidationRules(),
        );
    }

    /**
     * @param int $candidateId
     * @param CandidateVacancy|null $candidateVacancy
     */
    private function performCandidateVacancyUpdateCriteria(int $candidateId, ?CandidateVacancy $candidateVacancy)
    {
        $vacancyId = $this->getRequestParams()->getIntOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_VACANCY_ID
        );
        /**
         * vacancyId is not null
         */
        if (!is_null($vacancyId)) {
            /**
             * already existing candidate vacancy available
             */
            if (!is_null($candidateVacancy)) {
                /**
                 * provided vacancyId is not equal to existing candidateVacancy's vacancyId
                 * in that case, existing candidateVacancy record get deleted and new candidateVacancy initiated
                 */
                if ($vacancyId !== $candidateVacancy->getVacancy()->getId()) {
                    $this->removeVacancy($candidateVacancy);
                } else {
                    return;
                }
            }
            $this->attachVacancy($candidateId, $vacancyId);
        } /**
         * already existing candidateVacancy available but null vacancyId given
         * In this case, the existing candidateVacancy record get deleted and
         * now the candidate has not assigned to a vacancy
         */
        elseif (!is_null($candidateVacancy)) {
            $this->removeVacancy($candidateVacancy);
        }
        //else vacancyId is null and no existing vacancy available - Do nothing
    }

    /**
     * @param int $candidateId
     * @param int $vacancyId
     */
    private function attachVacancy(int $candidateId, int $vacancyId): void
    {
        $candidateVacancy = new CandidateVacancy();
        $this->setCandidateVacancy(
            $candidateVacancy,
            $candidateId,
            CandidateService::STATUS_MAP[WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_ATTACH_VACANCY]
        );
        $this->getCandidateService()
            ->getCandidateDao()
            ->saveCandidateVacancy($candidateVacancy);
        $candidateHistory = new CandidateHistory();
        $this->setCommonCandidateHistoryAttributes(
            $candidateHistory,
            $candidateId,
            WorkflowStateMachine::RECRUITMENT_APPLICATION_ACTION_ATTACH_VACANCY
        );
        $candidateHistory->getDecorator()->setVacancyById($vacancyId);
        $candidateHistory->setCandidateVacancyName($candidateVacancy->getVacancy()->getName());
        $this->getCandidateService()->getCandidateDao()->saveCandidateHistory($candidateHistory);
    }

    /**
     * @param CandidateVacancy $candidateVacancy
     */
    private function removeVacancy(CandidateVacancy $candidateVacancy)
    {
        $candidateHistoryRecords = $this->getCandidateService()
            ->getCandidateDao()
            ->getCandidateHistoryByCandidateIdAndVacancyId(
                $candidateVacancy->getCandidate()->getId(),
                $candidateVacancy->getVacancy()->getId()
            );
        foreach ($candidateHistoryRecords as $candidateHistoryRecord) {
            $candidateHistoryRecord->setVacancy(null);
            $this->getCandidateService()->getCandidateDao()->saveCandidateHistory($candidateHistoryRecord);
        }
        $this->getCandidateService()->getCandidateDao()->deleteCandidateVacancy(
            $candidateVacancy->getCandidate()->getId()
        );
        $candidateHistory = new CandidateHistory();
        $this->setCommonCandidateHistoryAttributes(
            $candidateHistory,
            $candidateVacancy->getCandidate()->getId(),
            CandidateService::RECRUITMENT_CANDIDATE_VACANCY_REMOVED
        );
        $candidateHistory->setCandidateVacancyName($candidateVacancy->getVacancy()->getName());
        $this->getCandidateService()->getCandidateDao()->saveCandidateHistory($candidateHistory);
    }
}
