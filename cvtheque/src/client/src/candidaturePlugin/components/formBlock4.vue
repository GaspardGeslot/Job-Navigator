<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle">Permis et véhicule</h3>
    <p class="formSubTitle">Plusieurs choix possibles.</p>
    <form class="formBlock5">
      <p class="CVText">Permis obtenus</p>
      <div class="checkbox-group">
        <div class="checkbox-item2">
          <input
            id="permitA"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis A"
          />
          <label for="permitA">Permis A</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitB"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis B"
          />
          <label for="permitB">Permis B</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitC"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis C"
          />
          <label for="permitC">Permis C</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitD"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis D"
          />
          <label for="permitD">Permis D</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitL"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis L"
          />
          <label for="permitL">Permis L</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitG"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis G"
          />
          <label for="permitG">Permis G</label>
        </div>
      </div>

      <p class="adjust-margin CVText">Possédez-vous un véhicule personnel ?</p>
      <div class="radio-group">
        <div id="radio-item-left" class="radio-item">
          <input id="vehicleYes" v-model="picked" type="radio" value="Oui" />
          <label for="vehicleYes">Oui</label>
        </div>
        <div class="radio-item">
          <input id="vehicleNo" v-model="picked" type="radio" value="Non" />
          <label for="vehicleNo">Non</label>
        </div>
      </div>
      <SubmitComponent @go-back="goBack" />
    </form>
  </div>
</template>

<script>
import SubmitComponent from './submit';
export default {
  name: 'FormFour',
  components: {
    SubmitComponent,
  },
  emits: ['situation-submitted', 'go-back'],
  data() {
    return {
      checkedPermits: [],
      picked: null,
    };
  },
  methods: {
    onSubmit() {
      if (this.picked === null) {
        alert("L'évaluation est incomplète. Veuillez remplir tous les champs.");
        return;
      }
      let situationReview = {
        permits: this.checkedPermits,
        vehicle: this.picked,
      };
      //console.log(this.checkedPermits);
      this.$emit('situation-submitted', situationReview);
      this.checkedPermits = [];
      this.picked = null;
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.checkbox-item2 label {
  font-family: 'Telegraf', sans-serif;
}
.adjust-margin {
  margin-top: 1.5rem;
}
.submitButton {
  margin-top: 2rem;
}
</style>
