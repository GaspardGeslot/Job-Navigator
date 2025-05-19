<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class DrivingLicenseController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('driving-license-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $licenses = $this->getDrivingLicenses($token);
        
        // S'assurer que nous avons un tableau
        if (!is_array($licenses)) {
            $licenses = [];
        }
        
        // Transformer les chaînes en objets avec une propriété title
        $formattedLicenses = array_map(function($license) {
            return ['title' => $license];
        }, $licenses);
        
        // Formater la réponse comme les autres contrôleurs
        $response = new Response(
            json_encode(['data' => $formattedLicenses]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        
        return $response;
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addDrivingLicense($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deleteDrivingLicense($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getDrivingLicenses(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/driving-license";
            error_log("URL de l'API Hedwige: " . $url);
            error_log("Token utilisé: " . $token);
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
            
            $body = $response->getBody()->getContents();
            error_log("Réponse brute de l'API Hedwige: " . $body);
            
            $data = json_decode($body, true);
            error_log("Données décodées: " . print_r($data, true));
            
            if (!is_array($data)) {
                error_log("Les données ne sont pas un tableau");
                return [];
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des permis: ' . $e->getMessage());
            return [];
        }
    }

    private function addDrivingLicense(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/driving-license";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error add driving license: ' . $e->getMessage());
        }
    }

    private function deleteDrivingLicense(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/driving-license";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error delete driving license: ' . $e->getMessage());
        }
    }
} 