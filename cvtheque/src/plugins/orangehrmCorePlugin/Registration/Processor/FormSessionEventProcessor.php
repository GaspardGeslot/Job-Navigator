<?php
/**
 * OrangeHRM Enterprise is a closed sourced comprehensive Human Resource Management (HRM)
 * System that captures all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM Inc is the owner of the patent, copyright, trade secrets, trademarks and any
 * other intellectual property rights which subsist in the Licensed Materials. OrangeHRM Inc
 * is the owner of the media / downloaded OrangeHRM Enterprise software files on which the
 * Licensed Materials are received. Title to the Licensed Materials and media shall remain
 * vested in OrangeHRM Inc. For the avoidance of doubt title and all intellectual property
 * rights to any design, new software, new protocol, new interface, enhancement, update,
 * derivative works, revised screen text or any other items that OrangeHRM Inc creates for
 * Customer shall remain vested in OrangeHRM Inc. Any rights not expressly granted herein are
 * reserved to OrangeHRM Inc.
 *
 * Please refer http://www.orangehrm.com/Files/OrangeHRM_Commercial_License.pdf for the license which includes terms and conditions on using this software.
 *
 */

namespace OrangeHRM\Core\Registration\Processor;

use OrangeHRM\Entity\RegistrationEventQueue;

class FormSessionEventProcessor extends AbstractRegistrationEventProcessor
{
    /**
     * @inheritDoc
     */
    public function getEventType(): int
    {
        // Retourne le type d'événement FORM_SESSION_START
        return RegistrationEventQueue::FORM_SESSION_START;
    }

    /**
     * @inheritDoc
     */
    public function getEventData(): array
    {
        // Ici, tu peux ajouter les données supplémentaires que tu souhaites associer à cet événement
        // Par exemple, si tu veux récupérer des statistiques sur les formulaires
        $registrationData = $this->getRegistrationEventGeneralData();
        
        // Tu pourrais ajouter ici des données spécifiques à la session de formulaire
        // Par exemple : le nombre total de sessions créées, ou des informations agrégées
        $registrationData['form_session_info'] = 'Some session-specific data';

        return $registrationData;
    }

    /**
     * @inheritDoc
     */
    public function getEventToBeSavedOrNot(): bool
    {
        // Ici, tu peux définir une condition pour décider si l'événement doit être enregistré ou non
        // Par exemple, tu pourrais vérifier si un certain nombre de formulaires ont été traités
        return true;  // Si tu veux toujours enregistrer cet événement, tu renvoies true
    }
}
