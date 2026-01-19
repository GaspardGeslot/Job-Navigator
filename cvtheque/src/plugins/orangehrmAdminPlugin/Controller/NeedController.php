<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class NeedController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('need-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $needs = $this->getNeeds($token);
        $response = new Response(
            json_encode($needs),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        return $response;
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addNeed($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deleteNeed($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getNeeds(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = "{$clientBaseUrl}/need";
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
            return [];
        }
    }

    private function addNeed(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/need";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error add need: ' . $e->getMessage());
        }
    }

    private function deleteNeed(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/need";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error delete need: ' . $e->getMessage());
        }
    }
} 