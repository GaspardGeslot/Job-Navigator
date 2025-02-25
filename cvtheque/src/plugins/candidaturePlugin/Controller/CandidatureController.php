<?php
namespace Candidature\Controller;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use OrangeHRM\Authentication\Auth\AuthProviderChain;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Authentication\Service\LoginService;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use GuzzleHttp\Client;
use OrangeHRM\Authentication\Auth\User as AuthUser;
use OrangeHRM\Authentication\Dto\AuthParams;
use OrangeHRM\Authentication\Dto\UserCredential;
use Symfony\Component\HttpFoundation\Response;
use OrangeHRM\Core\Controller\AbstractVueController;
use OrangeHRM\Core\Authorization\Service\HomePageService;
use OrangeHRM\Core\Vue\Component;
use OrangeHRM\Core\Vue\Prop;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Authentication\Exception\UserAlreadyEnrolledException;
use OrangeHRM\Authentication\Exception\AuthenticationException;
use OrangeHRM\Framework\Services;
use OrangeHRM\Core\Traits\ServiceContainerTrait;

class CandidatureController extends AbstractVueController implements PublicControllerInterface
{
    use ServiceContainerTrait;
    use AuthUserTrait;
    use ThemeServiceTrait;

    /**
     * @var null|LoginService
     */
    protected ?LoginService $loginService = null;

    /**
     * @var HomePageService|null
     */
    protected ?HomePageService $homePageService = null;

    /**
     * @return LoginService
     */
    public function getLoginService(): LoginService
    {
        if (is_null($this->loginService)) {
            $this->loginService = new LoginService();
        }
        return $this->loginService;
    }
    
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

        $keyMapping = [
            'utm_source' => 'utmSource',
            'BTPcheckedEXP' => 'specificProfessionalExperience',
            'checkedEXP' => 'professionalExperience',
            'permits' => 'drivingLicenses',
            'vehicle' => 'hasPersonalVehicle',
            ''
        ];

        // Désérialiser les champs qui sont des tableaux JSON
        if (isset($leadData['jobs'])) {
            $leadData['jobs'] = json_decode($leadData['jobs'], true);
        }
        if (isset($leadData['skills'])) {
            $leadData['skills'] = json_decode($leadData['skills'], true);
        }

        foreach ($keyMapping as $oldKey => $newKey) {
            if (isset($leadData[$oldKey])) {
                if ($oldKey === 'permits') {
                    // Désérialiser en tableau si ce n'est pas déjà un tableau
                    $leadData[$newKey] = is_array($leadData[$oldKey])
                        ? $leadData[$oldKey]
                        : json_decode($leadData[$oldKey], true);
                } else {
                    $leadData[$newKey] = $leadData[$oldKey];
                }
                unset($leadData[$oldKey]); // Supprime l'ancienne clé
            }
        }
        
        $empNumber = getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID');
        $screen = 'personal';
        $attachment_Id = 'No ID'; // Valeur par défaut si aucune pièce jointe n'est téléchargée

        // Gestion de la pièce jointe
        // if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK)
        if (isset($leadData['file'])) {
            // $fileTmpPath = $_FILES['file']['tmp_name'];
            // $fileName = $_FILES['file']['name'];
            // $fileSize = (int)$_FILES['file']['size'];
            // $fileType = $_FILES['file']['type'];
            // $fileContent = file_get_contents($fileTmpPath);

            // $base64Attachment = new \OrangeHRM\Core\Dto\Base64Attachment(
            //     $fileName,
            //     $fileType,
            //     $fileContent,
            //     $fileSize
            // );

            $decodedFileData = json_decode($leadData['file'], true);
            if (isset($decodedFileData['base64'], $decodedFileData['name'], $decodedFileData['type'], $decodedFileData['size'])) {
                $base64Attachment = new \OrangeHRM\Core\Dto\Base64Attachment(
                    $decodedFileData['name'],    // Nom du fichier
                    $decodedFileData['type'],    // Type MIME
                    $decodedFileData['base64'],  // Contenu du fichier en base64
                    $decodedFileData['size']     // Taille du fichier
                );

                $apiRequest = new \OrangeHRM\Core\Api\V2\Request($request);
                $employeeAttachmentApi = new \OrangeHRM\Pim\Api\EmployeeAttachmentAPI($apiRequest);

                try {
                    $result = $employeeAttachmentApi->createAndGetId($empNumber, $screen, $base64Attachment);
                    $normalizedResult = $result->normalize();
                    $attachment_Id = $normalizedResult["id"] ?? 'No ID';
                    $attachment_Id = strval($attachment_Id);
                } catch (Exception $e) {
                    $error_message = 'Error in createAndGetId: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
                    error_log($error_message); // Log the error
                    
                    return new Response('Internal Server Error during attachment creation: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }
        // Ajout de l'ID de la pièce jointe aux données du lead
        if ($attachment_Id !== 'No ID') {
            $leadData['resume'] = $attachment_Id;
        }

        // Encode les données du lead en JSON
        $leadDataJson = json_encode($leadData);

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

    /**
     * @inheritDoc
     */
    public function createNewAccount(Request $request)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $theme = $request->attributes->get('theme');
        $credentials = new UserCredential($email, $password, 'ESS');
        try {

            /** @var AuthProviderChain $authProviderChain */
            $authProviderChain = $this->getContainer()->get(Services::AUTH_PROVIDER_CHAIN);
            
            $token = $authProviderChain->signInFromCandidature(new AuthParams($credentials, null, $theme));
            $success = !is_null($token);

            if (!$success)
                throw AuthenticationException::invalidCredentials();
            $this->getAuthUser()->setIsAuthenticated($success);
            $this->getAuthUser()->setIsCandidate(true);
            $this->getAuthUser()->setUserHedwigeToken($token);
            $this->getLoginService()->addLogin($credentials);
        } catch (UserAlreadyEnrolledException $e) {
            return new Response('User already enrolled', Response::HTTP_CONFLICT);
        } catch (Throwable $e) {
            return new Response('Unexpected error occurred', Response::HTTP_BAD_REQUEST);
        }
        return new Response('User enrolled successfully', Response::HTTP_OK);
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