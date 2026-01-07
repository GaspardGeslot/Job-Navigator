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

namespace OrangeHRM\Framework\Event;

use OrangeHRM\Framework\Http\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * SubdomainThemeSubscriber
 * 
 * Ce subscriber détecte automatiquement le thème depuis le sous-domaine de la requête.
 * Il supporte également un fallback sur la détection par path pour la rétrocompatibilité.
 * 
 * Exemples de détection :
 * - constructys.domain.com/auth/login  → theme = 'constructys'
 * - olecio.domain.com/dashboard        → theme = 'olecio'
 * - maraudes.domain.com/home           → theme = 'maraudes'
 * - domain.com/constructys/auth/login  → theme = 'constructys' (fallback)
 */
class SubdomainThemeSubscriber implements EventSubscriberInterface
{
    /**
     * Liste des thèmes valides dans l'application
     */
    private const VALID_THEMES = ['constructys', 'olecio', 'maraudes', 'olecio-demo', 'constructys-demo', 'maraudes-demo'];

    /**
     * Thème par défaut si aucun thème n'est détecté
     */
    private const DEFAULT_THEME = 'constructys';

    /**
     * Active/désactive le fallback sur la détection par path
     * Mettre à false une fois la migration complète pour désactiver les anciennes URLs
     */
    private const ENABLE_PATH_FALLBACK = true;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        // Priority 99600 = s'exécute AVANT le RouterListener (qui est à 99500 dans Framework.php)
        // Cela garantit que le thème est injecté avant le matching des routes
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 99600],
        ];
    }

    /**
     * Gère l'événement de requête pour détecter et injecter le thème
     *
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Ne traiter que la requête principale (pas les sous-requêtes)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $theme = $this->detectTheme($request);
        
        // Détecter si on utilise un sous-domaine
        $host = $request->getHost();
        $hostWithoutPort = preg_replace('/:\d+$/', '', $host);
        $isSubdomain = false;
        foreach (self::VALID_THEMES as $validTheme) {
            if (preg_match("/^{$validTheme}\./i", $hostWithoutPort)) {
                $isSubdomain = true;
                break;
            }
        }
        
        // Injecter le thème dans les attributs de la requête
        // Cela le rend accessible via $request->attributes->get('theme')
        $request->attributes->set('theme', $theme);
        $request->attributes->set('_use_subdomain', $isSubdomain);
        
        // Optionnel : rendre le thème disponible globalement
        $_ENV['APP_THEME'] = $theme;
        $_ENV['USE_SUBDOMAIN'] = $isSubdomain ? '1' : '0';
    }

    /**
     * Détecte le thème depuis la requête
     * Priorité : Sous-domaine > Path (si fallback activé) > Default
     *
     * @param Request $request
     * @return string Le thème détecté
     */
    private function detectTheme(Request $request): string
    {
        // 1. Priorité : Détection depuis le sous-domaine
        $theme = $this->getThemeFromSubdomain($request->getHost());
        if ($theme !== null) {
            return $theme;
        }

        // 2. Fallback : Détection depuis le path (rétrocompatibilité)
        if (self::ENABLE_PATH_FALLBACK) {
            $theme = $this->getThemeFromPath($request->getPathInfo());
            if ($theme !== null) {
                return $theme;
            }
        }

        // 3. Default : Si aucune détection ne fonctionne
        return self::DEFAULT_THEME;
    }

    /**
     * Extrait le thème depuis le sous-domaine de l'hôte
     * 
     * Exemples :
     * - constructys.domain.com      → 'constructys'
     * - olecio.domain.fr            → 'olecio'
     * - maraudes.localhost:8080     → 'maraudes'
     * - domain.com                  → null
     * - www.domain.com              → null
     *
     * @param string $host Le hostname de la requête
     * @return string|null Le thème détecté ou null
     */
    private function getThemeFromSubdomain(string $host): ?string
    {
        // Enlever le port si présent (ex: localhost:8080)
        $hostWithoutPort = preg_replace('/:\d+$/', '', $host);
        
        // Vérifier si le host commence par un thème valide
        foreach (self::VALID_THEMES as $theme) {
            // Pattern : theme.domain.com ou theme.localhost
            if (preg_match("/^{$theme}\./i", $hostWithoutPort)) {
                return $theme;
            }
        }
        
        return null;
    }

    /**
     * Extrait le thème depuis le premier segment du path (fallback)
     * 
     * Exemples :
     * - /constructys/auth/login  → 'constructys'
     * - /olecio/dashboard        → 'olecio'
     * - /maraudes/home           → 'maraudes'
     * - /auth/login              → null
     * - /                        → null
     *
     * @param string $pathInfo Le path de la requête
     * @return string|null Le thème détecté ou null
     */
    private function getThemeFromPath(string $pathInfo): ?string
    {
        // Nettoyer et extraire les segments du path
        $segments = explode('/', trim($pathInfo, '/'));
        $firstSegment = $segments[0] ?? '';
        
        // Vérifier si le premier segment est un thème valide
        if (in_array($firstSegment, self::VALID_THEMES, true)) {
            return $firstSegment;
        }
        
        return null;
    }

    /**
     * Obtient la liste des thèmes valides
     * Utile pour les tests ou la configuration dynamique
     *
     * @return array
     */
    public static function getValidThemes(): array
    {
        return self::VALID_THEMES;
    }

    /**
     * Obtient le thème par défaut
     *
     * @return string
     */
    public static function getDefaultTheme(): string
    {
        return self::DEFAULT_THEME;
    }

    /**
     * Vérifie si le fallback par path est activé
     *
     * @return bool
     */
    public static function isPathFallbackEnabled(): bool
    {
        return self::ENABLE_PATH_FALLBACK;
    }
}

