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
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\EmployeeDecorator;

/**
 * @method EmployeeDecorator getDecorator()
 *
 * @ORM\Table(name="hs_hr_employee")
 * @ORM\Entity
 * @ORM\EntityListeners({"OrangeHRM\Entity\Listener\EmployeeListener"})
 */
class Employee
{
    use DecoratorTrait;

    public const GENDER_MALE = 1;
    public const GENDER_FEMALE = 2;
    public const GENDER_OTHER = 3;

    public const MARITAL_STATUS_SINGLE = 'Single';
    public const MARITAL_STATUS_MARRIED = 'Married';
    public const MARITAL_STATUS_OTHER = 'Other';

    public const STATE_ACTIVE = 'ACTIVE';
    public const STATE_TERMINATED = 'TERMINATED';
    public const STATE_NOT_EXIST = 'NOT_EXIST';

    public const GENDER = [
        self::GENDER_MALE => 'Male',
        self::GENDER_FEMALE => 'Female',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="emp_number", type="integer", length=4)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $empNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="employee_id", type="string", length=50, nullable=true)
     */
    private ?string $employeeId = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="profile_id", type="integer", length=4, nullable=true, options={"default" : -1})
     */
    private ?int $profileId;

    /**
     * @var string
     *
     * @ORM\Column(name="emp_course_start", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $courseStart = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_study_level", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $studyLevel = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_salary", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $salary = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_driving_license", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $drivingLicense = '';
    
    /**
     * @var string
     *
     * @ORM\Column(name="emp_motivation ", type="string", length=2000, nullable=true, options={"default" : ""})
     */
    private ?string $motivation = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_need", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $need = '';

    /**
     * @var int
     *
     * @ORM\Column(name="emp_resume", type="integer", length=100, nullable=true)
     */
    private ?int $resume;

    /**
     * @var int
     *
     * @ORM\Column(name="emp_attachment", type="integer", length=100, nullable=true)
     */
    private ?int $attachment;

    /**
     * @var string
     *
     * @ORM\Column(name="emp_jobs", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $jobs = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_roles", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $roles = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_skills", type="string", length=400, nullable=true, options={"default" : ""})
     */
    private ?string $emp_skills = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_siret", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companySiret = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_workforce", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyWorkforce = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_naf_code", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyNafCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_website", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyWebsite = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_presentation", type="string", length=1000, nullable=true, options={"default" : ""})
     */
    private ?string $companyPresentation = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_logo", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyLogo = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_id", type="integer", length=4, nullable=true, options={"default" : -1})
     */
    private ?string $companyId;

    /**
     * @var string
     *
     * @ORM\Column(name="company_location", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyLocation = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_phone_number_contact", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyPhoneNumberContact = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_email_contact", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyEmailContact = '';

    /**
     * @var string
     *
     * @ORM\Column(name="company_matching_job_title", type="string", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $companyMatchingJobTitle = '';

    /**
     * @var int
     * @ORM\Column(name="matching_id", type="integer", nullable=true)
     */
    private ?int $matchingId = null;

    /**
     * @var bool
     * @ORM\Column(name="company_allow_contact_via_email", type="boolean", options={"default" : 1})
     */
    private bool $companyAllowContactViaEmail = true;

    /**
     * @var bool
     * @ORM\Column(name="company_allow_contact_via_phone", type="boolean", options={"default" : 1})
     */
    private bool $companyAllowContactViaPhone = true;

    /**
     * @var string
     * @ORM\Column(name="candidature_status", length=300, nullable=true, options={"default" : ""})
     */
    private ?string $candidatureStatus = '';
    
    /**
     * @var string
     *
     * @ORM\Column(name="emp_lastname", type="string", length=100, options={"default" : ""})
     */
    private string $lastName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_firstname", type="string", length=100, options={"default" : ""})
     */
    private string $firstName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="emp_middle_name", type="string", length=100, options={"default" : ""})
     */
    private string $middleName = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_nick_name", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $nickName = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="emp_smoker", type="smallint", nullable=true, options={"default" : 0})
     */
    private ?int $smoker = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ethnic_race_code", type="string", length=13, nullable=true)
     */
    private ?string $ethnicRaceCode = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="emp_birthday", type="date", nullable=true)
     */
    private ?DateTime $birthday = null;

    /**
     * @var Nationality|null
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Nationality")
     * @ORM\JoinColumn(name="nation_code", referencedColumnName="id", nullable=true)
     */
    private ?Nationality $nationality = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="emp_gender", type="smallint", nullable=true)
     */
    private ?int $gender = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_marital_status", type="string", length=20, nullable=true)
     */
    private ?string $maritalStatus = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_ssn_num", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $ssnNumber = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_sin_num", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $sinNumber = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_other_id", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $otherId = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_dri_lice_num", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $drivingLicenseNo = '';

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="emp_dri_lice_exp_date", type="date", nullable=true)
     */
    private ?DateTime $drivingLicenseExpiredDate = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_military_service", type="string", length=100, nullable=true)
     */
    private ?string $militaryService = '';

    /**
     * @var EmploymentStatus|null
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\EmploymentStatus", inversedBy="employees")
     * @ORM\JoinColumn(name="emp_status", referencedColumnName="id", nullable=true)
     */
    private ?EmploymentStatus $empStatus = null;

    /**
     * @var JobTitle|null
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\JobTitle", inversedBy="employees")
     * @ORM\JoinColumn(name="job_title_code", referencedColumnName="id", nullable=true)
     */
    private ?JobTitle $jobTitle = null;

    /**
     * @var JobCategory|null
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\JobCategory")
     * @ORM\JoinColumn(name="eeo_cat_code", referencedColumnName="id", nullable=true)
     */
    private ?JobCategory $jobCategory = null;

    /**
     * @var Subunit|null
     *
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Subunit")
     * @ORM\JoinColumn(name="work_station", referencedColumnName="id", nullable=true)
     */
    private ?Subunit $subDivision = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_street1", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $street1 = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_street2", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $street2 = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="city_code", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $city = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="coun_code", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $country = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="provin_code", type="string", length=100, nullable=true, options={"default" : ""})
     */
    private ?string $province = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_zipcode", type="string", length=20, nullable=true)
     */
    private ?string $zipcode = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_mobility", type="string", length=300, nullable=true)
     */
    private ?string $mobility = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_hm_telephone", type="string", length=50, nullable=true)
     */
    private ?string $homeTelephone = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_mobile", type="string", length=50, nullable=true)
     */
    private ?string $mobile = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_work_telephone", type="string", length=50, nullable=true)
     */
    private ?string $workTelephone = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_work_email", type="string", length=50, nullable=true)
     */
    private ?string $workEmail = null;

    /**
     * @deprecated
     * @todo remove from schema
     * @var string|null
     *
     * @ORM\Column(name="sal_grd_code", type="string", length=13, nullable=true)
     */
    private ?string $salaryGradeCode = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="joined_date", type="date", nullable=true)
     */
    private ?DateTime $joinedDate = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emp_oth_email", type="string", length=50, nullable=true)
     */
    private ?string $otherEmail = null;

    /**
     * @var EmployeeTerminationRecord|null
     *
     * @ORM\OneToOne(targetEntity="OrangeHRM\Entity\EmployeeTerminationRecord", cascade={"persist"})
     * @ORM\JoinColumn(name="termination_id", referencedColumnName="id")
     */
    private ?EmployeeTerminationRecord $employeeTerminationRecord = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom1", type="string", length=250, nullable=true)
     */
    private ?string $custom1 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom2", type="string", length=250, nullable=true)
     */
    private ?string $custom2 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom3", type="string", length=250, nullable=true)
     */
    private ?string $custom3 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom4", type="string", length=250, nullable=true)
     */
    private ?string $custom4 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom5", type="string", length=250, nullable=true)
     */
    private ?string $custom5 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom6", type="string", length=250, nullable=true)
     */
    private ?string $custom6 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom7", type="string", length=250, nullable=true)
     */
    private ?string $custom7 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom8", type="string", length=250, nullable=true)
     */
    private ?string $custom8 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom9", type="string", length=250, nullable=true)
     */
    private ?string $custom9 = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="custom10", type="string", length=250, nullable=true)
     */
    private ?string $custom10 = null;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="purged_at", type="datetime", nullable=true)
     */
    private ?DateTime $purgedAt = null;

    /**
     * @var Location[]
     *
     * @ORM\ManyToMany(targetEntity="OrangeHRM\Entity\Location", inversedBy="employees")
     * @ORM\JoinTable(
     *     name="hs_hr_emp_locations",
     *     joinColumns={@ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")}
     * )
     */
    private iterable $locations;

    /**
     * @var EmpDependent[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmpDependent", mappedBy="employee")
     */
    private iterable $dependents;

    /**
     * @var EmpEmergencyContact[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmpEmergencyContact", mappedBy="employee")
     */
    private iterable $emergencyContacts;

    /**
     * @var EmployeeImmigrationRecord[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeImmigrationRecord", mappedBy="employee")
     */
    private iterable $immigrationRecords;

    /**
     * @var EmpWorkExperience[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmpWorkExperience", mappedBy="employee")
     */
    private iterable $workExperience;

    /**
     * @var EmployeeEducation[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeEducation", mappedBy="employee")
     */
    private iterable $educations;

    /**
     * @var EmployeeSkill[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeSkill", mappedBy="employee")
     */
    private iterable $skills;

    /**
     * @var EmployeeLanguage[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeLanguage", mappedBy="employee")
     */
    private iterable $languages;

    /**
     * @var EmployeeLicense[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeLicense", mappedBy="employee")
     */
    private iterable $licenses;

    /**
     * @var EmployeeMembership[]
     *
     * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeMembership", mappedBy="employee")
     */
    private iterable $memberships;

    /**
     * @var EmployeeSalary[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeSalary", mappedBy="employee")
     */
    private iterable $salaries;

    /**
     * @var EmpContract[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmpContract", mappedBy="employee")
     */
    private iterable $employmentContracts;

    /**
     * @var EmployeeAttachment[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeAttachment", mappedBy="employee")
     */
    private iterable $attachments;

    /**
     * @var EmployeeTerminationRecord[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\EmployeeTerminationRecord", mappedBy="employee")
     */
    private iterable $employeeTerminationRecords;

    /**
     * @var User[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\User", mappedBy="employee")
     */
    private iterable $users;

    /**
     * @var EmpPicture|null
     *
     * @ORM\OneToOne(targetEntity="OrangeHRM\Entity\EmpPicture", mappedBy="employee")
     */
    private ?EmpPicture $empPicture = null;

    /**
     * @var Employee[]
     *
     * @ORM\ManyToMany(targetEntity="OrangeHRM\Entity\Employee", mappedBy="supervisors")
     */
    private iterable $subordinates;

    /**
     * @var Employee[]
     *
     * @ORM\ManyToMany(targetEntity="OrangeHRM\Entity\Employee", inversedBy="subordinates")
     * @ORM\JoinTable(
     *     name="hs_hr_emp_reportto",
     *     joinColumns={@ORM\JoinColumn(name="erep_sub_emp_number", referencedColumnName="emp_number")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="erep_sup_emp_number", referencedColumnName="emp_number")}
     * )
     */
    private iterable $supervisors;

    /**
     * @var EmpUsTaxExemption
     *
     * @ORM\OneToOne(targetEntity="OrangeHRM\Entity\EmpUsTaxExemption", mappedBy="employee")
     */
    private EmpUsTaxExemption $empUsTax;

    /**
     * @var EmployeeWorkShift
     *
     * @ORM\OneToOne(targetEntity="OrangeHRM\Entity\EmployeeWorkShift", mappedBy="employee")
     */
    private EmployeeWorkShift $employeeWorkShift;

    /**
     * @var AttendanceRecord[]
     *
     * @ORM\OneToMany(targetEntity="OrangeHRM\Entity\AttendanceRecord", mappedBy="employee")
     */
    private iterable $attendanceRecords;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->dependents = new ArrayCollection();
        $this->emergencyContacts = new ArrayCollection();
        $this->immigrationRecords = new ArrayCollection();
        $this->workExperience = new ArrayCollection();
        $this->educations = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->licenses = new ArrayCollection();
        $this->memberships = new ArrayCollection();
        $this->salaries = new ArrayCollection();
        $this->employmentContracts = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->employeeTerminationRecords = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->subordinates = new ArrayCollection();
        $this->supervisors = new ArrayCollection();
        $this->attendanceRecords = new ArrayCollection();
    }

    /**
     * @param mixed $profileInfo
     */
    public function setProfileInfo(mixed $profileInfo): void 
    {
        $this->setProfileId($profileInfo['id'] ?? $this->empNumber ?? null);
        $this->setNeed($profileInfo['need'] ?? '');
        $this->setResume(array_key_exists('resume', $profileInfo) && $profileInfo['resume'] != null ? (int) $profileInfo['resume'] : -1);
        $this->setStudyLevel($profileInfo['studyLevel'] ?? '');
        $this->setDrivingLicense(json_encode($profileInfo['drivingLicenses'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setMotivation($profileInfo['motivation'] ?? '');
        $this->setSalary($profileInfo['salaryExpectation'] ?? '');
        $this->setCourseStart($profileInfo['courseStart'] ?? '');
        $this->setFirstName($profileInfo['firstName'] ?? '');
        $this->setLastName($profileInfo['lastName'] ?? '');
        $this->setBirthday(array_key_exists('birthDate', $profileInfo) && $profileInfo['birthDate'] != null ? new \DateTime($profileInfo['birthDate']) : null);
    }

    /**
     * @param mixed $profileContact
     */
    public function setProfileContact(mixed $profileContact): void 
    {
        $this->setWorkEmail($profileContact['email'] ?? '');
        $this->setOtherEmail($profileContact['contactEmail'] ?? '');
        $this->setMobile($profileContact['phoneNumber'] ?? '');
        $this->setMobility($profileContact['mobility'] ?? '');
        $this->setStreet1($profileContact['address']['street'] ?? '');
        $this->setCity($profileContact['address']['city'] ?? '');
        $this->setProvince($profileContact['address']['state'] ?? '');
        $this->setZipcode($profileContact['address']['postalCode'] ?? '');
        $this->setCountry($profileContact['address']['country'] ?? '');
    }

    /**
     * @param mixed $profileContact
     */
    public function setCompanyContact(mixed $companyContact): void 
    {
        $this->setOtherEmail($companyContact['contactEmail'] ?? '');
        $this->setMobile($companyContact['phoneNumber'] ?? '');
        $this->setStreet1($companyContact['address']['street'] ?? '');
        $this->setCity($companyContact['address']['city'] ?? '');
        $this->setProvince($companyContact['address']['state'] ?? '');
        $this->setZipcode($companyContact['address']['postalCode'] ?? '');
        $this->setCompanyAllowContactViaEmail($companyContact['allowContactViaEmail'] ?? true);
        $this->setCompanyAllowContactViaPhone($companyContact['allowContactViaPhone'] ?? true);
    }

    /**
     * @param mixed $company
     */
    public function setCompany(mixed $company): void 
    {
        $this->setCompanyId($company['id'] ?? -1);
        $this->setAttachment(array_key_exists('attachment', $company) && $company['attachment'] != null ? (int) $company['attachment'] : -1);
        $this->setCompanyName($company['name'] ?? '');
        $this->setCompanySiret($company['siret'] ?? '');
        $this->setCompanyWebsite($company['website'] ?? '');
        $this->setCompanyPresentation($company['presentation'] ?? '');
        $this->setCompanyNafCode($company['nafCode'] ?? '');
        $this->setCompanyWorkforce($company['workforce'] ?? '');
        $this->setCompanyLogo($company['logo'] ?? '');
        $this->setCompanyLocation($company['location'] ?? '');
        $this->setCompanyPhoneNumberContact($company['phoneNumberContact'] ?? '');
        $this->setCompanyEmailContact($company['emailContact'] ?? '');
        $this->setCompanyMatchingJobTitle($company['matchingJobTitle'] ?? '');
        $this->setMatchingId($company['matchingId'] ?? null);
        $this->setCandidatureStatus($company['candidatureStatus'] ?? '');
        $this->setProfileContact([]);
        $this->setProfileInfo([]);
    }

    /**
     * @param mixed $company
     */
    public function setCompanyDetails(mixed $company): void 
    {
        $this->setProfileContact([]);
        $this->setProfileInfo([]);
        if (array_key_exists('info', $company)) {
            $this->setAttachment(array_key_exists('attachment', $company['info']) && $company['info']['attachment'] != null ? (int) $company['info']['attachment'] : -1);
            $this->setCompanyName($company['info']['name'] ?? '');
            $this->setCompanySiret($company['info']['siret'] ?? '');
            $this->setCompanyWebsite($company['info']['website'] ?? '');
            $this->setCompanyPresentation($company['info']['presentation'] ?? '');
            $this->setCompanyNafCode($company['info']['nafCode'] ?? '');
            $this->setCompanyWorkforce($company['info']['workforce'] ?? '');
            $this->setCompanyLogo($company['info']['logo'] ?? '');
        }
        if (array_key_exists('contact', $company)) {
            $this->setCompanyPhoneNumberContact($company['contact']['phoneNumber'] ?? '');
            $this->setCompanyEmailContact($company['contact']['contactEmail'] ?? '');
            if (array_key_exists('address', $company['contact'])) {
                $this->setStreet1($company['contact']['address']['street'] ?? '');
                $this->setCity($company['contact']['address']['city'] ?? '');
                $this->setProvince($company['contact']['address']['state'] ?? '');
                $this->setZipcode($company['contact']['address']['postalCode'] ?? '');
            }
        }
        $this->setJobs(json_encode($company['jobs'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setCandidatureStatus($company['candidatureStatus'] ?? '');
    }

    /**
     * @return int
     */
    public function getEmpNumber(): int
    {
        return $this->empNumber;
    }

    /**
     * @param int $empNumber
     */
    public function setEmpNumber(int $empNumber): void
    {
        $this->empNumber = $empNumber;
    }

    /**
     * @return string|null
     */
    public function getEmployeeId(): ?string
    {
        return $this->employeeId;
    }

    /**
     * @param string|null $employeeId
     */
    public function setEmployeeId(?string $employeeId): void
    {
        $this->employeeId = $employeeId;
    }

    /**
     * @return int|null
     */
    public function getProfileId(): ?int
    {
        return $this->profileId;
    }

    /**
     * @param int|null $profileId
     */
    public function setProfileId(?int $profileId): void
    {
        $this->profileId = $profileId;
    }

    /**
     * @return int|null
     */
    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    /**
     * @param int|null $companyId
     */
    public function setCompanyId(?int $companyId): void
    {
        $this->companyId = $companyId;
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
     * @return string|null
     */
    public function getDrivingLicense(): ?string
    {
        return $this->drivingLicense;
    }

    /**
     * @param string $motivation
     */
    public function setMotivation(string $motivation): void
    {
        $this->motivation = $motivation;
    }

    /**
     * @return string
     */
    public function getMotivation(): string
    {
        return $this->motivation;
    }

    /**
     * @param ?string $drivingLicense
     */
    public function setDrivingLicense(?string $drivingLicense): void
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
     * @return ?int
     */
    public function getResume(): ?int
    {
        return $this->resume;
    }

    /**
     * @param ?int $resume
     */
    public function setResume(?int $resume): void
    {
        $this->resume = $resume;
    }

    /**
     * @return ?int
     */
    public function getAttachment(): ?int
    {
        return $this->attachment;
    }

    /**
     * @param ?int $attachment
     */
    public function setAttachment(?int $attachment): void
    {
        $this->attachment = $attachment;
    }

    /**
     * @return string|null
     */
    public function getJobs(): ?string
    {
        return $this->jobs;
    }

    /**
     * @param string $jobs
     */
    public function setJobs(?string $jobs): void
    {
        $this->jobs = $jobs;
    }

    /**
     * @return string
     */
    public function getRoles(): string
    {
        return $this->roles;
    }

    /**
     * @param string $roles
     */
    public function setRoles(string $roles): void
    {
        $this->roles = $roles ?? '';
    }

    /**
     * @return string
     */
    public function getEmpSkills(): string
    {
        return $this->emp_skills;
    }

    /**
     * @param string $emp_skills
     */
    public function setEmpSkills(string $emp_skills): void
    {
        $this->emp_skills = $emp_skills;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getCompanySiret(): string
    {
        return $this->companySiret;
    }

    /**
     * @param string $companySiret
     */
    public function setCompanySiret(string $companySiret): void
    {
        $this->companySiret = $companySiret;
    }

    /**
     * @return string
     */
    public function getCompanyPresentation(): string
    {
        return $this->companyPresentation;
    }

    /**
     * @param string $companyPresentation
     */
    public function setCompanyPresentation(string $companyPresentation): void
    {
        $this->companyPresentation = $companyPresentation;
    }

    /**
     * @return string
     */
    public function getCompanyWebsite(): string
    {
        return $this->companyWebsite;
    }

    /**
     * @param string $companyWebsite
     */
    public function setCompanyWebsite(string $companyWebsite): void
    {
        $this->companyWebsite = $companyWebsite;
    }

    /**
     * @return string
     */
    public function getCompanyWorkforce(): string
    {
        return $this->companyWorkforce;
    }

    /**
     * @param string $companyWorkforce
     */
    public function setCompanyWorkforce(string $companyWorkforce): void
    {
        $this->companyWorkforce = $companyWorkforce;
    }

    /**
     * @return string
     */
    public function getCompanyNafCode(): string
    {
        return $this->companyNafCode;
    }

    /**
     * @param string $companyNafCode
     */
    public function setCompanyNafCode(string $companyNafCode): void
    {
        $this->companyNafCode = $companyNafCode;
    }

    /**
     * @return string
     */
    public function getCompanyLogo(): string
    {
        return $this->companyLogo;
    }

    /**
     * @param string $companyLogo
     */
    public function setCompanyLogo(string $companyLogo): void
    {
        $this->companyLogo = $companyLogo;
    }

    /**
     * @return string
     */
    public function getCompanyLocation(): string
    {
        return $this->companyLocation;
    }

    /**
     * @param string $companyLocation
     */
    public function setCompanyLocation(string $companyLocation): void
    {
        $this->companyLocation = $companyLocation;
    }

    /**
     * @return string
     */
    public function getCompanyPhoneNumberContact(): string
    {
        return $this->companyPhoneNumberContact;
    }

    /**
     * @param string $companyPhoneNumberContact
     */
    public function setCompanyPhoneNumberContact(string $companyPhoneNumberContact): void
    {
        $this->companyPhoneNumberContact = $companyPhoneNumberContact;
    }

    /**
     * @return string
     */
    public function getCompanyEmailContact(): string
    {
        return $this->companyEmailContact;
    }

    /**
     * @param string $companyEmailContact
     */
    public function setCompanyEmailContact(string $companyEmailContact): void
    {
        $this->companyEmailContact = $companyEmailContact;
    }

    /**
     * @return string
     */
    public function getCompanyMatchingJobTitle(): string
    {
        return $this->companyMatchingJobTitle;
    }

    /**
     * @param string $companyMatchingJobTitle
     */
    public function setCompanyMatchingJobTitle(string $companyMatchingJobTitle): void
    {
        $this->companyMatchingJobTitle = $companyMatchingJobTitle;
    }

    /**
     * @return int|null
     */
    public function getMatchingId(): ?int
    {
        return $this->matchingId;
    }

    /**
     * @param int|null $matchingId
     */
    public function setMatchingId(?int $matchingId): void
    {
        $this->matchingId = $matchingId;
    }

    /**
     * @return bool
     */
    public function getCompanyAllowContactViaEmail(): bool
    {
        return $this->companyAllowContactViaEmail;
    }

    /**
     * @param bool $companyAllowContactViaEmail
     */
    public function setCompanyAllowContactViaEmail(bool $companyAllowContactViaEmail): void
    {
        $this->companyAllowContactViaEmail = $companyAllowContactViaEmail;
    }

    /**
     * @return bool
     */
    public function getCompanyAllowContactViaPhone(): bool
    {
        return $this->companyAllowContactViaPhone;
    }

    /**
     * @param bool $companyAllowContactViaPhone
     */
    public function setCompanyAllowContactViaPhone(bool $companyAllowContactViaPhone): void
    {
        $this->companyAllowContactViaPhone = $companyAllowContactViaPhone;
    }
    
    /**
     * @return string
     */
    public function getCandidatureStatus(): string
    {
        return $this->candidatureStatus;
    }

    /**
     * @param string $candidatureStatus
     */
    public function setCandidatureStatus(string $candidatureStatus): void
    {
        $this->candidatureStatus = $candidatureStatus;
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
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string|null
     */
    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    /**
     * @param string|null $nickName
     */
    public function setNickName(?string $nickName): void
    {
        $this->nickName = $nickName;
    }

    /**
     * @return int|null
     */
    public function getSmoker(): ?int
    {
        return $this->smoker;
    }

    /**
     * @param int|null $smoker
     */
    public function setSmoker(?int $smoker): void
    {
        $this->smoker = $smoker;
    }

    /**
     * @return string|null
     */
    public function getEthnicRaceCode(): ?string
    {
        return $this->ethnicRaceCode;
    }

    /**
     * @param string|null $ethnicRaceCode
     */
    public function setEthnicRaceCode(?string $ethnicRaceCode): void
    {
        $this->ethnicRaceCode = $ethnicRaceCode;
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
     * @return Nationality|null
     */
    public function getNationality(): ?Nationality
    {
        return $this->nationality;
    }

    /**
     * @param Nationality|null $nationality
     */
    public function setNationality(?Nationality $nationality): void
    {
        $this->nationality = $nationality;
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
     * @return string|null
     */
    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    /**
     * @param string|null $maritalStatus
     */
    public function setMaritalStatus(?string $maritalStatus): void
    {
        $this->maritalStatus = $maritalStatus;
    }

    /**
     * @return string|null
     */
    public function getSsnNumber(): ?string
    {
        return $this->ssnNumber;
    }

    /**
     * @param string|null $ssnNumber
     */
    public function setSsnNumber(?string $ssnNumber): void
    {
        $this->ssnNumber = $ssnNumber;
    }

    /**
     * @return string|null
     */
    public function getSinNumber(): ?string
    {
        return $this->sinNumber;
    }

    /**
     * @param string|null $sinNumber
     */
    public function setSinNumber(?string $sinNumber): void
    {
        $this->sinNumber = $sinNumber;
    }

    /**
     * @return string|null
     */
    public function getOtherId(): ?string
    {
        return $this->otherId;
    }

    /**
     * @param string|null $otherId
     */
    public function setOtherId(?string $otherId): void
    {
        $this->otherId = $otherId;
    }

    /**
     * @return string|null
     */
    public function getDrivingLicenseNo(): ?string
    {
        return $this->drivingLicenseNo;
    }

    /**
     * @param string|null $drivingLicenseNo
     */
    public function setDrivingLicenseNo(?string $drivingLicenseNo): void
    {
        $this->drivingLicenseNo = $drivingLicenseNo;
    }

    /**
     * @return DateTime|null
     */
    public function getDrivingLicenseExpiredDate(): ?DateTime
    {
        return $this->drivingLicenseExpiredDate;
    }

    /**
     * @param DateTime|null $drivingLicenseExpiredDate
     */
    public function setDrivingLicenseExpiredDate(?DateTime $drivingLicenseExpiredDate): void
    {
        $this->drivingLicenseExpiredDate = $drivingLicenseExpiredDate;
    }

    /**
     * @return string|null
     */
    public function getMilitaryService(): ?string
    {
        return $this->militaryService;
    }

    /**
     * @param string|null $militaryService
     */
    public function setMilitaryService(?string $militaryService): void
    {
        $this->militaryService = $militaryService;
    }

    /**
     * @return EmploymentStatus|null
     */
    public function getEmpStatus(): ?EmploymentStatus
    {
        return $this->empStatus;
    }

    /**
     * @param EmploymentStatus|null $empStatus
     */
    public function setEmpStatus(?EmploymentStatus $empStatus): void
    {
        $this->empStatus = $empStatus;
    }

    /**
     * @return JobTitle|null
     */
    public function getJobTitle(): ?JobTitle
    {
        return $this->jobTitle;
    }

    /**
     * @param JobTitle|null $jobTitle
     */
    public function setJobTitle(?JobTitle $jobTitle): void
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return JobCategory|null
     */
    public function getJobCategory(): ?JobCategory
    {
        return $this->jobCategory;
    }

    /**
     * @param JobCategory|null $jobCategory
     */
    public function setJobCategory(?JobCategory $jobCategory): void
    {
        $this->jobCategory = $jobCategory;
    }

    /**
     * @return Subunit|null
     */
    public function getSubDivision(): ?Subunit
    {
        return $this->subDivision;
    }

    /**
     * @param Subunit|null $subDivision
     */
    public function setSubDivision(?Subunit $subDivision): void
    {
        $this->subDivision = $subDivision;
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
    public function getStreet2(): ?string
    {
        return $this->street2;
    }

    /**
     * @param string|null $street2
     */
    public function setStreet2(?string $street2): void
    {
        $this->street2 = $street2;
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
    public function getMobility(): ?string
    {
        return $this->mobility;
    }

    /**
     * @param string|null $mobility
     */
    public function setMobility(?string $mobility): void
    {
        $this->mobility = $mobility;
    }

    /**
     * @return string|null
     */
    public function getHomeTelephone(): ?string
    {
        return $this->homeTelephone;
    }

    /**
     * @param string|null $homeTelephone
     */
    public function setHomeTelephone(?string $homeTelephone): void
    {
        $this->homeTelephone = $homeTelephone;
    }

    /**
     * @return string|null
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param string|null $mobile
     */
    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string|null
     */
    public function getWorkTelephone(): ?string
    {
        return $this->workTelephone;
    }

    /**
     * @param string|null $workTelephone
     */
    public function setWorkTelephone(?string $workTelephone): void
    {
        $this->workTelephone = $workTelephone;
    }

    /**
     * @return string|null
     */
    public function getWorkEmail(): ?string
    {
        return $this->workEmail;
    }

    /**
     * @param string|null $workEmail
     */
    public function setWorkEmail(?string $workEmail): void
    {
        $this->workEmail = $workEmail;
    }

    /**
     * @return DateTime|null
     */
    public function getJoinedDate(): ?DateTime
    {
        return $this->joinedDate;
    }

    /**
     * @param DateTime|null $joinedDate
     */
    public function setJoinedDate(?DateTime $joinedDate): void
    {
        $this->joinedDate = $joinedDate;
    }

    /**
     * @return string|null
     */
    public function getOtherEmail(): ?string
    {
        return $this->otherEmail;
    }

    /**
     * @param string|null $otherEmail
     */
    public function setOtherEmail(?string $otherEmail): void
    {
        $this->otherEmail = $otherEmail;
    }

    /**
     * @return EmployeeTerminationRecord|null
     */
    public function getEmployeeTerminationRecord(): ?EmployeeTerminationRecord
    {
        return $this->employeeTerminationRecord;
    }

    /**
     * @param EmployeeTerminationRecord|null $employeeTerminationRecord
     */
    public function setEmployeeTerminationRecord(?EmployeeTerminationRecord $employeeTerminationRecord): void
    {
        $this->employeeTerminationRecord = $employeeTerminationRecord;
    }

    /**
     * @return string|null
     */
    public function getCustom1(): ?string
    {
        return $this->custom1;
    }

    /**
     * @param string|null $custom1
     */
    public function setCustom1(?string $custom1): void
    {
        $this->custom1 = $custom1;
    }

    /**
     * @return string|null
     */
    public function getCustom2(): ?string
    {
        return $this->custom2;
    }

    /**
     * @param string|null $custom2
     */
    public function setCustom2(?string $custom2): void
    {
        $this->custom2 = $custom2;
    }

    /**
     * @return string|null
     */
    public function getCustom3(): ?string
    {
        return $this->custom3;
    }

    /**
     * @param string|null $custom3
     */
    public function setCustom3(?string $custom3): void
    {
        $this->custom3 = $custom3;
    }

    /**
     * @return string|null
     */
    public function getCustom4(): ?string
    {
        return $this->custom4;
    }

    /**
     * @param string|null $custom4
     */
    public function setCustom4(?string $custom4): void
    {
        $this->custom4 = $custom4;
    }

    /**
     * @return string|null
     */
    public function getCustom5(): ?string
    {
        return $this->custom5;
    }

    /**
     * @param string|null $custom5
     */
    public function setCustom5(?string $custom5): void
    {
        $this->custom5 = $custom5;
    }

    /**
     * @return string|null
     */
    public function getCustom6(): ?string
    {
        return $this->custom6;
    }

    /**
     * @param string|null $custom6
     */
    public function setCustom6(?string $custom6): void
    {
        $this->custom6 = $custom6;
    }

    /**
     * @return string|null
     */
    public function getCustom7(): ?string
    {
        return $this->custom7;
    }

    /**
     * @param string|null $custom7
     */
    public function setCustom7(?string $custom7): void
    {
        $this->custom7 = $custom7;
    }

    /**
     * @return string|null
     */
    public function getCustom8(): ?string
    {
        return $this->custom8;
    }

    /**
     * @param string|null $custom8
     */
    public function setCustom8(?string $custom8): void
    {
        $this->custom8 = $custom8;
    }

    /**
     * @return string|null
     */
    public function getCustom9(): ?string
    {
        return $this->custom9;
    }

    /**
     * @param string|null $custom9
     */
    public function setCustom9(?string $custom9): void
    {
        $this->custom9 = $custom9;
    }

    /**
     * @return string|null
     */
    public function getCustom10(): ?string
    {
        return $this->custom10;
    }

    /**
     * @param string|null $custom10
     */
    public function setCustom10(?string $custom10): void
    {
        $this->custom10 = $custom10;
    }

    /**
     * @return DateTime|null
     */
    public function getPurgedAt(): ?DateTime
    {
        return $this->purgedAt;
    }

    /**
     * @param DateTime|null $purgedAt
     */
    public function setPurgedAt(?DateTime $purgedAt): void
    {
        $this->purgedAt = $purgedAt;
    }

    /**
     * @return Location[]
     */
    public function getLocations(): iterable
    {
        return $this->locations;
    }

    /**
     * @param Location[] $locations
     */
    public function setLocations(iterable $locations): void
    {
        $this->locations = $locations;
    }

    /**
     * @return EmpDependent[]
     */
    public function getDependents(): iterable
    {
        return $this->dependents;
    }

    /**
     * @param EmpDependent[] $dependents
     */
    public function setDependents(iterable $dependents): void
    {
        $this->dependents = $dependents;
    }

    /**
     * @return EmpEmergencyContact[]
     */
    public function getEmergencyContacts(): iterable
    {
        return $this->emergencyContacts;
    }

    /**
     * @param EmpEmergencyContact[] $emergencyContacts
     */
    public function setEmergencyContacts(iterable $emergencyContacts): void
    {
        $this->emergencyContacts = $emergencyContacts;
    }

    /**
     * @return EmployeeImmigrationRecord[]
     */
    public function getImmigrationRecords(): iterable
    {
        return $this->immigrationRecords;
    }

    /**
     * @param EmployeeImmigrationRecord[] $immigrationRecords
     */
    public function setImmigrationRecords(iterable $immigrationRecords): void
    {
        $this->immigrationRecords = $immigrationRecords;
    }

    /**
     * @return EmpWorkExperience[]
     */
    public function getWorkExperience(): iterable
    {
        return $this->workExperience;
    }

    /**
     * @param EmpWorkExperience[] $workExperience
     */
    public function setWorkExperience(iterable $workExperience): void
    {
        $this->workExperience = $workExperience;
    }

    /**
     * @return EmployeeEducation[]
     */
    public function getEducations(): iterable
    {
        return $this->educations;
    }

    /**
     * @param EmployeeEducation[] $educations
     */
    public function setEducations(iterable $educations): void
    {
        $this->educations = $educations;
    }

    /**
     * @return EmployeeSkill[]
     */
    public function getSkills(): iterable
    {
        return $this->skills;
    }

    /**
     * @param EmployeeSkill[] $skills
     */
    public function setSkills(iterable $skills): void
    {
        $this->skills = $skills;
    }

    /**
     * @return EmployeeLanguage[]
     */
    public function getLanguages(): iterable
    {
        return $this->languages;
    }

    /**
     * @param EmployeeLanguage[] $languages
     */
    public function setLanguages(iterable $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @return EmployeeLicense[]
     */
    public function getLicenses(): iterable
    {
        return $this->licenses;
    }

    /**
     * @param EmployeeLicense[] $licenses
     */
    public function setLicenses(iterable $licenses): void
    {
        $this->licenses = $licenses;
    }

    /**
     * @return EmployeeMembership[]
     */
    public function getMemberships(): iterable
    {
        return $this->memberships;
    }

    /**
     * @param EmployeeMembership[] $memberships
     */
    public function setMemberships(iterable $memberships): void
    {
        $this->memberships = $memberships;
    }

    /**
     * @return EmployeeSalary[]
     */
    public function getSalaries(): iterable
    {
        return $this->salaries;
    }

    /**
     * @param EmployeeSalary[] $salaries
     */
    public function setSalaries(iterable $salaries): void
    {
        $this->salaries = $salaries;
    }

    /**
     * @return EmpContract[]
     */
    public function getEmploymentContracts(): iterable
    {
        return $this->employmentContracts;
    }

    /**
     * @param EmpContract[] $employmentContracts
     */
    public function setEmploymentContracts(iterable $employmentContracts): void
    {
        $this->employmentContracts = $employmentContracts;
    }

    /**
     * @return User[]
     */
    public function getUsers(): iterable
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     */
    public function setUsers(iterable $users): void
    {
        $this->users = $users;
    }

    /**
     * @return EmpPicture|null
     */
    public function getEmpPicture(): ?EmpPicture
    {
        return $this->empPicture;
    }

    /**
     * @param EmpPicture|null $empPicture
     */
    public function setEmpPicture(?EmpPicture $empPicture): void
    {
        $this->empPicture = $empPicture;
    }

    /**
     * @return Employee[]
     */
    public function getSubordinates(): iterable
    {
        return $this->subordinates;
    }

    /**
     * @param Employee[] $subordinates
     */
    public function setSubordinates(iterable $subordinates): void
    {
        $this->subordinates = $subordinates;
    }

    /**
     * @return Employee[]
     */
    public function getSupervisors(): iterable
    {
        return $this->supervisors;
    }

    /**
     * @param Employee[] $supervisors
     */
    public function setSupervisors(iterable $supervisors): void
    {
        $this->supervisors = $supervisors;
    }
}
