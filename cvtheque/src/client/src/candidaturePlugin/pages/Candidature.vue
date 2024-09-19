<template>
  <div class="VA_bg_img blank" :class="{faded: formVisible}">
    <div id="page-content" class="col remove">
      <div class="mt space-b top-row row">
        <div class="space-b row">
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
        <button class="bot_button" @click="showForm">JE POSTULE</button>
      </div>
    </div>
    <div v-if="formVisible" class="formContainer">
      <formComponent
        :options="options"
        class="formComponent"
        @close-form="hideForm"
      />
    </div>
  </div>
</template>

<script>
import formComponent from '../components/formComponent.vue';
//import {urlFor} from '@ohrm/core/util/helper/url';
import {navigate} from '@ohrm/core/util/helper/navigation';

export default {
  name: 'Candidature',
  components: {
    formComponent,
  },
  props: {
    options: {
      type: Object,
      default: () => null,
    },
  },
  data() {
    return {
      formVisible: false,
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

<style src="./view-application.scss" lang="scss" scoped></style>
