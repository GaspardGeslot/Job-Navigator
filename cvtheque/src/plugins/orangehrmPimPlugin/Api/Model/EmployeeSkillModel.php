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
     * Constructor for EmployeeSkillModel.
     *
     * @param string|array $skillDataJson JSON string or array containing skill data.
     */
    public function __construct($skillDataJson)
    {
        if (is_string($skillDataJson)) {
            $skillData = json_decode($skillDataJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
            }
        } elseif (is_array($skillDataJson)) {
            $skillData = $skillDataJson;
        } else {
            throw new \InvalidArgumentException('Invalid data type. Expected JSON string or array.');
        }

        // error_log('EmployeeSkillModel: Received skill data: ' . json_encode($skillData, JSON_PRETTY_PRINT));

        $this->type = $skillData['type'] ?? null;
        $this->title = $skillData['title'] ?? null;
        $this->description = $skillData['description'] ?? null;
    }

    /**
     * Normalize the model to an array.
     *
     * @return array Normalized data.
     */
    public function normalize(): array
    {
        $normalized = [
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
        ];

        // error_log('EmployeeSkillModel: Normalized data: ' . json_encode($normalized, JSON_PRETTY_PRINT));

        return $normalized;
    }

    /**
     * Convert the model to an array for compatibility with other parts of the system.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
        ];

        // error_log('EmployeeSkillModel: toArray result: ' . json_encode($array, JSON_PRETTY_PRINT));

        return $array;
    }
}
