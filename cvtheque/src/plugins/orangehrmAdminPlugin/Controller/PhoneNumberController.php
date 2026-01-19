<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class PhoneNumberController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('phone-number-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $phoneNumbers = $this->getPhoneNumbers($token);
        $response = new Response(
            json_encode($phoneNumbers),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        return $response;
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addPhoneNumber($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deletePhoneNumber($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getPhoneNumbers(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = "{$clientBaseUrl}/phone";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if (!is_array($data)) {
                return [];
            }
            
            $transformedData = array_map(function($phone) {
                return ['name' => $phone];
            }, $data);
            
            return $transformedData;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    private function addPhoneNumber(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/phone";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error add phone number: ' . $e->getMessage());
        }
    }

    private function deletePhoneNumber(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/phone";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error delete phone number: ' . $e->getMessage());
        }
    }
} 