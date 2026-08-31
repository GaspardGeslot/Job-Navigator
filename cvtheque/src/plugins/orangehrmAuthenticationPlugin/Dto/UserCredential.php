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

namespace OrangeHRM\Authentication\Dto;

class UserCredential implements UserCredentialInterface
{
    private ?string $username = null;
    private ?string $password = null;
    private ?string $role = null;
    private ?string $oauthSource = null;

    /**
     * @param string|null $username
     * @param string|null $password
     * @param string|null $role
     * @param string|null $oauthSource
     */
    public function __construct(
        ?string $username = null,
        ?string $password = null,
        ?string $role = null,
        ?string $oauthSource = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
        $this->oauthSource = $oauthSource;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     */
    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return string|null
     */
    public function getOauthSource(): ?string
    {
        return $this->oauthSource;
    }

    /**
     * @param string|null $oauthSource
     */
    public function setOauthSource(?string $oauthSource): void
    {
        $this->oauthSource = $oauthSource;
    }
}
