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
  <div class="orangehrm-forgot-password-container">
    <div class="orangehrm-forgot-password-wrapper">
      <div class="orangehrm-card-container">
        <oxd-form
          ref="resetForm"
          method="post"
          :action="submitUrl"
          @submit-valid="onSubmit"
        >
          <oxd-text tag="h6">
            {{ $t('auth.reset_password') }}
          </oxd-text>
          <oxd-divider />
          <card-note
            :note-text="$t('auth.set_new_password')"
            class="orangehrm-forgot-password-card-note"
          />
          <input name="_token" :value="token" type="hidden" />
          <oxd-form-row>
            <oxd-input-field
              :value="username"
              :label="$t('general.email')"
              readonly
              name="username"
              label-icon="person"
            />
          </oxd-form-row>
          <oxd-form-row class="orangehrm-forgot-password-row">
            <password-strength-indicator
              v-if="user.newPassword"
              :password-strength="passwordStrength"
            ></password-strength-indicator>
            <oxd-input-field
              v-model="user.newPassword"
              :rules="rules.newPassword"
              :label="$t('auth.new_password')"
              :placeholder="$t('auth.password')"
              name="password"
              type="password"
              label-icon="key"
              autocomplete="off"
            />
          </oxd-form-row>
          <oxd-form-row>
            <oxd-input-field
              v-model="user.confirmPassword"
              :rules="rules.confirmPassword"
              :placeholder="$t('auth.password')"
              :label="$t('general.confirm_password')"
              type="password"
              label-icon="key"
              autocomplete="off"
              name="confirmPassword"
            />
          </oxd-form-row>
          <oxd-divider />
          <div class="orangehrm-forgot-password-buttons">
            <oxd-button
              :label="$t('general.save')"
              size="large"
              type="submit"
              display-type="secondary"
              class="orangehrm-forgot-password-button"
            />
          </div>
        </oxd-form>
      </div>
    </div>
    <!--<slot name="footer"></slot>-->
  </div>
</template>

<script>
import {
  required,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';
import {promiseDebounce} from '@ohrm/oxd';
import CardNote from '../components/CardNote';
import {urlFor} from '@/core/util/helper/url';
import {APIService} from '@/core/util/services/api.service';
import usePasswordPolicy from '@/core/util/composable/usePasswordPolicy';
import PasswordStrengthIndicator from '@/core/components/labels/PasswordStrengthIndicator';

export default {
  name: 'ResetPassword',

  components: {
    'card-note': CardNote,
    'password-strength-indicator': PasswordStrengthIndicator,
  },

  props: {
    username: {
      type: String,
      required: true,
    },
    token: {
      type: String,
      required: true,
    },
  },

  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '');
    const {passwordStrength, validatePassword} = usePasswordPolicy(http);

    return {
      http,
      passwordStrength,
      validatePassword,
    };
  },

  data() {
    return {
      user: {
        username: '',
        newPassword: '',
        confirmPassword: '',
      },
      rules: {
        newPassword: [
          required,
          shouldNotExceedCharLength(64),
          promiseDebounce(this.validatePassword, 500),
        ],
        confirmPassword: [
          required,
          shouldNotExceedCharLength(64),
          (v) =>
            (!!v && v === this.user.newPassword) ||
            this.$t('general.passwords_do_not_match'),
        ],
      },
    };
  },

  computed: {
    submitUrl() {
      return urlFor(`/${window.appGlobal.theme}/auth/resetPassword`);
    },
  },

  methods: {
    onSubmit() {
      this.$refs.resetForm.$el.submit();
    },
  },
};
</script>

<style src="./reset-password.scss" lang="scss" scoped></style>
