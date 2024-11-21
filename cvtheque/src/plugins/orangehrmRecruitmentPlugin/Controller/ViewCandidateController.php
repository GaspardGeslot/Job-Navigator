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
            $component = new Component('view-candidate-profile');
            $component->addProp(new Prop('updatable', Prop::TYPE_BOOLEAN, false));
            $component->addProp(new Prop('candidate-id', Prop::TYPE_NUMBER, $leadId));
            $component->addProp(new Prop('matching-id', Prop::TYPE_NUMBER, $matchingId));

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
}
