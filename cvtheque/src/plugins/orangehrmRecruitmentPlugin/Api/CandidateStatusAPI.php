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

use GuzzleHttp\Client;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CollectionEndpoint;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\I18N\Traits\Service\I18NHelperTrait;
use OrangeHRM\Recruitment\Service\CandidateService;
use OrangeHRM\Recruitment\Traits\Service\VacancyServiceTrait;

class CandidateStatusAPI extends Endpoint implements CrudEndpoint
{
    use VacancyServiceTrait;
    use I18NHelperTrait;
    use AuthUserTrait;

    public const PARAMETER_CANDIDATE_ID = 'candidateId';
    public const PARAMETER_MATCHING_ID = 'matchingId';
    public const PARAMETER_CANDIDATURE_STATUS_ID = 'candidatureStatusId';

    /**
     * @OA\Get(
     *     path="/api/v2/recruitment/candidates/status",
     *     tags={"Recruitment/Candidates"},
     *     summary="List All Statuses",
     *     operationId="list-all-statuses",
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="label", type="string"),
     *             ),
     *         )
     *     ),
     * )
     * @inheritDoc
     */
    public function getAll(): EndpointResult
    {
        $candidateStatus = array_map(function ($key, $value) {
            return [
                'id' => $key,
                'label' => $this->getI18NHelper()->transBySource(ucwords(strtolower($value))),
            ];
        }, array_keys(CandidateService::STATUS_MAP), CandidateService::STATUS_MAP);
        return new EndpointResourceResult(ArrayModel::class, $candidateStatus);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection();
    }

    /**
     * @OA\Put(
     *     path="/api/v2/recruitment/candidates/{candidateId}/matching/{matchingId}/status/{candidatureStatusId}",
     *     tags={"Recruitment/Candidates"},
     *     summary="Update a Candidate status",
     *     operationId="update-a-candidate-status",
     *     @OA\PathParameter(
     *         name="candidateId",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="matchingId",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="candidatureStatusId",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     * @inheritDoc
     * @throws TransactionException
     */
    public function update(): EndpointResult
    {
        $candidateId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_CANDIDATE_ID
        );

        $matchingId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_MATCHING_ID
        );

        $candidatureStatusId = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_CANDIDATURE_STATUS_ID
        );

        $this->updateHedwigeCandidatureStatus($this->getAuthUser()->getUserHedwigeToken(), $candidateId, $matchingId, $candidatureStatusId);

        return new EndpointCollectionResult(
            ArrayModel::class,
            [],
            new ParameterBag([CommonParams::PARAMETER_TOTAL => 0])
        );
    }

    private function updateHedwigeCandidatureStatus(string $token, int $candidateId, int $matchingId, int $candidatureStatusId) : void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $client->request('PUT', "{$clientBaseUrl}/lead/{$candidateId}/matching/{$matchingId}?statusOrdinal={$candidatureStatusId}", [
                'headers' => [
                    'Authorization' => $token
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
        return new ParamRuleCollection(
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_CANDIDATE_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_MATCHING_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
            $this->getValidationDecorator()->notRequiredParamRule(
                new ParamRule(
                    self::PARAMETER_CANDIDATURE_STATUS_ID,
                    new Rule(Rules::POSITIVE)
                )
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function create(): EndpointResult
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
     * @inheritDoc
     */
    public function delete(): EndpointResult
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
     * Get one resource
     * @return EndpointResult
     * @throws NormalizeException
     * @throws RecordNotFoundException
     * @throws NotImplementedException
     * @throws ForbiddenException
     */
    public function getOne(): EndpointResult
    {
        throw $this->getNotImplementedException();
    }

    /**
     * Validation rules for CollectionEndpoint::getOne
     * @return ParamRuleCollection
     * @throws NotImplementedException
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        throw $this->getNotImplementedException();
    }
}
