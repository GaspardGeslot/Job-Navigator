<template>
  <div class="orangehrm-forgot-password-container">
    <div class="orangehrm-forgot-password-wrapper">
      <div class="orangehrm-card-container">
        <oxd-form
          ref="resetForm"
          method="post"
          :loading="isLoading"
          @submit-valid="onSubmit"
        >
          <oxd-text tag="h6">
            {{ $t('Redefinir mot de passe') }}
          </oxd-text>
          <oxd-divider />
          <div class="orangehrm-login-error">
            <oxd-alert
              :show="true"
              :message="
                error?.message ||
                $t(
                  'Votre mot de passe actuel n\'est pas assez sécurisé. Veuillez le redéfinir.',
                )
              "
              type="error"
            ></oxd-alert>
          </div>
          <oxd-form-row>
            <oxd-input-field
              :value="email"
              :label="$t('general.email')"
              readonly
              name="email"
              label-icon="person"
            />
            <oxd-input-field
              v-model="user.currentPassword"
              :rules="rules.currentPassword"
              :label="$t('pim.current_password')"
              type="password"
              label-icon="key"
              autocomplete="off"
              name="currentPassword"
            />
          </oxd-form-row>
          <oxd-form-row class="orangehrm-forgot-password-row">
            <password-strength-indicator
              v-if="user.newPassword"
              :password-strength="passwordStrength"
            >
            </password-strength-indicator>
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
  </div>
</template>

<script>
import {
  required,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';
import {promiseDebounce, OxdAlert} from '@ohrm/oxd';
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import usePasswordPolicy from '@/core/util/composable/usePasswordPolicy';
import PasswordStrengthIndicator from '@/core/components/labels/PasswordStrengthIndicator';

export default {
  name: 'RedefinePassword',

  components: {
    'password-strength-indicator': PasswordStrengthIndicator,
    'oxd-alert': OxdAlert,
  },

  props: {
    email: {
      type: String,
      required: true,
    },
  },

  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '/');
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
        currentPassword: '',
        confirmPassword: '',
      },
      rules: {
        currentPassword: [required, shouldNotExceedCharLength(64)],
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
      isLoading: false,
      error: null,
    };
  },

  methods: {
    onSubmit() {
      this.isLoading = true;
      const data = {
        email: this.email,
        password: this.user.currentPassword,
        newPassword: this.user.confirmPassword,
      };
      this.http
        .request({
          method: 'POST',
          url: `/api/v2/auth/redefinePassword`,
          data: data,
        })
        .then((response) => {
          if (response.data.error === false) {
            navigate(response.data.redirectUrl);
          } else {
            this.error = {
              message: response.data.message,
            };
            this.isLoading = false;
          }
        })
        .catch(() => {
          this.error = {
            message: this.$t('Identifiants invalides'),
          };
          this.isLoading = false;
        });
    },
  },
};
</script>

<style src="./reset-password.scss" lang="scss" scoped></style>
