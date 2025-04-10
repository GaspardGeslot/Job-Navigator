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

namespace OrangeHRM\Authentication\Auth;

use OrangeHRM\Authentication\Dto\AuthParamsInterface;
use OrangeHRM\Authentication\Exception\AuthenticationException;

abstract class AbstractAuthProvider
{
    /**
     * @param AuthParamsInterface $authParams
     * @return ?string
     * @throws AuthenticationException
     */
    abstract public function authenticate(AuthParamsInterface $authParams): ?string;

    /**
     * @param AuthParamsInterface $authParams
     * @return ?string
     * @throws AuthenticationException
     */
    abstract public function authenticateCompany(AuthParamsInterface $authParams): ?string;

    /**
     * @param AuthParamsInterface $authParams
     * @return ?string
     * @throws AuthenticationException
     */
    abstract public function signIn(AuthParamsInterface $authParams): ?string;

    /**
     * @param AuthParamsInterface $authParams
     * @return ?string
     * @throws AuthenticationException
     * @throws PasswordEnforceException
     * @throws UserAlreadyEnrolledException
     */
    abstract public function signInFromCandidature(AuthParamsInterface $authParams): ?string;

    /**
     * @return int
     */
    abstract public function getPriority(): int;
}
