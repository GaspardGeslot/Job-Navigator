<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle">Quelle expérience avez-vous ?</h3>
    <form class="formBlock5">
      <div class="checkbox-group two-columns">
        <div class="checkbox-item">
          <input
            id="xp1-1"
            v-model="checkedEXP"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Ce sera ma 1ère expérience"
          />
          <label for="xp1-1"> Ce sera ma 1ère expérience</label>
        </div>
        <div class="checkbox-item">
          <input
            id="xp1-2"
            v-model="checkedEXP"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="1 à 3 ans"
          />
          <label for="xp1-2"> 1 à 3 ans</label>
        </div>
        <div class="checkbox-item">
          <input
            id="xp1-3"
            v-model="checkedEXP"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="4 à 7 ans"
          />
          <label for="xp1-3"> 4 à 7 ans</label>
        </div>
        <div class="checkbox-item">
          <input
            id="xp1-4"
            v-model="checkedEXP"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="8 ans et +"
          />
          <label for="xp1-4">8 ans et +</label>
        </div>
      </div>
      <!--
      <input class="submitButton" type="submit" value="SUIVANT" />
      -->
      <SubmitComponent @go-back="goBack" />
    </form>
  </div>
</template>

<script>
import SubmitComponent from './submit';
export default {
  name: 'FormEight',
  components: {
    SubmitComponent,
  },
  emits: ['situation-submitted', 'go-back'],
  data() {
    return {
      checkedEXP: [],
      errorMessage: '',
    };
  },
  watch: {
    checkedEXP(newVal) {
      if (newVal.length > 5) {
        this.checkedEXP.pop();
        this.errorMessage =
          'Vous ne pouvez sélectionner que 5 options au total.';
      } else {
        this.errorMessage = '';
      }
    },
  },
  methods: {
    onSubmit() {
      let situationReview = {
        Xp: this.checkedEXP,
      };
      console.log('xp', this.checkedEXP);
      this.$emit('situation-submitted', situationReview);
      this.checkedEXP = [];
      this.errorMessage = '';
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss" scoped></style>

<style scoped>
.checkbox-group.two-columns {
  margin-top: 1rem;
  display: flex;
  flex-wrap: wrap;
}

.checkbox-item {
  flex: 1 1 50%;
  box-sizing: border-box;
  padding: 2px;
}

.checkbox-item input {
  margin-right: 5px;
}
.checkbox-item label {
  font-family: 'Telegraf', sans-serif;
}

.checkbox-item label {
  font-family: 'Telegraf', sans-serif;
  font-size: 0.875rem;
  line-height: 1.25rem;
  display: inline-block;
  padding-left: 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
