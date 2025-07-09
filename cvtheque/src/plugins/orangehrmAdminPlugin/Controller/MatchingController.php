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

class MatchingController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_TITLE = 'title';
    public const FILTER_ACTOR = 'actor';
    public const FILTER_JOB = 'job';
    public const FILTER_COURSE_ID = 'courseId';
    public const FILTER_IS_ACTIVE = 'isActive';
    public const FILTER_COUNTRIES = 'countries';
    public const FILTER_COURSE_STARTS = 'courseStarts';
    public const FILTER_FUNDINGS = 'fundings';
    public const FILTER_HANDICAPS = 'handicaps';
    public const FILTER_STUDY_LEVELS = 'studyLevels';
    public const FILTER_NEEDS = 'needs';
    public const FILTER_PHONE_NUMBERS = 'phoneNumbers';
    public const FILTER_PROFESSIONAL_EXPERIENCES = 'professionalExperiences';
    public const FILTER_STATUS = 'status';
    public const FILTER_TRAINING_METHODS = 'trainingMethods';
    public const FILTER_DRIVING_LICENSES = 'drivingLicenses';

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
        $component->addProp(new Prop('departments', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $id . ' - ' . $label
            ];
        }, array_keys($options['departments']), $options['departments'])));

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
        $isActiveFilter = $request->query->get(
            self::FILTER_IS_ACTIVE
        );
        if ($isActiveFilter !== null) {
            $isActiveFilter = filter_var($isActiveFilter, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        
        $countriesFilter = $request->query->get('countries');
        $courseStartsFilter = $request->query->get('courseStarts');
        $fundingsFilter = $request->query->get('fundings');
        $handicapsFilter = $request->query->get('handicaps');
        $studyLevelsFilter = $request->query->get('studyLevels');
        $needsFilter = $request->query->get('needs');
        $phoneNumbersFilter = $request->query->get('phoneNumbers');
        $professionalExperiencesFilter = $request->query->get('professionalExperiences');
        $statusFilter = $request->query->get('status');
        $trainingMethodsFilter = $request->query->get('trainingMethods');
        $drivingLicensesFilter = $request->query->get('drivingLicenses');
        
        $matchings = $this->getMatchings(
            $this->getAuthUser()->getUserHedwigeToken(), 
            $titleFilter, 
            $actorFilter, 
            $jobFilter, 
            $courseIdFilter, 
            $isActiveFilter, 
            $countriesFilter,
            $courseStartsFilter,
            $fundingsFilter,
            $handicapsFilter,
            $studyLevelsFilter,
            $needsFilter,
            $phoneNumbersFilter,
            $professionalExperiencesFilter,
            $statusFilter,
            $trainingMethodsFilter,
            $drivingLicensesFilter
        );
        return new Response(
            json_encode($matchings),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    public function create(Request $request): Response
    {
        try {
            error_log('create we are here ');
            error_log('Contenu de la requête : ' . $request->getContent());
            $matching = json_decode($request->getContent(), true);
            if (
                isset($matching['courses']) &&
                (empty($matching['courses']) || array_values($matching['courses']) === $matching['courses'])
            ) {
                unset($matching['courses']);
            }
            $this->createMatching($this->getAuthUser()->getUserHedwigeToken(), $matching);
            return new Response(json_encode(['message' => 'Matching created successfully']), Response::HTTP_OK);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => 'Error creating matching'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $matching = json_decode($request->getContent(), true);
            $this->updateMatching($this->getAuthUser()->getUserHedwigeToken(), $id, $matching);
            return new Response(json_encode(['message' => 'Matching updated successfully']), Response::HTTP_OK);
        } catch (ClientException $e) {
            return new Response(json_encode([
                'error' => true,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message
            ]), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => true, 
                'message' => 'Error updating matching'
            ]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $this->deleteMatching($this->getAuthUser()->getUserHedwigeToken(), $id);
        return new Response(json_encode(['message' => 'Matching deleted successfully']), Response::HTTP_OK);
    }

    private function getMatchings(string $token, ?string $titleFilter, ?string $actorFilter, ?string $jobFilter, ?string $courseIdFilter, ?bool $isActiveFilter, ?array $countriesFilter = null, ?array $courseStartsFilter = null, ?array $fundingsFilter = null, ?array $handicapsFilter = null, ?array $studyLevelsFilter = null, ?array $needsFilter = null, ?array $phoneNumbersFilter = null, ?array $professionalExperiencesFilter = null, ?array $statusFilter = null, ?array $trainingMethodsFilter = null, ?array $drivingLicensesFilter = null): array
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
            if ($isActiveFilter !== null) {
                $url .= 'isActive=' . ($isActiveFilter ? 'true' : 'false') . '&';
            }
            // if ($countriesFilter != null && !empty($countriesFilter)) {
            //     $url .= 'countries=' . urlencode(implode(',', $countriesFilter)) . '&';
            // }
            if ($countriesFilter != null && !empty($countriesFilter)) {
                foreach ($countriesFilter as $country) {
                    $url .= 'countries=' . urlencode($country) . '&';
                }
            }
            if ($courseStartsFilter != null && !empty($courseStartsFilter)) {
                foreach ($courseStartsFilter as $courseStarts) {
                    $url .= 'courseStarts=' . urlencode($courseStarts) . '&';
                }
            }
            // if ($courseStartsFilter != null && !empty($courseStartsFilter)) {
            //     $url .= 'courseStarts=' . urlencode(implode(',', $courseStartsFilter)) . '&';
            // }
            if ($fundingsFilter != null && !empty($fundingsFilter)) {
                foreach ($fundingsFilter as $funding) {
                    $url .= 'fundings=' . urlencode($funding) . '&';
                }
            }
            // if ($fundingsFilter != null && !empty($fundingsFilter)) {
            //     $url .= 'fundings=' . urlencode(implode(',', $fundingsFilter)) . '&';
            // }
            // if ($handicapsFilter != null && !empty($handicapsFilter)) {
            //     $url .= 'handicaps=' . urlencode(implode(',', $handicapsFilter)) . '&';
            // }
            if ($handicapsFilter != null && !empty($handicapsFilter)) {
                foreach ($handicapsFilter as $handicap) {
                    $url .= 'handicaps=' . urlencode($handicap) . '&';
                }
            }
            if ($studyLevelsFilter != null && !empty($studyLevelsFilter)) {
                foreach ($studyLevelsFilter as $studyLevel) {
                    $url .= 'studyLevels=' . urlencode($studyLevel) . '&';
                }
            }
            // if ($studyLevelsFilter != null && !empty($studyLevelsFilter)) {
            //     $url .= 'studyLevels=' . urlencode(implode(',', $studyLevelsFilter)) . '&';
            // }
            // if ($needsFilter != null && !empty($needsFilter)) {
            //     $url .= 'needs=' . urlencode(implode(',', $needsFilter)) . '&';
            // }
            // if ($phoneNumbersFilter != null && !empty($phoneNumbersFilter)) {
            //     $url .= 'phoneNumbers=' . urlencode(implode(',', $phoneNumbersFilter)) . '&';
            // }
            // if ($professionalExperiencesFilter != null && !empty($professionalExperiencesFilter)) {
            //     $url .= 'professionalExperiences=' . urlencode(implode(',', $professionalExperiencesFilter)) . '&';
            // }
            // if ($statusFilter != null && !empty($statusFilter)) {
            //     $url .= 'status=' . urlencode(implode(',', $statusFilter)) . '&';
            // }
            // if ($trainingMethodsFilter != null && !empty($trainingMethodsFilter)) {
            //     $url .= 'trainingMethods=' . urlencode(implode(',', $trainingMethodsFilter)) . '&';
            // }
            // if ($drivingLicensesFilter != null && !empty($drivingLicensesFilter)) {
            //     $url .= 'drivingLicenses=' . urlencode(implode(',', $drivingLicensesFilter)) . '&';
            // }
            if ($needsFilter != null && !empty($needsFilter)) {
                foreach ($needsFilter as $need) {
                    $url .= 'needs=' . urlencode($need) . '&';
                }
            }
            if ($phoneNumbersFilter != null && !empty($phoneNumbersFilter)) {
                foreach ($phoneNumbersFilter as $phoneNumber) {
                    $url .= 'phoneNumbers=' . urlencode($phoneNumber) . '&';
                }
            }
            if ($professionalExperiencesFilter != null && !empty($professionalExperiencesFilter)) {
                foreach ($professionalExperiencesFilter as $professionalExperience) {
                    $url .= 'professionalExperiences=' . urlencode($professionalExperience) . '&';
                }
            }
            if ($statusFilter != null && !empty($statusFilter)) {
                foreach ($statusFilter as $status) {
                    $url .= 'status=' . urlencode($status) . '&';
                }
            }
            if ($trainingMethodsFilter != null && !empty($trainingMethodsFilter)) {
                foreach ($trainingMethodsFilter as $trainingMethod) {
                    $url .= 'trainingMethods=' . urlencode($trainingMethod) . '&';
                }
            }
            if ($drivingLicensesFilter != null && !empty($drivingLicensesFilter)) {
                foreach ($drivingLicensesFilter as $drivingLicense) {
                    $url .= 'drivingLicenses=' . urlencode($drivingLicense) . '&';
                }
            }
            // error_log("URL de requête : " . urldecode($url));
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
        $url = "{$clientBaseUrl}/matching";
        error_log('data here ' . $data);
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $data
        ]);
    }

    private function updateMatching(string $token, int $id, array $matching): void
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        $data = json_encode($matching);

        $url = "{$clientBaseUrl}/matching/{$id}";
        $response = $client->request('PUT', $url, [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ],
            'body' => $data
        ]);
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
