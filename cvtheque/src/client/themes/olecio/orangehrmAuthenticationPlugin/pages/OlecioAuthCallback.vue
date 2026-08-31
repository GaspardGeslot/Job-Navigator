<template>
  <div class="olecio-callback">
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    <p v-else>{{ statusMessage }}</p>
  </div>
</template>

<script>
import {
  consumeOlecioAuthContext,
  handleOlecioAuthCallback,
  parseOlecioCredentials,
} from '../utils/olecioAuth';

export default {
  name: 'OlecioAuthCallback',

  props: {
    token: {
      type: String,
      default: '',
    },
    userId: {
      type: String,
      default: '',
    },
    email: {
      type: String,
      default: '',
    },
  },

  data() {
    return {
      errorMessage: '',
      statusMessage: 'Connexion en cours…',
    };
  },

  mounted() {
    const result = handleOlecioAuthCallback({
      token: this.token,
      user_id: this.userId,
      email: this.email,
    });

    if (result.status === 'posted') {
      this.statusMessage = 'Connexion réussie, fermeture…';
      return;
    }

    if (result.status === 'error') {
      this.errorMessage = result.message;
      return;
    }

    const credentials = parseOlecioCredentials(result.payload);
    if (!credentials) {
      this.errorMessage =
        'Authentification échouée : informations utilisateur incomplètes.';
      return;
    }

    const context = consumeOlecioAuthContext();
    const origin = context?.origin || 'login';
    const returnPath =
      context?.returnPath ||
      (origin === 'createAccount'
        ? `/${window.appGlobal.theme}/auth/createAccount`
        : `/${window.appGlobal.theme}/auth/admin/login`);

    const redirectUrl = new URL(returnPath, window.location.origin);
    redirectUrl.searchParams.set('token', result.payload.token);
    redirectUrl.searchParams.set('user_id', credentials.sub);
    redirectUrl.searchParams.set('email', credentials.email);
    window.location.replace(redirectUrl.toString());
  },
};
</script>

<style scoped>
.olecio-callback {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: #333;
  padding: 1rem;
}

.error {
  color: #e74c3c;
  text-align: center;
  max-width: 420px;
}
</style>
