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

namespace OrangeHRM\Recruitment\Controller;

use GuzzleHttp\Client;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Traits\Controller\VueComponentPermissionTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Recruitment\Service\CandidateService;
use OrangeHRM\Recruitment\Traits\Service\CandidateServiceTrait;
use OrangeHRM\Recruitment\Service\RecruitmentAttachmentService;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;

class ViewCandidateController extends AbstractVueController
{
    use VueComponentPermissionTrait;
    use CandidateServiceTrait;
    use ConfigServiceTrait;
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')
            || ($request->attributes->has('leadId')
            && $request->attributes->has('matchingId'))) {

            $leadId = $request->attributes->has('leadId') ? $request->attributes->getInt('leadId') : $request->attributes->getInt('id');
            $matchingId = $request->attributes->has('matchingId') ? $request->attributes->getInt('matchingId') : null;

            $queryParams = [];
            foreach ($request->query->all() as $key => $value) {
                $queryParams[$key] = $value;
            }

            $component = new Component('view-candidate-profile');
            $component->addProp(new Prop('updatable', Prop::TYPE_BOOLEAN, false));
            $component->addProp(new Prop('candidate-id', Prop::TYPE_NUMBER, $leadId));
            $component->addProp(new Prop('matching-id', Prop::TYPE_NUMBER, $matchingId));
            $component->addProp(new Prop('filter-params', Prop::TYPE_OBJECT, $queryParams));

            $component->addProp(
                new Prop('max-file-size', Prop::TYPE_NUMBER, $this->getConfigService()->getMaxAttachmentSize())
            );

            $component->addProp(
                new Prop(
                    'allowed-file-types',
                    Prop::TYPE_ARRAY,
                    RecruitmentAttachmentService::ALLOWED_CANDIDATE_ATTACHMENT_FILE_TYPES
                )
            );

            $options = $this->getHedwigeStatusOptions($this->getAuthUser()->getUserHedwigeToken());
            $component->addProp(new Prop('candidature-statuses', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label
                ];
            }, array_keys($options), $options)));

            if ($matchingId)
                $this->visualizeCandidatureHedwige($this->getAuthUser()->getUserHedwigeToken(), $leadId, $matchingId);
        } else {
            $component = new Component('view-candidates-list');

            $options = $this->getHedwigeOptions();

            $component->addProp(new Prop('study-levels', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label
                ];
            }, array_keys($options['studyLevels']), $options['studyLevels'])));
            $component->addProp(new Prop('course-starts', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label
                ];
            }, array_keys($options['courseStarts']), $options['courseStarts'])));
            $component->addProp(new Prop('needs', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $options['needs'], array_keys($options['needs']))));
            $component->addProp(new Prop('professional-experiences', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label
                ];
            }, array_keys($options['professionalExperiences']), $options['professionalExperiences'])));
            $component->addProp(new Prop('sectors', Prop::TYPE_ARRAY, array_map(function($sector, $index) {
                return [
                    'id' => $index,
                    'label' => $sector['title'],
                    'jobs' => $sector['jobs']
                ];
            }, $options['sectors'], array_keys($options['sectors']))));

            if ($request->query->has('statusId')) {
                $statusId = $request->query->getInt('statusId');

                $candidateStatus = array_map(function ($key, $value) {
                    return [
                        'id' => $key,
                        'label' => $value,
                    ];
                }, array_keys(CandidateService::STATUS_MAP), CandidateService::STATUS_MAP);

                $component->addProp(
                    new Prop(
                        'status',
                        Prop::TYPE_OBJECT,
                        $candidateStatus[$statusId - 1],
                    )
                );
            }
            
            $this->setPermissions(['recruitment_candidates']);
        }
        $this->setComponent($component);
    }

    public function getAll(Request $request) {
        $params = $request->query->all();

        $queryParams = [];

        $page = !empty($params['page']) ? intval($params['page']) : 0;
        $size = !empty($params['size']) ? intval($params['size']) : 20;

        $matchingId = null;
        if (!empty($params['matchingId']))
            $matchingId = $params['matchingId'];

        $allLeads = null;
        if (!empty($params['allLeads']))
            $allLeads = $params['allLeads'];

        $otherLeads = null;
        if (!empty($params['otherLeads']))
            $otherLeads = $params['otherLeads'];

        $jobSector = null;
        if (!empty($params['jobSector']))
            $jobSector = $params['jobSector'];
        
        $professionalExperienceFilter = null;
        if (!empty($params['professionalExperienceFilter']))
            $professionalExperienceFilter = $params['professionalExperienceFilter'];
        
        $jobTitleFilter = null;
        if (!empty($params['jobTitleFilter']))
            $jobTitleFilter = $params['jobTitleFilter'];

        $needFilter = null;
        if (!empty($params['needFilter']))
            $needFilter = $params['needFilter'];

        $studyLevelFilter = null;
        if (!empty($params['studyLevelFilter']))
            $studyLevelFilter = $params['studyLevelFilter'];

        $courseStartFilter = null;
        if (!empty($params['courseStartFilter']))
            $courseStartFilter = $params['courseStartFilter'];

        $statusJob = null;
        if (!empty($params['statusJob']))
            $statusJob = $params['statusJob'];

        $sortDirection = !empty($params['sortDirection']) ? $params['sortDirection'] : 'DESC';

        if($otherLeads == 'entreprise')
            $leads = $this->getOtherLeads($this->getAuthUser()->getUserHedwigeToken(), $matchingId, $otherLeads, $page, $size, $sortDirection);
        else
            $leads = $this->getLeads($this->getAuthUser()->getUserHedwigeToken(), $matchingId, $allLeads, $jobTitleFilter, $needFilter, $studyLevelFilter, $courseStartFilter, $professionalExperienceFilter, $statusJob, $page, $size, $sortDirection);

        return new Response(
            json_encode($leads),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getHedwigeOptions(): array
    {
        $client = new Client();
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/options", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }

    public function getHedwigeStatusOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/candidature-status", [
                'headers' => [
                    'Authorization' => $token
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }

    public function visualizeCandidatureHedwige(string $token, int $leadId, int $matchingId): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $client->request('PUT', "{$clientBaseUrl}/lead/{$leadId}/matching/{$matchingId}/visualize", [
                'headers' => [
                    'Authorization' => $token
                ]
            ]);
        } catch (\Exceptionon $e) {
        }
    }

    protected function getOtherLeads(string $token, ?int $matchingId = null, ?string $otherLeads = null, ?int $page = 0, ?int $size = 20, ?string $sortDirection = 'DESC') : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = "{$clientBaseUrl}/company/leads/other/page?";
            if ($matchingId && $matchingId != ''){
                $url .= 'matchingId=' . urlencode($matchingId) . '&';
            }
            $url .= 'page=' . urlencode($page) . '&';
            $url .= 'size=' . urlencode($size) . '&';
            $url .= 'sort=date,' . urlencode($sortDirection);
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            $formattedData = [
                'data' => [],
                'meta' => [
                    'total' => $data['totalElements'] ?? 0,
                    'currentPage' => $data['number'] ?? 0,
                    'pageSize' => $data['size'] ?? 0,
                    'totalPages' => $data['totalPages'] ?? 0,
                    'first' => $data['first'] ?? true,
                    'last' => $data['last'] ?? true,
                    'empty' => $data['empty'] ?? true
                ]
            ];

            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $lead) {
                    $formattedLead = [
                        'id' => $lead['id'],
                        'jobs' => $lead['jobs'],
                        'job' => $lead['job'],
                        'date' => $lead['date'],
                        'firstName' => $lead['firstName'],
                        'lastName' => $lead['lastName'],
                        'email' => $lead['email'],
                        'phoneNumber' => $lead['phoneNumber'],
                        'candidatureStatus' => $lead['candidatureStatus']
                    ];
                    $formattedData['data'][] = $formattedLead;
                }
            }

            return $formattedData;
        } catch (\Exceptionon $e) {
            return null;
        }
    }

    protected function getLeads(string $token, ?int $matchingId = null, ?string $allLeads = null, ?string $jobTitleFilter = '', ?string $needFilter = '', ?string $studyLevelFilter = '', ?string $courseStartFilter = '', ?string $professionalExperienceFilter = '', ?string $statusJob = '', ?int $page = 0, ?int $size = 20, ?string $sortDirection = 'desc') : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = $allLeads ? "{$clientBaseUrl}/client/leads/page?" : "{$clientBaseUrl}/company/leads/page?";
            if ($jobTitleFilter && $jobTitleFilter !== '') {
                $url .= 'job=' . urlencode($jobTitleFilter) . '&';
            }
            if ($needFilter && $needFilter !== '') {
                $url .= 'need=' . urlencode($needFilter) . '&';
            }
            if ($studyLevelFilter && $studyLevelFilter !== '') {
                $url .= 'studyLevel=' . urlencode($studyLevelFilter) . '&';
            }
            if ($courseStartFilter && $courseStartFilter !== '') {
                $url .= 'courseStart=' . urlencode($courseStartFilter) . '&';
            }
            if ($professionalExperienceFilter && $professionalExperienceFilter !== '') {
                $url .= 'professionalExperience=' . urlencode($professionalExperienceFilter) . '&';
            }
            if ($matchingId && $matchingId != ''){
                $url .= 'matchingId=' . urlencode($matchingId) . '&';
            }
            if ($statusJob && $statusJob !== ''){
                $url .= 'status=' . urlencode($statusJob) . '&';
            }
            $url .= 'page=' . urlencode($page) . '&';
            $url .= 'size=' . urlencode($size) . '&';
            $url .= 'sort=date,' . urlencode($sortDirection);
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            $formattedData = [
                'data' => [],
                'meta' => [
                    'total' => $data['totalElements'] ?? 0,
                    'currentPage' => $data['number'] ?? 0,
                    'pageSize' => $data['size'] ?? 0,
                    'totalPages' => $data['totalPages'] ?? 0,
                    'first' => $data['first'] ?? true,
                    'last' => $data['last'] ?? true,
                    'empty' => $data['empty'] ?? true
                ]
            ];

            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $lead) {
                    $formattedLead = [
                        'id' => $lead['id'],
                        'jobs' => $lead['jobs'],
                        'job' => $lead['job'],
                        'date' => $lead['date'],
                        'firstName' => $lead['firstName'],
                        'lastName' => $lead['lastName'],
                        'email' => $lead['email'],
                        'phoneNumber' => $lead['phoneNumber'],
                        'candidatureStatus' => $lead['candidatureStatus']

                    ];
                    $formattedData['data'][] = $formattedLead;
                }
            }

            return $formattedData;
        } catch (\Exceptionon $e) {
            return null;
        }
    }
}