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

class ActorController extends AbstractVueController
{
    use AuthUserTrait;
    use UserServiceTrait;
    use ThemeServiceTrait;
    
    public const FILTER_NAME = 'name';
    public const FILTER_JOB = 'job';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('actor-list');

        $options = $this->getHedwigeOptions($this->getAuthUser()->getUserHedwigeToken());

        $component->addProp(new Prop('study-levels', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options['studyLevels']), $options['studyLevels'])));
        $component->addProp(new Prop('needs', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['needs'], array_keys($options['needs']))));
        $component->addProp(new Prop('countries', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['countries'], array_keys($options['countries']))));
        $component->addProp(new Prop('fundings', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['fundings'], array_keys($options['fundings']))));
        $component->addProp(new Prop('status', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['status'], array_keys($options['status']))));
        $component->addProp(new Prop('training-methods', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['trainingMethods'], array_keys($options['trainingMethods']))));
        $component->addProp(new Prop('sources', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['sources'], array_keys($options['sources']))));
        $component->addProp(new Prop('time-slots', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['timeSlots'], array_keys($options['timeSlots']))));

        $themes = array_filter(
            $this->getThemeService()->getThemes(),
            function($theme) { return $theme->getClientId() === 1; }
        );
        $component->addProp(new Prop('themes', Prop::TYPE_ARRAY, array_map(function($theme, $index) {
            return [
                'id' => $index,
                'label' => $theme->getName()
            ];
        }, $themes, array_keys($themes))));

        $this->setComponent($component);
    }
    
    public function getAll(Request $request): Response
    {
        $nameFilter = $request->query->get(
            self::FILTER_NAME
        );
        $jobFilter = $request->query->get(
            self::FILTER_JOB
        );
        $actors = $this->getActors($this->getAuthUser()->getUserHedwigeToken(), $nameFilter, $jobFilter);
        return new Response(
            json_encode($actors),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function create(Request $request): Response
    {
        try {
            $actor = json_decode($request->getContent(), true);
            $this->createActor($this->getAuthUser()->getUserHedwigeToken(), $actor);
            return new Response(json_encode(['message' => 'Actor created successfully']), Response::HTTP_OK);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => 'Error creating actor'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $actor = json_decode($request->getContent(), true);
            $this->updateActor($this->getAuthUser()->getUserHedwigeToken(), $id, $actor);
            return new Response(json_encode(['message' => 'Actor updated successfully']), Response::HTTP_OK);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true, 
                'message' => 'Error updating actor'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $this->deleteActor($this->getAuthUser()->getUserHedwigeToken(), $id);
        return new Response(json_encode(['message' => 'Actor deleted successfully']), Response::HTTP_OK);
    }

    public function addAdministrator(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $administrator = json_decode($request->getContent(), true);
            $this->addHedwigeAdministrator($this->getAuthUser()->getUserHedwigeToken(), $id, $administrator);
            $credentials = new UserCredential($administrator['email'], $administrator['password'], 'HiringManager');
            $exists = $this->getUserService()->checkExistsUser($credentials, $administrator['theme']);
            if (!$exists)
                $this->getUserService()->createCredentials($credentials, $administrator['theme']);
            return new Response(null, Response::HTTP_OK);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteAdministrator(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $administrator = json_decode($request->getContent(), true);
            $this->deleteHedwigeAdministrator($this->getAuthUser()->getUserHedwigeToken(), $id);
            $credentials = new UserCredential($administrator['email'], null, 'HiringManager');
            $admin = $this->getUserService()->getUserByCredentialsAndTheme($credentials, $administrator['theme']);
            if ($admin)
                $this->getUserService()->deleteSystemUser($admin->getId());
            return new Response(null, Response::HTTP_OK);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }

    private function getActors(string $token, ?string $nameFilter, ?string $jobFilter): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/actor?";
            if ($nameFilter != null && $nameFilter !== '') {
                $url .= 'name=' . urlencode($nameFilter) . '&';
            }
            if ($jobFilter != null && $jobFilter !== '') {
                $url .= 'job=' . urlencode($jobFilter) . '&';
            }
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

    private function getHedwigeOptions(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/matching/options";
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

    private function createActor(string $token, array $actor): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $data = json_encode($actor);

        $url = "{$clientBaseUrl}/actor";
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $data
        ]);
    }

    private function updateActor(string $token, int $id, array $actor): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $data = json_encode($actor);

        $url = "{$clientBaseUrl}/actor/{$id}";
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $data
        ]);
    }

    private function deleteActor(string $token, int $id): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/actor/{$id}";
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error deleting actor');
        }
    }

    private function addHedwigeAdministrator(string $token, int $id, array $administrator): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $data = json_encode($administrator);
        
        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        $loginUrl = $urlGenerator->generate('subdomain_auth_login', [], UrlGenerator::ABSOLUTE_URL);

        $url = "{$clientBaseUrl}/user/other?";
        $url .= 'actor=' . urlencode($id) . '&';
        $url .= 'role=ACTOR' . '&';
        $url .= 'urlPrefix=' . urlencode($loginUrl);

        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $data
        ]);
    }

    private function deleteHedwigeAdministrator(string $token, int $id): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        
        $url = "{$clientBaseUrl}/user/{$id}";
        $response = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => $token,
            ]
        ]);
    }
}
