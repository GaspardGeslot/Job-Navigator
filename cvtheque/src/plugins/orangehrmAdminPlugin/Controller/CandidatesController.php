<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;

class CandidatesController extends AbstractVueController
{
    use AuthUserTrait;

    public const FILTER_PROFESSIONAL_EXPERIENCE = 'professionalExperienceFilter';
    public const FILTER_JOB_TITLE = 'jobTitleFilter';
    public const FILTER_NEED = 'needFilter';
    public const FILTER_STUDY_LEVEL = 'studyLevelFilter';
    public const FILTER_COURSE_START = 'courseStartFilter';
    public const FILTER_START_DATE = 'startDate';
    public const FILTER_TO_DATE = 'toDate';
    public const FILTER_UTM_SOURCE = 'utmSourceFilter';

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('candidates-list');

        $options = $this->getHedwigeOptions();
        $utmSources = $this->getUtmSourcesOptions();

        $component->addProp(new Prop('utm-sources', Prop::TYPE_ARRAY, array_map(function($index, $label) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, array_keys($utmSources), $utmSources)));
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
        $component->addProp(new Prop('needs', Prop::TYPE_ARRAY, array_map(function($label, $index) {
            return [
                'id' => $index,
                'label' => $label
            ];
        }, $options['needs'], array_keys($options['needs']))));
        $component->addProp(new Prop('professional-experiences', Prop::TYPE_ARRAY, array_map(function($id, $label) {
            return [
                'id' => $id,
                'label' => $label
            ];
        }, array_keys($options['professionalExperiences']), $options['professionalExperiences'])));
        $component->addProp(new Prop('sectors', Prop::TYPE_ARRAY, array_map(function($sector, $index) {
            return [
                'id' => $index,
                'label' => $sector['title'],
                'jobs' => $sector['jobs']
            ];
        }, $options['sectors'], array_keys($options['sectors']))));

        $this->setComponent($component);
    }

    /**
     * @inheritDoc
     */
    public function getAll(Request $request)
    {
        $professionalExperienceFilter = $request->query->get(
            self::FILTER_PROFESSIONAL_EXPERIENCE
        );
        $jobTitleFilter = $request->query->get(
            self::FILTER_JOB_TITLE
        );
        $needFilter = $request->query->get(
            self::FILTER_NEED
        );
        $studyLevelFilter = $request->query->get(
            self::FILTER_STUDY_LEVEL
        );
        $courseStartFilter = $request->query->get(
            self::FILTER_COURSE_START
        );
        $utmSourceFilter = $request->query->get(
            self::FILTER_UTM_SOURCE
        );
        $fromDateFilter = $request->query->get(self::FILTER_START_DATE);
        $toDateFilter = $request->query->get(self::FILTER_TO_DATE);
        $fromDate = $fromDateFilter ? new \DateTime($fromDateFilter) : null;
        $toDate = $toDateFilter ? new \DateTime($toDateFilter) : null;

        $candidates = $this->getCandidates($this->getAuthUser()->getUserHedwigeToken(), $jobTitleFilter, $needFilter, $studyLevelFilter, $courseStartFilter, $professionalExperienceFilter, $utmSourceFilter, $fromDate, $toDate);
        return new Response(
            json_encode($candidates),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        
    }

    protected function getCandidates(string $token, ?string $jobTitleFilter, ?string $needFilter, ?string $studyLevelFilter, ?string $courseStartFilter, ?string $professionalExperienceFilter, ?string $utmSourceFilter, ?\DateTime $fromDate, ?\DateTime $toDate) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/client/leads/stats?";
            if ($fromDate !== null) {
                $url .= 'from=' . urlencode($fromDate->format('Y-m-d')) . '&';
            }
            if ($toDate !== null) {
                $url .= 'to=' . urlencode($toDate->format('Y-m-d')) . '&';
            }
            if ($jobTitleFilter != null &&$jobTitleFilter !== '') {
                $url .= 'job=' . urlencode($jobTitleFilter) . '&';
            }
            if ($needFilter != null && $needFilter !== '') {
                $url .= 'need=' . urlencode($needFilter) . '&';
            }
            if ($studyLevelFilter != null && $studyLevelFilter !== '') {
                $url .= 'studyLevel=' . urlencode($studyLevelFilter) . '&';
            }
            if ($courseStartFilter != null && $courseStartFilter !== '') {
                $url .= 'courseStart=' . urlencode($courseStartFilter) . '&';
            }
            if ($professionalExperienceFilter != null && $professionalExperienceFilter !== '') {
                $url .= 'professionalExperience=' . urlencode($professionalExperienceFilter) . '&';
            }
            if ($utmSourceFilter != null && $utmSourceFilter !== '') {
                $url .= 'utmSource=' . urlencode($utmSourceFilter) . '&';
            }
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => $token,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return null;
        }
    }

    public function getHedwigeOptions(): array
    {
        $client = new Client();
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/options", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }

    public function getUtmSourcesOptions(): array
    {
        $client = new Client();
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/utm-source", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exceptionon $e) {
            return [];
        }
    }
}
