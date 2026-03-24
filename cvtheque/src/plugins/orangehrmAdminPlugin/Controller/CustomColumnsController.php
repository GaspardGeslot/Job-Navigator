<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Admin\Traits\Service\UserServiceTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;

class CustomColumnsController extends AbstractVueController
{
    use AuthUserTrait;
    use UserServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('custom-columns-list');

        $typeOptions = $this->getHedwigeTypeOptions($this->getAuthUser()->getUserHedwigeToken());
        $component->addProp(new Prop('type-options', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($typeOptions), $typeOptions)));

        $this->setComponent($component);
    }

    public function getAll()
    {
        try {
            $customColumns = $this->getCustomColumns($this->getAuthUser()->getUserHedwigeToken());
            return new Response(
                json_encode($customColumns),
                    Response::HTTP_OK,
                    ['Content-Type' => 'application/json']
                );
        } catch (\ClientException $e) {
            return new Response(
                json_encode(['error' => json_decode($e->getResponse()->getBody()->getContents())->message]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function add(Request $request)
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->addCustomColumn($data, $this->getAuthUser()->getUserHedwigeToken());
            return new Response(
                null,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\ClientException $e) {
            return new Response(
                json_encode(['error' => json_decode($e->getResponse()->getBody()->getContents())->message]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function delete(Request $request)
    {
        try {
            $id = $request->attributes->get('id');
            $this->deleteCustomColumn($id, $this->getAuthUser()->getUserHedwigeToken());
            return new Response(
                null,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\ClientException $e) {
            return new Response(
                json_encode(['error' => json_decode($e->getResponse()->getBody()->getContents())->message]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function update(Request $request)
    {
        try {
            $id = (int) $request->attributes->get('id');
            $data = json_decode($request->getContent(), true) ?? [];
            $hasFilter = array_key_exists('hasFilter', $data) ? (bool) $data['hasFilter'] : null;

            $this->updateCustomColumnHasFilter($id, $hasFilter, $this->getAuthUser()->getUserHedwigeToken());

            return new Response(
                null,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\ClientException $e) {
            return new Response(
                json_encode(['error' => json_decode($e->getResponse()->getBody()->getContents())->message]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    private function getHedwigeTypeOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/client/reporting-custom-columns";
            $response = $client->request('GET', $url, [
                'headers' => [
                        'Authorization' => $token,
                    ],
                ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getCustomColumns(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/reporting-columns";
        $response = $client->request('GET', $url, [
            'headers' => [
                    'Authorization' => $token,
                ],
            ]);
        return json_decode($response->getBody(), true);
    }

    private function addCustomColumn(array $data, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/reporting-columns";
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
        ]);
    }

    private function deleteCustomColumn(int $id, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/reporting-columns/{$id}";
        $response = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);
    }

    private function updateCustomColumnHasFilter(int $id, ?bool $hasFilter, string $token): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $query = [];
        if ($hasFilter !== null) {
            $query['hasFilter'] = $hasFilter ? 'true' : 'false';
        }

        $url = "{$clientBaseUrl}/reporting-columns/{$id}";

        $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
            'query' => $query,
        ]);
    }
}
