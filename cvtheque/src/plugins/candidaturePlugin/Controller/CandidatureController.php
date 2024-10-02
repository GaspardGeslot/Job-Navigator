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
    error_log('postNewLead called');

    $empNumber = 6;
    $screen = 'personal';
    echo '<pre>';
    print_r($screen);
    echo '</pre>';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = (int)$_FILES['file']['size'];  // Taille réelle du fichier
        $fileType = $_FILES['file']['type'];
        $fileContent = file_get_contents($fileTmpPath);

        // error_log('File details: Name = ' . $fileName . ', Size = ' . $fileSize . ', Type = ' . $fileType);
        // echo '<pre>';
        // print_r($fileSize);
        // echo '</pre>';
        // Création de l'attachement encodé en base64
        $base64Attachment = new \OrangeHRM\Core\Dto\Base64Attachment(
            $fileName,
            $fileType,
            base64_encode($fileContent),
            $fileSize
        );
        // echo '<pre>';
        // print_r($base64Attachment);
        // echo '</pre>';
        
        // Créer une instance de Request
        $apiRequest = new \OrangeHRM\Core\Api\V2\Request($request);
        // echo '<pre>';
        // print_r($apiRequest);
        // echo '</pre>';
        // Créer une instance de EmployeeAttachmentAPI en passant l'instance de Request
        $employeeAttachmentApi = new \OrangeHRM\Pim\Api\EmployeeAttachmentAPI($apiRequest);
        // echo '<pre>';
        // print_r($employeeAttachmentApi);
        // echo '</pre>';

        try {
            $result = $employeeAttachmentApi->createAndGetId($empNumber, $screen, $base64Attachment);
            $attachmentId = $result->normalize()['attachmentId'];
            echo '<pre>';
            print_r('Attachment ID: ' . $attachmentId);
            echo '</pre>';
        } catch (Exception $e) {
            $error_message = 'Error in createAndGetId: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
            file_put_contents('/opt/lampp/logs/php_error_log', $error_message, FILE_APPEND);
            
            return new Response('Internal Server Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        

        return new Response('Lead submitted successfully with attachment ID: ' . $attachmentId, Response::HTTP_OK);
    } else {
        error_log('File upload error');
        return new Response('Error: No file uploaded or file upload error', Response::HTTP_BAD_REQUEST);
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