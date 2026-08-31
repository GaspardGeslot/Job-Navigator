<template>
  <div v-if="enabled" class="olecio-login-container">
    <button
      class="olecio-login-button"
      type="button"
      :disabled="isLoading"
      :class="{loading: isLoading}"
      aria-label="Se connecter avec Olecio"
      @click="triggerOlecioLogin"
    >
      <img
        v-if="!isLoading"
        src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/olecio_logo_small_v2.png"
        alt="Connexion Olecio"
        class="olecio-logo"
      />
      <div v-else class="loading-spinner"></div>
    </button>

    <form
      ref="olecioValidateForm"
      method="post"
      :action="submitUrl"
      class="olecio-hidden-form"
    >
      <input name="_token" :value="csrfToken" type="hidden" />
      <input name="email" :value="formEmail" type="hidden" />
      <input name="password" :value="formPassword" type="hidden" />
      <input name="origin" :value="oauthOrigin" type="hidden" />
    </form>
  </div>
</template>

<script>
import {urlFor} from '@/core/util/helper/url';
import {
  handleOlecioAuthCallback,
  isOlecioAuthConfigured,
  parseOlecioCredentials,
  startOlecioAuthFlow,
} from '../utils/olecioAuth';

export default {
  name: 'OlecioConnexion',

  props: {
    enabled: {
      type: Boolean,
      default: false,
    },
    authUrl: {
      type: String,
      default: '',
    },
    authClientId: {
      type: String,
      default: '',
    },
    /** Page d'origine pour le redirect d'erreur uniquement : login | createAccount */
    oauthOrigin: {
      type: String,
      default: 'login',
    },
    csrfToken: {
      type: String,
      required: true,
    },
  },

  emits: ['error', 'loading'],

  data() {
    return {
      isLoading: false,
      formEmail: '',
      formPassword: '',
      submitted: false,
    };
  },

  computed: {
    isConfigured() {
      return (
        this.enabled && isOlecioAuthConfigured(this.authUrl, this.authClientId)
      );
    },
    submitUrl() {
      return urlFor(`/${window.appGlobal.theme}/auth/validateOlecio`);
    },
  },

  mounted() {
    if (this.enabled) {
      this.tryCompleteRedirectFlow();
    }
  },

  methods: {
    setLoading(loading) {
      this.isLoading = loading;
      this.$emit('loading', loading);
    },

    triggerOlecioLogin() {
      if (!this.isConfigured) {
        this.$emit('error', 'Configuration OAuth Olecio incomplète.');
        return;
      }

      this.setLoading(true);
      startOlecioAuthFlow(
        this.authUrl,
        this.authClientId,
        (payload) => {
          void this.processOlecioAuth(payload);
        },
        () => {
          this.setLoading(false);
        },
        {
          origin: this.oauthOrigin,
          returnPath: window.location.pathname,
        },
      );
    },

    processOlecioAuth(payload) {
      this.setLoading(true);
      const credentials = parseOlecioCredentials(payload);

      if (!credentials) {
        this.setLoading(false);
        this.$emit(
          'error',
          'Authentification échouée : informations utilisateur incomplètes.',
        );
        return;
      }

      this.submitCredentials(credentials.email, credentials.sub);
    },

    submitCredentials(email, sub) {
      if (this.submitted) {
        return;
      }
      this.submitted = true;
      this.formEmail = email;
      this.formPassword = sub;
      this.$nextTick(() => {
        this.$refs.olecioValidateForm.submit();
      });
    },

    tryCompleteRedirectFlow() {
      const params = new URLSearchParams(window.location.search);
      const token = params.get('token');
      if (!token) {
        return;
      }

      // Ignore CSRF-only pages: OAuth redirect always includes user_id or email.
      if (!params.get('user_id') && !params.get('email')) {
        return;
      }

      const result = handleOlecioAuthCallback({
        token,
        user_id: params.get('user_id') || '',
        email: params.get('email') || '',
      });

      if (result.status === 'posted') {
        return;
      }

      if (result.status === 'error') {
        this.$emit('error', result.message);
        return;
      }

      window.history.replaceState({}, '', window.location.pathname);
      this.processOlecioAuth(result.payload);
    },
  },
};
</script>

<style scoped lang="scss">
.olecio-login-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.25rem;
  gap: 0.75rem;
}

.olecio-login-label {
  font-size: 0.8rem;
  text-align: center;
  color: #666;
  margin: 0;
}

.olecio-login-button {
  height: 3.5rem;
  width: 3.5rem;
  padding: 0.5rem;
  background-color: #ffffff;
  border: 1px solid #dadce0;
  border-radius: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.olecio-login-button:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #202124;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
  transform: translateY(-1px);
}

.olecio-login-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.olecio-login-text {
  font-weight: 700;
  font-size: 1rem;
  color: #202124;
}

.olecio-hidden-form {
  display: none;
}

.loading-spinner {
  width: 1.6rem;
  height: 1.6rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #2c3e50;
  border-radius: 50%;
  animation: olecio-spin 1s linear infinite;
}

.olecio-logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

@keyframes olecio-spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
