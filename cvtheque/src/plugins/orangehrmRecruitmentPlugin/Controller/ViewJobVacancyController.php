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
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class ViewJobVacancyController extends AbstractVueController
{
    use AuthUserTrait;

    public function preRender(Request $request): void
    {
        $component = new Component('view-job-vacancy');

        $options = $this->getMatchings($this->getAuthUser()->getUserHedwigeToken());

        $matchings = array_map(function($item) {
            return [
                'id' => $item['id'],
                'label' => $item['title'] ?? ''
            ];
        }, $options);
        $component->addProp(new Prop('matchings', Prop::TYPE_ARRAY, $matchings));
        
        if ($request->attributes->has('id')) {
            $idToFind = $request->attributes->getInt('id');
            $matchingItem = array_filter($matchings, function ($item) use ($idToFind) {
                return isset($item['id']) && $item['id'] === $idToFind;
            });
            $matchingItem = reset($matchingItem) ?: null;
            if ($matchingItem !== null)
                $component->addProp(new Prop('matching-selected', Prop::TYPE_OBJECT, $matchingItem));
        }

        $component->addProp(new Prop('has-name', Prop::TYPE_BOOLEAN, $this->checkHasName($this->getAuthUser()->getUserHedwigeToken())));
        
        $options = $this->getHedwigeStatusOptions($this->getAuthUser()->getUserHedwigeToken());
        $component->addProp(new Prop('candidature-statuses', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options), $options)));

        $this->setComponent($component);
    }

    protected function getMatchings(string $token) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/matching/company", [
                'headers' => [
                    'Authorization' => $token
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
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

    public function checkHasName(string $token): bool
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/company/hasName", [
                'headers' => [
                    'Authorization' => $token
                ]
            ]);

            return filter_var($response->getBody()->getContents(), FILTER_VALIDATE_BOOLEAN);
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }
}
