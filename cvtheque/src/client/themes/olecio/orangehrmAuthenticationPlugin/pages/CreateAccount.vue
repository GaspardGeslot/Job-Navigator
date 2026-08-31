<template>
  <login-layout>
    <oxd-text class="orangehrm-login-title" tag="h5">
      {{ $t('auth.create_accont') }}
    </oxd-text>
    <div class="orangehrm-login-form">
      <olecio-connexion
        :enabled="olecioAuthEnabled"
        :auth-url="olecioAuthUrl"
        :auth-client-id="olecioAuthClientId"
        oauth-origin="createAccount"
        :csrf-token="token"
        @error="onOlecioError"
        @loading="onOlecioLoading"
      />

      <div class="orangehrm-login-error">
        <oxd-alert
          :show="displayError !== null"
          :message="displayError?.message || ''"
          type="error"
        ></oxd-alert>
      </div>

      <oxd-form
        ref="createAccountForm"
        method="post"
        :action="submitUrl"
        @submit-valid="onSubmit"
      >
        <input name="_token" :value="token" type="hidden" />

        <oxd-form-row>
          <oxd-input-field
            v-model="email"
            name="email"
            :label="$t('general.email')"
            label-icon="person"
            :placeholder="$t('general.email')"
            :rules="rules.email"
            autofocus
          />
        </oxd-form-row>

        <oxd-form-row>
          <oxd-input-field
            v-model="password"
            name="password"
            :label="$t('general.password')"
            label-icon="key"
            :placeholder="$t('auth.password')"
            type="password"
            :rules="rules.password"
          />
        </oxd-form-row>

        <oxd-form-row>
          <oxd-input-field
            v-model="password_confirm"
            name="password_confirm"
            :label="$t('general.password_confirm')"
            label-icon="key"
            :placeholder="$t('auth.password_confirm')"
            type="password"
            :rules="rules.password_confirm"
          />
        </oxd-form-row>

        <div class="orangehrm-login-error">
          <oxd-alert
            :show="error_email_format"
            :message="$t('auth.error_email_format')"
            type="error"
          ></oxd-alert>
        </div>

        <div class="orangehrm-login-error">
          <oxd-alert
            :show="error_password"
            :message="$t('auth.error_password_no_match')"
            type="error"
          ></oxd-alert>
        </div>

        <oxd-form-actions class="orangehrm-login-action">
          <oxd-button
            class="orangehrm-login-button"
            display-type="main"
            :label="$t('auth.create_accont')"
            type="submit"
            :disabled="olecioLoading"
          />
        </oxd-form-actions>
        <oxd-form-actions class="orangehrm-create-account-action">
          <oxd-button
            class="orangehrm-create-account-button"
            display-type="main"
            :label="$t('candidature.login')"
            :disabled="olecioLoading"
            @click="navigateUrl"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </login-layout>
</template>

<script>
import {urlFor} from '@/core/util/helper/url';
import {OxdAlert} from '@ohrm/oxd';
import {required} from '@/core/util/validation/rules';
import {navigate, reloadPage} from '@/core/util/helper/navigation';
import LoginLayout from '../components/LoginLayout.vue';
import OlecioConnexion from '../components/OlecioConnexion.vue';

export default {
  components: {
    'oxd-alert': OxdAlert,
    'login-layout': LoginLayout,
    'olecio-connexion': OlecioConnexion,
  },

  props: {
    error: {
      type: Object,
      default: () => null,
    },
    token: {
      type: String,
      required: true,
    },
    olecioAuthUrl: {
      type: String,
      default: '',
    },
    olecioAuthClientId: {
      type: String,
      default: '',
    },
    olecioAuthEnabled: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      email: '',
      password: '',
      password_confirm: '',
      rules: {
        email: [required],
        password: [required],
        password_confirm: [required],
      },
      submitted: false,
      error_password: false,
      error_email_format: false,
      olecioError: null,
      olecioLoading: false,
    };
  },

  computed: {
    submitUrl() {
      return urlFor(`/${window.appGlobal.theme}/auth/validateNewAccount/admin`);
    },
    displayError() {
      return this.olecioError || this.error;
    },
  },

  beforeMount() {
    setTimeout(() => {
      reloadPage();
    }, 1200000); // 20 * 60 * 1000 (20 minutes);
  },

  methods: {
    onSubmit() {
      // eslint-disable-next-line
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
        this.error_email_format = true;
        this.error_password = false;
      } else if (this.password !== this.password_confirm) {
        this.error_email_format = false;
        this.error_password = true;
      } else if (!this.submitted && !this.olecioLoading) {
        this.error_email_format = false;
        this.error_password = false;
        this.submitted = true;
        this.$refs.createAccountForm.$el.submit();
      }
    },
    onOlecioError(message) {
      this.olecioError = {message};
    },
    onOlecioLoading(loading) {
      this.olecioLoading = loading;
    },
    navigateUrl() {
      navigate(`/${window.appGlobal.theme}/auth/admin/login`);
    },
  },
};
</script>

<style src="./login.scss" lang="scss" scoped></style>
