<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class MatchingController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_TITLE = 'titleFilter';
    public const FILTER_ACTOR = 'actorFilter';
    public const FILTER_JOB = 'jobFilter';
    public const FILTER_COURSE_ID = 'courseIdFilter';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('matching-list');

        $options = $this->getHedwigeOptions($this->getAuthUser()->getUserHedwigeToken());

        $component->addProp(new Prop('study-levels', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options['studyLevels']), $options['studyLevels'])));
        $component->addProp(new Prop('course-starts', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options['courseStarts']), $options['courseStarts'])));
        $component->addProp(new Prop('driving-licenses', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options['drivingLicenses']), $options['drivingLicenses'])));
        $component->addProp(new Prop('professional-experiences', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options['professionalExperiences']), $options['professionalExperiences'])));
        $component->addProp(new Prop('needs', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['needs'], array_keys($options['needs']))));
        $component->addProp(new Prop('actors', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['actors'], array_keys($options['actors']))));
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
        $component->addProp(new Prop('handicaps', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['handicaps'], array_keys($options['handicaps']))));
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
        $component->addProp(new Prop('phone-numbers', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['phoneNumbers'], array_keys($options['phoneNumbers']))));
        $component->addProp(new Prop('actors', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['actors'], array_keys($options['actors']))));

        $this->setComponent($component);
    }
    
    public function getAll(Request $request): Response
    {
        $titleFilter = $request->query->get(
            self::FILTER_TITLE
        );
        $actorFilter = $request->query->get(
            self::FILTER_ACTOR
        );
        $jobFilter = $request->query->get(
            self::FILTER_JOB
        );
        $courseIdFilter = $request->query->get(
            self::FILTER_COURSE_ID
        );
        $matchings = $this->getMatchings($this->getAuthUser()->getUserHedwigeToken(), $titleFilter, $actorFilter, $jobFilter, $courseIdFilter);
        return new Response(
            json_encode($matchings),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function create(Request $request): Response
    {
        $matching = json_decode($request->getContent(), true);
        $this->createMatching($this->getAuthUser()->getUserHedwigeToken(), $matching);
        return new Response(json_encode(['message' => 'Matching created successfully']), Response::HTTP_OK);
    }

    public function delete(Request $request): Response
    {
        error_log('delete : ' . $request->attributes->get('id'));
        $id = $request->attributes->get('id');
        $this->deleteMatching($this->getAuthUser()->getUserHedwigeToken(), $id);
        return new Response(json_encode(['message' => 'Matching deleted successfully']), Response::HTTP_OK);
    }

    private function getMatchings(string $token, ?string $titleFilter, ?string $actorFilter, ?string $jobFilter, ?string $courseIdFilter): array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/matching?";
            if ($titleFilter != null && $titleFilter !== '') {
                $url .= 'title=' . urlencode($titleFilter) . '&';
            }
            if ($actorFilter != null && $actorFilter !== '') {
                $url .= 'actor=' . urlencode($actorFilter) . '&';
            }
            if ($jobFilter != null && $jobFilter !== '') {
                $url .= 'job=' . urlencode($jobFilter) . '&';
            }
            if ($courseIdFilter != null && $courseIdFilter !== '') {
                $url .= 'course=' . urlencode($courseIdFilter) . '&';
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

    private function createMatching(string $token, array $matching): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $data = json_encode($matching);
        error_log('data : ' . $data);

        try {
            $url = "{$clientBaseUrl}/matching";
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ],
                'body' => $data
            ]);
        } catch (\Exception $e) {   
            error_log($e->getMessage());
            throw new \Exception('Error creating matching');
        }
    }

    private function deleteMatching(string $token, int $id): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/matching/{$id}";
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error deleting matching');
        }
    }
}
