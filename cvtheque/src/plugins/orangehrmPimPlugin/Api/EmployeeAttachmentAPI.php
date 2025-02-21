<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\Pim\Api;

use GuzzleHttp\Client;
use OrangeHRM\Core\Api\CommonParams;
use OrangeHRM\Core\Api\V2\CrudEndpoint;
use OrangeHRM\Core\Api\V2\Endpoint;
use OrangeHRM\Core\Api\V2\EndpointCollectionResult;
use OrangeHRM\Core\Api\V2\EndpointResourceResult;
use OrangeHRM\Core\Api\V2\EndpointResult;
use OrangeHRM\Core\Api\V2\Model\ArrayModel;
use OrangeHRM\Core\Api\V2\ParameterBag;
use OrangeHRM\Core\Api\V2\RequestParams;
use OrangeHRM\Core\Api\V2\Validator\ParamRule;
use OrangeHRM\Core\Api\V2\Validator\ParamRuleCollection;
use OrangeHRM\Core\Api\V2\Validator\Rule;
use OrangeHRM\Core\Api\V2\Validator\Rules;
use OrangeHRM\Core\Dto\Base64Attachment;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Entity\EmployeeAttachment;
use OrangeHRM\Pim\Api\Model\EmployeeAttachmentModel;
use OrangeHRM\Pim\Dto\PartialEmployeeAttachment;
use OrangeHRM\Recruitment\Dto\RecruitmentAttachment;
use OrangeHRM\Recruitment\Api\Model\CandidateAttachmentModel;
use OrangeHRM\Pim\Service\EmployeeAttachmentService;
use Symfony\Component\HttpFoundation\Response;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;

class EmployeeAttachmentAPI extends Endpoint implements CrudEndpoint
{
    use AuthUserTrait;
    use UserRoleManagerTrait;

    public const PARAMETER_THEME = 'theme';
    public const PARAMETER_SCREEN = 'screen';
    public const PARAMETER_ATTACHMENT = 'attachment';
    public const PARAMETER_ATTACHMENT_ID = 'attachmentId';
    public const PARAMETER_DESCRIPTION = 'description';

    public const PARAM_RULE_ATTACHMENT_FILE_NAME_MAX_LENGTH = 100;
    public const PARAM_RULE_DESCRIPTION_MAX_LENGTH = 200;

    /**
     * @var EmployeeAttachmentService|null
     */
    protected ?EmployeeAttachmentService $employeeAttachmentService = null;

    /**
     * @return EmployeeAttachmentService
     */
    public function getEmployeeAttachmentService(): EmployeeAttachmentService
    {
        if (!$this->employeeAttachmentService instanceof EmployeeAttachmentService) {
            $this->employeeAttachmentService = new EmployeeAttachmentService();
        }
        return $this->employeeAttachmentService;
    }

    /**
     * @OA\Get(
     *     path="/api/v2/pim/attachments/{attachmentId}",
     *     tags={"PIM/Employee Attachment"},
     *     summary="Get an Employee's Attachment on a Screen",
     *     operationId="get-an-employees-attachment-on-a-screen",
     *     description="This endpoint allows you to get one of an employee's attachments on a particular PIM screen.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="screen",
     *         description="Specify the name of the desired PIM screen",
     *         @OA\Schema(type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *     ),
     *     @OA\PathParameter(
     *         name="id",
     *         description="Specify the numerical ID of the attachment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeAttachmentModel"
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="empNumber", description="The employee number given in the request", type="integer"),
     *                 @OA\Property(property="screen", description="The PIM screen name given in the request", type="string", example="personal")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function getOne(): EndpointResult
    {
        //list($empNumber, $screen, $id) = $this->getUrlAttributes();
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            self::PARAMETER_ATTACHMENT_ID
        );
        /*$partialEmployeeAttachment = $this->getEmployeeAttachmentService()->getEmployeeAttachmentDetails($empNumber, $id, $screen);
        $this->throwRecordNotFoundExceptionIfNotExist($partialEmployeeAttachment, PartialEmployeeAttachment::class);*/

