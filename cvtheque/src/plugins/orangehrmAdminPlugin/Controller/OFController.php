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
        
        $queryParams = [];

        if (!empty($params['name'])) {
            $queryParams['name'] = $params['name'];
        }
        if (!empty($params['actor'])) {
            $queryParams['actor'] = $params['actor'];
        }

        $queryParams['page'] = !empty($params['page']) ? intval($params['page']) : 0;
        $queryParams['size'] = !empty($params['size']) ? intval($params['size']) : 20;
        
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
            return [];
        }

        if (!$token) {
            return [];
        }

        try {
            $url = "{$clientBaseUrl}/OF";
            
            $queryParams = $params;
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'query' => $queryParams
            ]);
            
            $data = json_decode($response->getBody(), true);
            
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
                    $formattedOF = [
                        'id' => $of['id'],
                        'name' => $of['name'],
                        'contact' => $of['contact'],
                        'actor' => $of['actor'],
                    ];
                    $formattedData['data'][] = $formattedOF;
                }
            }

            return $formattedData;
        } catch (\Exception $e) {
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
            return new Response(
                json_encode(['error' => 'Configuration error']),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }

        try {
            $data = json_decode($request->getContent(), true);

            $url = "{$clientBaseUrl}/OF";
            
            $body = [
                    'name' => $data['name'] ?? null,
                    'contact' => $data['contact'] ?? null,
                    'actor' => $data['actor'] ?? null,
            ];
            
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
            return [];
        }

        if (!$id) {
            return [];
        }

        try {
            $url = "{$clientBaseUrl}/OF/{$id}";
            
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
            return new Response(
                json_encode(['error' => 'Configuration error']),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }

        if (!$id) {
            return new Response(
                json_encode(['error' => 'OF ID is required']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        try {
            $data = json_decode($request->getContent(), true);

            $body = [
                'name' => $data['name'] ?? null,
                'contact' => $data['contact'] ?? null,
                'actor' => $data['actor'] ?? null,
            ];


            $url = "{$clientBaseUrl}/OF/{$id}";
            
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
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }
}