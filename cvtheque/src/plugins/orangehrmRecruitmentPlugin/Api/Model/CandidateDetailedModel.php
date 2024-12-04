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

namespace OrangeHRM\Recruitment\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\Candidate;
use OrangeHRM\Entity\Vacancy;
use OrangeHRM\Recruitment\Traits\Service\RecruitmentAttachmentServiceTrait;

/**
 * @OA\Schema(
 *     schema="Recruitment-CandidateDetailedModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="firstName", type="string"),
 *     @OA\Property(property="middleName", type="string"),
 *     @OA\Property(property="lastName", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="contactNumber", type="string"),
 *     @OA\Property(property="comment", type="string"),
 *     @OA\Property(property="keywords", type="string"),
 *     @OA\Property(property="modeOfApplication", type="string"),
 *     @OA\Property(property="dateOfApplication", type="string", format="date"),
 *     @OA\Property(
 *         property="vacancy",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="status", type="boolean"),
 *         @OA\Property(
 *             property="jobTitle",
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="isDeleted", type="boolean")
 *         ),
 *         @OA\Property(
 *             property="hiringManager",
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="firstName", type="string"),
 *             @OA\Property(property="middleName", type="string"),
 *             @OA\Property(property="lastName", type="string"),
 *             @OA\Property(property="terminationId", type="integer", nullable=true)
 *         )
 *     ),
 *     @OA\Property(property="status", type="array",
 *         @OA\Items(
 *             @OA\Property(property="id", type="string"),
 *             @OA\Property(property="lable", type="string")
 *         )
 *     ),
 *     @OA\Property(property="hasAttachment", type="boolean"),
 *     @OA\Property(property="consentToKeepData", type="boolean")
 * )
 */
class CandidateDetailedModel implements Normalizable
{
    use RecruitmentAttachmentServiceTrait;

    /**
     * @var Candidate
     */
    private Candidate $candidate;

    public function __construct(Candidate $candidate)
    {
        $this->candidate = $candidate;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $candidateVacancies = $this->candidate->getCandidateVacancy();
        $candidateVacancy = !empty($candidateVacancies) ? $candidateVacancies[0] : null;
        $hasCandidateAttachment = $this->getRecruitmentAttachmentService()
            ->getRecruitmentAttachmentDao()
            ->hasCandidateAttachmentByCandidateId($this->candidate->getId());
        /**
         * @var Vacancy
         */
        $vacancy = !is_null($candidateVacancy) ? $candidateVacancy->getVacancy() : null;

        return [
            'birthday' => $this->candidate->getDecorator()->getBirthday(),
            'candidatureStatus' => $this->candidate->getCandidatureStatus(),
            'certificates' => $this->candidate->getCertificates(),
            'city' => $this->candidate->getCity(),
            'comment' => $this->candidate->getComment(),
            'consentToKeepData' => $this->candidate->isConsentToKeepData(),
            'contactNumber' => $this->candidate->getContactNumber(),
            'country' => $this->candidate->getCountry(),
            'courseStart' => $this->candidate->getCourseStart(),
            'dateOfApplication' => $this->candidate->getDecorator()->getDateOfApplication(),
            'drivingLicense' => $this->candidate->getDrivingLicense(),
            'email' => $this->candidate->getEmail(),
            'firstName' => $this->candidate->getFirstName(),
            'gender' => $this->candidate->getGender(),
            'hasAttachment' => $hasCandidateAttachment,
            'hasPersonalVehicule' => $this->candidate->getHasPersonalVehicule(),
            'id' => $this->candidate->getId(),
            'jobTitle' => $this->candidate->getJobTitle(),
            'jobs' => $this->candidate->getJobs(),
            'keywords' => $this->candidate->getKeywords(),
            'lastName' => $this->candidate->getLastName(),
            'leadId' => $this->candidate->getLeadId(),
            'matchingId' => $this->candidate->getMatchingId(),
            'middleName' => $this->candidate->getMiddleName(),
            'motivation' => $this->candidate->getMotivation(),
            'need' => $this->candidate->getNeed(),
            'professionalExperience' => $this->candidate->getProfessionalExperience(),
            'province' => $this->candidate->getProvince(),
            'resume' => $this->candidate->getResume(),
            'salary' => $this->candidate->getSalary(),
            'specificProfessionalExperience' => $this->candidate->getSpecificProfessionalExperience(),
            'skills' => $this->candidate->getCandidateSkills(),
            'status' => is_null($candidateVacancy) ? null :
                $candidateVacancy->getDecorator()->getCandidateVacancyStatus(),
            'street1' => $this->candidate->getStreet1(),
            'studyLevel' => $this->candidate->getStudyLevel(),
            'vacancy' => is_null($vacancy) ? null :
                [
                    'id' => $vacancy->getId(),
                    'name' => $vacancy->getName(),
                    'status' => $vacancy->getStatus(),
                    'jobTitle' => [
                        'id' => $vacancy->getJobTitle()->getId(),
                        'title' => $vacancy->getJobTitle()->getJobTitleName(),
                        'isDeleted' => $vacancy->getJobTitle()->isDeleted(),
                    ],
                    'hiringManager' => is_null($vacancy->getHiringManager()) ? null : [
                        'id' => $vacancy->getHiringManager()->getEmpNumber(),
                        'firstName' => $vacancy->getHiringManager()->getFirstName(),
                        'middleName' => $vacancy->getHiringManager()->getMiddleName(),
                        'lastName' => $vacancy->getHiringManager()->getLastName(),
                        'terminationId' => is_null($vacancy->getHiringManager()->getEmployeeTerminationRecord()) ?
                            null :
                            $vacancy->getHiringManager()->getEmployeeTerminationRecord()->getId()
                        ,
                    ]
                    ],
            'zipcode' => $this->candidate->getZipcode(),
            //'modeOfApplication' => $this->candidate->getModeOfApplication(),
        ];
    }
}
