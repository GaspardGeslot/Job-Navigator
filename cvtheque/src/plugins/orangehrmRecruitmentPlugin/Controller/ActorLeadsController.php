<?php

namespace OrangeHRM\Recruitment\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ActorLeadsController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_FROM_DATE = 'from';
    public const FILTER_TO_DATE = 'to';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();

        if ($request->attributes->has('id')) {
            $component = new Component('view-lead');
            $component->addProp(new Prop('lead-id', Prop::TYPE_NUMBER, $request->attributes->getInt('id')));

            // Types de prise de contact pour la pop-up commune
            $contactLogOptions = $this->getHedwigeContactOptions($token);
            $component->addProp(new Prop('contact-log-types', Prop::TYPE_ARRAY, array_map(function ($id, $label) {
                return [
                    'id' => $id,
                    'label' => $label,
                ];
            }, array_keys($contactLogOptions), $contactLogOptions)));
        } else {
            $component = new Component('leads-list');
        }

        $reportingColumns = $this->getReportingColumns($token);

        if (!empty($reportingColumns)) {
            $component->addProp(new Prop('default-columns', Prop::TYPE_OBJECT, $reportingColumns["defaultColumns"]));
            $component->addProp(new Prop('custom-columns', Prop::TYPE_ARRAY, $reportingColumns["customColumns"]));
        }

        $this->setComponent($component);
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

    public function getReportingColumnsDefault(Request $request): Response
    {
        $token = $this->getAuthUser()->getUserHedwigeToken();
        $actor = $request->query->get('actor');

        $columns = $this->getReportingColumns($token, $actor);

        return new Response(
            json_encode($columns),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
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

    public function getAll(Request $request): Response
    {
        $from = $request->query->get(self::FILTER_FROM_DATE);
        $to = $request->query->get(self::FILTER_TO_DATE);
        $leads = $this->getLeads($this->getAuthUser()->getUserHedwigeToken(), $from, $to);
        return new Response(
            json_encode($leads),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getLeads(string $token, string $from, string $to): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/actor/leads?";
            if ($from != null && $from !== '')
                $url .= 'from=' . urlencode($from) . '&';
            if ($to != null && $to !== '')
                $url .= 'to=' . urlencode($to) . '&';
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