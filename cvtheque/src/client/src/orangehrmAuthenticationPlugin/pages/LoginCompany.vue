<!--
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
 -->

<template>
  <login-layout>
    <oxd-text class="orangehrm-login-title" tag="h5">
      {{ $t('auth.login') }}
    </oxd-text>
    <div class="orangehrm-login-form">
      <div class="orangehrm-login-error">
        <oxd-alert
          :show="error !== null"
          :message="error?.message || ''"
          type="error"
        ></oxd-alert>
      </div>
      <oxd-form
        ref="loginForm"
        method="post"
        :action="submitUrl"
        @submit-valid="onSubmit"
      >
        <input name="_token" :value="token" type="hidden" />

        <oxd-form-row>
          <oxd-input-field
            v-model="siret"
            name="siret"
            :label="$t('auth.siret')"
            label-icon="person"
            :placeholder="$t('auth.siret')"
            :rules="rules.siret"
            autofocus
          />
        </oxd-form-row>

        <oxd-form-row>
          <oxd-input-field
            v-model="adherentCode"
            name="adherentCode"
            :label="$t('auth.adherent_code_constructys')"
            label-icon="key"
            :placeholder="$t('auth.adherent_code_constructys')"
            type="password"
            :rules="rules.adherentCode"
          />
        </oxd-form-row>

        <div class="orangehrm-login-error">
          <oxd-alert
            :show="error_adherent_code"
            :message="$t('Le code adhérent doit être un nombre')"
            type="error"
          ></oxd-alert>
        </div>

        <oxd-form-actions class="orangehrm-login-action">
          <oxd-button
            class="orangehrm-login-button"
            display-type="main"
            :label="$t('auth.login')"
            type="submit"
          />
        </oxd-form-actions>
        <div class="orangehrm-login-forgot">
          <oxd-text
            class="orangehrm-login-forgot-header"
            @click="navigateUrlConnexion"
            style="text-align: center;"
          >
            {{ $t('auth.connection_candidate') }}
          </oxd-text>
        </div>
      </oxd-form>
      <template v-if="authenticators.length > 0">
        <oxd-divider class="orangehrm-login-seperator"></oxd-divider>
        <social-media-auth :authenticators="authenticators"></social-media-auth>
      </template>
    </div>
  </login-layout>
</template>

<script>
import {urlFor} from '@ohrm/core/util/helper/url';
import {OxdAlert} from '@ohrm/oxd';
import {required} from '@ohrm/core/util/validation/rules';
import {navigate, reloadPage} from '@ohrm/core/util/helper/navigation';
import LoginLayout from '@/orangehrmAuthenticationPlugin/components/LoginLayout.vue';
import SocialMediaAuth from '@/orangehrmAuthenticationPlugin/components/SocialMediaAuth.vue';

export default {
  components: {
    'oxd-alert': OxdAlert,
    //'oxd-sheet': OxdSheet,
    'login-layout': LoginLayout,
    'social-media-auth': SocialMediaAuth,
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
    showSocialMedia: {
      type: Boolean,
      default: true,
    },
    isDemoMode: {
      type: Boolean,
      default: false,
    },
    authenticators: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      siret: '',
      adherentCode: '',
      rules: {
        siret: [required],
        adherentCode: [required],
      },
      error_adherent_code: false,
      submitted: false,
    };
  },

  computed: {
    submitUrl() {
      return urlFor('/auth/company/validate');
    },
  },

  beforeMount() {
    setTimeout(() => {
      reloadPage();
    }, 1200000); // 20 * 60 * 1000 (20 minutes);
  },

  methods: {
    onSubmit() {
      if (isNaN(this.adherentCode)) {
        this.error_adherent_code = true;
      } else if (!this.submitted) {
        this.error_adherent_code = false;
        this.submitted = true;
        this.$refs.loginForm.$el.submit();
      }
    },
    navigateUrlForgotadherentCode() {
      navigate('/auth/requestadherentCodeResetCode');
    },
    navigateUrlConnexion() {
      navigate('/auth/login');
    },
  },
};
</script>

<style src="./login.scss" lang="scss" scoped></style>
