<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class ProfessionalExperienceController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('professional-experience-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();
        error_log('Token récupéré: ' . $token);
        
        $professionalExperiences = $this->getProfessionalExperiences($token);
        error_log('XP pro récupérés: ' . json_encode($professionalExperiences));
        
        $response = new Response(
            json_encode($professionalExperiences),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        error_log('Réponse envoyée: ' . $response->getContent());
        return $response;
    }
    
    private function getProfessionalExperiences(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        error_log('URL de base: ' . $clientBaseUrl);
        try {
            $url = "{$clientBaseUrl}/professional-experience";
            error_log('URL complète: ' . $url);
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
            
            error_log('Statut de la réponse: ' . $response->getStatusCode());
            error_log('Corps de la réponse brute: ' . $response->getBody());
            
            $data = json_decode($response->getBody(), true);
            error_log('Données décodées: ' . json_encode($data));
            
            if (!is_array($data)) {
                error_log('Les données ne sont pas un tableau');
                return [];
            }
            
            // $transformedData = array_map(function($studyLevel) {
            //     return ['name' => $studyLevel];
            // }, $data);
            
            // error_log('Données transformées: ' . json_encode($transformedData));
            // return $transformedData;
            return $data;
            
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des XP pro : ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    public function delete(Request $request, string $id)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        if (!$clientBaseUrl) {
            error_log('HEDWIGE_URL environment variable is not set');
            return [];
        }

        if (!$id) {
            error_log('XP ID is required');
            return [];
        }

        try {
            $url = "{$clientBaseUrl}/professional-experience/{$id}";
            error_log("Calling Hedwige API for deletion: {$url}");
            
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getAuthUser()->getUserHedwigeToken(),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);
            
            return new Response(
                json_encode(['success' => true]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            error_log("Error deleting of: " . $e->getMessage());
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function create(Request $request)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        if (!$clientBaseUrl) {
            error_log('HEDWIGE_URL environment variable is not set');
            return new Response(
                json_encode(['error' => 'Configuration error']),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
        try {
            $data = json_decode($request->getContent(), true);
            error_log('Received data: ' . json_encode($data));
            $url = "{$clientBaseUrl}/professional-experience";
            error_log("Calling Hedwige API for creation: {$url}");
            $body = [
                    'name' => $data['name'] ?? null,
                    'quantity' => $data['quantity'] ?? null,
            ];
            error_log("Request body: " . json_encode($body));
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getAuthUser()->getUserHedwigeToken(),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $body
            ]);
            return new Response(
                $response->getBody(),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            error_log("Error creating course: " . $e->getMessage());
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function update(Request $request, string $id)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        if (!$clientBaseUrl) {
            error_log('HEDWIGE_URL environment variable is not set');
            return new Response(
                json_encode(['error' => 'Configuration error']),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }

        if (!$id) {
            error_log('XP ID is required');
            return new Response(
                json_encode(['error' => 'OF ID is required']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        try {
            $data = json_decode($request->getContent(), true);
            error_log('Received data for update: ' . json_encode($data));

            $body = [
                'id' => $data['id'] ?? null,
                'name' => $data['name'] ?? null,
                'quantity' => $data['quantity'] ?? null,
            ];

            error_log('Request body for update: ' . json_encode($body));

            $url = "{$clientBaseUrl}/professional-experience/{$id}";
            error_log("Calling Hedwige API for update: {$url}");
            
            $response = $client->request('PUT', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getAuthUser()->getUserHedwigeToken(),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $body
            ]);
            
            return new Response(
                $response->getBody(),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            error_log("Error updating of: " . $e->getMessage());
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }
}