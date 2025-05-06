<template>
  <div class="orangehrm-card-container">
    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Informations') }}
      </oxd-text>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.title"
            :label="$t('recruitment.vacancy_name')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.price"
            :label="$t('Prix')"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.maxAmountPerDay"
            :label="$t('Quantité maximale par jour')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.maxAmountPerMonth"
            :label="$t('Quantité maximale par mois')"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <date-input
            v-model="matching.startDate"
            :label="$t('Date de début')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <date-input
            v-model="matching.endDate"
            :label="$t('Date de fin')"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('general.active') }}
          </oxd-text>
          <oxd-switch-input v-model="matching.isActive" :disabled="!editable" />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t(`Date d'arrêt - Début`) }}
      </oxd-text>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.startBreakDate.dayOfWeek"
            :label="$t('Jour de la semaine')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.startBreakDate.hour"
            :label="$t('Heure')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.startBreakDate.minutes"
            :label="$t('Minute')"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t(`Date d'arrêt - Fin`) }}
      </oxd-text>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.endBreakDate.dayOfWeek"
            :label="$t('Jour de la semaine')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.endBreakDate.hour"
            :label="$t('Heure')"
            :disabled="!editable"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.endBreakDate.minutes"
            :label="$t('Minute')"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <br />
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('CV obligatoire') }}
          </oxd-text>
          <oxd-switch-input
            v-model="matching.isResumeNeeded"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Métiers') }}
      </oxd-text>
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
              style="margin-left: 1rem"
              name="plus"
              @click="addJob"
            />
          </oxd-input-group>
        </oxd-grid-item>
        <oxd-grid-item
          v-for="(job, index) in matching.jobs"
          :key="index"
          class="orangehrm-job-selection-criteria-selected --offset-column-1"
        >
          <oxd-icon-button name="trash-fill" @click="onClickDeleteJob" />
          <oxd-text class="orangehrm-job-selection-criteria-name">
            {{ job }}
          </oxd-text>
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Pays') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in countries"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.countries"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Début de formation') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in courseStarts"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.courseStarts"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Financement') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in fundings"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.fundings"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Handicap') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in handicap"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.handicaps"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t(`Niveau d'étude`) }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in studyLevels"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.levels"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Besoin') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in needs"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.needs"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Préfixe téléphonique') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in phoneNumbers"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.phones"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Situation actuelle') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in status"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.status"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Modalité de formation') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in trainingMethods"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.trainingMethods"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <br />
      <oxd-divider />
      <oxd-form-actions>
        <required-text />
        <oxd-button
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <submit-button />
      </oxd-form-actions>
    </oxd-form>
  </div>
</template>
<script>
import {OxdSwitchInput} from '@ohrm/oxd';
import {APIService} from '@/core/util/services/api.service';

const MatchingModel = {
  id: null,
  isActive: true,
  actor: null,
  isResumeNeeded: false,
  ages: {
    type: Object,
    default: () => ({
      min: null,
      max: null,
    }),
  },
  countries: [],
  courseStarts: [],
  courses: [],
  departments: [],
  needs: [],
  endBreakDate: {
    type: Object,
    default: () => ({
      dayOfWeek: null,
      hour: null,
      minutes: null,
    }),
  },
  startBreakDate: {
    type: Object,
    default: () => ({
      dayOfWeek: null,
      hour: null,
      minutes: null,
    }),
  },
  endDate: null,
  startDate: null,
  fundings: [],
  handicaps: [],
  title: null,
  jobs: [],
  levels: [],
  locationPostalCodes: [],
  maxAmountPerDay: 0,
  maxAmountPerMonth: 0,
  phones: [],
  price: 0.0,
  resumeNeeded: false,
  status: [],
  trainingMethods: [],
  professionalExperiences: [],
  drivingLicenses: [],
};

export default {
  name: 'MatchingCard',

  components: {
    'oxd-switch-input': OxdSwitchInput,
  },

  props: {
    matchingCurrent: {
      type: Object,
      required: true,
    },
    actors: {
      type: Array,
      default: () => [],
    },
    countries: {
      type: Array,
      default: () => [],
    },
    courseStarts: {
      type: Array,
      default: () => [],
    },
    fundings: {
      type: Array,
      default: () => [],
    },
    handicaps: {
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
    phoneNumbers: {
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
    professionalExperiences: {
      type: Array,
      default: () => [],
    },
    drivingLicenses: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/job/search`,
    );
    return {
      http,
    };
  },
  data() {
    return {
      isLoading: false,
      editable: true,
      matching: {...MatchingModel},
      selectedJobs: [],
    };
  },
  watch: {
    matchingCurrent() {
      this.fetchMatching();
    },
  },
  beforeMount() {
    this.fetchMatching();
  },
  methods: {
    addJob() {
      for (const job of this.selectedJobs) this.matching.jobs.push(job.label);
      this.selectedJobs = [];
    },
    loadJobs(searchParam) {
      return new Promise((resolve) => {
        if (searchParam.trim() && searchParam.length < 100) {
          this.http
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
    fetchMatching() {
      this.isLoading = true;
      this.matching.id = this.matchingCurrent.id;
      this.matching.title = this.matchingCurrent.title;
      this.matching.isActive = this.matchingCurrent.isActive;
      this.matching.maxAmountPerDay = this.matchingCurrent.maxAmountPerDay;
      this.matching.maxAmountPerMonth = this.matchingCurrent.maxAmountPerMonth;
      this.matching.price = this.matchingCurrent.price;
      this.matching.startDate = this.matchingCurrent.startDate;
      this.matching.endDate = this.matchingCurrent.endDate;
      this.matching.startBreakDate =
        this.matchingCurrent.startBreakDate !== null
          ? this.matchingCurrent.startBreakDate
          : {dayOfWeek: '', hour: '', minutes: ''};
      this.matching.endBreakDate =
        this.matchingCurrent.endBreakDate !== null
          ? this.matchingCurrent.endBreakDate
          : {dayOfWeek: '', hour: '', minutes: ''};
      this.matching.countries = this.matchingCurrent.countries;
      this.matching.courseStarts = this.matchingCurrent.courseStarts;
      this.matching.fundings = this.matchingCurrent.fundings;
      this.matching.handicaps = this.matchingCurrent.handicaps;
      this.matching.levels = this.matchingCurrent.levels;
      this.matching.needs = this.matchingCurrent.needs;
      this.matching.phones = this.matchingCurrent.phones;
      this.matching.status = this.matchingCurrent.status;
      this.matching.trainingMethods = this.matchingCurrent.trainingMethods;
      this.matching.professionalExperiences =
        this.matchingCurrent.professionalExperiences;
      this.matching.drivingLicenses = this.matchingCurrent.drivingLicenses;
      this.matching.isResumeNeeded = this.matchingCurrent.isResumeNeeded;
      this.matching.jobs = ['Test'];
      this.isLoading = false;
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
  justify-content: space-between;

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
