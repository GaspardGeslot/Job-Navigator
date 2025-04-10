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

namespace OrangeHRM\Recruitment\Controller\File;

use OrangeHRM\Core\Controller\AbstractFileController;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;
use OrangeHRM\Entity\EmployeeAttachment;
use OrangeHRM\Pim\Service\EmployeeAttachmentService;
use OrangeHRM\Recruitment\Traits\Service\RecruitmentAttachmentServiceTrait;

class CandidateAttachment extends AbstractFileController
{
    use RecruitmentAttachmentServiceTrait;

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

    public function handle(Request $request): Response
    {
        $attachmentId = $request->attributes->get('attachmentId');
        $response = $this->getResponse();

        if ($attachmentId) {
            $employeeAttachment = $this->getEmployeeAttachmentService()->getEmployeeAttachment(getenv('HEDWIGE_CANDIDATURE_EMPLOYEE_ID'), $attachmentId);
        
            if($employeeAttachment) {
                $this->setCommonHeadersToResponse(
                    $employeeAttachment->getFileName(),
                    $employeeAttachment->getFileType(),
                    $employeeAttachment->getSize(),
                    $response
                );
                $response->setContent($employeeAttachment->getDecorator()->getAttachment());
                return $response;
                
                /*$attachmentCandidate = $this->getRecruitmentAttachmentService()
                    ->getRecruitmentAttachmentDao()
                    ->getCandidateAttachmentByCandidateId($candidateId);
                
                if ($candidateAttachment instanceof \OrangeHRM\Entity\CandidateAttachment) {
                    $this->setCommonHeadersToResponse(
                        $candidateAttachment->getFileName(),
                        $candidateAttachment->getFileType(),
                        $candidateAttachment->getFileSize(),
                        $response
                    );
                    $response->setContent($employeeAttachment->getDecorator()->getAttachment());
                    return $response;
                }*/
            }
        }
        return $this->handleBadRequest();
    }
}
