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
use OrangeHRM\Entity\Decorator\DecoratorTrait;
use OrangeHRM\Entity\Decorator\VacancyDecorator;
use Doctrine\ORM\Mapping as ORM;

/**
 * @method VacancyDecorator getDecorator()
 *
 * @ORM\Table(name="ohrm_job_vacancy")
 * @ORM\Entity
 *
 */
class Vacancy
{
    use DecoratorTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", length=13)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private int $id;

    /**
     * @var JobTitle
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\JobTitle", inversedBy="vacancies", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="job_title_code", referencedColumnName="id", nullable=false)
     */
    private JobTitle $jobTitle;

    /**
     * @var Employee|null
     * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee", inversedBy="vacancies", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="hiring_manager_id", referencedColumnName="emp_number", nullable=true)
     */
    private ?Employee $hiringManager;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=100)
     */
    private string $name;
    
    /**
     * @var string |null
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @var string |null
     * @ORM\Column(name="jobs", type="text", nullable=true)
     */
    private ?string $jobs;

    /**
     * @var string |null
     * @ORM\Column(name="needs", type="text", nullable=true)
     */
    private ?string $needs;

    /**
     * @var string |null
     * @ORM\Column(name="levels", type="text", nullable=true)
     */
    private ?string $levels;

    /**
     * @var string |null
     * @ORM\Column(name="course_starts", type="text", nullable=true)
     */
    private ?string $courseStarts;

    /**
     * @var string |null
     * @ORM\Column(name="countries", type="text", nullable=true)
     */
    private ?string $countries;

    /**
     * @var string |null
     * @ORM\Column(name="professional_experiences", type="text", nullable=true)
     */
    private ?string $professionalExperiences;

    /**
     * @var string |null
     * @ORM\Column(name="driving_licenses", type="text", nullable=true)
     */
    private ?string $drivingLicenses;

    /**
     * @var int |null
     * @ORM\Column(name="no_of_positions", type="integer", length=13, nullable=true)
     */
    private ?int $numOfPositions = null;

    /**
     * @var bool
     * @ORM\Column(name="status", type="boolean", options={"default" : 1})
     */
    private bool $status = true;

    /**
     * @var bool
     * @ORM\Column(name="published_in_feed", type="boolean", options={"default" : 0})
     */
    private bool $isPublished = false;

    /**
     * @var DateTime
     * @ORM\Column(name="defined_time", type="datetime")
     */
    private DateTime $definedTime;

    /**
     * @var DateTime
     * @ORM\Column(name="updated_time", type="datetime")
     */
    private DateTime $updatedTime;

    public function setMatching(mixed $matching): void
    {
        $this->setId($matching['id']);
        $this->setName($matching['title']);
        $this->setJobs(json_encode($matching['jobs'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setNeeds(json_encode($matching['needs'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setLevels(json_encode($matching['levels'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setCourseStarts(json_encode($matching['courseStarts'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setCountries(json_encode($matching['countries'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setProfessionalExperiences(json_encode($matching['professionalExperiences'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setDrivingLicenses(json_encode($matching['drivingLicenses'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->setStatus($matching['active']);
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
     * @return JobTitle
     */
    public function getJobTitle(): JobTitle
    {
        return $this->jobTitle;
    }

    /**
     * @param JobTitle $jobTitle
     */
    public function setJobTitle(JobTitle $jobTitle): void
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return Employee|null
     */
    public function getHiringManager(): ?Employee
    {
        return $this->hiringManager;
    }

    /**
     * @param Employee|null $hiringManager
     */
    public function setHiringManager(?Employee $hiringManager): void
    {
        $this->hiringManager = $hiringManager;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
     * @return string|null
     */
    public function getNeeds(): ?string
    {
        return $this->needs;
    }

    /**
     * @param string|null $needs
     */
    public function setNeeds(?string $needs): void
    {
        $this->needs = $needs;
    }

    /**
     * @return string|null
     */
    public function getLevels(): ?string
    {
        return $this->levels;
    }

    /**
     * @param string|null $levels
     */
    public function setLevels(?string $levels): void
    {
        $this->levels = $levels;
    }

    /**
     * @return string|null
     */
    public function getCourseStarts(): ?string
    {
        return $this->courseStarts;
    }

    /**
     * @param string|null $courseStarts
     */
    public function setCourseStarts(?string $courseStarts): void
    {
        $this->courseStarts = $courseStarts;
    }

    /**
     * @return string|null
     */
    public function getCountries(): ?string
    {
        return $this->countries;
    }

    /**
     * @param string|null $countries
     */
    public function setCountries(?string $countries): void
    {
        $this->countries = $countries;
    }

    /**
     * @return string|null
     */
    public function getProfessionalExperiences(): ?string
    {
        return $this->professionalExperiences;
    }

    /**
     * @param string|null $professionalExperiences
     */
    public function setProfessionalExperiences(?string $professionalExperiences): void
    {
        $this->professionalExperiences = $professionalExperiences;
    }

    /**
     * @return string|null
     */
    public function getDrivingLicenses(): ?string
    {
        return $this->drivingLicenses;
    }

    /**
     * @param string|null $drivingLicenses
     */
    public function setDrivingLicenses(?string $drivingLicenses): void
    {
        $this->drivingLicenses = $drivingLicenses;
    }

    /**
     * @return int|null
     */
    public function getNumOfPositions(): ?int
    {
        return $this->numOfPositions;
    }

    /**
     * @param int|null $numOfPositions
     */
    public function setNumOfPositions(?int $numOfPositions): void
    {
        $this->numOfPositions = $numOfPositions;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    /**
     * @param bool $isPublished
     */
    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    /**
     * @return DateTime
     */
    public function getDefinedTime(): DateTime
    {
        return $this->definedTime;
    }

    /**
     * @param DateTime $definedTime
     */
    public function setDefinedTime(DateTime $definedTime): void
    {
        $this->definedTime = $definedTime;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedTime(): DateTime
    {
        return $this->updatedTime;
    }

    /**
     * @param DateTime $updatedTime
     */
    public function setUpdatedTime(DateTime $updatedTime): void
    {
        $this->updatedTime = $updatedTime;
    }
}
