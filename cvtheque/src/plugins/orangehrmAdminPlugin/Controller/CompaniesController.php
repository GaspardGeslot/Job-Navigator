<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use GuzzleHttp\Client;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;

class CompaniesController extends AbstractVueController
{
    use AuthUserTrait;
    
    public const FILTER_PROFESSIONAL_EXPERIENCE = 'professionalExperienceFilter';
    public const FILTER_JOB_TITLE = 'jobTitleFilter';
    public const FILTER_NEED = 'needFilter';
    public const FILTER_STUDY_LEVEL = 'studyLevelFilter';
    public const FILTER_COURSE_START = 'courseStartFilter';
    public const FILTER_START_DATE = 'startDate';
    public const FILTER_TO_DATE = 'toDate';
    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('companies-list');

        $options = $this->getHedwigeOptions();

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
        $fromDateFilter = $request->query->get(self::FILTER_START_DATE);
        $toDateFilter = $request->query->get(self::FILTER_TO_DATE);
        $fromDate = $fromDateFilter ? new \DateTime($fromDateFilter) : null;
        $toDate = $toDateFilter ? new \DateTime($toDateFilter) : null;

        $companies = $this->getCompanies($this->getAuthUser()->getUserHedwigeToken(), $fromDate, $toDate);
        return new Response(
            json_encode($companies),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        
    }

    /**
     * @inheritDoc
     */
    public function getMatchings(Request $request)
    {
        $fromDateFilter = $request->query->get(self::FILTER_START_DATE);
        $toDateFilter = $request->query->get(self::FILTER_TO_DATE);
        $fromDate = $fromDateFilter ? new \DateTime($fromDateFilter) : null;
        $toDate = $toDateFilter ? new \DateTime($toDateFilter) : null;

        $companies = $this->getHedwigeMatchings($this->getAuthUser()->getUserHedwigeToken(), $fromDate, $toDate);
        return new Response(
            json_encode($companies),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        
    }

    /**
     * @inheritDoc
     */
    public function getCompaniesMatchings(Request $request)
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

        $matchings = $this->getHedwigeCompaniesMatchings($this->getAuthUser()->getUserHedwigeToken(), $jobTitleFilter, $needFilter, $studyLevelFilter, $courseStartFilter, $professionalExperienceFilter);
        return new Response(
            json_encode($matchings),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
        
    }

    protected function getCompanies(string $token, ?\DateTime $fromDate, ?\DateTime $toDate) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/client/companies/stats?";
            if ($fromDate !== null) {
                $url .= 'from=' . urlencode($fromDate->format('Y-m-d')) . '&';
            }
            if ($toDate !== null) {
                $url .= 'to=' . urlencode($toDate->format('Y-m-d')) . '&';
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

    protected function getHedwigeMatchings(string $token, ?\DateTime $fromDate, ?\DateTime $toDate) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/client/matching/stats?";
            if ($fromDate !== null) {
                $url .= 'from=' . urlencode($fromDate->format('Y-m-d')) . '&';
            }
            if ($toDate !== null) {
                $url .= 'to=' . urlencode($toDate->format('Y-m-d')) . '&';
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

    protected function getHedwigeCompaniesMatchings(string $token, ?string $jobTitleFilter, ?string $needFilter, ?string $studyLevelFilter, ?string $courseStartFilter, ?string $professionalExperienceFilter) : array
    {
        $client = new Client();
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = "{$clientBaseUrl}/client/companies/matching/stats?";
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
}
