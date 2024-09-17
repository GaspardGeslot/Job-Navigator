import SubmitButton from '@/core/components/buttons/SubmitButton.vue';
<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle">Quel métier vous intéresse ?</h3>
    <p class="formSubTitle">3 choix possibles.</p>
    <form v-if="sectors" class="formBlock5">
      <div v-for="(item, itemIndex) in sectors" :key="itemIndex">
        <p class="CVText">{{ item.title }}</p>
        <div class="checkbox-group two-columns">
          <div
            v-for="(elem, elemIndex) in item.jobs"
            :key="elemIndex"
            class="checkbox-item"
          >
            <input
              :id="`job${itemIndex}-${elemIndex}`"
              v-model="checkedJobs"
              class="custom-checkbox custom-input"
              type="checkbox"
              :value="elem"
            />
            <label :for="`job${itemIndex}-${elemIndex}`">{{ elem }}</label>
          </div>
        </div>
      </div>
      <p v-if="errorMessage" id="alert-msg03" class="alert-msg">
        {{ errorMessage }}
      </p>
      <SubmitComponent @go-back="goBack" />
    </form>
  </div>
</template>

<script>
import SubmitComponent from './submit';
export default {
  name: 'FormTwo',
  components: {
    SubmitComponent,
  },
  props: {
    sectors: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['situation-submitted', 'go-back'],
  data() {
    return {
      checkedJobs: [],
      errorMessage: '',
      errors: {
        tooMuchOptions: false,
      },
    };
  },
  watch: {
    checkedJobs(newVal) {
      if (newVal.length > 3) {
        this.checkedJobs.pop();
        this.errorMessage =
          'Vous ne pouvez sélectionner que 3 options au total.';
        this.errors.tooMuchOptions = true;
        //console.log(this.errorMessage);
      } else {
        this.errorMessage = '';
        this.errors.tooMuchOptions = false;
      }
    },
  },
  methods: {
    onSubmit() {
      this.errors.tooMuchOptions = false;
      if (this.checkedJobs.length > 3) {
        this.errorMessage =
          'Vous ne pouvez sélectionner que 3 options au total.';
        this.errors.tooMuchOptions = true;
        return;
      }
      let situationReview = {
        jobs: this.checkedJobs,
      };
      console.log('Jobs', this.checkedJobs);
      this.$emit('situation-submitted', situationReview);
      // this.checkedJobs = [];
      // this.errorMessage = '';
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
