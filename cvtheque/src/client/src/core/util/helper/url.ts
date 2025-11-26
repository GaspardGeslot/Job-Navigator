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

/**
 * @param endpoint
 * @param params
 * @param query
 * @returns {string}
 */
export const prepare = function (
  endpoint: string,
  params: {[key: string]: string | number} = {},
  query: {[key: string]: string | number | boolean | string[]} = {},
): string {
  let preparedEndpoint = endpoint;
  query = JSON.parse(JSON.stringify(query));
  Object.keys(params).forEach((param) => {
    const paramPlaceholder = `{${param}}`;
    if (preparedEndpoint.includes(paramPlaceholder)) {
      let paramValue = params[param];
      if (typeof paramValue === 'number') {
        paramValue = paramValue.toString();
      }
      preparedEndpoint = preparedEndpoint.replace(paramPlaceholder, paramValue);
    } else {
      // eslint-disable-next-line no-console
      console.error('Invalid parameter.');
    }
  });
  let preparedQueryString = '?';
  const queryKeys = Object.keys(query);
  queryKeys.forEach((queryKey, index) => {
    if (index !== 0) {
      preparedQueryString += '&';
    }
    const queryValue = query[queryKey];
    if (Array.isArray(queryValue)) {
      queryValue.forEach((queryValueItem, itemIndex) => {
        if (itemIndex !== 0) {
          preparedQueryString += '&';
        }
        preparedQueryString += `${queryKey}[]=${queryValueItem}`;
      });
    } else {
      preparedQueryString += `${queryKey}=${queryValue}`;
    }
  });
  return encodeURI(
    preparedEndpoint + (queryKeys.length === 0 ? '' : preparedQueryString),
  );
};

/**
 * Détecte si on utilise un sous-domaine pour le thème
 * @returns {boolean}
 */
export const isUsingSubdomain = function (): boolean {
  const validThemes = ['constructys', 'olecio', 'maraudes'];
  const hostname = window.location.hostname;

  for (const theme of validThemes) {
    if (hostname.startsWith(`${theme}.`)) {
      return true;
    }
  }

  return false;
};

/**
 * Construit une URL en tenant compte du mode subdomain ou fallback
 * @param path - Le chemin sans le theme (ex: '/auth/login')
 * @returns {string}
 */
export const buildThemeUrl = function (path: string): string {
  // @ts-expect-error: appGlobal is not in window object by default
  const theme = window.appGlobal.theme || 'constructys';
  // @ts-expect-error: appGlobal is not in window object by default
  const baseUrl = window.appGlobal.baseUrl || '';

  // Si on utilise un sous-domaine, ne pas ajouter le theme dans l'URL
  if (isUsingSubdomain()) {
    return baseUrl + path;
  }

  // Mode fallback : ajouter le theme dans l'URL
  return baseUrl + `/${theme}${path}`;
};

/**
 * Construit une URL d'API en tenant compte du mode subdomain
 * Utilisez cette fonction au lieu de `${window.appGlobal.theme}/api/...`
 * @param apiPath - Le chemin de l'API (ex: '/api/v2/admin/users' ou 'api/v2/admin/users')
 * @returns {string}
 */
export const apiUrl = function (apiPath: string): string {
  // Assure que le path commence par /
  const normalizedPath = apiPath.startsWith('/') ? apiPath : `/${apiPath}`;

  // @ts-expect-error: appGlobal is not in window object by default
  const theme = window.appGlobal.theme || 'constructys';

  // Si on utilise un sous-domaine, retourner le path sans theme
  if (isUsingSubdomain()) {
    return normalizedPath;
  }

  // Mode fallback : ajouter le theme dans l'URL
  return `${theme}${normalizedPath}`;
};

/**
 * Nettoie l'endpoint en supprimant le theme si on est en mode subdomain
 * @param endpoint
 * @returns {string}
 */
const cleanEndpointForSubdomain = function (endpoint: string): string {
  if (!isUsingSubdomain()) {
    return endpoint;
  }

  const validThemes = ['constructys', 'olecio', 'maraudes'];

  // Supprime le theme du début du path si présent
  for (const theme of validThemes) {
    const themePrefix = `/${theme}/`;
    if (endpoint.startsWith(themePrefix)) {
      // Retourne l'endpoint sans le theme mais garde le / au début
      return '/' + endpoint.substring(themePrefix.length);
    }
  }

  return endpoint;
};

/**
 * @param endpoint
 * @param params
 * @param query
 * @returns {string}
 */
export const urlFor = function (
  endpoint: string,
  params: {[key: string]: string | number} = {},
  query: {[key: string]: string | number | boolean | string[]} = {},
): string {
  // Nettoie l'endpoint si on est en mode subdomain
  const cleanedEndpoint = cleanEndpointForSubdomain(endpoint);

  // @ts-expect-error: appGlobal is not in window object by default
  return window.appGlobal.baseUrl + prepare(cleanedEndpoint, params, query);
};
