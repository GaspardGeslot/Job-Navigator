<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class TrainingMethodController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('training-method-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        error_log('Début de getAll()');
        $token = $this->getAuthUser()->getUserHedwigeToken();
        error_log('Token récupéré: ' . $token);
        
        $trainingMethods = $this->getTrainingMethods($token);
        error_log('Méthodes de formation récupérées: ' . json_encode($trainingMethods));
        
        $response = new Response(
            json_encode($trainingMethods),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        error_log('Réponse envoyée: ' . $response->getContent());
        return $response;
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addTrainingMethod($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deleteTrainingMethod($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getTrainingMethods(string $token): array
    {
        error_log('Début de getTrainingMethods()');
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        error_log('URL de base: ' . $clientBaseUrl);
        
        try {
            $url = "{$clientBaseUrl}/training-method";
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
            
            $transformedData = array_map(function($trainingMethod) {
                return ['name' => $trainingMethod];
            }, $data);
            
            error_log('Données transformées: ' . json_encode($transformedData));
            return $transformedData;
            
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des méthodes de formation: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    private function addTrainingMethod(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/training-method";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error add training method: ' . $e->getMessage());
        }
    }

    private function deleteTrainingMethod(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/training-method";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error delete training method: ' . $e->getMessage());
        }
    }
} 