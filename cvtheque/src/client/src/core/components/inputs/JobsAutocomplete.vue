<template>
  <oxd-grid :cols="2" class="orangehrm-full-width-grid">
    <oxd-grid-item class="orangehrm-job-selection-criteria --span-column-2">
      <oxd-input-field
        v-model="selectedJobs"
        type="autocomplete"
        :label="$t('Métier')"
        :clear="true"
        :create-options="loadJobs"
        :placeholder="$t('Rechercher un métier')"
        :multiple="true"
      />
      <oxd-input-group>
        <oxd-icon-button
          style="margin-left: 1rem; margin-bottom: 1rem"
          name="plus"
          @click="addJob"
        />
      </oxd-input-group>
    </oxd-grid-item>
  </oxd-grid>
  <oxd-grid :cols="4" class="orangehrm-full-width-grid">
    <oxd-grid-item
      v-for="(job, index) in jobs"
      :key="index"
      class="orangehrm-job-selection-criteria-selected"
    >
      <oxd-icon-button name="trash-fill" @click="onClickDeleteJob(job)" />
      <oxd-text class="orangehrm-job-selection-criteria-name">
        {{ job }}
      </oxd-text>
    </oxd-grid-item>
  </oxd-grid>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';

export default {
  name: 'JobsAutocomplete',

  props: {
    jobs: {
      type: Array,
      required: true,
    },
    apiPath: {
      type: String,
      default: '/api/v2/admin/job/search',
    },
  },

  emits: ['delete-job', 'add-jobs'],

  setup(props) {
    const httpJob = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}${props.apiPath}`,
    );
    return {
      httpJob,
    };
  },

  data() {
    return {
      selectedJobs: [],
    };
  },

  methods: {
    onClickDeleteJob(job) {
      this.$emit('delete-job', job);
    },

    addJob() {
      const newJobs = [];
      for (const job of this.selectedJobs) {
        if (!this.jobs.includes(job.label)) {
          newJobs.push(job.label);
        }
      }
      if (newJobs.length > 0) {
        this.$emit('add-jobs', newJobs);
      }
      this.selectedJobs = [];
    },

    loadJobs(searchParam) {
      return new Promise((resolve) => {
        if (searchParam.trim() && searchParam.length < 100) {
          this.httpJob
            .getAll({
              title: searchParam.trim(),
            })
            .then(({data}) => {
              resolve(data);
            });
        } else {
          resolve([]);
        }
      });
    },
  },
};
</script>

<style scoped lang="scss">
.orangehrm-job-selection {
  &-criteria {
    display: flex;
    align-items: center;
  }
  &-criteria-selected {
    display: flex;
    align-items: baseline;
  }
  &-criteria-name {
    margin-left: 1rem;
    font-weight: 700;
    font-size: $oxd-input-control-font-size;
    padding: $oxd-input-control-vertical-padding 0rem;
  }
  &-icon {
    margin-left: 1rem;
  }
}
</style>
