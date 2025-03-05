import { inject } from 'vue';
<template>
  <div class="formBlockLayout">
    <form class="formBlock6" @submit.prevent="onSubmit">
      <h3 class="formTitle">Pour finir</h3>
      <SubmitComponent :is-disabled="validationSuivant" @go-back="goBack" />
      <p v-if="errors.incompleteForm" id="alert-msg05" class="alert-msg">
        L'évaluation est incomplète. Veuillez remplir tous les champs.
      </p>
      <p v-if="errors.email" id="alert-msg06" class="alert-msg">
        Veuillez entrer une adresse e-mail valide.
      </p>
      <p v-if="errors.password" id="alert-msg07" class="alert-msg">
        Les mots de passe ne correspondent pas. Veuillez réessayer.
      </p>
      <p v-if="errors.phoneNumber" id="alert-msg08" class="alert-msg">
        Veuillez indiquer un numéro de téléphone valide.
      </p>
      <p v-if="errors.checked" id="alert-msg09" class="alert-msg">
        Vous devez accepter les conditions générales et la politique de
        confidentialité des données.
      </p>
      <p v-if="cannotCreateAccount" class="alert-msg">
        Cette adresse mail est déjà liée à un compte, veuillez renseigner une
        autre adresse ou vous
        <a
          style="text-decoration: underline; cursor: pointer"
          @click="sendLeadAndRedirect"
        >
          connecter à votre compte
        </a>
      </p>
      <input
        id="lastName"
        v-model="lastName"
        class="blackPlaceholder"
        placeholder="Nom"
      />
      <input
        id="firstName"
        v-model="firstName"
        class="blackPlaceholder"
        placeholder="Prénom"
      />
      <input
        id="email"
        v-model="email"
        type="email"
        class="blackPlaceholder"
        placeholder="E-mail"
      />
      <input
        id="phone"
        v-model="phone"
        type="number"
        class="blackPlaceholder"
        placeholder="Téléphone ex:0142274949"
      />
      <div class="AcceptanceofTerms-container">
        <div class="checkbox-container">
          <label class="switch">
            <input v-model="createAccount" type="checkbox" @change="toggle" />
            <span class="slider round"></span>
          </label>
          <label class="AcceptanceofTermsText">
            Je souhaite créer un compte pour pouvoir accéder à des opportunités
            et modifier mes informations ultérieurement ou mettre en pause ma
            recherche d'emploi.
          </label>
        </div>
        <div v-if="createAccount" class="passwordFields">
          <div class="password-input-wrapper">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="blackPlaceholder"
              placeholder="Mot de passe *"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
            >
              <svg
                v-if="!showPassword"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path
                  d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </button>
          </div>
          <div class="password-input-wrapper">
            <input
              v-model="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="blackPlaceholder"
              placeholder="Confirmer le mot de passe *"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <svg
                v-if="!showConfirmPassword"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path
                  d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </button>
          </div>
        </div>
        <div class="checkbox-container">
          <label class="switch big-switch">
            <input v-model="checked" type="checkbox" @change="toggle" />
            <span class="slider round"></span>
          </label>
          <label class="AcceptanceofTermsText">
            J'accepte les
            <a @click="navigateToCGU" style="color: blue; cursor: pointer">
              conditions générales du service
            </a>
            ainsi que la
            <a @click="navigateToCGU" style="color: blue; cursor: pointer">
              politique de confidentialité des données
            </a>
            .J'accepte d'être contacté par les entreprises qui recrutent et qui
            sont interessées par mon profil
          </label>
        </div>
      </div>
    </form>
  </div>
