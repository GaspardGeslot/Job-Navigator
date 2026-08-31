/**
 * Olecio / Chevêche OAuth helpers for Job Navigator (olecio theme).
 */

export const OLECIO_AUTH_MESSAGE_TYPE = 'olecio-auth-success';
export const OLECIO_AUTH_STORAGE_KEY = 'olecio-auth-pending';

/**
 * @param {string} authUrl
 * @param {string} clientId
 * @returns {boolean}
 */
export function isOlecioAuthConfigured(authUrl, clientId) {
  return Boolean(authUrl && clientId);
}

/**
 * @param {string} authUrl
 * @param {string} clientId
 * @returns {string}
 */
export function buildOlecioAuthUrl(authUrl, clientId) {
  if (!authUrl || !clientId) {
    throw new Error('Configuration OAuth Olecio incomplète.');
  }
  const baseUrl = authUrl.replace(/\/$/, '');
  return `${baseUrl}/login?client_id=${encodeURIComponent(clientId)}`;
}

export function shouldUsePopup() {
  return window.matchMedia('(min-width: 768px)').matches;
}

/**
 * @param {string} authUrl
 * @param {string} clientId
 * @returns {Window|null}
 */
export function openOlecioAuthPopup(authUrl, clientId) {
  const width = 420;
  const height = 720;
  const left = window.screenX + (window.outerWidth - width) / 2;
  const top = window.screenY + (window.outerHeight - height) / 2;

  return window.open(
    buildOlecioAuthUrl(authUrl, clientId),
    'olecio-auth',
    `width=${width},height=${height},left=${left},top=${top},popup=yes,scrollbars=yes`,
  );
}

/**
 * Decode JWT payload without external dependency.
 * @param {string} token
 * @returns {{sub?: string, email?: string}|null}
 */
function decodeJwtPayload(token) {
  try {
    const parts = token.split('.');
    if (parts.length < 2) {
      return null;
    }
    const base64 = parts[1].replace(/-/g, '+').replace(/_/g, '/');
    const padded = base64.padEnd(
      base64.length + ((4 - (base64.length % 4)) % 4),
      '=',
    );
    return JSON.parse(atob(padded));
  } catch {
    return null;
  }
}

/**
 * @param {{token: string, user_id?: string|null, email?: string|null}} payload
 * @returns {{email: string, sub: string}|null}
 */
export function parseOlecioCredentials(payload) {
  let sub = payload.user_id || null;
  let email = payload.email || null;

  const decoded = decodeJwtPayload(payload.token);
  if (decoded) {
    sub = sub || decoded.sub || null;
    email = email || decoded.email || null;
  }

  if (!sub || !email) {
    return null;
  }

  return {email, sub};
}

/**
 * @param {string} authUrl
 * @param {string} clientId
 * @param {(payload: {token: string, user_id?: string|null, email?: string|null}) => void} onSuccess
 * @param {() => void} [onCancel]
 * @param {{role: string, returnPath: string}} [context]
 */
export function startOlecioAuthFlow(
  authUrl,
  clientId,
  onSuccess,
  onCancel,
  context,
) {
  if (context) {
    try {
      sessionStorage.setItem(OLECIO_AUTH_STORAGE_KEY, JSON.stringify(context));
    } catch {
      // ignore storage errors
    }
  }

  if (!shouldUsePopup()) {
    window.location.href = buildOlecioAuthUrl(authUrl, clientId);
    return;
  }

  const popup = openOlecioAuthPopup(authUrl, clientId);
  if (!popup) {
    window.location.href = buildOlecioAuthUrl(authUrl, clientId);
    return;
  }

  const handleMessage = (event) => {
    if (event.origin !== window.location.origin) return;
    if (event.data?.type !== OLECIO_AUTH_MESSAGE_TYPE) return;

    window.removeEventListener('message', handleMessage);
    clearInterval(pollTimer);

    onSuccess({
      token: event.data.token,
      user_id: event.data.user_id,
      email: event.data.email,
    });
  };

  window.addEventListener('message', handleMessage);

  const pollTimer = setInterval(() => {
    if (popup.closed) {
      clearInterval(pollTimer);
      window.removeEventListener('message', handleMessage);
      onCancel?.();
    }
  }, 500);
}

/**
 * Handle callback page: postMessage to opener or return payload for full-page flow.
 * @param {{token: string, user_id?: string, email?: string}} query
 * @returns {{status: 'posted'|'ready'|'error', payload?: object, message?: string}}
 */
export function handleOlecioAuthCallback(query) {
  const token = query.token || '';
  if (!token) {
    return {
      status: 'error',
      message: 'Authentification échouée : token absent.',
    };
  }

  const payload = {
    token,
    user_id: query.user_id || query.userId || '',
    email: query.email || '',
  };

  if (window.opener) {
    window.opener.postMessage(
      {
        type: OLECIO_AUTH_MESSAGE_TYPE,
        token: payload.token,
        user_id: payload.user_id,
        email: payload.email,
      },
      window.location.origin,
    );
    window.close();
    return {status: 'posted'};
  }

  return {status: 'ready', payload};
}

/**
 * @returns {{role: string, returnPath: string}|null}
 */
export function consumeOlecioAuthContext() {
  try {
    const raw = sessionStorage.getItem(OLECIO_AUTH_STORAGE_KEY);
    sessionStorage.removeItem(OLECIO_AUTH_STORAGE_KEY);
    if (!raw) return null;
    return JSON.parse(raw);
  } catch {
    return null;
  }
}
