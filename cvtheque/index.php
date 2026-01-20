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

use OrangeHRM\Config\Config;

require realpath(__DIR__ . '/src/vendor/autoload.php');

/* For logging PHP errors */
include_once('./src/config/log_settings.php');

if (!Config::isInstalled()) {
    header('Location: ./installer/index.php');
} else {
    // Détection du sous-domaine
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $subdomain = '';
    
    // Extraire le sous-domaine si présent (format: subdomain.domain.com)
    $parts = explode('.', $host);
    if (count($parts) >= 3 || (count($parts) === 2 && $parts[1] === 'localhost')) {
        $subdomain = $parts[0];
    }

    // Déterminer la redirection selon le sous-domaine
    $location = './web/index.php/constructys/candidature/index'; // Par défaut (pas de sous-domaine)
    
    if ($subdomain === 'olecio' || $subdomain === 'olecio-demo') {
        $location = './web/index.php/auth/admin/login';
    } elseif ($subdomain === 'maraudes' || $subdomain === 'maraudes-demo') {
        $location = './web/index.php/home/index';
    }
    
    header("Location: $location");
}
