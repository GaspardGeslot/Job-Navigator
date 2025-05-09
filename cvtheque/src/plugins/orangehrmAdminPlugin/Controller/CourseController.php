<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class CourseController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_VALUE = 'value';

    public function search(Request $request): Response
    {
        $value = $request->query->get(
            self::FILTER_VALUE
        );
        $courses = $this->getCourses($this->getAuthUser()->getUserHedwigeToken(), $value);
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

    private function getCourses(string $token, ?string $value): array
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

}
