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

    public function search(Request $request): Response
    {
        $title = $request->query->get(
            self::FILTER_TITLE
        );
        $jobs = $this->getJobs($this->getAuthUser()->getUserHedwigeToken(), $title);
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

    private function getJobs(string $token, ?string $title): array
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

}
