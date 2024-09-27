<template>
  <div class="VA_bg_img blank" :class="{faded: formVisible}">
    <div id="page-content" class="col remove">
      <div class="mt space-b-to-center top-row row">
        <div class="mr space-b row">
          <img
            id="olecio-logo"
            class="mr-2 mt-05 top-row-logo"
            src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/olecio_logo_white.png"
            style="margin-top: 0.82rem"
          />
          <img
            id="constructys-logo"
            class="ml-6 mb-05 top-row-logo"
            src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/constructys_logo2.png"
            style="margin-bottom: 0.82rem"
          />
        </div>
        <div>
          <button class="connexion_button" @click="navigateToLogin">
            CONNEXION
          </button>
        </div>
      </div>
      <div class="row title-container">
        <h1 class="text-white main-title">
          Avec ou sans CV, trouvez un emploi dans le secteur de la construction.
        </h1>
      </div>
      <div class="row">
        <button v-show="!formVisible" class="bot_button" @click="showForm">
          JE POSTULE
        </button>
      </div>
    </div>
    <div v-if="formVisible" class="formContainer">
      <!--
        <formComponent
        :options="options"
        class="formComponent"
        @close-form="hideForm"
      />
      <button
        class="exit-button"
        style="
          position: absolute;
          top: 10px;
          right: 10px;
          background-color: transparent;
          border: none;
        "
        @click="hideForm"
      >
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M4.70711 3.29289C4.31658 2.90237 3.68342 2.90237 3.29289 3.29289C2.90237 3.68342 2.90237 4.31658 3.29289 4.70711L10.5858 12L3.29289 19.2929C2.90237 19.6834 2.90237 20.3166 3.29289 20.7071C3.68342 21.0976 4.31658 21.0976 4.70711 20.7071L12 13.4142L19.2929 20.7071C19.6834 21.0976 20.3166 21.0976 20.7071 20.7071C21.0976 20.3166 21.0976 19.6834 20.7071 19.2929L13.4142 12L20.7071 4.70711C21.0976 4.31658 21.0976 3.68342 20.7071 3.29289C20.3166 2.90237 19.6834 2.90237 19.2929 3.29289L12 10.5858L4.70711 3.29289Z"
            fill="white"
          />
        </svg>
      </button>
      -->
    </div>
  </div>
</template>

<script>
//import formComponent from '../components/formComponent.vue';
//import {urlFor} from '@ohrm/core/util/helper/url';
import {navigate} from '@ohrm/core/util/helper/navigation';

export default {
  name: 'Candidature',
  // components: {
  //   formComponent,
  // },
  props: {
    options: {
      type: Object,
      default: () => null,
    },
  },
  data() {
    return {
      formVisible: true,
    };
  },

  methods: {
    showForm() {
      console.log('options :', this.options);
      this.formVisible = true;
    },
    hideForm() {
      this.formVisible = false;
    },
    navigateToLogin() {
      navigate('/auth/login');
    },
  },
};
</script>

<style src="./view-application.scss" lang="scss"></style>
<style scoped>
.title-container,
#olecio-logo {
  display: none;
}
@media screen and (max-width: 461px) {
  .exit-button {
    display: none;
  }
}
</style>
