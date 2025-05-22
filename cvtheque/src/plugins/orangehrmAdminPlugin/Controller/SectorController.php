<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class SectorController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_JOB = 'job';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('sector-list');
        $this->setComponent($component);
    }

    public function getAll(Request $request)
    {
        $filter = $request->query->get(self::FILTER_JOB);
        $sectors = $this->getSectors($this->getAuthUser()->getUserHedwigeToken(), $filter);
        return new Response(
            json_encode($sectors),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function add(Request $request)
    {
        $sector = json_decode($request->getContent(), true);
        $this->addSector($sector, $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function update(Request $request)
    {
        $id = $request->attributes->get('id');
        $sector = json_decode($request->getContent(), true);
        $this->updateSector($id, $sector, $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $id = $request->attributes->get('id');
        $this->deleteSector($id, $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getSectors(string $token, ?string $filter): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = "{$clientBaseUrl}/sector";
            if ($filter != null && $filter !== '') {
                $url .= '?job=' . urlencode($filter);
            }
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

    private function addSector(array $sector, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/sector";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
            ],
            'body' => json_encode($sector)
            ]);
        } catch (\Exception $e) {
            error_log('error add sector: ' . $e->getMessage());
        }
    }

    private function updateSector(int $id, array $sector, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/sector/{$id}";
        try {
            $client->request('PUT', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($sector)
            ]);
        } catch (\Exception $e) {
            error_log('error update sector: ' . $e->getMessage());
        }
    }

    private function deleteSector(int $id, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/sector/{$id}";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            error_log('error delete sector: ' . $e->getMessage());
        }
    }
}