</template>
<script>
import {navigate} from '@/core/util/helper/navigation';
import SubmitComponent from './submit';
export default {
  name: 'FormSix',
  components: {
    SubmitComponent,
  },
  props: {
    cannotCreateAccount: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['situation-submitted', 'go-back', 'send-lead'],
  data() {
    return {
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      createAccount: false,
      password: '',
      confirmPassword: '',
      checked: false,
      errors: {
        incompleteForm: false,
        email: false,
        password: false,
        phoneNumber: false,
        checked: false,
      },
      validationSuivant: true,
      showPassword: false,
      showConfirmPassword: false,
    };
  },
  computed: {
    isFormValid() {
      return (
        this.lastName !== '' &&
        this.firstName !== '' &&
        this.email !== '' &&
        this.phone !== '' &&
        this.checked === true
      );
    },
  },
  watch: {
    isFormValid(newVal) {
      this.validationSuivant = !newVal;
    },
  },
  methods: {
    navigateToCGU() {
      navigate(`/${window.appGlobal.theme}/cgu/index`);
    },
    validateEmail() {
      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailPattern.test(this.email)) {
        alert('Veuillez entrer une adresse e-mail valide.');
        return false;
      }
      return true;
    },
    onSubmit() {
      this.isLoading = true;
      this.errors.incompleteForm = false;
      this.errors.email = false;
      this.errors.password = false;
      this.errors.phoneNumber = false;
      this.errors.checked = false;
      if (
        this.firstName === '' ||
        this.lastName === '' ||
        this.phone === '' ||
        this.email === ''
      ) {
        this.errors.incompleteForm = true;
        return;
      }
      if (!this.validateEmail()) {
        this.errors.email = true;
        return;
      }
      if (this.createAccount && this.password !== this.confirmPassword) {
        this.errors.password = true;
        return;
      }
      if (!this.checked) {
        this.errors.checked = true;
        return;
      }
      this.phone = this.phone.toString();
      if (this.phone.length == 9) {
        this.phone = '0' + this.phone;
      }
      if (this.phone.length != 10) {
        this.errors.phoneNumber = true;
        return;
      }
      let productReview = {
        firstName: this.firstName,
        lastName: this.lastName,
        email: this.email,
        phoneNumber: this.phone,
      };
      if (this.createAccount) {
        productReview.password = this.password;
      }
      this.$emit('situation-submitted', productReview);
      // this.firstName = '';
      // this.lastName = '';
      // this.email = '';
      // this.phone = '';
      this.isLoading = false;
    },
    sendLeadAndRedirect() {
      this.password = '';
      this.confirmPassword = '';
      this.onSubmit();
      navigate(`/${window.appGlobal.theme}/auth/login`);
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.submit-block {
  margin-top: 1rem;
}
.formBlock6 {
  display: flex;
  flex-direction: column;
}
.formBlock6 > *:not(.submit-block) {
  flex: 1;
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
  padding-top: 0.65rem;
  padding-bottom: 0.65rem;
}
.formBlock6 input::placeholder,
.formBlock6 select {
  padding-left: 0.75rem;
}
.AcceptanceofTermsText {
  padding-left: 1.5rem;
  font-family: 'Telegraf', sans-serif;
}
.switch {
  position: relative;
  display: inline-block;
  width: 46px;
  height: 14px;
}
/* .short-switch {
  width: 34.22px;
} */
.big-switch {
  width: 70px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
  border-radius: 18px;
}

.slider:before {
  position: absolute;
  content: '';
  height: 12.5px;
  width: 12.5px;
  left: 1px;
  bottom: 0.7px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #31ea3d;
}

input:focus + .slider {
  box-shadow: 0 0 1px #31ea3d;
}

input:checked + .slider:before {
  /* -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px); */
  transform: translateX(16px);
}
.slider.round {
  border-radius: 17px;
}

.slider.round:before {
  border-radius: 50%;
}
.passwordFields > * {
  flex: 1;
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
  padding-top: 0.65rem;
  padding-bottom: 0.65rem;
}
.passwordFields input::placeholder {
  text-indent: -0.75rem;
}
.passwordFields input {
  width: 100%;
  box-sizing: border-box;
  padding-left: 1rem;
}
@media screen and (max-width: 450px) {
  input:checked + .slider:before {
    transform: translateX(15px);
  }
}
@media screen and (max-width: 600px) {
  .slider {
    width: 29px;
  }
}
</style>
