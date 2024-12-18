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
 use Doctrine\ORM\Mapping as ORM;
 use OrangeHRM\Entity\Decorator\DecoratorTrait;
 use OrangeHRM\Entity\Decorator\EmployeeMembershipDecorator;
 
 /**
  * @method EmployeeMembershipDecorator getDecorator()
  *
  * @ORM\Table(name="hs_hr_emp_member_detail")
  * @ORM\Entity
  */
 class EmployeeMembership
 {
     use DecoratorTrait;
 
     public const COMPANY = 'Company';
     public const INDIVIDUAL = 'Individual';
 
     /**
      * @var int
      *
      * @ORM\Column(name="id", type="integer", length=6)
      * @ORM\Id
      * @ORM\GeneratedValue(strategy="AUTO")
      */
     private int $id;
 
     /**
      * @var Employee
      *
      * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Employee", inversedBy="memberships", cascade={"persist"})
      * @ORM\JoinColumn(name="emp_number", referencedColumnName="emp_number")
      */
     private Employee $employee;
 
     /**
      * @var Membership
      *
      * @ORM\ManyToOne(targetEntity="OrangeHRM\Entity\Membership")
      * @ORM\JoinColumn(name="membship_code", referencedColumnName="id")
      */
     private Membership $membership;
 
     // Nouvelles colonnes ajoutées
     /**
      * @var string|null
      *
      * @ORM\Column(name="title", type="string", length=100, nullable=true)
      */
     private ?string $title = null;
 
     /**
      * @var string|null
      *
      * @ORM\Column(name="description", type="string", length=300, nullable=true)
      */
     private ?string $description = null;

     /**
      * @var string|null
      *
      * @ORM\Column(name="employer", type="string", length=300, nullable=true)
      */
      private ?string $employer = null;

    /**
    * @var string|null
    *
    * @ORM\Column(name="professional_experience", type="string", length=100, nullable=true)
    */
    private ?string $professionalExperience = null;

    /**
     * @var string|null
     * 
     * @ORM\Column(name="specific_professional_experience", type="string", length=100, nullable=true)
     */
    private ?string $specificProfessionalExperience = null;
 
     /**
      * @var string|null
      *
      * @ORM\Column(name="year", type="string", length=100)
      */
     private ?string $year = null;
 
     // Méthodes Getter et Setter pour les nouvelles colonnes
 
     public function getTitle(): ?string
     {
         return $this->title;
     }
 
     public function setTitle(?string $title): void
     {
         $this->title = $title;
     }
 
     public function getDescription(): ?string
     {
         return $this->description;
     }
 
     public function setDescription(?string $description): void
     {
         $this->description = $description;
     }

     public function getEmployer(): ?string
     {
         return $this->employer;
     }
 
     public function setEmployer(?string $employer): void
     {
         $this->employer = $employer;
     }
 
     public function getYear(): ?string
     {
         return $this->year;
     }
 
     public function setYear(?string $year): void
     {
         $this->year = $year;
     }

     public function getProfessionalExperience(): ?string
    {
        return $this->professionalExperience;
    }

    public function setProfessionalExperience(?string $professionalExperience): void
    {
        $this->professionalExperience = $professionalExperience;
    }

    public function getSpecificProfessionalExperience(): ?string
    {
        return $this->specificProfessionalExperience;
    }

    public function setSpecificProfessionalExperience(?string $specificProfessionalExperience): void
    {
        $this->specificProfessionalExperience = $specificProfessionalExperience;
    }
 
     // Méthodes existantes (inchangées)
     public function getId(): int
     {
         return $this->id;
     }
 
     public function setId(int $id): void
     {
         $this->id = $id;
     }
 
     public function getEmployee(): Employee
     {
         return $this->employee;
     }
 
     public function setEmployee(Employee $employee): void
     {
         $this->employee = $employee;
     }
 
     public function getMembership(): Membership
     {
         return $this->membership;
     }
 
     public function setMembership(Membership $membership): void
     {
         $this->membership = $membership;
     }
 
     /*
     public function getSubscriptionFee(): ?string
     {
         return $this->subscriptionFee;
     }
 
     public function setSubscriptionFee(?string $subscriptionFee): void
     {
         $this->subscriptionFee = $subscriptionFee;
     }
 
     public function getSubscriptionPaidBy(): ?string
     {
         return $this->subscriptionPaidBy;
     }
 
     public function setSubscriptionPaidBy(?string $subscriptionPaidBy): void
     {
         $this->subscriptionPaidBy = $subscriptionPaidBy;
     }
 
     public function getSubscriptionCurrency(): ?string
     {
         return $this->subscriptionCurrency;
     }
 
     public function setSubscriptionCurrency(?string $subscriptionCurrency): void
     {
         $this->subscriptionCurrency = $subscriptionCurrency;
     }
 
     public function getSubscriptionCommenceDate(): ?DateTime
     {
         return $this->subscriptionCommenceDate;
     }
 
     public function setSubscriptionCommenceDate(?DateTime $subscriptionCommenceDate): void
     {
         $this->subscriptionCommenceDate = $subscriptionCommenceDate;
     }
 
     public function getSubscriptionRenewalDate(): ?DateTime
     {
         return $this->subscriptionRenewalDate;
     }
 
     public function setSubscriptionRenewalDate(?DateTime $subscriptionRenewalDate): void
     {
         $this->subscriptionRenewalDate = $subscriptionRenewalDate;
     }
    */
 }
 