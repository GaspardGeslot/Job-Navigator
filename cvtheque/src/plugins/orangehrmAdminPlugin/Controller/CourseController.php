<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;

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

        $queryParams['page'] = !empty($params['page']) ? intval($params['page']) : 0;
        $queryParams['size'] = !empty($params['size']) ? intval($params['size']) : 20;
        
        
        $courses = $this->getCourses($this->getAuthUser()->getUserHedwigeToken(), $queryParams);
        return new Response(
            json_encode($courses),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function search(Request $request): Response
    {
        $value = $request->query->get(
            self::FILTER_VALUE
        );
        $courses = $this->searchCourses($this->getAuthUser()->getUserHedwigeToken(), $value);
        $courses = array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $id . ' - ' . $label
            ];
        }, array_keys($courses), $courses);
        return new Response(
            json_encode($courses),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function searchCourses(string $token, ?string $value): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/course/search?value=" . urlencode($value);
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

    private function getOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/actor/options";
            
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer " . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
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
            return [];
        }
    }

    private function getCourses(string $token, array $params = []): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/course";
            
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
                foreach ($data['content'] as $course) {
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

    public function delete(Request $request, string $id)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        try {
            $url = "{$clientBaseUrl}/course/{$id}";
            
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

    public function create(Request $request)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $data = json_decode($request->getContent(), true);

            $url = "{$clientBaseUrl}/course";
            
            $body = [
                'id' => intval($data['id'] ?? 0),
                'title' => $data['title'] ?? null,
                'code' => $data['code'] ?? null,
                'of' => [
                    'name' => $data['of']['name'] ?? null,
                    'contact' => $data['of']['contact'] ?? null,
                    'actor' => $data['of']['actor'] ?? null
                ],
                'actorCourseTitle' => $data['actorCourseTitle'] ?? null,
                'actorCourseId' => !empty($data['actorCourseId']) ? intval($data['actorCourseId']) : null,
                'thematic' => $data['thematic'] ?? null,
                'trainingCode' => $data['trainingCode'] ?? null,
                'trainingId' => !empty($data['trainingId']) ? intval($data['trainingId']) : null,
                'utmCampaign' => $data['utmCampaign'] ?? null
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

        if (!$id) {
            return new Response(
                json_encode(['error' => 'Course ID is required']),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        try {
            $data = json_decode($request->getContent(), true);

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

            $url = "{$clientBaseUrl}/course/{$id}";
            
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
