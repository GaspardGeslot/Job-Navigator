<?php

namespace OrangeHRM\Admin\Controller;

use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class SaveMatchingController extends AbstractVueController
{
    use AuthUserTrait;

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('save-matching');

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
}
