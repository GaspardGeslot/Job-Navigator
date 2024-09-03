import { inject } from 'vue';
<template>
  <div class="formBlockLayout">
    <form class="formBlock6" @submit.prevent="onSubmit">
      <h3 class="formTitle">Pour finir</h3>
      <input
        id="name"
        v-model="name"
        class="blackPlaceholder"
        placeholder="Nom"
      />
      <input
        id="surname"
        v-model="surname"
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
      <!--
      <input type="text" required />
      <div class="placeholder">Téléphone <span>ex:0142274949</span></div>
      -->
      <div class="AcceptanceofTerms-container">
        <div class="checkbox-container">
          <label class="switch">
            <input v-model="createAccount" type="checkbox" @change="toggle" />
            <span class="slider round"></span>
          </label>
          <label class="AcceptanceofTermsText">
            Je souhaite créer un compte pour pouvoir modifier mes informations
            ultérieurement ou mettre en pause ma recherche d’emploi.
          </label>
        </div>
        <div v-if="createAccount" class="passwordFields">
          <input
            id="password"
            v-model="password"
            type="password"
            class="blackPlaceholder"
            placeholder="Mot de passe"
            required
          />
          <input
            id="confirmPassword"
            v-model="confirmPassword"
            type="password"
            class="blackPlaceholder"
            placeholder="Confirmer le mot de passe"
            required
          />
        </div>
        <!--
        <div class="checkbox-container">
          <label class="switch short-switch">
            <input type="checkbox" @change="toggle" />
            <span class="slider round"></span>
          </label>
          <label class="AcceptanceofTermsText">
            J’accepte d’être contacté par des entreprises qui recrutent et qui
            sont intéressées par mon profil.
          </label>
        </div>
        -->
        <div class="checkbox-container">
          <label class="switch big-switch">
            <input v-model="checked" type="checkbox" @change="toggle" />
            <span class="slider round"></span>
          </label>
          <label class="AcceptanceofTermsText">
            J'accepte les conditions générales du service ainsi que la politique
            de confidentialité des données. J'accepte d'être contacté par les
            entreprises qui recrutent et qui sont interessées par mon profil
          </label>
        </div>
      </div>
      <SubmitComponent />
    </form>
  </div>
</template>
<script>
import SubmitComponent from './submit';
export default {
  name: 'FormSix',
  components: {
    SubmitComponent,
  },
  emits: ['situation-submitted'],
  data() {
    return {
      name: '',
      surname: '',
      email: '',
      phone: '',
      createAccount: false,
      password: '',
      confirmPassword: '',
      checked: false,
    };
  },
  methods: {
    validateEmail() {
      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailPattern.test(this.email)) {
        alert('Veuillez entrer une adresse e-mail valide.');
        return false;
      }
      return true;
    },
    onSubmit() {
      if (
        this.name === '' ||
        this.surname === '' ||
        this.phone === '' ||
        this.email === ''
      ) {
        alert("L'évaluation est incomplète. Veuillez remplir tous les champs.");
        return;
      }
      if (!this.validateEmail()) {
        alert('Veuillez entrer une adresse e-mail valide.');
        return;
      }
      if (this.createAccount && this.password !== this.confirmPassword) {
        alert('Les mots de passe ne correspondent pas. Veuillez réessayer.');
        return;
      }
      if (!this.checked) {
        alert(
          'Vous devez accepter les conditions générales et la politique de confidentialité des données.',
        );
        return;
      }
      this.phone = this.phone.toString();
      //console.log(this.phone, typeof this.phone, this.phone.length);
      if (this.phone.length == 9) {
        console.log(this.phone, typeof this.phone);
        this.phone = '0' + this.phone;
        // console.log('ici');
        // console.log(this.phone, typeof this.phone);
      }
      if (this.phone.length != 10) {
        alert('Veuillez indiquer un numéro de téléphone valide.');
        return;
      }
      let productReview = {
        name: this.name,
        surname: this.surname,
        email: this.email,
        phone: this.phone,
      };
      if (this.createAccount) {
        productReview.password = this.password;
        productReview.confirmPassword = this.confirmPassword;
      }
      this.$emit('situation-submitted', productReview);
      this.name = '';
      this.surname = '';
      this.email = '';
      this.phone = '';
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
