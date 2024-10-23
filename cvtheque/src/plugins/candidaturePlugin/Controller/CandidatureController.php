<?php
namespace Candidature\Controller;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use GuzzleHttp\Client;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use Symfony\Component\HttpFoundation\Response;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Authorization\Service\HomePageService;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\Request;

class CandidatureController extends AbstractVueController implements PublicControllerInterface
{
    use AuthUserTrait;
    use ThemeServiceTrait;

    /**
     * @var HomePageService|null
     */
    protected ?HomePageService $homePageService = null;

    
    /**
     * @return HomePageService
     */
    public function getHomePageService(): HomePageService
    {
        if (!$this->homePageService instanceof HomePageService) {
            $this->homePageService = new HomePageService();
        }
        return $this->homePageService;
    }

    /**
     * @inheritDoc
     */
    public function preRender(Request $request): void
    {
        $component = new Component('new-candidature');
        $component->addProp(
            new Prop('candidature-logo-src', Prop::TYPE_STRING, $request->getBasePath() . '/images/cvtheque_logo.png')
        );
        $component->addProp(
            new Prop('candidature-banner-src', Prop::TYPE_STRING, $this->getThemeService()->getLoginBannerURL($request))
        );
        $component->addProp(
            new Prop('options', Prop::TYPE_OBJECT, $this->getCandidatureOptions())
        );
        $this->setComponent($component);
        $this->setTemplate('no_header.html.twig');
    }

    /**
     * @inheritDoc
     */
    public function postNewLead(Request $request)
    {
        $leadData = $request->request->all();

        // Désérialiser les champs qui sont des tableaux JSON
        if (isset($leadData['jobs'])) {
            $leadData['jobs'] = json_decode($leadData['jobs'], true);
        }
        if (isset($leadData['drivingLicenses'])) {
            $leadData['drivingLicenses'] = json_decode($leadData['permits'], true);
        }
        if (isset($leadData['skills'])) {
            $leadData['skills'] = json_decode($leadData['skills'], true);
        }

        // echo '<pre>';
        // var_dump($leadData);
        // echo '</pre>';
        
        $empNumber = getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID');
        $screen = 'personal';
        $attachment_Id = 'No ID'; // Valeur par défaut si aucune pièce jointe n'est téléchargée

        // Gestion de la pièce jointe
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            $fileSize = (int)$_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
            $fileContent = file_get_contents($fileTmpPath);

            $base64Attachment = new \OrangeHRM\Core\Dto\Base64Attachment(
                $fileName,
                $fileType,
                $fileContent,
                $fileSize
            );

            $apiRequest = new \OrangeHRM\Core\Api\V2\Request($request);
            $employeeAttachmentApi = new \OrangeHRM\Pim\Api\EmployeeAttachmentAPI($apiRequest);

            try {
                $result = $employeeAttachmentApi->createAndGetId($empNumber, $screen, $base64Attachment);
                $normalizedResult = $result->normalize();
                $attachment_Id = $normalizedResult["id"] ?? 'No ID';
                // echo '<pre>attachment_Id';
                // var_dump($attachment_Id);
                // echo 'attachment_Id</pre>';
                $attachment_Id = strval($attachment_Id);
                // echo '<pre>strval_attachment_Id';
                // var_dump($attachment_Id);
                // echo 'strval_attachment_Id</pre>';
            } catch (Exception $e) {
                $error_message = 'Error in createAndGetId: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
                error_log($error_message); // Log the error
                
                return new Response('Internal Server Error during attachment creation: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Ajout de l'ID de la pièce jointe aux données du lead
        if ($attachment_Id !== 'No ID') {
            $leadData['resume'] = $attachment_Id;
        }

        // Debug : Affichage des données du lead avant et après l'encodage en JSON
        // echo '<pre>';

        // print_r('Lead Data before submission:');
        // print_r($leadData);
        // echo '</pre>';

        // Encode les données du lead en JSON
        $leadDataJson = json_encode($leadData);
        // echo '<pre>';
        // print_r('Lead Data post json_encode:');
        // print_r($leadDataJson);
        // echo '</pre>';

        // Envoi du lead avec les données encodées en JSON
        $client = new \GuzzleHttp\Client();
        $clientId = getenv('HEDWIGE_CLIENT_ID');
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            // Envoi de la requête
            $response = $client->request('POST', "{$clientBaseUrl}/lead", [
                'headers' => [
                    'Authorization' => $clientToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => $leadDataJson // Envoi des données encodées en JSON
            ]);

            $responseBody = (string) $response->getBody();
            // echo '<pre>';
            // print_r('Response from HEDWIGE: ' . $responseBody);
            // echo '</pre>';
            $responseData = [
                "MatchResponse" => intval($responseBody),
                "attachmentId" => $attachment_Id
            ];
            return new Response(
                json_encode($responseData),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            $error_message = 'Error in submitting lead: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
            error_log($error_message); // Log the error

            return new Response('Failed to submit lead: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getCandidatureOptions(): array|object
    {
        $client = new Client();
        $clientId = getenv('HEDWIGE_CLIENT_ID');
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $response = $client->request('GET', "{$clientBaseUrl}/client/options", [
                'headers' => [
                    'Authorization' => $clientToken
                ]
            ]);

            return json_decode($response->getBody());
        } catch (\Exceptionon $e) {
            return new \stdClass();
        }
    }
}
