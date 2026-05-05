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
use OrangeHRM\Framework\Routing\UrlGenerator;
use OrangeHRM\Framework\Services;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Admin\Traits\Service\UserServiceTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;

class APIAccessController extends AbstractVueController
{
    use AuthUserTrait;
    use UserServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('api-access');
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $plan = $this->getActorPlan($token);
        $apiAccessLimit = isset($plan['apiAccessLimit']) ? (int) $plan['apiAccessLimit'] : 0;
        $reportingColumns = $this->getDefaultReportingColumns($token);

        $component->addProp(new Prop('api-access-limit', Prop::TYPE_NUMBER, $apiAccessLimit));
        $component->addProp(new Prop('reporting-columns-default', Prop::TYPE_OBJECT, $reportingColumns));

        $this->setComponent($component);
    }

    private function getActorPlan(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/actor/plan";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
            return json_decode($response->getBody(), true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getDefaultReportingColumns(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/reporting-columns/default";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);

            return json_decode($response->getBody(), true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getAll(): Response
    {
        try {
            $apiAccesses = $this->getActorApiAccesses(
                $this->getAuthUser()->getUserHedwigeToken()
            );

            return new Response(
                json_encode($apiAccesses),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(
                json_encode([
                    'error' => json_decode($e->getResponse()->getBody()->getContents())->message ?? null,
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function update(Request $request): Response
    {
        try {
            $id = (int) $request->attributes->get('id');
            $data = json_decode($request->getContent(), true) ?? [];
            $rawIsActive = $data['isActive'] ?? $request->query->get('isActive');
            $isActive = filter_var($rawIsActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $source = array_key_exists('source', $data)
                ? trim((string) $data['source'])
                : null;
            if ($source === '') {
                $source = null;
            }

            if (!is_bool($isActive)) {
                return new Response(
                    json_encode(['error' => 'isActive must be a boolean']),
                    Response::HTTP_BAD_REQUEST,
                    ['Content-Type' => 'application/json']
                );
            }

            $this->updateActorApiAccess(
                $id,
                $isActive,
                $source,
                $this->getAuthUser()->getUserHedwigeToken()
            );

            return new Response(
                null,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(
                json_encode([
                    'error' => json_decode($e->getResponse()->getBody()->getContents())->message ?? null,
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function create(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];
            $title = isset($data['title']) ? trim((string) $data['title']) : '';
            $source = array_key_exists('source', $data)
                ? trim((string) $data['source'])
                : null;
            if ($source === '') {
                $source = null;
            }

            if ($title === '') {
                return new Response(
                    json_encode(['error' => 'title is required']),
                    Response::HTTP_BAD_REQUEST,
                    ['Content-Type' => 'application/json']
                );
            }

            $tokenValue = $this->createActorApiAccess(
                $title,
                $source,
                $this->getAuthUser()->getUserHedwigeToken()
            );

            return new Response(
                json_encode($tokenValue),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(
                json_encode([
                    'error' => json_decode($e->getResponse()->getBody()->getContents())->message ?? null,
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function delete(Request $request): Response
    {
        try {
            $id = (int) $request->attributes->get('id');
            $this->deleteActorApiAccess($id, $this->getAuthUser()->getUserHedwigeToken());

            return new Response(
                null,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (ClientException $e) {
            return new Response(
                json_encode([
                    'error' => json_decode($e->getResponse()->getBody()->getContents())->message ?? null,
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    public function documentation(): Response
    {
        try {
            $pdfResponse = $this->getActorApiDocumentation(
                $this->getAuthUser()->getUserHedwigeToken()
            );

            return new Response(
                $pdfResponse['content'],
                Response::HTTP_OK,
                [
                    'Content-Type' => $pdfResponse['contentType'],
                    'Content-Disposition' => 'inline; filename="api-documentation.pdf"',
                ]
            );
        } catch (ClientException $e) {
            return new Response(
                json_encode([
                    'error' => json_decode($e->getResponse()->getBody()->getContents())->message ?? null,
                ]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }
    }

    private function getActorApiAccesses(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/actor/api";

        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);

        return json_decode($response->getBody(), true) ?? [];
    }

    private function getActorApiDocumentation(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/actor/api/documentation";

        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);

        $contentType = $response->getHeaderLine('Content-Type') ?: 'application/pdf';

        return [
            'content' => (string) $response->getBody(),
            'contentType' => $contentType,
        ];
    }

    private function updateActorApiAccess(int $id, bool $isActive, ?string $source, string $token): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/actor/api/{$id}";

        $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
            'query' => [
                'isActive' => $isActive ? 'true' : 'false',
                'source' => $source ?? '',
            ],
        ]);
    }

    private function deleteActorApiAccess(int $id, string $token): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/actor/api/{$id}";

        $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);
    }

    private function createActorApiAccess(string $title, ?string $source, string $token): string
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/actor/api";

        $payload = [
            'title' => $title,
            'source' => $source,
        ];

        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
            'json' => $payload,
        ]);

        $rawBody = trim((string) $response->getBody());
        if ($rawBody === '') {
            return '';
        }

        $decoded = json_decode($rawBody, true);
        if (is_string($decoded)) {
            return $decoded;
        }

        return $rawBody;
    }
}