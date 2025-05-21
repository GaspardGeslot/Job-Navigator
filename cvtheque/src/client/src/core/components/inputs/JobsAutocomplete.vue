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
  <oxd-grid v-if="!isActorSpecific" :cols="4" class="orangehrm-full-width-grid">
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
  <div v-else v-for="(job, index) in jobs" :key="index">
    <oxd-grid :cols="4" class="orangehrm-full-width-grid">
      <oxd-grid-item class="orangehrm-job-selection-criteria-selected">
        <oxd-icon-button name="trash-fill" @click="onClickDeleteJob(job)" />
        <oxd-text class="orangehrm-job-selection-criteria-name">
          {{ job.title }}
        </oxd-text>
      </oxd-grid-item>
      <oxd-grid-item class="orangerhrm-switch-wrapper">
        <oxd-text class="orangehrm-text" tag="p">
          {{ $t('Est prioritaire ?') }}
        </oxd-text>
        <oxd-switch-input v-model="job.isPriority" />
      </oxd-grid-item>
    </oxd-grid>
    <br />
    <oxd-grid :cols="4" class="orangehrm-full-width-grid">
      <oxd-grid-item>
        <oxd-input-field
          v-model="job.actorJobTitle"
          :label="$t('Actor Job Title')"
          :rules="rules.text"
        />
      </oxd-grid-item>
      <oxd-grid-item>
        <oxd-input-field
          v-model="job.actorJobId"
          :label="$t('Actor Job ID')"
          :rules="rules.numeric"
        />
      </oxd-grid-item>
      <oxd-grid-item>
        <oxd-input-field
          v-model="job.thematic"
          :label="$t('Thematic')"
          :rules="rules.text"
        />
      </oxd-grid-item>
      <oxd-grid-item>
        <oxd-input-field
          v-model="job.trainingCode"
          :label="$t('Training Code')"
          :rules="rules.text"
        />
      </oxd-grid-item>
      <oxd-grid-item>
        <oxd-input-field
          v-model="job.trainingId"
          :label="$t('Training ID')"
          :rules="rules.numeric"
        />
      </oxd-grid-item>
      <oxd-grid-item>
        <oxd-input-field
          v-model="job.utmCampaign"
          :label="$t('UTM Campaign')"
          :rules="rules.text"
        />
      </oxd-grid-item>
    </oxd-grid>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import {OxdSwitchInput} from '@ohrm/oxd';
import {
  shouldNotExceedCharLength,
  numericOnly,
} from '@/core/util/validation/rules';

export default {
  name: 'JobsAutocomplete',

  components: {
    'oxd-switch-input': OxdSwitchInput,
  },

  props: {
    jobs: {
      type: Array,
      required: true,
    },
    apiPath: {
      type: String,
      default: '/api/v2/admin/job/search',
    },
    isActorSpecific: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['delete-job', 'add-jobs'],

  setup(props) {
    const httpJob = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}${props.apiPath}`,
    );
    const rules = {
      text: [shouldNotExceedCharLength(100)],
      numeric: [numericOnly],
    };
    return {
      httpJob,
      rules,
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
        if (
          this.isActorSpecific &&
          !this.jobs.some((j) => j.title === job.label)
        )
          newJobs.push(job.label);
        else if (!this.isActorSpecific && !this.jobs.includes(job.label))
          newJobs.push(job.label);
      }

      if (newJobs.length > 0) this.$emit('add-jobs', newJobs);
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
    align-items: center;
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
.orangerhrm-switch-wrapper {
  display: flex;
  flex-direction: row;
  justify-content: start;
  align-items: center;
  gap: 1rem;

  @include oxd-respond-to('sm') {
    max-width: 50%;
  }
  @include oxd-respond-to('md') {
    max-width: 100%;
  }
}
.orangehrm-text {
  font-size: 12px;
  font-weight: 600;
  color: $oxd-interface-gray-darken-1-color;
}
</style>