        $employeeAttachment = $this->getEmployeeAttachmentService()->getEmployeeAttachment(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'), $id);
        $candidateAttachment = !$employeeAttachment ? new RecruitmentAttachment() : new RecruitmentAttachment($employeeAttachment->getAttachId(), $employeeAttachment->getFilename(), $employeeAttachment->getFileType(), (string) $employeeAttachment->getSize(), null, null);

        return new EndpointResourceResult(CandidateAttachmentModel::class, $candidateAttachment);
    }

    /**
     * @return array
     */
    private function getUrlAttributes(): array
    {
        $empNumber = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_EMP_NUMBER
        );
        $screen = $this->getRequestParams()->getString(RequestParams::PARAM_TYPE_ATTRIBUTE, self::PARAMETER_SCREEN);
        $id = $this->getRequestParams()->getInt(
            RequestParams::PARAM_TYPE_ATTRIBUTE,
            CommonParams::PARAMETER_ID
        );
        return [$empNumber, $screen, $id];
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetOne(): ParamRuleCollection
    {
        /*return new ParamRuleCollection(
            new ParamRule(CommonParams::PARAMETER_ID, new Rule(Rules::POSITIVE)),
            $this->getEmpNumberRule(),
            $this->getScreenRule(),
        );*/
        return new ParamRuleCollection(
            new ParamRule(self::PARAMETER_ATTACHMENT_ID),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v2/pim/employees/{empNumber}/screen/{screen}/attachments",
     *     tags={"PIM/Employee Attachment"},
     *     summary="List an Employee's Attachments on a Screen",
     *     operationId="list-an-employees-attachments-on-a-screen",
     *     description="This endpoint allows you the list an employee's attachments for a particular PIM screen.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="screen",
     *         description="Specify the name of the desired PIM screen",
     *         @OA\Schema(type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/sortOrder"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *     @OA\Parameter(ref="#/components/parameters/offset"),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Pim-EmployeeAttachmentModel")
     *             ),
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="empNumber", description="The employee number given in the request", type="integer"),
     *                 @OA\Property(property="screen", description="The PIM screen name given in the request", type="string", example="personal")
     *             )
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     */
    public function getAll(): EndpointCollectionResult
    {
        list($empNumber, $screen) = $this->getUrlAttributes();
        $employeeAttachments = $this->getEmployeeAttachmentService()->getEmployeeAttachments($empNumber, $screen);

        return new EndpointCollectionResult(
            EmployeeAttachmentModel::class,
            $employeeAttachments,
            new ParameterBag(
                [
                    CommonParams::PARAMETER_EMP_NUMBER => $empNumber,
                    self::PARAMETER_SCREEN => $screen,
                    CommonParams::PARAMETER_TOTAL => count($employeeAttachments),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForGetAll(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
            $this->getEmpNumberRule(),
            $this->getScreenRule(),
            ...$this->getSortingAndPaginationParamsRules()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v2/pim/attachments",
     *     tags={"PIM/Employee Attachment"},
     *     summary="Add an Attachment to an Employee on a Screen",
     *     operationId="add-an-attachment-to-an-employee-on-a-screen",
     *     description="This endpoint allows you to upload an attachment for a particular employee on a particular PIM screen.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="screen",
     *         description="Specify the name of the desired PIM screen",
     *         @OA\Schema(type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="description",
     *                 description="Specify the description of the attachment",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeAttachmentAPI::PARAM_RULE_DESCRIPTION_MAX_LENGTH
     *             ),
     *             @OA\Property(property="attachment", ref="#/components/schemas/Base64Attachment"),
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeAttachmentModel"
     *             ),
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="empNumber", description="The employee number given in the request", type="integer"),
     *                 @OA\Property(property="screen", description="The PIM screen name given in the request", type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *             )
     *         )
     *     ),
     * )
     *
     * @inheritDoc
     */
    public function createAndGetId($empNumber, $screen = 'personal', $base64Attachment, $description = null): EndpointResourceResult
{
    try {
        // Logique de création
        $employeeAttachment = new EmployeeAttachment();
        $employeeAttachment->getDecorator()->setEmployeeByEmpNumber(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'));
        $employeeAttachment->setScreen($screen);
        $employeeAttachment->setDescription($description);
        $this->setAttachmentAttributesForCreateAndGetId($employeeAttachment, $base64Attachment, $empNumber, $screen);

        $newEmployeeAttachment = $this->getEmployeeAttachmentService()->saveEmployeeAttachment($employeeAttachment);
        $attachmentId = $newEmployeeAttachment->getAttachId();
        var_dump($attachmentId);
        echo '<pre>';
        print_r('Attachment ID: ' . $newEmployeeAttachment->getAttachId());
        print_r('attachmentId: ' . $attachmentId);
        echo '</pre>';

        $result = new EndpointResourceResult(
            EmployeeAttachmentModel::class,
            $this->getPartialEmployeeAttachment($employeeAttachment),
            new ParameterBag([
                CommonParams::PARAMETER_EMP_NUMBER => $empNumber,
                self::PARAMETER_SCREEN => $screen,
                'attachmentId' => $attachmentId
            ])
        );
        var_dump($result);

        // Assurez-vous que le résultat est bien retourné
        return $result;
    } catch (\Exception $e) {
        // Capture et retour de l'erreur
        echo '<pre>';
        print_r('Error: ' . $e->getMessage());
        echo '</pre>';
        return new Response('Internal Server Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}



    public function create(): EndpointResourceResult
    {
        //list($empNumber, $screen) = $this->getUrlAttributes();
        $attachment = $this->getRequestParams()->getAttachment(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_ATTACHMENT
        );
        /*$description = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_DESCRIPTION
        );*/

        $employeeAttachment = new EmployeeAttachment();
        $employeeAttachment->getDecorator()->setEmployeeByEmpNumber(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'));
        $employeeAttachment->setScreen("personal");
        //$employeeAttachment->setDescription($description);
        $this->setAttachmentAttributes($employeeAttachment, $attachment);

        $this->getEmployeeAttachmentService()->saveEmployeeAttachment($employeeAttachment);

        return new EndpointResourceResult(
            EmployeeAttachmentModel::class,
            $this->getPartialEmployeeAttachment($employeeAttachment),
            new ParameterBag(
                [
                    CommonParams::PARAMETER_EMP_NUMBER => $empNumber,
                    self::PARAMETER_SCREEN => $screen,
                ]
            )
        );
    }

    /**
     * @param EmployeeAttachment $employeeAttachment
     * @param Base64Attachment $base64Attachment
     * @return EmployeeAttachment
     */
    private function setAttachmentAttributesForCreateAndGetId(
        EmployeeAttachment $employeeAttachment,
        Base64Attachment $base64Attachment,
        int $empNumber,
        string $screen
    ): EmployeeAttachment {

        $size = (int)$base64Attachment->getSize();
    
        if (!is_int($size)) {
            throw new \Exception('Attachment size must be an integer');
        }
        if ($base64Attachment->getSize() > 10485760) {
            throw new Exception('File size exceeds the limit of 10 MB.');
        }

        $content = $base64Attachment->getContent();
        if (is_string($content)) {
            $employeeAttachment->setAttachment($content);
        } else {
            throw new \Exception('Attachment content must be a valid binary string');
        }
    
        $employeeAttachment->setFilename($base64Attachment->getFilename());
        $employeeAttachment->setSize($size);
        $employeeAttachment->setFileType($base64Attachment->getFileType());
    
        $employeeAttachment->setAttachedBy($empNumber);
        $employeeAttachment->setAttachedByName($screen);
    
        return $employeeAttachment;
    }
    
     /**
     * @param EmployeeAttachment $employeeAttachment
     * @param Base64Attachment $base64Attachment
     * @return EmployeeAttachment
     */
    private function setAttachmentAttributes(
        EmployeeAttachment $employeeAttachment,
        Base64Attachment $base64Attachment
    ): EmployeeAttachment {
        $employeeAttachment->setFilename($base64Attachment->getFilename());
        $employeeAttachment->setSize($base64Attachment->getSize());
        $employeeAttachment->setFileType($base64Attachment->getFileType());
        $employeeAttachment->setAttachment($base64Attachment->getContent());

        $employeeAttachment->setAttachedBy($this->getUserRoleManager()->getUser()->getId());
        $employeeAttachment->setAttachedByName($this->getUserRoleManager()->getUser()->getUserName());
        return $employeeAttachment;
    }

    
    

    /**
     * @inheritDoc
     */
    public function getValidationRuleForCreate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
            $this->getEmpNumberRule(),
            $this->getScreenRule(),
            $this->getAttachmentRule(),
            $this->getValidationDecorator()->notRequiredParamRule($this->getDescriptionRule(), true),
        );
    }

    /**
     * @return ParamRule
     */
    private function getEmpNumberRule(): ParamRule
    {
        return new ParamRule(
            CommonParams::PARAMETER_EMP_NUMBER,
            new Rule(Rules::IN_ACCESSIBLE_EMP_NUMBERS)
        );
    }

    /**
     * @return ParamRule
     */
    private function getScreenRule(): ParamRule
    {
        return new ParamRule(
            self::PARAMETER_SCREEN,
            new Rule(Rules::IN, [EmployeeAttachment::SCREENS])
        );
    }

    /**
     * @return ParamRule
     */
    private function getAttachmentRule(): ParamRule
    {
        return new ParamRule(
            self::PARAMETER_ATTACHMENT,
            new Rule(
                Rules::BASE_64_ATTACHMENT,
                [null, null, self::PARAM_RULE_ATTACHMENT_FILE_NAME_MAX_LENGTH]
            )
        );
    }

    /**
     * @return ParamRule
     */
    private function getDescriptionRule(): ParamRule
    {
        return new ParamRule(
            self::PARAMETER_DESCRIPTION,
            new Rule(
                Rules::LENGTH,
                [null, self::PARAM_RULE_DESCRIPTION_MAX_LENGTH]
            )
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v2/pim/employees/{empNumber}/screen/{screen}/attachments/{id}",
     *     tags={"PIM/Employee Attachment"},
     *     summary="Update an Employee's Attachment on a Screen",
     *     operationId="update-an-employees-attachment-on-a-screen",
     *     description="This endpoint allows you to update an employee's attachment on a particular PIM screen.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="screen",
     *         description="Specify the name of the desired PIM screen",
     *         @OA\Schema(type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *     ),
     *     @OA\PathParameter(
     *         name="id",
     *         description="Specify the numerical ID of the attachment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="description",
     *                 description="Specify the description of the attachment",
     *                 type="string",
     *                 maxLength=OrangeHRM\Pim\Api\EmployeeAttachmentAPI::PARAM_RULE_DESCRIPTION_MAX_LENGTH
     *             ),
     *             @OA\Property(property="attachment", ref="#/components/schemas/Base64Attachment"),
     *         )
     *     ),
     *     @OA\Response(response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Pim-EmployeeAttachmentModel"
     *             ),
     *             @OA\Property(property="meta",
     *                 type="object",
     *                 @OA\Property(property="empNumber", description="The employee number given in the request", type="integer"),
     *                 @OA\Property(property="screen", description="The PIM screen name given in the request", type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/RecordNotFound")
     * )
     *
     * @inheritDoc
     */
    public function update(): EndpointResourceResult
    {
        list($empNumber, $screen, $id) = $this->getUrlAttributes();
        $attachment = $this->getRequestParams()->getAttachmentOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_ATTACHMENT
        );
        $description = $this->getRequestParams()->getStringOrNull(
            RequestParams::PARAM_TYPE_BODY,
            self::PARAMETER_DESCRIPTION,
            null,
            false
        );

        $employeeAttachment = $this->getEmployeeAttachmentService()->getEmployeeAttachment($empNumber, $id, $screen);
        $this->throwRecordNotFoundExceptionIfNotExist($employeeAttachment, EmployeeAttachment::class);

        if (!is_null($description)) {
            $employeeAttachment->setDescription($description);
        }

        if ($attachment) {
            $this->setAttachmentAttributes($employeeAttachment, $attachment);
        }

        $this->getEmployeeAttachmentService()->saveEmployeeAttachment($employeeAttachment);

        return new EndpointResourceResult(
            EmployeeAttachmentModel::class,
            $this->getPartialEmployeeAttachment($employeeAttachment),
            new ParameterBag(
                [
                    CommonParams::PARAMETER_EMP_NUMBER => $empNumber,
                    self::PARAMETER_SCREEN => $screen,
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForUpdate(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(CommonParams::PARAMETER_ID, new Rule(Rules::POSITIVE)),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
            $this->getEmpNumberRule(),
            $this->getScreenRule(),
            $this->getValidationDecorator()->notRequiredParamRule($this->getAttachmentRule()),
            $this->getValidationDecorator()->notRequiredParamRule($this->getDescriptionRule(), true),
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/pim/employees/{empNumber}/screen/{screen}/attachments",
     *     tags={"PIM/Employee Attachment"},
     *     summary="Delete an Employee's Attachments on a Screen",
     *     operationId="delete-an-employees-attachments-on-a-screen",
     *     description="This endpoint allows you to delete an employee's attachment on a particular PIM screen.",
     *     @OA\PathParameter(
     *         name="empNumber",
     *         description="Specify the employee number of the desired employee",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\PathParameter(
     *         name="screen",
     *         description="Specify the name of the desired PIM screen",
     *         @OA\Schema(type="string", enum=OrangeHRM\Entity\EmployeeAttachment::SCREENS)
     *     ),
     *     @OA\RequestBody(ref="#/components/requestBodies/DeleteRequestBody"),
     *     @OA\Response(response="200", ref="#/components/responses/DeleteResponse")
     * )
     *
     * @inheritDoc
     */
    public function delete(): EndpointResourceResult
    {
        list($empNumber, $screen) = $this->getUrlAttributes();
        $ids = $this->getRequestParams()->getArray(RequestParams::PARAM_TYPE_BODY, CommonParams::PARAMETER_IDS);
        $this->getEmployeeAttachmentService()->deleteEmployeeAttachments($empNumber, $screen, $ids);
        return new EndpointResourceResult(ArrayModel::class, $ids);
    }

    /**
     * @inheritDoc
     */
    public function getValidationRuleForDelete(): ParamRuleCollection
    {
        return new ParamRuleCollection(
            new ParamRule(
                CommonParams::PARAMETER_IDS,
                new Rule(Rules::ARRAY_TYPE)
            ),
            new ParamRule(
                self::PARAMETER_THEME,
                new Rule(Rules::STRING_TYPE)
            ),
            $this->getEmpNumberRule(),
            $this->getScreenRule(),
        );
    }

    /**
     * @param EmployeeAttachment $employeeAttachment
     * @return PartialEmployeeAttachment
     */
    private function getPartialEmployeeAttachment(EmployeeAttachment $employeeAttachment): PartialEmployeeAttachment
    {
        return new PartialEmployeeAttachment(
            $employeeAttachment->getAttachId(),
            $employeeAttachment->getDescription(),
            $employeeAttachment->getFilename(),
            $employeeAttachment->getSize(),
            $employeeAttachment->getFileType(),
            $employeeAttachment->getAttachedBy(),
            $employeeAttachment->getAttachedByName(),
            $employeeAttachment->getAttachedTime()
        );
    }
}
