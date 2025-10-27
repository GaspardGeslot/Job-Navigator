<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class SourceController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('source-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $sources = $this->getSources($token);
        $response = new Response(
            json_encode($sources),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        return $response;
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addSource($data['name'], $data['price'] ?? null, $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function update(Request $request)
    {
        $title = urldecode($request->attributes->get('title'));
        $data = json_decode($request->getContent(), true);
        $this->updateSource($title, $data['price'] ?? null, $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deleteSource($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getSources(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/source";
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
            
            $data = json_decode($response->getBody(), true);
            if (!is_array($data)) {
                return [];
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des sources: ' . $e->getMessage());
            return [];
        }
    }

    private function addSource(string $name, ?float $price, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $query = http_build_query([
            'source' => $name,
            'price' => $price
        ]);
        $url = "{$clientBaseUrl}/source?{$query}";
        
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            error_log('error add source: ' . $e->getMessage());
        }
    }

    private function updateSource(string $name, ?float $price, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $query = http_build_query([
            'source' => $name,
            'price' => $price
        ]);
        $url = "{$clientBaseUrl}/source?{$query}";
        
        try {
            $client->request('PUT', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            error_log('error update source: ' . $e->getMessage());
        }
    }

    private function deleteSource(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $query = http_build_query(['title' => $name]);
        $url = "{$clientBaseUrl}/source?{$query}";
        
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            error_log('error delete source: ' . $e->getMessage());
        }
    }
}

