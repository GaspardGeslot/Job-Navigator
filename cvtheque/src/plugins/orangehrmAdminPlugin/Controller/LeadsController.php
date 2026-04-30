<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class LeadsController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_FROM_DATE = 'from';
    public const FILTER_TO_DATE = 'to';
    public const FILTER_MATCHING_STATUS = 'matchingStatuses';
    public const FILTER_ACTORS = 'actors';
    public const FILTER_DEPARTMENT_CODES = 'departmentCodes';
    public const FILTER_JOBS = 'jobs';
    public const FILTER_COURSE_ONLY = 'courseOnly';
    public const FILTER_HIDE_TESTS = 'hideTests';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $component = new Component('view-lead');
            $leadId = $request->attributes->getInt('id');
            $component->addProp(new Prop('lead-id', Prop::TYPE_NUMBER, $leadId));

            $token = $this->getAuthUser()->getUserHedwigeToken();
            $lead = $this->getLead($token, $leadId);
            $actor = $lead['actor'] ?? null;

            $allOptions = $this->getHedwigeOptions($token);
            $component->addProp(new Prop('all-statuses', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $allOptions, array_keys($allOptions))));
            
            $actorOptions = $this->getHedwigeOptions($token, $actor);
            $component->addProp(new Prop('actor-statuses', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $actorOptions, array_keys($actorOptions))));

            $allStudyLevelsOptions = $this->getHedwigeStudyLevels($token);
            $component->addProp(new Prop('all-study-levels', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $allStudyLevelsOptions, array_keys($allStudyLevelsOptions))));

            $actorStudyLevelsOptions = $this->getHedwigeStudyLevels($token, $actor);
            $component->addProp(new Prop('actor-study-levels', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $actorStudyLevelsOptions, array_keys($actorStudyLevelsOptions))));

            $contactLogOptions = $this->getHedwigeContactOptions($this->getAuthUser()->getUserHedwigeToken());
            $component->addProp(new Prop('contact-log-types', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label
                ];
            }, array_keys($contactLogOptions), $contactLogOptions)));

            $reportingColumns = $this->getReportingColumns($token, $actor);

            if (!empty($reportingColumns))
                $component->addProp(new Prop('default-columns', Prop::TYPE_OBJECT, $reportingColumns["defaultColumns"]));
        }
        else {
            $component = new Component('leads-list');
            
            $actorOptions = $this->getHedwigeActorOptions($this->getAuthUser()->getUserHedwigeToken());
            $matchingStatusFilterOptions = $this->getHedwigeMatchingStatusFilterOptions($this->getAuthUser()->getUserHedwigeToken());
            $departmentCodeOptions = $this->getHedwigeDepartmentCodeOptions($this->getAuthUser()->getUserHedwigeToken());	

            $component->addProp(new Prop('actors', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $actorOptions, array_keys($actorOptions))));
            $component->addProp(new Prop('matching-status-filters', Prop::TYPE_ARRAY, array_map(function($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label
                ];
            }, array_keys($matchingStatusFilterOptions), $matchingStatusFilterOptions)));
            // Sort departmentCodeOptions by code before mapping
            $sortedDepartmentCodeOptions = $departmentCodeOptions;
            ksort($sortedDepartmentCodeOptions);
            $component->addProp(new Prop('department-codes', Prop::TYPE_ARRAY, array_map(function($code, $label) {
                return [
                    'id' => $code,
                    'label' => $code . ' - ' . $label
                ];
            }, array_keys($sortedDepartmentCodeOptions), $sortedDepartmentCodeOptions)));
        }
        $this->setComponent($component);
    }

    public function getAll(Request $request): Response
    {
        $from = $request->query->get(self::FILTER_FROM_DATE);
        $to = $request->query->get(self::FILTER_TO_DATE);
        $matchingStatus = $request->query->get(self::FILTER_MATCHING_STATUS);
        $actors = $request->query->get(self::FILTER_ACTORS);
        $departmentCodes = $request->query->get(self::FILTER_DEPARTMENT_CODES);
        $jobs = $request->query->get(self::FILTER_JOBS);
        $courseOnly = $request->query->get(self::FILTER_COURSE_ONLY);
        $hideTests = $request->query->get(self::FILTER_HIDE_TESTS);
        if ($courseOnly !== null) {
            $courseOnly = filter_var($courseOnly, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if ($hideTests !== null) {
            $hideTests = filter_var($hideTests, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        $leads = $this->getLeads($this->getAuthUser()->getUserHedwigeToken(), $from, $to, $matchingStatus, $actors, $jobs, $courseOnly, $hideTests, $departmentCodes);
        return new Response(
            json_encode($leads),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getOne(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $lead = $this->getLead($this->getAuthUser()->getUserHedwigeToken(), $id);
        return new Response(
            json_encode($lead),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function update(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $data = json_decode($request->getContent(), true);
            $this->updateLead($this->getAuthUser()->getUserHedwigeToken(), $id, $data);
            return new Response(
                json_encode(['message' => 'Lead updated successfully']),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }


    public function deliver(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $ofEmail = $request->query->get('ofEmail');
            $this->deliverLead($this->getAuthUser()->getUserHedwigeToken(), $id, $ofEmail);
            return new Response(
                json_encode(['message' => 'Lead delivered successfully']),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }
    
    public function reprocess(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $this->reprocessLead($this->getAuthUser()->getUserHedwigeToken(), $id);
            return new Response(
                json_encode(['message' => 'Lead reprocessed successfully']),
                Response::HTTP_OK,
                    ['Content-Type' => 'application/json']
                );
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => 'Error reprocessing lead'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function addTelephoneContact(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $data = json_decode($request->getContent(), true);
            $this->addHedwigeTelephoneContact($this->getAuthUser()->getUserHedwigeToken(), $id, $data);
            return new Response(
                json_encode(['message' => 'Telephone contact added successfully']),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }


    public function deleteTelephoneContact(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $date = $request->query->get('date');
            $this->deleteHedwigeTelephoneContact($this->getAuthUser()->getUserHedwigeToken(), $id, $date);
            return new Response(
                json_encode(['message' => 'Telephone contact deleted successfully']),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => 'Error deleting telephone contact'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateTelephoneContact(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $data = json_decode($request->getContent(), true);
            $this->updateHedwigeTelephoneContact($this->getAuthUser()->getUserHedwigeToken(), $id, $data);
            return new Response(
                json_encode(['message' => 'Telephone contact updated successfully']),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => 'Error updating telephone contact'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getGlobalOptions(Request $request): Response
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();

        $allOptions = $this->getHedwigeOptions($token);
        $allStudyLevelsOptions = $this->getHedwigeStudyLevels($token);
        $contactLogOptions = $this->getHedwigeContactOptions($token);

        return new Response(
            json_encode([
                'allStatuses' => array_map(function ($label, $index) {
                    return ['id' => $index, 'label' => $label];
                }, $allOptions, array_keys($allOptions)),
                'allStudyLevels' => array_map(function ($label, $index) {
                    return ['id' => $index, 'label' => $label];
                }, $allStudyLevelsOptions, array_keys($allStudyLevelsOptions)),
                'contactLogTypes' => array_map(function ($id, $label) {
                    return ['id' => $id, 'label' => $label];
                }, array_keys($contactLogOptions), $contactLogOptions),
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getLeadOptions(Request $request): Response
    {
        $id = $request->attributes->getInt('id');
        $token = $this->getAuthUser()->getUserHedwigeToken();

        $lead = $this->getLead($token, $id);
        $actor = $lead['actor'] ?? null;

        $actorOptions = $this->getHedwigeOptions($token, $actor);
        $reportingColumns = $this->getReportingColumns($token, $actor);

        return new Response(
            json_encode([
                'actorStatuses' => array_map(function ($label, $index) {
                    return ['id' => $index, 'label' => $label];
                }, $actorOptions, array_keys($actorOptions)),
                'actorStudyLevels' => array_map(function ($label, $index) {
                    return ['id' => $index, 'label' => $label];
                }, $studyLevelsOptions, array_keys($studyLevelsOptions)),
                'defaultColumns' => !empty($reportingColumns) ? ($reportingColumns['defaultColumns'] ?? null) : null,
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getReportingColumns(string $token, ?string $actor = null): array
    {
        try {
            $client = new Client();
            $clientBaseUrl = getenv('HEDWIGE_URL');
            $url = "{$clientBaseUrl}/reporting-columns/default";
            if ($actor !== null && $actor !== '') {
                $url .= '?actor=' . urlencode($actor);
            }
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            return [];
        }
    }

    public function getLeads(string $token, string $from, string $to, ?array $matchingStatus, ?array $actors, ?array $jobs, ?bool $courseOnly, ?bool $hideTests, ?array $departmentCodes): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/lead?";
            if ($from != null && $from !== '')
                $url .= 'from=' . urlencode($from) . '&';
            if ($to != null && $to !== '')
                $url .= 'to=' . urlencode($to) . '&';
            if ($matchingStatus != null && $matchingStatus !== [])
                $url .= 'matchingStatuses=' . urlencode(implode(',', $matchingStatus)) . '&';
            if ($actors != null && $actors !== [])
                $url .= 'actors=' . urlencode(implode(',', $actors)) . '&';
            if ($jobs != null && $jobs !== [])
                $url .= 'jobs=' . urlencode(implode(',', $jobs)) . '&';
            if ($courseOnly !== null) {
                $url .= 'courseOnly=' . ($courseOnly ? 'true' : 'false') . '&';
            }
            if ($hideTests !== null) {
                $url .= 'hideTests=' . ($hideTests ? 'true' : 'false') . '&';
            }
            if ($departmentCodes != null && $departmentCodes !== [])
                $url .= 'departmentCodes=' . urlencode(implode(',', $departmentCodes)) . '&';
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            return [];
        }
    }

    public function getLead(string $token, int $id): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}";
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getHedwigeOptions(string $token, ?string $actor = null): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/client/status";

        $options = [
            'headers' => [
                'Authorization' => $token,
            ],
        ];

        if ($actor !== null && trim($actor) !== '') {
            $options['query'] = [
                'actor' => $actor,
            ];
        }

        $response = $client->request('GET', $url, $options);
        return json_decode($response->getBody(), true);
    }

    public function getHedwigeActorOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/actor/options?active=true";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            return [];
        }
    }

    public function getHedwigeMatchingStatusFilterOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/matching/status-filters";
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getHedwigeDepartmentCodeOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/client/department";
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function updateLead(string $token, int $id, array $data): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/info";
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data)
        ]);
    }


    public function deliverLead(string $token, int $id, ?string $ofEmail = null): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/deliver";
        if ($ofEmail !== null && trim($ofEmail) !== '') {
            $url .= '?ofEmail=' . urlencode($ofEmail);
        }
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);
    }

    public function reprocessLead(string $token, int $id): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $url = "{$clientBaseUrl}/lead/{$id}";
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function addHedwigeTelephoneContact(string $token, int $id, array $data): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/contact-log";
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data)
        ]);
    }

    public function deleteHedwigeTelephoneContact(string $token, int $id, string $date): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/contact-log?date=" . urlencode($date);
        $response = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function updateHedwigeTelephoneContact(string $token, int $id, array $data): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/contact-log";
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data)
        ]);
    }

    public function getHedwigeContactOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/client/contact-logs";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            return [];
        }
    }
}
