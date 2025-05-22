<template>
  <back-button v-if="isAdding"></back-button>
  <div
    class="orangehrm-card-container"
    :style="{marginTop: isAdding ? '1rem' : '0'}"
  >
    <oxd-text v-if="isAdding" tag="h6" class="orangehrm-main-title">
      {{ $t('Ajouter un secteur') }}
    </oxd-text>
    <oxd-divider v-if="isAdding" />

    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Informations') }}
      </oxd-text>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="sector.title"
            :label="$t('Titre')"
            :disabled="!editable"
            :rules="rules.title"
            required
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Métiers') }}
      </oxd-text>
      <jobs-autocomplete
        :jobs="sector.jobs"
        :is-sector-specific="true"
        @delete-job="onClickDeleteJob"
        @add-jobs="addJobs"
      />
      <br />
      <oxd-divider />
      <oxd-form-actions>
        <required-text />
        <oxd-button
          v-if="isAdding"
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <oxd-button
          v-else
          :label="$t('performance.delete')"
          display-type="danger"
          @click="onClickDelete"
        />
        <submit-button />
      </oxd-form-actions>
    </oxd-form>
  </div>
</template>
<script>
import BackButton from '@/core/components/buttons/BackButton';
import JobsAutocomplete from '@/core/components/inputs/JobsAutocomplete';
import {
  required,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';

const SectorModel = {
  id: null,
  title: null,
  jobs: [],
};

export default {
  name: 'SectorCard',

  components: {
    'back-button': BackButton,
    'jobs-autocomplete': JobsAutocomplete,
  },

  props: {
    sectorCurrent: {
      type: Object,
      required: true,
    },
    isAdding: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['cancel', 'delete', 'save'],

  setup() {
    const rules = {
      title: [shouldNotExceedCharLength(100), required],
    };
    return {
      rules,
    };
  },
  data() {
    return {
      editable: true,
      sector: {...SectorModel},
    };
  },
  watch: {
    sectorCurrent() {
      this.fetchSector();
    },
  },
  beforeMount() {
    if (!this.isAdding) this.fetchSector();
  },
  methods: {
    onCancel() {
      this.$emit('cancel');
    },
    onSave() {
      if (this.sector.jobs) {
        for (const job of this.sector.jobs)
          if (job.priority) job.priority = parseInt(job.priority);
      }
      this.$emit('save', this.sector);
    },
    onClickDeleteJob(job) {
      this.sector.jobs = this.sector.jobs.filter((j) => j !== job);
    },
    addJobs(newJobs) {
      for (const job of newJobs)
        this.sector.jobs.push({title: job, priority: ''});
    },
    onClickDelete() {
      this.$emit('delete', this.sector.id);
    },
    fetchSector() {
      this.sector.id = this.sectorCurrent.id;
      this.sector.title = this.sectorCurrent.title;
      this.sector.jobs = this.sectorCurrent.jobs ?? [];
      for (const job of this.sector.jobs) job.priority = job.priority ?? '';
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

.orangerhrm-switch-wrapper {
  display: flex;
  flex-direction: row;
  justify-content: start;
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
