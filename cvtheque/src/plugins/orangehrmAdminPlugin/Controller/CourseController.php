<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class CourseController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('course-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $actorOptions = $this->getOptions($this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            json_encode($actorOptions),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getAllCourses(Request $request) {
        $params = $request->query->all();
        error_log('$params ' . json_encode($params));
        
        $queryParams = [];
        
        if (!empty($params['title'])) {
            $queryParams['title'] = $params['title'];
        }
        if (!empty($params['of'])) {
            $queryParams['of'] = $params['of'];
        }
        if (!empty($params['actor'])) {
            $queryParams['actor'] = $params['actor'];
            // if ($params['actor'] == 'Aucun') {
            //     $queryParams['actor'] = null;
            // } else {
            //     $queryParams['actor'] = $params['actor'];
            // }
        }
        
        error_log('Query params before getCourses: ' . json_encode($queryParams));
        
        $courses = $this->getCourses($this->getAuthUser()->getUserHedwigeToken(), $queryParams);
        return new Response(
            json_encode($courses),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getOptions(string $token): array
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
            $url = "{$clientBaseUrl}/actor/options";
            error_log("Calling Hedwige API: {$url}");
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
                // 'query' => [
                //     'maxAmountPerDay' => 0
                // ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            error_log("Hedwige API response: " . json_encode($data));
            
            $options = [];
            if (is_array($data)) {
                foreach ($data as $name) {
                    $options[] = [
                        'id' => $name,
                        'label' => $name
                    ];
                }
            }
            return $options;
        } catch (\Exception $e) {
            error_log("Error calling Hedwige API: " . $e->getMessage());
            return [];
        }
    }

    private function getCourses(string $token, array $params = []): array
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
            $url = "{$clientBaseUrl}/course";
            error_log("Calling Hedwige API: {$url}");
            error_log("Params received in getCourses: " . json_encode($params));
            
            $queryParams = [];
            
            if (!empty($params['title'])) {
                $queryParams['title'] = $params['title'];
            }
            if (!empty($params['of'])) {
                $queryParams['of'] = $params['of'];
            }
            if (!empty($params['actor'])) {
                $queryParams['actor'] = $params['actor'];
            }
            
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
                ]
            ];

            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $course) {
                    error_log("Processing course: " . json_encode($course));
                    $formattedCourse = [
                        'id' => $course['id'],
                        'name' => $course['title'],
                        'of' => isset($course['of']) ? ($course['of']['name'] ?? '') : '',
                        'code' => $course['code'],
                        'actorCourseTitle' => $course['actorCourseTitle'] ?? '',
                        'actorCourseId' => $course['actorCourseId'] ?? null,
                        'trainingCode' => $course['trainingCode'] ?? '',
                        'trainingId' => $course['trainingId'] ?? null,
                        'utmCampaign' => $course['utmCampaign'] ?? '',
                        'thematic' => $course['thematic'] ?? ''
                    ];
                    error_log("Formatted course: " . json_encode($formattedCourse));
                    $formattedData['data'][] = $formattedCourse;
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
                ]
            ];
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
            $url = "{$clientBaseUrl}/course/{$id}";
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
            error_log("Error deleting course: " . $e->getMessage());
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
            error_log('$data["id"]: ' . $data['id']);
            error_log('intval($data["id"] ?? 0): ' . intval($data['id'] ?? 0));

            $url = "{$clientBaseUrl}/course";
            error_log("Calling Hedwige API for creation: {$url}");
            
            $body = [
                'id' => intval($data['id'] ?? 0),
                'title' => $data['title'] ?? null,
                'code' => $data['code'] ?? null,
                'of' => [
                    'name' => $data['of']['name'] ?? null
                ],
                'actorCourseTitle' => $data['actorCourseTitle'] ?? null,
                'actorCourseId' => !empty($data['actorCourseId']) ? intval($data['actorCourseId']) : null,
                'thematic' => $data['thematic'] ?? null,
                'trainingCode' => $data['trainingCode'] ?? null,
                'trainingId' => !empty($data['trainingId']) ? intval($data['trainingId']) : null,
                'utmCampaign' => $data['utmCampaign'] ?? null
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
            error_log('Course ID is required');
            return new Response(
                json_encode(['error' => 'Course ID is required']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        try {
            $data = json_decode($request->getContent(), true);
            error_log('Received data for update: ' . json_encode($data));

            $body = [
                'title' => $data['title'] ?? null,
                'code' => $data['code'] ?? null,
                'of' => !empty($data['of']['name']) ? [
                    'name' => $data['of']['name']
                ] : null,
                'actorCourseTitle' => $data['actorCourseTitle'] ?? null,
                'actorCourseId' => !empty($data['actorCourseId']) ? intval($data['actorCourseId']) : null,
                'thematic' => $data['thematic'] ?? null,
                'trainingCode' => $data['trainingCode'] ?? null,
                'trainingId' => !empty($data['trainingId']) ? intval($data['trainingId']) : null,
                'utmCampaign' => $data['utmCampaign'] ?? null
            ];

            error_log('Request body for update: ' . json_encode($body));

            $url = "{$clientBaseUrl}/course/{$id}";
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
            error_log("Error updating course: " . $e->getMessage());
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }
}