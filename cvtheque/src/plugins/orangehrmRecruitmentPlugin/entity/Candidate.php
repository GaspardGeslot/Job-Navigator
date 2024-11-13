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

namespace OrangeHRM\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use OrangeHRM\Entity\Decorator\CandidateDecorator;
use OrangeHRM\Entity\Decorator\DecoratorTrait;

/**
 * @method CandidateDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_job_candidate")
 * @ORM\Entity
 *
 */
class Candidate
{
    use DecoratorTrait;

    public const MODE_OF_APPLICATION_MANUAL = 1;
    public const MODE_OF_APPLICATION_ONLINE = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", length=13)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=30)
     */
    private string $firstName;

    /**
     * @var string|null
     * @ORM\Column(name="middle_name", type="string", length=30, nullable=true)
     */
    private ?string $middleName = null;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=30)
     */
    private string $lastName;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=100)
     */
    private string $email;

    /**
     * @var string|null
     * @ORM\Column(name="contact_number", type="string", length=30, nullable=true)
     */
    private ?string $contactNumber = null;

    /**
     * @var int|null
     * @ORM\Column(name="lead_id", type="int", length=13, nullable=true)
     */
    private ?int $leadId = null;

    /**
     * @var string|null
     * @ORM\Column(name="job_title", type="string", length=300, nullable=true)
     */
    private ?string $jobTitle = null;

    /**
     * @var string|null
     * @ORM\Column(name="jobs", type="string", length=1024, nullable=true)
     */
    private ?string $jobs = null;

    /**
     * @var string
     *
     * @ORM\Column(name="emp_course_start", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $courseStart = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_study_level", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $studyLevel = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_salary", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $salary = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_driving_license", type="string", length=1024, nullable=true, options={"default" : ""})
     */
    private ?string $drivingLicense = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_need", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $need = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="emp_gender", type="smallint", nullable=true)
     */
    private ?int $gender = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="emp_birthday", type="date", nullable=true)
     */
    private ?DateTime $birthday = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_street1", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $street1 = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="city_code", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $city = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="coun_code", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $country = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_zipcode", type="string", length=20, nullable=true)
     */
    private ?string $zipcode = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="provin_code", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $province = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_professional_experience", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $professionalExperience = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_specific_professional_experience", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $specificProfessionalExperience = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_has_personal_vehicule", type="boolean", nullable=true)
     */
    private ?bool $hasPersonalVehicule;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_motivation", type="string", nullable=true, options={"default" : ""})
     */
    private ?string $motivation = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_certificates", type="string", nullable=true, options={"default" : ""})
     */
    private ?string $certificates = '';

    /**
     * @var int
     * @ORM\Column(name="resume", type="integer", nullable=true, length=4)
     * @deprecated
     */
    private ?int $resume = null;
    
    /**
     * @var int
     * @ORM\Column(name="status", type="integer", length=4)
     * @deprecated
     */
    private int $status = 1;

    /**
     * @var string|null
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private ?string $comment = null;

    /**
     * @var int
     * @ORM\Column(name="mode_of_application", type="integer", length=4)
     */
    private int $modeOfApplication;

    /**
     * @var DateTime
     * @ORM\Column(name="date_of_application", type="date")
     */
    private DateTime $dateOfApplication;

    /**
     * @var int|null
     * @ORM\Column(name="cv_file_id", type="integer", length=13, nullable=true)
     * @deprecated
     */
    private ?int $cvFileId = null;

    /**
     * @var string|null
     * @ORM\Column(name="cv_text_version", type="text", nullable=true)
     * @deprecated
     */
    private ?string $cvTextVersion = null;

    /**
     * @var string|null
     * @ORM\Column(name="keywords", type="string", length=255, nullable=true)
     */
    private ?string $keywords = null;

    /**
     * @var Employee|null
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee", inversedBy="candidates", cascade={"persist"})
     * @ORM\JoinColumn(name="added_person", referencedColumnName="emp_number", nullable=true)
     */
    private ?Employee $addedPerson = null;

    /**
     * @var bool
     * @ORM\Column(name="consent_to_keep_data", type="boolean")
     */
    private bool $consentToKeepData = false;

    /**
     * @var iterable|CandidateVacancy[]
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\CandidateVacancy", mappedBy="candidate")
     */
    private iterable $candidateVacancy;

    /**
     * @var iterable|CandidateAttachment[]
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\CandidateAttachment", mappedBy="candidate")
     */
    private iterable $candidateAttachment;

    /**
     * @var iterable|CandidateHistory[]
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\CandidateHistory", mappedBy="candidate")
     */
    private iterable $candidateHistory;

    public function __construct()
    {
        $this->candidateVacancy = new ArrayCollection();
        $this->candidateAttachment = new ArrayCollection();
        $this->candidateHistory = new ArrayCollection();
    }

    /**
     * @param mixed $leadInfo
     */
    public function setLeadInfo(mixed $leadInfo): void 
    {
        $this->setId($leadInfo['id']);
        $this->setLeadId($leadInfo['id']);
        $this->setJobTitle($leadInfo['job'] ?? '');
        $this->setJobs(json_encode($leadInfo['jobs'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setFirstName($leadInfo['firstName'] ?? '');
        $this->setLastName($leadInfo['lastName'] ?? '');
        $this->setEmail($leadInfo['email'] ?? '');
        $this->setContactNumber($leadInfo['phoneNumber'] ?? '');
        $this->setDateOfApplication(array_key_exists('date', $leadInfo) && $leadInfo['date'] != null ? new \DateTime($leadInfo['date']) : null);
        $this->setNeed($leadInfo['need'] ?? '');
        $this->setStudyLevel($leadInfo['studyLevel'] ?? '');
        $this->setDrivingLicense(json_encode($leadInfo['drivingLicenses'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setSalary($leadInfo['salaryExpectation'] ?? '');
        $this->setCourseStart($leadInfo['courseStart'] ?? '');   
        $this->setBirthday(array_key_exists('birthDate', $leadInfo) && $leadInfo['birthDate'] != null ? new \DateTime($leadInfo['birthDate']) : null);
        $this->setStreet1($leadInfo['address'] ?? '');
        $this->setCity($leadInfo['city'] ?? '');
        $this->setZipcode($leadInfo['postalCode'] ?? '');
        $this->setProfessionalExperience($leadInfo['professionalExperience'] ?? '');
        $this->setSpecificProfessionalExperience($leadInfo['specificProfessionalExperience'] ?? '');
        $this->setHasPersonalVehicule($leadInfo['hasPersonalVehicle'] ?? false);
        $this->setMotivation($leadInfo['motivation'] ?? '');
        $this->setCertificates(json_encode($leadInfo['certificates'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setResume(array_key_exists('resume', $leadInfo) && $leadInfo['resume'] != null ? (int) $leadInfo['resume'] : null);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @param string|null $middleName
     */
    public function setMiddleName(?string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getContactNumber(): ?string
    {
        return $this->contactNumber;
    }

    /**
     * @param string|null $contactNumber
     */
    public function setContactNumber(?string $contactNumber): void
    {
        $this->contactNumber = $contactNumber;
    }

    /**
     * @return string|null
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @param string|null $jobTitle
     */
    public function setJobTitle(?string $jobTitle): void
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return string|null
     */
    public function getJobs(): ?string
    {
        return $this->jobs;
    }

    /**
     * @param string|null $jobs
     */
    public function setJobs(?string $jobs): void
    {
        $this->jobs = $jobs;
    }

    /**
     * @return string
     */
    public function getStudyLevel(): string
    {
        return $this->studyLevel;
    }


    /**
     * @param string $studyLevel
     */
    public function setStudyLevel(string $studyLevel): void
    {
        $this->studyLevel = $studyLevel;
    }

    /**
     * @return string
     */
    public function getSalary(): string
    {
        return $this->salary;
    }


    /**
     * @param string $salary
     */
    public function setSalary(string $salary): void
    {
        $this->salary = $salary;
    }

    /**
     * @return string
     */
    public function getDrivingLicense(): string
    {
        return $this->drivingLicense;
    }

    /**
     * @param string $drivingLicense
     */
    public function setDrivingLicense(string $drivingLicense): void
    {
        $this->drivingLicense = $drivingLicense;
    }

    /**
     * @return string
     */
    public function getCourseStart(): string
    {
        return $this->courseStart;
    }

    /**
     * @param string $courseStart
     */
    public function setCourseStart(string $courseStart): void
    {
        $this->courseStart = $courseStart;
    }

    /**
     * @return string
     */
    public function getNeed(): string
    {
        return $this->need;
    }

    /**
     * @param string $need
     */
    public function setNeed(string $need): void
    {
        $this->need = $need;
    }

    /**
     * @return int|null
     */
    public function getGender(): ?int
    {
        return $this->gender;
    }

    /**
     * @param int|null $gender
     */
    public function setGender(?int $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return DateTime|null
     */
    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    /**
     * @param DateTime|null $birthday
     */
    public function setBirthday(?DateTime $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string|null
     */
    public function getStreet1(): ?string
    {
        return $this->street1;
    }

    /**
     * @param string|null $street1
     */
    public function setStreet1(?string $street1): void
    {
        $this->street1 = $street1;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getProvince(): ?string
    {
        return $this->province;
    }

    /**
     * @param string|null $province
     */
    public function setProvince(?string $province): void
    {
        $this->province = $province;
    }

    /**
     * @return string|null
     */
    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    /**
     * @param string|null $zipcode
     */
    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string|null
     */
    public function getProfessionalExperience(): ?string
    {
        return $this->professionalExperience;
    }

    /**
     * @param string|null $professionalExperience
     */
    public function setProfessionalExperience(?string $professionalExperience): void
    {
        $this->professionalExperience = $professionalExperience;
    }

    /**
     * @return string|null
     */
    public function getSpecificProfessionalExperience(): ?string
    {
        return $this->specificProfessionalExperience;
    }

    /**
     * @param string|null $specificProfessionalExperience
     */
    public function setSpecificProfessionalExperience(?string $specificProfessionalExperience): void
    {
        $this->specificProfessionalExperience = $specificProfessionalExperience;
    }

    /**
     * @return bool|null
     */
    public function getHasPersonalVehicule(): ?bool
    {
        return $this->hasPersonalVehicule;
    }

    /**
     * @param bool|null $hasPersonalVehicule
     */
    public function setHasPersonalVehicule(?bool $hasPersonalVehicule): void
    {
        $this->hasPersonalVehicule = $hasPersonalVehicule;
    }

    /**
     * @return string|null
     */
    public function getMotivation(): ?string
    {
        return $this->motivation;
    }

    /**
     * @param string|null $motivation
     */
    public function setMotivation(?string $motivation): void
    {
        $this->motivation = $motivation;
    }

    /**
     * @return string|null
     */
    public function getCertificates(): ?string
    {
        return $this->certificates;
    }

    /**
     * @param string|null $certificates
     */
    public function setCertificates(?string $certificates): void
    {
        $this->certificates = $certificates;
    }

    /**
     * @return int|null
     */
    public function getResume(): ?int
    {
        return $this->resume;
    }

    /**
     * @param int|null $resume
     */
    public function setResume(?int $resume): void
    {
        $this->resume = $resume;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getLeadId(): int
    {
        return $this->leadId;
    }

    /**
     * @param int $leadId
     * @deprecated
     */
    public function setLeadId(int $leadId): void
    {
        $this->leadId = $leadId;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @deprecated
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return int
     */
    public function getModeOfApplication(): int
    {
        return $this->modeOfApplication;
    }

    /**
     * @param int $modeOfApplication
     */
    public function setModeOfApplication(int $modeOfApplication): void
    {
        $this->modeOfApplication = $modeOfApplication;
    }

    /**
     * @return DateTime
     */
    public function getDateOfApplication(): DateTime
    {
        return $this->dateOfApplication;
    }

    /**
     * @param DateTime $dateOfApplication
     */
    public function setDateOfApplication(DateTime $dateOfApplication): void
    {
        $this->dateOfApplication = $dateOfApplication;
    }

    /**
     * @return int|null
     * @deprecated
     */
    public function getCvFileId(): ?int
    {
        return $this->cvFileId;
    }

    /**
     * @param int|null $cvFileId
     * @deprecated
     */
    public function setCvFileId(?int $cvFileId): void
    {
        $this->cvFileId = $cvFileId;
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getCvTextVersion(): ?string
    {
        return $this->cvTextVersion;
    }

    /**
     * @param string|null $cvTextVersion
     * @deprecated
     */
    public function setCvTextVersion(?string $cvTextVersion): void
    {
        $this->cvTextVersion = $cvTextVersion;
    }

    /**
     * @return string|null
     */
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    /**
     * @param string|null $keywords
     */
    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @return Employee|null
     */
    public function getAddedPerson(): ?Employee
    {
        return $this->addedPerson;
    }

    /**
     * @param Employee|null $addedPerson
     */
    public function setAddedPerson(?Employee $addedPerson): void
    {
        $this->addedPerson = $addedPerson;
    }

    /**
     * @return bool
     */
    public function isConsentToKeepData(): bool
    {
        return $this->consentToKeepData;
    }

    /**
     * @param bool $consentToKeepData
     */
    public function setConsentToKeepData(bool $consentToKeepData): void
    {
        $this->consentToKeepData = $consentToKeepData;
    }

    /**
     * @return CandidateVacancy[]|iterable
     */
    public function getCandidateVacancy()
    {
        return $this->candidateVacancy;
    }

    /**
     * @return CandidateAttachment[]|iterable
     */
    public function getCandidateAttachment()
    {
        return $this->candidateAttachment;
    }

    /**
     * @return iterable|CandidateHistory[]
     */
    public function getCandidateHistory()
    {
        return $this->candidateHistory;
    }
}
