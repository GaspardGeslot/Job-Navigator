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

namespace OrangeHRM\Core\Registration\Dao;

use OrangeHRM\Core\Dao\BaseDao;
use OrangeHRM\Entity\RegistrationEventQueue;
use OrangeHRM\ORM\ListSorter;

class RegistrationEventQueueDao extends BaseDao
{
    /**
     * @param RegistrationEventQueue $registrationEventQueue
     * @return RegistrationEventQueue
     */
    public function saveRegistrationEvent(RegistrationEventQueue $registrationEventQueue): RegistrationEventQueue
    {
        $this->persist($registrationEventQueue);
        return $registrationEventQueue;
    }

    /**
     * @param $eventType
     * @return RegistrationEventQueue|null
     */
    public function getRegistrationEventByType($eventType): ?RegistrationEventQueue
    {
        $q = $this->createQueryBuilder(RegistrationEventQueue::class, 'registrationEvent');
        $q->where('registrationEvent.eventType = :eventType')
            ->setParameter('eventType', $eventType)
            ->addOrderBy('registrationEvent.id', ListSorter::DESCENDING);

        return $this->fetchOne($q);
    }

    /**
     * @param $limit
     * @return array
     */
    public function getUnpublishedRegistrationEvents($limit): array
    {
        $q = $this->createQueryBuilder(RegistrationEventQueue::class, 'registrationEvent');
        $q->andWhere('registrationEvent.published = :published')
            ->setParameter('published', false);
        $q->setMaxResults($limit);
        return $q->getQuery()->execute();
    }
    /**
     * Sauvegarde un événement spécifique aux sessions de formulaire.
     *
     * @param string $sessionId
     * @param int $step
     * @param \DateTime $createdAt
     * @return RegistrationEventQueue
     */
    public function saveFormSessionEvent(string $sessionId, int $step, \DateTime $createdAt): RegistrationEventQueue
    {
        $event = new RegistrationEventQueue();
        $event->setEventType($step);  // Enregistrer l'étape dans event_type
        $event->setEventTime($createdAt);  // Enregistrer la date dans event_time
        $event->setPublished(false);  // Marquer comme non publié

        // Enregistrer l'ID de session dans data
        $event->setData([
            'sessionId' => $sessionId
        ]);
        // $event->setData(json_encode(['sessionId' => $sessionId]));
        // $event->setData($sessionId);
        // Persiste l'événement dans la base de données
        $this->persist($event);

        return $event;
    }

    /**
     * Récupère un événement de session de formulaire par sessionId.
     *
     * @param string $sessionId
     * @return RegistrationEventQueue|null
     */
    public function getEventBySessionId(string $sessionId): ?RegistrationEventQueue
    {
        $events = $this->createQueryBuilder(RegistrationEventQueue::class, 'e')
                   ->getQuery()
                   ->getResult();

        foreach ($events as $event) {
            $data = $event->getData();

            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            if (isset($data['sessionId']) && $data['sessionId'] === $sessionId) {
                return $event;
            }
        }
        return null;
    }

    public function setEventQueueDao(RegistrationEventQueueDao $eventQueueDao): void
    {
        $this->eventQueueDao = $eventQueueDao;
    }
}
