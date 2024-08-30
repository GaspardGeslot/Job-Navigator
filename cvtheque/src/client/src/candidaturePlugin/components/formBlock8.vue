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
            value="Aucune"
          />
          <label for="xp1-1"> Aucune</label>
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
            value="3 à 7 ans"
          />
          <label for="xp1-3"> 3 à 7 ans</label>
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
      <input class="submitButton" type="submit" value="SUIVANT" />
    </form>
  </div>
</template>

<script>
export default {
  name: 'FormEight',
  emits: ['situation-submitted'],
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
  font-size: 0.75rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
