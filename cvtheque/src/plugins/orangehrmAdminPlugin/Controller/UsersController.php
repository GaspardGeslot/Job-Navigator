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


class UsersController extends AbstractVueController
{
    use AuthUserTrait;
    use UserServiceTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('users-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        try {
        $token = $this->getAuthUser()->getUserHedwigeToken();
            $users = $this->getUsers($token);
            return new Response(
                json_encode($users),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }

    public function add(Request $request)
    {
        try {
            $theme = $request->attributes->get('theme');
            $data = json_decode($request->getContent(), true);
            $email = $data['email'];
            $password = $data['password'];
            $role = $data['role'];
            $this->addUser($email, $password, $role, $this->getAuthUser()->getUserHedwigeToken());
            $credentials = new UserCredential($email, $password, $role === 'ACTOR' ? 'HiringManager' : 'Interviewer');
            $exists = $this->getUserService()->checkExistsUser($credentials, $theme);
            if (!$exists)
                $this->getUserService()->createCredentials($credentials, $theme);
            return new Response(null, Response::HTTP_OK);
        } catch (\ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request)
    {
        try {
            $theme = $request->attributes->get('theme');
            $id = $request->attributes->get('id');
            $data = json_decode($request->getContent(), true);
            $this->updateUser($id, $data['isAdmin'] === true ? 'ACTOR' : 'AGENT', $this->getAuthUser()->getUserHedwigeToken());
            $credentials = new UserCredential($data['email'], null, null);
            $user = $this->getUserService()->getUserByCredentialsAndTheme($credentials, $theme);
            if ($user) {
                $user->getDecorator()->setUserRoleById($data['isAdmin'] === true ? 6 : 5);
                $this->getUserService()->saveSystemUser($user);
            }
            return new Response(
                null,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(Request $request)
    {
        try {
            $theme = $request->attributes->get('theme');
            $data = json_decode($request->getContent(), true);
            $this->deleteUser($data['id'], $this->getAuthUser()->getUserHedwigeToken());
            $credentials = new UserCredential($data['email'], null, $data['isAdmin'] === true ? 'HiringManager' : 'Interviewer');
            $user = $this->getUserService()->getUserByCredentialsAndTheme($credentials, $theme);
            if ($user)
                $this->getUserService()->deleteSystemUser($user->getId());
            return new Response(null, Response::HTTP_OK);
        } catch (\ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        }
    }

    private function getUsers(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/user/actor";
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);
        return json_decode($response->getBody(), true) ?? [];
    }

    private function addUser(string $email, string $password, string $role, string $token): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        /** @var UrlGenerator $urlGenerator */
        $urlGenerator = $this->getContainer()->get(Services::URL_GENERATOR);
        $loginUrl = $urlGenerator->generate('subdomain_auth_login', [], UrlGenerator::ABSOLUTE_URL);

        $url = "{$clientBaseUrl}/user/other?";
        $url .= 'role=' . urlencode($role) . '&';
        $url .= 'urlPrefix=' . urlencode($loginUrl);

        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'email' => $email,
                'password' => $password,
            ]),
        ]);
    }

    private function updateUser(int $id, string $role, string $token): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/user/{$id}/agent?role=" . urlencode($role);
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);
    }

    private function deleteUser(int $id, string $token): void
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
