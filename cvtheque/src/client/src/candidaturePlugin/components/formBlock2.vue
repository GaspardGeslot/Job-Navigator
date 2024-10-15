<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle">Quel métier vous intéresse ?</h3>
    <p class="formSubTitle">3 choix possibles.</p>
    <form v-if="sectors" class="formBlock5">
      <div v-for="(item, itemIndex) in sectors" :key="itemIndex">
        <p class="CVText">{{ item.title }}</p>
        <div class="checkbox-group two-columns">
          <div
            v-for="(elem, elemIndex) in limitedJobs(item.jobs, itemIndex)"
            :key="`${itemIndex}-${elemIndex}`"
            class="checkbox-item"
          >
            <input
              :id="`job${itemIndex}-${elemIndex}`"
              v-model="checkedJobs"
              class="custom-checkbox custom-input"
              type="checkbox"
              :value="elem"
            />
            <label class="jobItemTitle" :for="`job${itemIndex}-${elemIndex}`">{{
              elem
            }}</label>
          </div>
        </div>
        <div @click="toggleShowMore(itemIndex)"
        class="toggleShowMoreContainer"
        type="button" v-if="item.jobs.length > 5" style="display: flex; flex-direction: row; align-items: center; margin-top: 0.25rem; cursor: pointer;">
          <button
            class="toggleShowMoreButton"
            type="button"
          >
            {{ showMore[itemIndex] ? 'Voir moins' : 'Voir d’autres métiers' }}
          </button>
          <svg v-if="showMore[itemIndex]" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-icon">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M11.2929 8.79289C11.6834 8.40237 12.3166 8.40237 12.7071 8.79289L17.7071 13.7929C18.0976 14.1834 18.0976 14.8166 17.7071 15.2071C17.3166 15.5976 16.6834 15.5976 16.2929 15.2071L12 10.9142L7.70711 15.2071C7.31658 15.5976 6.68342 15.5976 6.29289 15.2071C5.90237 14.8166 5.90237 14.1834 6.29289 13.7929L11.2929 8.79289Z" fill="currentColor"/>
            </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-icon">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M6.29289 8.79289C6.68342 8.40237 7.31658 8.40237 7.70711 8.79289L12 13.0858L16.2929 8.79289C16.6834 8.40237 17.3166 8.40237 17.7071 8.79289C18.0976 9.18342 18.0976 9.81658 17.7071 10.2071L12.7071 15.2071C12.3166 15.5976 11.6834 15.5976 11.2929 15.2071L6.29289 10.2071C5.90237 9.81658 5.90237 9.18342 6.29289 8.79289Z" fill="currentColor"/>
            </svg>
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
      showMore: [],
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
      } else {
        this.errorMessage = '';
        this.errors.tooMuchOptions = false;
      }
    },
    sectors(newVal) {
      this.showMore = new Array(newVal.length).fill(false);
    },
  },
  mounted() {
    this.showMore = new Array(this.sectors.length).fill(false);
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
      const situationReview = {
        jobs: this.checkedJobs,
      };
      console.log('Jobs', this.checkedJobs);
      this.$emit('situation-submitted', situationReview);
    },
    goBack() {
      this.$emit('go-back');
    },
    toggleShowMore(index) {
      this.showMore[index] = !this.showMore[index];
    },
    limitedJobs(jobs, index) {
      return this.showMore[index] ? jobs : jobs.slice(0, 5);
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss" scoped></style>

<style scoped>
.toggleShowMoreButton {
  background-color: white;
  border: none;
  font-family: 'Telegraf', sans-serif;
  font-size: 0.875rem;
  line-height: 1.25rem;
  padding-right: 0rem;
  cursor: pointer;
}
/* .toggleShowMoreButton:hover {
  color: grey;
} */
.arrow-icon {
  fill: black;
  transition: fill 0.3s;
}
.toggleShowMoreContainer:hover .toggleShowMoreButton,
.toggleShowMoreContainer:hover .arrow-icon {
  color: grey;
  fill: grey;
  transition: color 0.3s, fill 0.3s;
}

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
  font-family: 'Telegraf', sans-serif;
  font-size: 0.875rem;
  line-height: 1.25rem;
  display: inline-block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
