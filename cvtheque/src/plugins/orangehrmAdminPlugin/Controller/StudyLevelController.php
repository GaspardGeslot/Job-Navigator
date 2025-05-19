<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class StudyLevelController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('study-level-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        error_log('Début de getAll()');
        $token = $this->getAuthUser()->getUserHedwigeToken();
        error_log('Token récupéré: ' . $token);
        
        $studyLevels = $this->getStudyLevels($token);
        error_log('Niveaux d\'études récupérés: ' . json_encode($studyLevels));
        
        $response = new Response(
            json_encode($studyLevels),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        error_log('Réponse envoyée: ' . $response->getContent());
        return $response;
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addStudyLevel($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deleteStudyLevel($data['name'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getStudyLevels(string $token): array
    {
        error_log('Début de getStudyLevels()');
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        error_log('URL de base: ' . $clientBaseUrl);
        
        try {
            $url = "{$clientBaseUrl}/study-level";
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
            
            $transformedData = array_map(function($studyLevel) {
                return ['name' => $studyLevel];
            }, $data);
            
            error_log('Données transformées: ' . json_encode($transformedData));
            return $transformedData;
            
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des niveaux d\'études: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    private function addStudyLevel(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/study-level";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error add study level: ' . $e->getMessage());
        }
    }

    private function deleteStudyLevel(string $name, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/study-level";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'body' => $name
            ]);
        } catch (\Exception $e) {
            error_log('error delete study level: ' . $e->getMessage());
        }
    }
} 