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

class AuthParams implements AuthParamsInterface
{
    private ?UserCredentialInterface $credential;
    private ?AuthAttributeBagInterface $attributeBag;
    private ?string $theme;

    /**
     * @param UserCredentialInterface|null $credential
     * @param AuthAttributeBagInterface|null $attributeBag
     * @param string|null $theme
     */
    public function __construct(?UserCredentialInterface $credential, ?AuthAttributeBagInterface $attributeBag = null, ?string $theme = null)
    {
        $this->credential = $credential;
        $this->attributeBag = $attributeBag;
        $this->theme = $theme;
    }
    /**
     * @inheritDoc
     */
    public function getCredential(): ?UserCredentialInterface
    {
        return clone $this->credential;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeBag(): ?AuthAttributeBagInterface
    {
        return clone $this->attributeBag;
    }

    /**
     * @inheritDoc
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }
}
