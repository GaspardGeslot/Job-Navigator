<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class OFController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('of-list');
        $this->setComponent($component);
    }
    
    public function getAllOFs(Request $request) {
        $params = $request->query->all();
        error_log('$params ' . json_encode($params));
        
        $queryParams = [];

        if (!empty($params['name'])) {
            $queryParams['name'] = $params['name'];
        }
        if (!empty($params['actor'])) {
            $queryParams['actor'] = $params['actor'];
        }

        $queryParams['page'] = !empty($params['page']) ? intval($params['page']) : 0;
        $queryParams['size'] = !empty($params['size']) ? intval($params['size']) : 20;
        
        error_log('Query params before getOFs: ' . json_encode($queryParams));
        
        $ofs = $this->getOFs($this->getAuthUser()->getUserHedwigeToken(), $queryParams);
        return new Response(
            json_encode($ofs),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getOFs(string $token, array $params = []): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        if (!$clientBaseUrl) {
            error_log('HEDWIGE_URL environment variable is not set');
            return [];
        }

        if (!$token) {
            error_log('No authentication token provided');
            return [];
        }

        try {
            $url = "{$clientBaseUrl}/OF";
            error_log("Calling Hedwige API: {$url}");
            error_log("Params received in getofs: " . json_encode($params));
            
            $queryParams = $params;
            
            error_log("Final query params: " . json_encode($queryParams));
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'query' => $queryParams
            ]);
            
            $data = json_decode($response->getBody(), true);
            error_log("Hedwige API response: " . json_encode($data));
            
            $formattedData = [
                'data' => [],
                'meta' => [
                    'total' => $data['totalElements'] ?? 0,
                    'currentPage' => $data['number'] ?? 0,
                    'pageSize' => $data['size'] ?? 0,
                    'totalPages' => $data['totalPages'] ?? 0,
                    'first' => $data['first'] ?? true,
                    'last' => $data['last'] ?? true,
                    'empty' => $data['empty'] ?? true
                ]
            ];

            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $of) {
                    error_log("Processing OF: " . json_encode($of));
                    $formattedOF = [
                        'id' => $of['id'],
                        'name' => $of['name'],
                        'contact' => $of['contact'],
                        'actor' => $of['actor'],
                    ];
                    error_log("Formatted OF: " . json_encode($formattedOF));
                    $formattedData['data'][] = $formattedOF;
                }
            }

            return $formattedData;
        } catch (\Exception $e) {
            error_log("Error calling Hedwige API: " . $e->getMessage());
            return [
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'currentPage' => 0,
                    'pageSize' => 0,
                    'totalPages' => 0,
                    'first' => true,
                    'last' => true,
                    'empty' => true
                ]
            ];
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

            $url = "{$clientBaseUrl}/OF";
            error_log("Calling Hedwige API for creation: {$url}");
            
            $body = [
                    'name' => $data['name'] ?? null,
                    'contact' => $data['contact'] ?? null,
                    'actor' => $data['actor'] ?? null,
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

    public function delete(Request $request, string $id)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        if (!$clientBaseUrl) {
            error_log('HEDWIGE_URL environment variable is not set');
            return [];
        }

        if (!$id) {
            error_log('Course ID is required');
            return [];
        }

        try {
            $url = "{$clientBaseUrl}/OF/{$id}";
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
            error_log('OF ID is required');
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
                'name' => $data['name'] ?? null,
                'contact' => $data['contact'] ?? null,
                'actor' => $data['actor'] ?? null,
            ];

            error_log('Request body for update: ' . json_encode($body));

            $url = "{$clientBaseUrl}/OF/{$id}";
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