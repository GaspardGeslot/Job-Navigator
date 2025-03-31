<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class CourseStartController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('course-start-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $courseStarts = $this->getCourseStarts($this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            json_encode($courseStarts),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addCourseStart($data['title'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getCourseStarts(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = "{$clientBaseUrl}/course-start";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ],
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function addCourseStart(string $title, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/course-start";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
            ],
            'body' => $title
            ]);
        } catch (\Exception $e) {
            error_log('error: ' . $e->getMessage());
        }
    }
}
