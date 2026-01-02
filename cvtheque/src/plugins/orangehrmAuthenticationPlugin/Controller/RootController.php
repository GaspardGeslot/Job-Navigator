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

namespace OrangeHRM\Authentication\Controller;

use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Core\Controller\PublicControllerInterface;
use OrangeHRM\Framework\Http\RedirectResponse;
use OrangeHRM\Framework\Http\Request;

class RootController extends AbstractController implements PublicControllerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(Request $request)
    {
        $theme = $request->attributes->get('theme');
        $host = $request->getHost();
        
        // Détecte si on utilise un sous-domaine (ex: constructys.domain.com)
        $hostWithoutPort = preg_replace('/:\d+$/', '', $host);
        $isSubdomain = preg_match("/^{$theme}\./i", $hostWithoutPort);
        
        error_log('[RootController] Theme: ' . $theme . ', Host: ' . $host . ', IsSubdomain: ' . ($isSubdomain ? 'YES' : 'NO'));
        
        $baseUrl = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
        
        // Détermine la page d'accueil selon le thème
        switch ($theme) {
            case 'constructys':
                // Constructys supporte les 2 modes (subdomain + fallback)
                $redirectUrl = $isSubdomain 
                    ? $baseUrl . '/candidature/index'  // constructys.domain.com → /candidature/index
                    : $baseUrl . '/constructys/candidature/index';  // domain.com → /constructys/candidature/index
                break;
                
            case 'olecio':
                // Olecio supporte les 2 modes (subdomain + fallback)
                $redirectUrl = $isSubdomain 
                    ? $baseUrl . '/auth/admin/login'  // olecio.domain.com → /auth/admin/login
                    : $baseUrl . '/olecio/auth/admin/login';  // domain.com → /olecio/auth/admin/login
                break;
                
            case 'maraudes':
                // Maraudes : UNIQUEMENT subdomain (pas de fallback)
                $redirectUrl = $baseUrl . '/maraudes/index';  // maraudes.domain.com → /maraudes/index
                break;
                
            default:
                // Par défaut : page de login générique
                $redirectUrl = $baseUrl . '/auth/login';
                break;
        }
        
        error_log('[RootController] Redirecting to: ' . $redirectUrl);
        return new RedirectResponse($redirectUrl);
    }
}
