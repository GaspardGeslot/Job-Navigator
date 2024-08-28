<template>
  <div class="VA_bg_img blank" :class="{faded: formVisible}">
    <div class="col remove">
      <div class="mt space-b top-row row">
        <div class="space-b row">
          <img
            class="mr-2 mt-05 top-row-logo"
            src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/olecio_logo_white.png"
          />
          <img
            class="mb-05 top-row-logo"
            src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/constructys_logo2.png"
          />
        </div>
        <div>
          <button class="connexion_button">CONNEXION</button>
        </div>
      </div>
      <div class="row">
        <h1 class="text-white main-title">
          Avec ou sans CV, trouvez un emploi dans le secteur de la construction.
        </h1>
      </div>
      <div class="row">
        <button class="bot_button" @click="showForm">JE POSTULE</button>
      </div>
    </div>
    <div v-if="formVisible" class="formContainer">
      <formComponent class="formComponent" />
    </div>
  </div>
</template>

<script>
import formComponent from '../components/formComponent.vue';

export default {
  name: 'ViewApplication',
  components: {
    formComponent,
  },
  data() {
    return {
      formVisible: false,
    };
  },
  methods: {
    showForm() {
      this.formVisible = true;
    },
  },
};
</script>

<style src="./view-application.scss" lang="scss"></style>
<style scoped>
@media screen and (max-width: 430px) {
  .remove {
    display: none;
  }
  .blank {
    background-image: none;
    background-color: gainsboro;
  }
}
</style>
