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
    public const FILTER_MATCHING_STATUS = 'matchingStatus';
    public const FILTER_ACTORS = 'actors';
    public const FILTER_JOBS = 'jobs';
    public const FILTER_COURSE_ONLY = 'courseOnly';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        if ($request->attributes->has('id')) {
            $component = new Component('view-lead');
            $component->addProp(new Prop('lead-id', Prop::TYPE_NUMBER, $request->attributes->getInt('id')));


            $options = $this->getHedwigeOptions($this->getAuthUser()->getUserHedwigeToken());
            $component->addProp(new Prop('statuses', Prop::TYPE_ARRAY, array_map(function($label, $index) {
                return [
                    'id' => $index,
                    'label' => $label
                ];
            }, $options, array_keys($options))));
        }
        else {
            $component = new Component('leads-list');
            
            $actorOptions = $this->getHedwigeActorOptions($this->getAuthUser()->getUserHedwigeToken());
            $matchingStatusFilterOptions = $this->getHedwigeMatchingStatusFilterOptions($this->getAuthUser()->getUserHedwigeToken());
            
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
        }
        $this->setComponent($component);
    }

    public function getAll(Request $request): Response
    {
        $from = $request->query->get(self::FILTER_FROM_DATE);
        $to = $request->query->get(self::FILTER_TO_DATE);
        $matchingStatus = $request->query->get(self::FILTER_MATCHING_STATUS);
        $actors = $request->query->get(self::FILTER_ACTORS);
        $jobs = $request->query->get(self::FILTER_JOBS);
        $courseOnly = $request->query->get(self::FILTER_COURSE_ONLY);
        if ($courseOnly !== null)
            $courseOnly = filter_var($courseOnly, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $leads = $this->getLeads($this->getAuthUser()->getUserHedwigeToken(), $from, $to, $matchingStatus, $actors, $jobs, $courseOnly);
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
            $this->deliverLead($this->getAuthUser()->getUserHedwigeToken(), $id);
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

    public function getLeads(string $token, string $from, string $to, ?string $matchingStatus, ?array $actors, ?array $jobs, ?bool $courseOnly): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/lead?";
            if ($from != null && $from !== '')
                $url .= 'from=' . urlencode($from) . '&';
            if ($to != null && $to !== '')
                $url .= 'to=' . urlencode($to) . '&';
            if ($matchingStatus != null && $matchingStatus !== '')
                $url .= 'matchingStatus=' . urlencode($matchingStatus) . '&';
            if ($actors != null && $actors !== [])
                $url .= 'actors=' . urlencode(implode(',', $actors)) . '&';
            if ($jobs != null && $jobs !== [])
                $url .= 'jobs=' . urlencode(implode(',', $jobs)) . '&';
            if ($courseOnly !== null) {
                $url .= 'courseOnly=' . ($courseOnly ? 'true' : 'false') . '&';
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

    public function getHedwigeOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/client/status";
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getHedwigeActorOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/actor/options";
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

    public function updateLead(string $token, int $id, array $data): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/info";
        error_log('data: ' . json_encode($data));
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data)
        ]);
    }


    public function deliverLead(string $token, int $id): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/lead/{$id}/deliver";
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
        $url = "{$clientBaseUrl}/lead/{$id}/telephone-contact";
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
        $url = "{$clientBaseUrl}/lead/{$id}/telephone-contact?date=" . urlencode($date);
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
        $url = "{$clientBaseUrl}/lead/{$id}/telephone-contact";
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data)
        ]);
    }
}
