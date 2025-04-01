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

namespace OrangeHRM\Authentication\Service;

use GuzzleHttp\Client;
use OrangeHRM\Admin\Traits\Service\UserServiceTrait;
use OrangeHRM\CorporateBranding\Traits\ThemeServiceTrait;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Authentication\Exception\AuthenticationException;
use OrangeHRM\Core\Traits\Auth\AuthUserTrait;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Entity\EmployeeTerminationRecord;
use OrangeHRM\Entity\User;

class AuthenticationService
{
    use AuthUserTrait;
    use UserServiceTrait;
    use ThemeServiceTrait;

    /**
     * @param User|null $user
     * @return bool
     * @throws AuthenticationException
     */
    public function setCredentialsForUser(?User $user): bool
    {
        if (!$user instanceof User || $user->isDeleted()) {
            return false;
        } else {
            if (!$user->getStatus()) {
                throw AuthenticationException::userDisabled();
            } elseif ($user->getEmpNumber() === null) {
                throw AuthenticationException::employeeNotAssigned();
            }/* elseif ($user->getEmployee()->getEmployeeTerminationRecord() instanceof EmployeeTerminationRecord) {
                throw AuthenticationException::employeeTerminated();
            }*/

            $this->setUserAttributes($user);
            return true;
        }
    }

    /**
     * @param UserCredential $credentials
     * @return bool
     * @throws AuthenticationException
     */
    public function hasCredentials(UserCredential $credentials, string $theme): bool
    {
        return $this->getUserService()->checkExistsUser($credentials, $theme);
    }
    
    /**
     * @param UserCredential $credentials
     * @return ?string
     * @throws AuthenticationException
     */
    public function setCredentials(UserCredential $credentials, bool $isCompany, string $theme): ?string
    {
        error_log("SetCredentials Credentials : " . json_encode($credentials));
        error_log("SetCredentials Credentials Username : " . json_encode($credentials->getUsername()));
        error_log("SetCredentials Credentials Password : " . json_encode($credentials->getPassword()));
        error_log("SetCredentials Credentials Role : " . json_encode($credentials->getRole()));
        $user = $this->getUserService()->getCredentials($credentials, $theme);
        error_log("SetCredentials User : " . json_encode($user));
        $success = $this->setCredentialsForUser($user);
        error_log("SetCredentials Success : " . json_encode($success));
        $token = null;
        if ($success)
        {
            $token = $this->setHedwigeCredentials($credentials, $isCompany, $theme);
            error_log("SetCredentials Token : " . json_encode($token));
        }
        return $token;
    }

    /**
     * @param UserCredential $credentials
     * @param bool $company
     * @return string
     * @throws AuthenticationException
     */
    public function createCredentials(UserCredential $credentials, bool $isCompany, string $theme): ?string
    {
        $user = $this->getUserService()->createCredentials($credentials, $theme);
        $success = $this->setCredentialsForUser($user);
        $token = null;
        if ($success)
        {
            $success = $this->createHedwigeCredentials($credentials, $isCompany, $theme);
            if ($success)
                $token = $this->setHedwigeCredentials($credentials, $isCompany, $theme);
        }
        return $token;
    }

    /**
     * @param UserCredential $credentials
     * @return string
     */
    protected function setHedwigeCredentials(UserCredential $credentials, bool $isCompany, string $theme) : ?string
    {
        $clientId = $this->getThemeService()->getClientId($theme);

        $client = new Client();
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = $isCompany ? "{$clientBaseUrl}/company/{$clientId}/login" : "{$clientBaseUrl}/user/{$clientId}/login";
            $response = $client->request('POST', $url, [
                'json' => [
                    'email' => $credentials->getUsername(),
                    'password' => $credentials->getPassword()
                ]
            ]);
            return (string) $response->getBody();
        } catch (\Exceptionon $e) {
            return null;
        }
    }
    
    /**
     * @param UserCredential $credentials
     * @return bool
     */
    protected function createHedwigeCredentials(UserCredential $credentials, bool $isCompany, string $theme) : bool
    {
        $clientId = $this->getThemeService()->getClientId($theme);

        $client = new Client();
        $clientToken = getenv('HEDWIGE_CLIENT_TOKEN');
        $clientBaseUrl = getenv('HEDWIGE_URL');

        try {
            $url = $isCompany ? "{$clientBaseUrl}/company/{$clientId}" : "{$clientBaseUrl}/user/{$clientId}";
            $client->request('POST', $url, [
                'json' => [
                    'email' => $credentials->getUsername(),
                    'password' => $credentials->getPassword()
                ]
            ]);
            return true;
        } catch (\Exceptionon $e) {
            return false;
        }
    }

    /**
     * @param User $user
     */
    protected function setUserAttributes(User $user): void
    {
        $this->getAuthUser()->setUserId($user->getId());
        $this->getAuthUser()->setUserRoleId($user->getUserRole()->getId());
        $this->getAuthUser()->setUserRoleName($user->getUserRole()->getName());
        if ($user->getEmployee() != null && $user->getEmployee() instanceof Employee) {
            $this->getAuthUser()->setEmpNumber($user->getEmployee()->getEmpNumber());
        }
    }
}
