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
use OrangeHRM\Admin\Service\CountryService;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Services;

class EmployeeContactDetailsController extends BaseViewEmployeeController
{
    use ServiceContainerTrait;

    public function preRender(Request $request): void
    {
        $empNumber = $request->attributes->get('empNumber');
        if ($empNumber) {
            $component = new Component('employee-contact-details');
            $component->addProp(new Prop('emp-number', Prop::TYPE_NUMBER, $empNumber));

            /** @var CountryService $countryService */
            $countryService = $this->getContainer()->get(Services::COUNTRY_SERVICE);
            
            $options = $this->getCandidatureOptions();
            $component->addProp(new Prop('mobilities', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $options['mobilities'], array_keys($options['mobilities']))));
            
            $this->setComponent($component);

            $this->setPermissionsForEmployee(
                ['contact_details', 'contact_attachment', 'contact_custom_fields'],
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

    /**
     * @inheritDoc
     */
    protected function getDataGroupsForCapabilityCheck(): array
    {
        return ['contact_details'];
    }
}
