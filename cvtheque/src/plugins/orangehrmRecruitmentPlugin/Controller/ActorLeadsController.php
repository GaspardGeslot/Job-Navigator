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
        $customFilters = $request->query->all('customFilter');
        $leads = $this->getLeads($this->getAuthUser()->getUserHedwigeToken(), $from, $to, $customFilters);
        return new Response(
            json_encode($leads),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function getLeads(string $token, ?string $from, ?string $to, array $customFilters = []): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $queryParams = [];

            if ($from !== null && $from !== '') {
                $queryParams['from'] = $from;
            }
            if ($to !== null && $to !== '') {
                $queryParams['to'] = $to;
            }

            // Construire la liste de CustomFilterDto
            $customFilterDtos = [];
            foreach ($customFilters as $columnId => $value) {
                $dto = ['reportingCustomColumnId' => (int) $columnId];

                if (is_array($value)) {
                    // Plage de dates
                    if (
                        array_key_exists('from', $value) ||
                        array_key_exists('to', $value)
                    ) {
                        if (!empty($value['from'])) {
                            $dto['from'] = $value['from'];
                        }
                        if (!empty($value['to'])) {
                            $dto['to'] = $value['to'];
                        }
                    } elseif (array_key_exists('id', $value)) {
                        // select simple OXD renvoie parfois un objet {id, label, ...}
                        $rawId = $value['id'];
                        if ($rawId === true || $rawId === 'true') {
                            $dto['activate'] = true;
                        } elseif ($rawId === false || $rawId === 'false') {
                            $dto['activate'] = false;
                        }
                    } elseif (array_is_list($value)) {
                        // Multi-select : peut arriver soit en liste de scalaires ['1','2'],
                        // soit en liste d'objets [{id:'1',...},{id:'2',...}]
                        if (!empty($value) && is_array($value[0]) && array_key_exists('id', $value[0])) {
                            $dto['options'] = array_map(
                                fn($v) => $v['id'],
                                $value
                            );
                        } else {
                            // liste scalaire
                            $dto['options'] = $value;
                        }
                    }
                } elseif (
                    $value === true ||
                    $value === false ||
                    $value === 'true' ||
                    $value === 'false'
                ) {
                    // Booléen (peut arriver en bool ou en string)
                    $dto['activate'] = $value === true || $value === 'true';
                } elseif ($value !== null && $value !== '') {
                    // Texte libre ou multiselect
                    if (str_contains($value, ',')) {
                        $dto['options'] = explode(',', $value);
                    } else {
                        $dto['searchValue'] = $value;
                    }
                }

                if (count($dto) > 1) {
                    $customFilterDtos[] = $dto;
                }
            }



            $response = $client->request('GET', "{$clientBaseUrl}/actor/leads", [
                'headers' => ['Authorization' => $token, 'Content-Type' => 'application/json'],
                'query'   => $queryParams,
                'json'    => $customFilterDtos,
            ]);

            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            return [];
        }
    }
}