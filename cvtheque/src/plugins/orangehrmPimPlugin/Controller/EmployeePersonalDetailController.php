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

namespace OrangeHRM\Pim\Controller;

use GuzzleHttp\Client;
use OrangeHRM\Admin\Service\NationalityService;
use OrangeHRM\Core\Traits\Service\ConfigServiceTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Recruitment\Service\RecruitmentAttachmentService;

class EmployeePersonalDetailController extends BaseViewEmployeeController
{
    use AuthUserTrait;
    use ConfigServiceTrait;

    /**
     * @var NationalityService|null
     */
    protected ?NationalityService $nationalityService = null;

    /**
     * @return NationalityService
     */
    protected function getNationalityService(): NationalityService
    {
        if (!$this->nationalityService instanceof NationalityService) {
            $this->nationalityService = new NationalityService();
        }
        return $this->nationalityService;
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $empNumber = $request->attributes->get('empNumber');
        if ($empNumber) {
            $component = new Component('employee-personal-details');
            $component->addProp(new Prop('emp-number', Prop::TYPE_NUMBER, $empNumber));

            $showDeprecatedFields = $this->getConfigService()->showPimDeprecatedFields();
            $showSsn = $this->getConfigService()->showPimSSN();
            $showSin = $this->getConfigService()->showPimSIN();
            $component->addProp(new Prop('show-deprecated-fields', Prop::TYPE_BOOLEAN, $showDeprecatedFields));
            $component->addProp(new Prop('show-ssn-field', Prop::TYPE_BOOLEAN, $showSsn));
            $component->addProp(new Prop('show-sin-field', Prop::TYPE_BOOLEAN, $showSin));

            $nationalities = $this->getNationalityService()->getNationalityArray();
            $component->addProp(new Prop('nationalities', Prop::TYPE_ARRAY, $nationalities));
            
            if ($this->getAuthUser()->getIsCandidate()) {
                $options = $this->getCandidatureOptions();
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
                $component->addProp(new Prop('driving-licenses', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                    return [
                        'id' => $id,
                        'label' => $label
                    ];
                }, array_keys($options['drivingLicenses']), $options['drivingLicenses'])));
                $component->addProp(new Prop('needs', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                    return [
                        'id' => $index,
                        'label' => $label
                    ];
                }, $options['needs'], array_keys($options['needs']))));
            } else {
                $options = $this->getCompanyOptions();
                $component->addProp(new Prop('workforces', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                    return [
                        'id' => $index,
                        'label' => $label
                    ];
                }, $options['workforces'], array_keys($options['workforces']))));
                
                $component->addProp(new Prop('naf-codes', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                    return [
                        'id' => $index,
                        'label' => $label
                    ];
                }, $options['nafCodes'], array_keys($options['nafCodes']))));
            }

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

            $this->setComponent($component);

            $this->setPermissionsForEmployee(
                [
                    'personal_information',
                    'personal_attachment',
                    'personal_custom_fields',
                    'personal_sensitive_information'
                ],
                $empNumber
            );
        } else {
            $this->handleBadRequest();
        }
    }

    public function getCandidatureOptions(): array
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

    public function getCompanyOptions(): array
    {
        $client = new Client();
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/options/company", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }

    /**
     * @inheritDoc
     */
    protected function getDataGroupsForCapabilityCheck(): array
    {
        return ['personal_information'];
    }
}
