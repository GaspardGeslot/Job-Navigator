<template>
  <back-button v-if="isAdding"></back-button>
  <div
    class="orangehrm-card-container"
    :style="{marginTop: isAdding ? '1rem' : '0'}"
  >
    <oxd-text v-if="isAdding" tag="h6" class="orangehrm-main-title">
      {{ $t('Ajouter un acteur') }}
    </oxd-text>
    <oxd-divider v-if="isAdding" />

    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Informations') }}
      </oxd-text>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.name"
            :label="$t('Nom')"
            :disabled="!editable"
            :rules="rules.title"
            required
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.documentation"
            :label="$t('Documentation')"
            :disabled="!editable"
            :rules="rules.documentation"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('Est un OF ?') }}
          </oxd-text>
          <oxd-switch-input v-model="actor.isOf" :disabled="true" />
        </oxd-grid-item>
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('Est prioritaire ?') }}
          </oxd-text>
          <oxd-switch-input v-model="actor.isPriority" :disabled="!editable" />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.maxAmountPerDay"
            :label="$t('Quantité maximale par jour')"
            :disabled="!editable"
            :rules="rules.maxAmountPerDay"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.maxAmountPerMonth"
            :label="$t('Quantité maximale par mois')"
            :disabled="!editable"
            :rules="rules.maxAmountPerMonth"
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Métiers spécifiques') }}
      </oxd-text>
      <jobs-autocomplete
        :jobs="actor.jobs"
        :is-actor-specific="true"
        @delete-job="onClickDeleteJob"
        @add-jobs="addJobs"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Ages') }}
      </oxd-text>
      <age-autocomplete
        :ages="actor.ages"
        :disabled="!editable"
        :is-actor-specific="true"
        @delete-age="onClickDeleteAge"
        @add-age="addAgeRange"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Situations actuelles spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Situation actuelle')"
        :custom-fields="actor.status"
        :custom-field-options="status"
        @delete-custom-field="onClickDeleteStatus"
        @add-custom-field="addStatus"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t("Niveaux d'étude spécifiques") }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Niveau d\'étude')"
        :custom-fields="actor.studyLevels"
        :custom-field-options="studyLevels"
        @delete-custom-field="onClickDeleteStudyLevel"
        @add-custom-field="addStudyLevel"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Pays spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Pays')"
        :custom-fields="actor.countries"
        :custom-field-options="countries"
        @delete-custom-field="onClickDeleteCountry"
        @add-custom-field="addCountry"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Méthodes de financement spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Méthode de financement')"
        :custom-fields="actor.fundings"
        :custom-field-options="fundings"
        @delete-custom-field="onClickDeleteFunding"
        @add-custom-field="addFunding"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Besoins spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Besoins')"
        :custom-fields="actor.needs"
        :custom-field-options="needs"
        @delete-custom-field="onClickDeleteNeed"
        @add-custom-field="addNeed"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Modalités de formation spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Modalité de formation')"
        :custom-fields="actor.trainingMethods"
        :custom-field-options="trainingMethods"
        @delete-custom-field="onClickDeleteTrainingMethod"
        @add-custom-field="addTrainingMethod"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Sources spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Source')"
        :custom-fields="actor.sources"
        :custom-field-options="sources"
        :custom-field-label="$t('Campagne UTM')"
        @delete-custom-field="onClickDeleteSource"
        @add-custom-field="addSource"
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
import {OxdSwitchInput} from '@ohrm/oxd';
import BackButton from '@/core/components/buttons/BackButton';
import JobsAutocomplete from '@/core/components/inputs/JobsAutocomplete';
import AgeAutocomplete from '@/core/components/inputs/AgeAutocomplete';
import CustomFieldAutocomplete from '@/core/components/inputs/CustomFieldAutocomplete';
import {
  required,
  numericOnly,
  digitsOnlyWithTwoDecimalPoints,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';

const ActorModel = {
  id: null,
  name: null,
  isOf: true,
  isPriority: false,
  documentation: null,
  maxAmountPerDay: 0,
  maxAmountPerMonth: 0,
  ages: [],
  countries: [],
  needs: [],
  fundings: [],
  jobs: [],
  studyLevels: [],
  status: [],
  trainingMethods: [],
  sources: [],
};

export default {
  name: 'ActorCard',

  components: {
    'oxd-switch-input': OxdSwitchInput,
    'back-button': BackButton,
    'jobs-autocomplete': JobsAutocomplete,
    'custom-field-autocomplete': CustomFieldAutocomplete,
    'age-autocomplete': AgeAutocomplete,
  },

  props: {
    actorCurrent: {
      type: Object,
      required: true,
    },
    countries: {
      type: Array,
      default: () => [],
    },
    fundings: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    status: {
      type: Array,
      default: () => [],
    },
    trainingMethods: {
      type: Array,
      default: () => [],
    },
    sources: {
      type: Array,
      default: () => [],
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
      actor: [required],
      title: [shouldNotExceedCharLength(100), required],
      price: [digitsOnlyWithTwoDecimalPoints],
      maxAmountPerDay: [numericOnly],
      maxAmountPerMonth: [numericOnly],
      postalCode: [numericOnly],
    };
    return {
      rules,
    };
  },
  data() {
    return {
      editable: true,
      actor: {...ActorModel},
    };
  },
  watch: {
    actorCurrent() {
      this.fetchActor();
    },
  },
  beforeMount() {
    if (!this.isAdding) this.fetchActor();
  },
  methods: {
    onCancel() {
      this.$emit('cancel');
    },
    onSave() {
      this.actor.maxAmountPerDay = parseInt(this.actor.maxAmountPerDay);
      this.actor.maxAmountPerMonth = parseInt(this.actor.maxAmountPerMonth);
      if (this.actor.ages) {
        for (const age of this.actor.ages) {
          age.min = parseInt(age.min);
          age.max = parseInt(age.max);
        }
      }
      if (this.actor.jobs) {
        for (const job of this.actor.jobs) {
          if (job.actorJobId) job.actorJobId = parseInt(job.actorJobId);
          if (job.trainingId) job.trainingId = parseInt(job.trainingId);
        }
      }
      this.$emit('save', this.actor);
    },
    onClickDeleteJob(job) {
      this.actor.jobs = this.actor.jobs.filter((j) => j !== job);
    },
    addJobs(newJobs) {
      for (const job of newJobs)
        this.actor.jobs.push({title: job, actorJobId: '', trainingId: ''});
    },
    onClickDeleteStatus(status) {
      this.actor.status = this.actor.status.filter((s) => s !== status);
    },
    addStatus(newStatus) {
      this.actor.status.push({title: newStatus});
    },
    onClickDeleteStudyLevel(studyLevel) {
      this.actor.studyLevels = this.actor.studyLevels.filter(
        (s) => s !== studyLevel,
      );
    },
    addStudyLevel(newStudyLevel) {
      this.actor.studyLevels.push({title: newStudyLevel});
    },
    onClickDeleteAge(age) {
      this.actor.ages = this.actor.ages.filter((a) => a !== age);
    },
    addAgeRange(age) {
      this.actor.ages.push(age);
    },
    onClickDeleteCountry(country) {
      this.actor.countries = this.actor.countries.filter((c) => c !== country);
    },
    addCountry(newCountry) {
      this.actor.countries.push({title: newCountry});
    },
    onClickDeleteFunding(funding) {
      this.actor.fundings = this.actor.fundings.filter((f) => f !== funding);
    },
    addFunding(newFunding) {
      this.actor.fundings.push({title: newFunding});
    },
    onClickDeleteNeed(need) {
      this.actor.needs = this.actor.needs.filter((n) => n !== need);
    },
    addNeed(newNeed) {
      this.actor.needs.push({title: newNeed});
    },
    onClickDeleteTrainingMethod(trainingMethod) {
      this.actor.trainingMethods = this.actor.trainingMethods.filter(
        (t) => t !== trainingMethod,
      );
    },
    addTrainingMethod(newTrainingMethod) {
      this.actor.trainingMethods.push({title: newTrainingMethod});
    },
    onClickDeleteSource(source) {
      this.actor.sources = this.actor.sources.filter((s) => s !== source);
    },
    addSource(newSource) {
      this.actor.sources.push({title: newSource});
    },
    onClickDelete() {
      this.$emit('delete', this.actor.id);
    },
    fetchActor() {
      this.actor.id = this.actorCurrent.id;
      this.actor.name = this.actorCurrent.name;
      this.actor.isOf = this.actorCurrent.isOf;
      this.actor.isPriority = this.actorCurrent.isPriority;
      this.actor.documentation = this.actorCurrent.documentation;
      this.actor.maxAmountPerDay = this.actorCurrent.maxAmountPerDay;
      this.actor.maxAmountPerMonth = this.actorCurrent.maxAmountPerMonth;
      this.actor.ages = this.actorCurrent.ages;
      this.actor.ages.sort((a, b) => a.min - b.min);
      this.actor.countries = this.actorCurrent.countries;
      this.actor.needs = this.actorCurrent.needs;
      this.actor.fundings = this.actorCurrent.fundings;
      this.actor.jobs = this.actorCurrent.jobs;
      for (const job of this.actor.jobs) {
        job.actorJobId = !job.actorJobId ? '' : job.actorJobId;
        job.trainingId = !job.trainingId ? '' : job.trainingId;
      }
      this.actor.studyLevels = this.actorCurrent.studyLevels;
      this.actor.status = this.actorCurrent.status;
      this.actor.trainingMethods = this.actorCurrent.trainingMethods;
      this.actor.sources = this.actorCurrent.sources;
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
