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

namespace OrangeHRM\Pim\Api\Model;

use OrangeHRM\Core\Api\V2\Serializer\ModelTrait;
use OrangeHRM\Core\Api\V2\Serializer\Normalizable;
use OrangeHRM\Entity\Employee;
// use OrangeHRM\Entity\EmployeeSkill;

/**
 * @OA\Schema(
 *     schema="Pim-EmployeeSkillModel",
 *     type="object",
 *     @OA\Property(property="type", description="The type of the skill", type="string"),
 *     @OA\Property(property="title", description="The title of the skill", type="string"),
 *     @OA\Property(property="description", description="The description of the skill", type="string")
 * )
 */
class EmployeeSkillModel implements Normalizable
{
    use ModelTrait;

    /**
     * @var string|null
     */
    private ?string $type;

    /**
     * @var string|null
     */
    private ?string $title;

    /**
     * @var string|null
     */
    private ?string $description;

    /**
     * @param string|object $skillDataJson
     * Accepte soit une chaîne JSON, soit un objet contenant les données de compétence
     */
    public function __construct($skillDataJson)
    {
        if (is_string($skillDataJson)) {
            $skillData = json_decode($skillDataJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur lors de la décodification du JSON : ' . json_last_error_msg());
            }
        } else {
            $skillData = (array) $skillDataJson;
        }

        $this->type = $skillData['type'] ?? null;
        $this->title = $skillData['title'] ?? null;
        $this->description = $skillData['description'] ?? null;

        // $this->setEntity($employeeSkill);
        // $this->setFilters(
        //     [
        //         'yearsOfExp',
        //         'comments',
        //         ['getSkill', 'getId'],
        //         ['getSkill', 'getName'],
        //         ['getSkill', 'getDescription']
        //     ]
        // );
        // $this->setAttributeNames(
        //     [
        //         'yearsOfExperience',
        //         'comments',
        //         ['skill', 'id'],
        //         ['skill', 'name'],
        //         ['skill', 'description']
        //     ]
        // );
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
