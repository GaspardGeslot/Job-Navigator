<template>
  <div class="formBlockLayout">
    <form class="formBlock6" @submit.prevent="onSubmit">
      <h3 class="formTitle">Facultatif (mais apprécié)</h3>
      <p class="CVText">
        Qu'est-ce qui vous plairait dans le fait de travailler dans la
        construction ?
      </p>
      <textarea
        id="review"
        v-model="motivation"
        class="blackPlaceholder"
        placeholder="Indiquez ici ce que vous aimez. Par exemple : travailler en équipe, être dehors, le travail manuel...."
      ></textarea>
      <p class="CVText">
        Vous avez un CV ? Déposez le pour les recruteurs découvrent votre
        parcours.
      </p>
      <InputFile @emit-input="setFile" />
      <p v-if="file" class="fileNameDisplay">{{ file.name }}</p>
      <!-- 
        <div class="custom-file-input">
        <p class="CVText">Vous avez un CV ?</p>
        <input
          type="file"
          placeholder="Télécharger"
          @change="handleFileUpload"
        />
      </div>
      <input
        class="marginButton submitButton"
        :class="{'adjust-margin': file && file.name}"
        type="submit"
        value="SUIVANT"
      />
    -->
      <SubmitComponent @go-back="goBack" />
    </form>
  </div>
</template>
<script>
import InputFile from './inputFile.vue';
import SubmitComponent from './submit';
export default {
  name: 'FormFive',
  components: {
    InputFile,
    SubmitComponent,
  },
  emits: ['situation-submitted', 'go-back'],
  data() {
    return {
      motivation: '',
      file: null,
      fileName: '',
    };
  },
  methods: {
    setFile(file, fileName) {
      this.file = file;
      this.fileName = fileName;
      console.log(this.file);
      console.log(typeof this.file.name);
    },
    handleFileUpload(event) {
      this.file = event.target.files[0];
      //console.log(event.target.files[0]);
      this.fileName = this.file.name;
      console.log(this.file);
    },
    onSubmit() {
      let productReview = {
        motivation: this.motivation,
        file: this.file,
        fileName: this.fileName,
      };
      this.$emit('situation-submitted', productReview);
      this.motivation = '';
      this.file = null;
      this.fileName = '';
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.fileNameDisplay {
  text-align: center;
  margin: 0.5rem auto;
}
.adjust-margin {
  margin-top: 0.15rem;
}
.CVText {
  color: #414957;
  font-family: Nunito Sans, sans-serif;
}
</style>
