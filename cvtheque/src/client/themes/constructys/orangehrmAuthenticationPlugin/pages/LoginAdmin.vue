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
    <oxd-form class="orangehrm-login-form">
      <oxd-form-row>
        <oxd-text class="orangehrm-profile-title" tag="h6">Je suis</oxd-text>
      </oxd-form-row>
      <oxd-form-row>
        <oxd-form-actions>
          <oxd-button
            class="orangehrm-profile-button"
            display-type="secondary"
            :label="$t('Un administrateur')"
          />
        </oxd-form-actions>
        <oxd-form-actions>
          <oxd-button
            class="orangehrm-profile-button"
            display-type="ghost"
            :label="$t('Un candidat')"
            @click="navigateUrlConnexion"
          />
          <oxd-button
            class="orangehrm-profile-button"
            display-type="ghost"
            :label="$t('Une entreprise')"
            @click="navigateUrlLoginCompany"
          />
        </oxd-form-actions>
      </oxd-form-row>
    </oxd-form>
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
            v-model="username"
            name="username"
            :label="$t('general.email')"
            label-icon="person"
            :placeholder="$t('general.email')"
            :rules="rules.username"
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
            @click="navigateUrlForgotPassword"
          >
            {{ $t('auth.forgot_password') }} ?
          </oxd-text>
        </div>
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

export default {
  components: {
    'oxd-alert': OxdAlert,
    'login-layout': LoginLayout,
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
  },

  data() {
    return {
      username: '',
      password: '',
      rules: {
        username: [required],
        password: [required],
      },
      submitted: false,
    };
  },

  computed: {
    submitUrl() {
      return urlFor(`/${window.appGlobal.theme}/auth/validate/admin`);
    },
  },

  beforeMount() {
    setTimeout(() => {
      reloadPage();
    }, 1200000); // 20 * 60 * 1000 (20 minutes);
  },

  methods: {
    onSubmit() {
      if (!this.submitted) {
        this.submitted = true;
        this.$refs.loginForm.$el.submit();
      }
    },
    navigateUrlForgotPassword() {
      navigate(`/${window.appGlobal.theme}/auth/requestPasswordResetCode`);
    },
    navigateUrlLoginCompany() {
      navigate(`/${window.appGlobal.theme}/auth/company/login`);
    },
    navigateUrlConnexion() {
      navigate(`/${window.appGlobal.theme}/auth/login`);
    },
  },
};
</script>

<style src="./login.scss" lang="scss" scoped></style>
