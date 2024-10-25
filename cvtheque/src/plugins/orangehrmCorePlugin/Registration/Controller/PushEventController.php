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

namespace OrangeHRM\Core\Registration\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Core\Registration\Processor\RegistrationEventProcessorFactory;
use OrangeHRM\Core\Registration\Dao\RegistrationEventQueueDao;
use OrangeHRM\Core\Traits\CacheTrait;
use OrangeHRM\Entity\RegistrationEventQueue;
use OrangeHRM\Framework\Http\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class PushEventController extends AbstractController implements PublicControllerInterface
{
    use CacheTrait;

    private RegistrationEventQueueDao $registrationEventQueueDao;

    /**
     * @return RegistrationEventQueueDao
     */
    public function getRegistrationEventQueueDao(): RegistrationEventQueueDao
    {
        return $this->registrationEventQueueDao ??= new RegistrationEventQueueDao();
    }

    /**
     * @return Response
     */
    public function handle(): Response
    {
        $cacheItem = $this->getCache()->getItem('core.registration.event.pushed');
        if ($cacheItem->isHit()) {
            return $this->getResponse();
        }

        try {
            $registrationEventProcessorFactory = new RegistrationEventProcessorFactory();
            $registrationEventProcessor = $registrationEventProcessorFactory->getRegistrationEventProcessor(
                RegistrationEventQueue::ACTIVE_EMPLOYEE_COUNT
            );
            $registrationEventProcessor->publishRegistrationEvents();
        } catch (Throwable $e) {
        }

        $cacheItem->expiresAfter(3600);
        $cacheItem->set(true);
        $this->getCache()->save($cacheItem);
        return $this->getResponse();
    }
    
     /**
     * Gère la logique de création et mise à jour des sessions de formulaire.
     * @return Response
     */
    public function createFormSession(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if ($request->isMethod('POST')) {
            if (!isset($data['sessionId']) || !isset($data['step']) || !isset($data['createdAt'])) {
                return $this->getResponse()->setContent(json_encode([
                    'error' => 'Missing session ID, step, or createdAt'
                ]))->setStatusCode(400);
            }

            $sessionId = $data['sessionId'];
            $step = (int) $data['step'];
            $createdAt = new \DateTime($data['createdAt']);

            try {
                $this->getRegistrationEventQueueDao()->saveFormSessionEvent($sessionId, $step, $createdAt);
                // $this->eventQueueDao->saveFormSessionEvent($sessionId, $step, $createdAt);
                // $eventQueueDao = new RegistrationEventQueueDao();
                // $eventQueueDao->saveFormSessionEvent($sessionId, $step, $createdAt);
                error_log("Form session event saved successfully");
                return $this->getResponse()->setContent(json_encode([
                    'message' => 'Form session event created successfully',
                    'sessionId' => $sessionId,
                    'step' => $step
                ]))->setStatusCode(200);

            } catch (Throwable $e) {
                error_log("Error in handleFormSession: " . $e->getMessage());
                return $this->getResponse()->setContent(json_encode([
                    'error' => $e->getMessage()
                ]))->setStatusCode(500);
            }
        }
    }

    /**
     * Gère la logique de création et mise à jour des sessions de formulaire.
     * @return Response
     */
    public function updateFormSession(Request $request, $sessionId): Response
    {
        $data = json_decode($request->getContent(), true);

    if (!isset($data['step'])) {
        return $this->getResponse()->setContent(json_encode([
            'error' => 'Missing step for update'
        ]))->setStatusCode(400);
    }

    $step = (int) $data['step'];

    try {
        $event = $this->getRegistrationEventQueueDao()->getEventBySessionId($sessionId);

        if (!$event) {
            return $this->getResponse()->setContent(json_encode([
                'error' => 'Session event not found'
            ]))->setStatusCode(404);
        }

        $event->setEventType($step);
        $this->getRegistrationEventQueueDao()->saveRegistrationEvent($event);

        error_log("Form session event updated successfully");
        return $this->getResponse()->setContent(json_encode([
            'message' => 'Form session event updated successfully',
            'sessionId' => $sessionId,
            'step' => $step
        ]))->setStatusCode(200);

        } catch (Throwable $e) {
            error_log("Error in updateFormSession: " . $e->getMessage());
            return $this->getResponse()->setContent(json_encode([
                'error' => $e->getMessage()
            ]))->setStatusCode(500);
        }
    }
}
