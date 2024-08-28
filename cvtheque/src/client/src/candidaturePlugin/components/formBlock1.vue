import SubmitButton from '@/core/components/buttons/SubmitButton.vue';
<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle" style="margin-top: 0.5rem">
      Prêt(e) pour une nouvelle aventure ?
    </h3>
    <p class="formSubTitle">
      Complétez le formulaire, votre profil sera automatiquement envoyé aux
      entreprises qui recrutent.
    </p>
    <form class="formBlock1">
      <select v-model="type_de_contrat">
        <option disabled value="">Type de contrat</option>
        <option>CDI</option>
        <option>CDD</option>
        <option>Intérim</option>
        <option>Alternance</option>
        <option>Stage</option>
      </select>
      <input
        id="postalCode"
        v-model="postal_code"
        class="blackPlaceholder"
        placeholder="Mon code postal"
      />
      <select v-model="availability">
        <option disabled value="">Disponibilité</option>
        <option>Immédiatement</option>
        <option>Dans 1 à 3 mois</option>
        <option>Plus de 3 mois</option>
      </select>
      <select v-model="mobility">
        <option disabled value="">Mobilité</option>
        <option>10 kms</option>
        <option>30 kms</option>
        <option>50 kms</option>
        <option>100 kms</option>
        <option>Ile-de-France</option>
        <option>France entière</option>
      </select>
      <select v-model="education">
        <option disabled value="">Niveau d’études</option>
        <option>Aucun diplôme</option>
        <option>CFG, DNB</option>
        <option>CAP, BEP, BT</option>
        <option>Baccalauréat</option>
        <option>BTS, DUT, DEUG</option>
        <option>Licence</option>
        <option>Maîtrise, Master</option>
        <option>Doctorat</option>
      </select>
      <input
        id="formBlock1SubmitButton"
        class="submitButton"
        type="submit"
        value="SUIVANT"
        style="margin-top: 2rem"
      />
    </form>
  </div>
</template>
<script>
export default {
  name: 'FormOne',
  emits: ['situation-submitted'],
  data() {
    return {
      type_de_contrat: '',
      postal_code: '',
      availability: '',
      mobility: '',
      education: '',
    };
  },
  methods: {
    onSubmit() {
      if (this.type_de_contrat === '' || this.postal_code === '') {
        alert("L'évaluation est incomplète. Veuillez remplir tous les champs.");
        return;
      }
      let situationReview = {
        type_de_contrat: this.type_de_contrat,
        postal_code: this.postal_code,
        availability: this.availability,
        mobility: this.mobility,
        education: this.education,
      };
      this.$emit('situation-submitted', situationReview);
      this.type_de_contrat = '';
      this.postal_code = '';
      this.availability = '';
      this.mobility = '';
      this.education = '';
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.formBlock1 select {
  /* border: unset;
  width: 100%;
  max-width: 400px;
  padding: 0.75rem;
  border: 1px solid rgba(0, 0, 0, 0.1); */
  padding-left: 1rem;
  appearance: none;
  background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
  background-repeat: no-repeat;
  background-position: right 1rem top 50%;
  background-size: 0.65rem auto;
}
#formBlock1SubmitButton {
  margin-top: 1rem;
}
.formBlock1 input::placeholder {
  text-indent: -0.75rem;
}
.formBlock1 input:not(.submitButton) {
  width: 100%;
  box-sizing: border-box;
  padding-left: 1rem;
}
</style>
