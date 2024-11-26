<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <form class="formBlock5">
      <!--
          <h3 class="formTitle">Avez-vous des certificats ou habilitations ?</h3>
          <p class="formSubTitle">Plusieurs choix possibles.</p>
          <div class="checkbox-group">
            <div class="checkbox-item">
              <input
                id="B0"
                v-model="checkedSkills"
                class="custom-checkbox custom-input"
                type="checkbox"
                value="Habilitations électriques"
              />
              <label for="B0"> Habilitations électriques</label>
            </div>
            <div class="checkbox-item">
              <input
                id="B1"
                v-model="checkedSkills"
                class="custom-checkbox custom-input"
                type="checkbox"
                value="CACES"
              />
              <label for="B1"> CACES</label>
            </div>
            <div class="checkbox-item">
              <input
                id="B2"
                v-model="checkedSkills"
                class="custom-checkbox custom-input"
                type="checkbox"
                value="Autre"
              />
              <label for="B2"> Autre</label>
            </div>
            <div class="checkbox-item">
              <input
                id="BR"
                v-model="checkedSkills"
                class="custom-checkbox custom-input"
                type="checkbox"
                value="Non, je n’ai ni habilitations, ni certificats"
              />
              <label for="BR">
                Non, je n'ai ni habilitations, ni certificats
              </label>
            </div>
          </div>
      -->
      <h3 class="formTitle" style="margin-top: 1rem">Permis et véhicule</h3>
      <SubmitComponent @go-back="goBack" />
      <p v-if="errorMessage" id="alert-msg04" class="alert-msg">
        {{ errorMessage }}
      </p>
      <p class="formSubTitle">Plusieurs choix possibles.</p>
      <p class="CVText">Permis obtenus</p>
      <!--
      <div class="checkbox-group">
        <div class="checkbox-item2">
          <input
            id="permitA"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis A"
          />
          <label class="permitLabel" for="permitA">Permis A</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitB"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis B"
          />
          <label class="permitLabel" for="permitB">Permis B</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitBE"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis BE"
          />
          <label class="permitLabel" for="permitBE">Permis BE</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitC"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis C"
          />
          <label class="permitLabel" for="permitC">Permis C</label>
        </div>
      </div>
      -->
      <div v-if="drivingLicenses" class="checkbox-group">
        <div class="checkbox-item2">
          <div
            v-for="(elem, elemIndex) in drivingLicenses"
            :key="elemIndex"
            class="checkbox-item"
          >
            <input
              :id="'permit' + elemIndex"
              v-model="checkedPermits"
              class="custom-checkbox custom-input"
              type="checkbox"
              :value="elem"
            />
            <label :for="'permit' + elemIndex" class="permitLabel">{{
              elem
            }}</label>
          </div>
        </div>
      </div>
      <p class="adjust-margin CVText">Possédez-vous un véhicule personnel ?</p>
      <div class="radio-group">
        <div id="radio-item-left" class="radio-item">
          <input id="vehicleYes" v-model="picked" type="radio" value="true" />
          <label for="vehicleYes">Oui</label>
        </div>
        <div class="radio-item">
          <input id="vehicleNo" v-model="picked" type="radio" value="false" />
          <label for="vehicleNo">Non</label>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import SubmitComponent from './submit';
export default {
  name: 'FormThree',
  components: {
    SubmitComponent,
  },
  props: {
    drivingLicenses: {
      type: [Array, Object],
      default: () => ({}),
    },
  },
  emits: ['situation-submitted', 'go-back'],
  data() {
    return {
      // checkedSkills: [],
      checkedPermits: [],
      picked: null,
      errorMessage: '',
    };
  },
  methods: {
    onSubmit() {
      this.errorMessage = '';
      if (this.picked === null) {
        this.errorMessage =
          "L'évaluation est incomplète. Veuillez remplir tous les champs.";
        return;
      }
      let situationReview = {
        permits: this.checkedPermits,
        vehicle: this.picked,
        // skills: this.checkedSkills,
      };
      //console.log(this.checkedPermits);
      this.$emit('situation-submitted', situationReview);
      // this.checkedPermits = [];
      // this.picked = null;
      //console.log('skills', this.checkedSkills);
      // this.checkedSkills = [];
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.radio-group {
  font-family: 'Telegraf', sans-serif;
  font-size: 0.875rem;
  line-height: 1.25rem;
}
.checkbox-item {
  /*display: flex;
  align-items: center;*/
  margin-bottom: 0.5rem;
}

.checkbox-item input {
  margin-right: 5px;
}

.checkbox-item label {
  font-family: 'Telegraf', sans-serif;
  font-size: 0.875rem;
  line-height: 1.25rem;
  display: inline-block;
  vertical-align: middle;
  padding-left: 6px;
  text-indent: -1.3rem;
}
.checkbox-item2 label {
  font-family: 'Telegraf', sans-serif;
}

.checkbox-item label::before {
  content: '';
  display: inline-block;
  width: 20px;
}
</style>
