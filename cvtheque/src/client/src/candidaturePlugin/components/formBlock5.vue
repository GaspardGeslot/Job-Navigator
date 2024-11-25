<template>
  <div class="formBlockLayout">
    <form class="formBlock6" @submit.prevent="onSubmit">
      <h3 class="formTitle" style="margin-top: 1rem">
        Facultatif (mais apprécié)
      </h3>
      <SubmitComponent @go-back="goBack" :is-disabled="validationSuivant"/>
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
        Vous avez un CV ? Déposez-le pour que les recruteurs découvrent votre
        parcours.
      </p>
      <!-- Le composant InputFile émet le fichier sélectionné via @emit-input -->
      <InputFile @emit-input="setFile" />
      <p v-if="file" class="fileNameDisplay">{{ file.name }}</p>
    </form>
  </div>
</template>

<script>
import InputFile from './inputFile.vue';
import SubmitComponent from './submit';
//import {APIService} from '@/core/util/services/api.service';

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
      file: null, // Stocker le fichier ici après réception du composant enfant
      isLoading: false,
      validationSuivant: false,
    };
  },
  methods: {
    setFile(file) {
      this.file = file; // Enregistrer le fichier reçu
    },
    async onSubmit() {
      // Vérifier si un fichier a été déposé
      if (!this.file) {
        // Si aucun fichier, ne pas faire la requête et simplement émettre l'événement avec uniquement la motivation
        this.$emit('situation-submitted', {
          motivation: this.motivation,
          file: this.file,
          //file: null,
        });
        // this.$toast.info(
        //   'Aucun fichier à envoyer, mais la motivation a été sauvegardée.',
        // );
        return; // Stopper ici, pas de requête API
      }

      // Si un fichier a été déposé, procéder à la requête
      // console.log('this.file', this.file);
      const formData = new FormData();
      formData.append('file', this.file);
      // console.log('formData', formData);

      this.isLoading = true;

      try {
        // const http = new APIService(
        //   window.appGlobal.baseUrl,
        //   `/api/v2/pim/employees/6/screen/candidature/attachments`,
        // );

        // await http.create(formData, {
        //   headers: {
        //     'Content-Type': 'multipart/form-data',
        //   },
        // });

        // this.$toast.success('Fichier envoyé avec succès');

        // Émettre l'événement avec la motivation et le fichier
        this.$emit('situation-submitted', {
          motivation: this.motivation,
          file: this.file,
        });
      } catch (error) {
        // console.error('Erreur lors de la soumission :', error);
        this.$toast.error('Erreur lors de la soumission');
      } finally {
        this.isLoading = false;
      }
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>
