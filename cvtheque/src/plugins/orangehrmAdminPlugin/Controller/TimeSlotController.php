<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class TimeSlotController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('time-slot-list');
        $this->setComponent($component);
    }

    public function getAll()
    {
        $timeSlots = $this->getTimeSlots($this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            json_encode($timeSlots),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->addTimeSlot($data['title'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function delete(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->deleteTimeSlot($data['title'], $this->getAuthUser()->getUserHedwigeToken());
        return new Response(
            null,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function getTimeSlots(string $token): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        try {
            $url = "{$clientBaseUrl}/time-slot";
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

    private function addTimeSlot(string $title, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/time-slot";
        try {
            $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
            ],
            'body' => $title
            ]);
        } catch (\Exception $e) {
            error_log('error add time slot: ' . $e->getMessage());
        }
    }

    private function deleteTimeSlot(string $title, string $token)
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');
        $url = "{$clientBaseUrl}/time-slot";
        try {
            $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $title
            ]);
        } catch (\Exception $e) {
            error_log('error delete time slot: ' . $e->getMessage());
        }
    }
} 