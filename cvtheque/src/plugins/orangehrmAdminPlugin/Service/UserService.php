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

namespace OrangeHRM\Admin\Service;

use OrangeHRM\Admin\Dao\UserDao;
use OrangeHRM\Admin\Dto\UserSearchFilterParams;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Core\Utility\PasswordHash;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\User;
use OrangeHRM\Entity\UserRole;

class UserService
{
    use UserRoleManagerTrait;

    public const USERNAME_MIN_LENGTH = 5;
    public const USERNAME_MAX_LENGTH = 40;

    private UserDao $userDao;
    private PasswordHash $passwordHasher;

    /**
     * @return UserDao
     */
    public function getUserDao(): UserDao
    {
        return $this->userDao ??= new UserDao();
    }

    /**
     * @param UserDao $userDao
     */
    public function setUserDao(UserDao $userDao): void
    {
        $this->userDao = $userDao;
    }

    /**
     * @return PasswordHash
     */
    public function getPasswordHasher(): PasswordHash
    {
        return $this->passwordHasher ??= new PasswordHash();
    }

    /**
     * @param PasswordHash $passwordHasher
     */
    public function setPasswordHasher(PasswordHash $passwordHasher): void
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @param User $user
     * @return User|null
     */
    public function saveSystemUser(User $user): ?User
    {
        if ((Config::PRODUCT_MODE === Config::MODE_DEMO && is_null($user->getCreatedBy()))) {
            return $user;
        }
        if (!is_null($user->getDecorator()->getNonHashedPassword())) {
            $user->setUserPassword(
                $user->getDecorator()->getNonHashedPassword() !==  "" ?
                    $this->hashPassword($user->getDecorator()->getNonHashedPassword()) :
                    null
            );
            $user->getDecorator()->setNonHashedPassword(null);
        }

        return $this->getUserDao()->saveSystemUser($user);
    }

    /**
     * Get System User for given User Id
     * @param int $userId
     * @return User|null
     */
    public function getSystemUser(int $userId): ?User
    {
        return $this->getUserDao()->getSystemUser($userId);
    }

    /**
     * Soft Delete System Users
     * @param array $deletedIds
     * @return int
     */
    public function deleteSystemUsers(array $deletedIds): int
    {
        return $this->getUserDao()->deleteSystemUsers($deletedIds);
    }

    /**
     * Get User role with given name
     * @param string $roleName
     * @return UserRole|null
     */
    public function getUserRole(string $roleName): ?UserRole
    {
        return $this->getUserDao()->getUserRole($roleName);
    }

    /**
     * @param UserSearchFilterParams $userSearchParamHolder
     * @return int
     */
    public function getSearchSystemUsersCount(UserSearchFilterParams $userSearchParamHolder): int
    {
        return $this->getUserDao()->getSearchSystemUsersCount($userSearchParamHolder);
    }

    /**
     * @param UserSearchFilterParams $userSearchParamHolder
     * @return User[]
     */
    public function searchSystemUsers(UserSearchFilterParams $userSearchParamHolder): array
    {
        return $this->getUserDao()->searchSystemUsers($userSearchParamHolder);
    }

    /**
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function isCurrentPassword(int $userId, string $password): bool
    {
        $user = $this->getUserDao()->getSystemUser($userId);

        if (!$user instanceof User || $user->getUserPassword() === null) {
            return false;
        }

        $hash = $user->getUserPassword();
        if ($this->checkPasswordHash($password, $hash)) {
            return true;
        } elseif ($this->checkForOldHash($password, $hash)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $roleName
     * @param bool $includeInactive
     * @param bool $includeTerminated
     * @return Employee[]
     */
    public function getEmployeesByUserRole(
        string $roleName,
        bool $includeInactive = false,
        bool $includeTerminated = false
    ): array {
        return $this->getUserDao()->getEmployeesByUserRole($roleName, $includeInactive, $includeTerminated);
    }

    /**
     * @param UserCredential $credentials
     * @return User|null
     */
    public function getCredentials(UserCredential $credentials): ?User
    {
        $user = $this->getUserDao()->isExistingSystemUser($credentials);
        if ($user instanceof User) {
            $hash = $user->getUserPassword();
            if ($this->checkPasswordHash($credentials->getPassword(), $hash)) {
                return $user;
            } elseif ($this->checkForOldHash($credentials->getPassword(), $hash)) {
                // password matches, but in old format. Need to update hash
                $user->getDecorator()->setNonHashedPassword($credentials->getPassword());
                return $this->saveSystemUser($user);
            }
        }

        return null;
    }

    /**
     * @param UserCredential $credentials
     * @return User|null
     */
    public function createCredentials(UserCredential $credentials): ?User
    {
        return $this->getUserDao()->saveNewUser($credentials->getUsername(), $this->hashPassword($credentials->getPassword()));
    }

    /**
     * Hash password for storage
     * @param string $password
     * @return string hashed password
     */
    private function hashPassword(string $password): string
    {
        return $this->getPasswordHasher()->hash($password);
    }

    /**
     * @param UserCredential $credentials
     * @return bool
     */
    public function checkExistsUser(UserCredential $credentials): bool
    {
        return !is_null($this->getUserDao()->isExistingSystemUser($credentials));
    }

    /**
     * Checks if the password hash matches the password.
     * @param string $password
     * @param string $hash
     * @return bool
     */
    private function checkPasswordHash(string $password, string $hash): bool
    {
        return $this->getPasswordHasher()->verify($password, $hash);
    }

    /**
     * Check if password matches hash for hashes stored using older hash methods.
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    private function checkForOldHash(string $password, string $hash): bool
    {
        return $hash == md5($password);
    }

    /**
     * @return int[]
     */
    public function getUndeletableUserIds(): array
    {
        $undeletableIds = [];
        $user = $this->getUserRoleManager()->getUser();
        if ($user instanceof User) {
            $undeletableIds[] = $user->getId();
        }
        if (Config::PRODUCT_MODE === Config::MODE_DEMO &&
            ($defaultAdminUser = $this->getUserDao()->getDefaultAdminUser()) instanceof User) {
            $undeletableIds[] = $defaultAdminUser->getId();
        }

        return $undeletableIds;
    }
}
