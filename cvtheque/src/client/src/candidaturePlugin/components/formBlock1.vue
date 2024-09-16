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
      <select v-if="needs" v-model="type_de_contrat">
        <option disabled value="">Type de contrat recherché *</option>
        <option v-for="(item, index) in needs" :key="index" :value="item">
          {{ item }}
        </option>
      </select>
      <input
        id="postalCode"
        v-model="postal_code"
        type="number"
        class="blackPlaceholder"
        placeholder="Mon code postal *"
      />
      <select v-if="courseStarts" v-model="availability">
        <option disabled value="">Ma disponibilité *</option>
        <option
          v-for="(item, index) in courseStarts"
          :key="index"
          :value="item"
        >
          {{ item }}
        </option>
      </select>
      <select v-model="mobility">
        <option disabled value="">Ma mobilité géographique</option>
        <option>10 kms</option>
        <option>30 kms</option>
        <option>50 kms</option>
        <option>100 kms</option>
        <option>Ile-de-France</option>
        <option>France entière</option>
      </select>
      <select v-if="studyLevels" id="educationForm" v-model="education">
        <option disabled value="">Plus haut niveau de diplôme *</option>
        <option v-for="(item, index) in studyLevels" :key="index" :value="item">
          {{ item }}
        </option>
      </select>
      <p v-if="errors.postalCode" id="alert-msg01" class="alert-msg">
        Veuillez indiquer un code postal valide.
      </p>
      <p v-if="errors.incompleteForm" id="alert-msg02" class="alert-msg">
        L'évaluation est incomplète. Veuillez remplir tous les champs.
      </p>
      <input
        id="formBlock1SubmitButton"
        class="submitButton"
        type="submit"
        value="SUIVANT"
      />
    </form>
  </div>
</template>
<script>
export default {
  name: 'FormOne',
  props: {
    courseStarts: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['situation-submitted'],
  data() {
    return {
      type_de_contrat: '',
      postal_code: '',
      availability: '',
      mobility: '',
      education: '',
      errors: {
        postalCode: false,
        incompleteForm: false,
      },
    };
  },
  methods: {
    onSubmit() {
      let hasError = false;
      this.errors.postalCode = false;
      this.errors.incompleteForm = false;
      if (
        this.type_de_contrat === '' ||
        this.postal_code === '' ||
        this.availability === '' ||
        this.education === ''
      ) {
        this.errors.incompleteForm = true;
        hasError = true;
      }
      // let arrayOfPostalCode = Array.from(this.postal_code, Number);
      // console.log(typeof this.postal_code);
      // console.log(arrayOfPostalCode, arrayOfPostalCode.length);
      // if (this.postal_code != '' && arrayOfPostalCode.length < 5) {
      //   if (arrayOfPostalCode.length < 4) return;
      //   else if (arrayOfPostalCode.length == 4) {
      //     arrayOfPostalCode.unshift(0);
      //     return;
      //   }
      // }
      let postal_code_array = Array.from(
        String(Number(this.postal_code)),
        Number,
      );
      console.log(postal_code_array);
      if (postal_code_array.length != 5) {
        if (postal_code_array.length == 4) {
          postal_code_array.unshift(0);
        } else {
          this.errors.postalCode = true;
          hasError = true;
        }
      }
      if (hasError) {
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
/* #educationForm {
  background-image: none;
} */

#formBlock1SubmitButton {
  margin-top: 2rem;
}
.formBlock1 input::placeholder {
  text-indent: -0.75rem;
}
.formBlock1 input:not(.submitButton) {
  width: 100%;
  box-sizing: border-box;
  padding-left: 1rem;
}
/* .formBlock1 > input {
  width: 100%;
  box-sizing: border-box;
  padding-left: 1rem;
} */
#postalCode::-webkit-outer-spin-button,
#postalCode::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
/* #postalCode {
  appearance: none;
  -moz-appearance: textfield;
} */
@media screen and (max-width: 400px) {
  .formBlock1 select {
    background-image: none;
    padding-left: 0.5rem;
  }
  .formBlock1 input:not(.submitButton) {
    width: 100%;
    box-sizing: border-box;
    padding-left: 0.5rem;
  }
  /* .formBlock1 > input {
    width: 100%;
    box-sizing: border-box;
    padding-left: 0.5rem;
  } */
}
@media screen and (max-width: 450px) {
  #formBlock1SubmitButton {
    margin-top: 1rem;
  }
}
</style>
