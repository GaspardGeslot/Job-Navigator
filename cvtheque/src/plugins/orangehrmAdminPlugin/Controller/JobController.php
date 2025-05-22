<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class JobController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_TITLE = 'title';
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('job-list');
        $this->setComponent($component);
    }

    public function getJobs(Request $request) {
        $params = $request->query->all();
        
        $queryParams = [];

        if (!empty($params['title'])) {
            $queryParams['title'] = $params['title'];
        }

        $queryParams['page'] = !empty($params['page']) ? intval($params['page']) : 0;
        $queryParams['size'] = !empty($params['size']) ? intval($params['size']) : 20;
        $jobs = $this->getJobsWithFilters($this->getAuthUser()->getUserHedwigeToken(), $queryParams);
        return new Response(
            json_encode($jobs),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getJobsWithFilters(string $token, array $params = []): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/job";
            
            $queryParams = [];
            
            if (!empty($params['title'])) {
                $queryParams['title'] = $params['title'];
            }
            
            $queryParams['page'] = $params['page'];
            $queryParams['size'] = $params['size'];
            
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
                foreach ($data['content'] as $job) {
                    $formattedCourse = [
                        'id' => $job['id'],
                        'title' => $job['title'],
                        'domain' => $job['domain'],
                        'typeFormTitle' => $job['typeFormTitle'],
                        'otherTitle' => $job['otherTitle'],
                        'inOlecio' => $job['inOlecio'],
                    ];
                    $formattedData['data'][] = $formattedCourse;
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

    public function search(Request $request): Response
    {
        $title = $request->query->get(
            self::FILTER_TITLE
        );
        $jobs = $this->searchJobs($this->getAuthUser()->getUserHedwigeToken(), $title);
        $jobs = array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($jobs), $jobs);
        return new Response(
            json_encode($jobs),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function searchJobs(string $token, ?string $title): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/job/search?title=" . urlencode($title);
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);    
        } catch (\Exception $e) {
            return null;
        }
    }

    public function create(Request $request)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $data = json_decode($request->getContent(), true);

            $url = "{$clientBaseUrl}/job";
            
            $body = [
                'title' => $data['title'] ?? null,
                'domain' => $data['domain'] ?? null,
                'inOlecio' => $data['inOlecio'] ?? false,
                'typeFormTitle' => $data['typeFormTitle'] ?? null,
                'otherTitle' => $data['otherTitle'] ?? null,
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

        try {
            $data = json_decode($request->getContent(), true);

            $body = [
                'title' => $data['title'] ?? null,
                'domain' => $data['domain'] ?? null,
                'inOlecio' => $data['inOlecio'] ?? false,
                'typeFormTitle' => $data['typeFormTitle'] ?? null,
                'otherTitle' => $data['otherTitle'] ?? null,
            ];

            $url = "{$clientBaseUrl}/job/{$id}";
            
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

    public function delete(Request $request, string $id)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/job/{$id}";
            
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
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }

}
