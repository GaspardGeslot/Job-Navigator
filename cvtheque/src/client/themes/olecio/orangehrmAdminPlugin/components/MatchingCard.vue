<template>
  <back-button v-if="isAdding"></back-button>
  <div
    class="orangehrm-card-container"
    :style="{marginTop: isAdding ? '1rem' : '0'}"
  >
    <oxd-text v-if="isAdding" tag="h6" class="orangehrm-main-title">
      {{ $t('recruitment.add_vacancy') }}
    </oxd-text>
    <oxd-divider v-if="isAdding" />

    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Informations') }}
      </oxd-text>
      <oxd-grid v-if="isAdding" :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.actor"
            :label="$t('Acteur')"
            :disabled="!editable"
            :required="true"
            type="select"
            :options="actors"
            :rules="rules.actor"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.title"
            :label="$t('recruitment.vacancy_name')"
            :disabled="!editable"
            :rules="rules.title"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.price"
            :label="$t('Prix')"
            :disabled="!editable"
            :required="true"
            :rules="rules.price"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.maxAmountPerDay"
            :label="$t('Quantité maximale par jour')"
            :disabled="!editable"
            :rules="rules.maxAmountPerDay"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.maxAmountPerMonth"
            :label="$t('Quantité maximale par mois')"
            :disabled="!editable"
            :rules="rules.maxAmountPerMonth"
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
      <jobs-autocomplete
        :jobs="matching.jobs"
        @delete-job="onClickDeleteJob"
        @add-jobs="addJobs"
      />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Formations') }}
      </oxd-text>
      <courses-autocomplete
        :courses="matching.courses"
        :disabled="!editable"
        @delete-course="onClickDeleteCourse"
        @add-courses="addCourses"
      />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Ages') }}
      </oxd-text>
      <age-autocomplete
        :ages="matching.ages"
        :disabled="!editable"
        @delete-age="onClickDeleteAge"
        @add-age="addAgeRange"
      />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Localisation') }}
      </oxd-text>
      <location-autocomplete
        :departments="matching.departments"
        :location-postal-codes="matching.locationPostalCodes"
        :disabled="!editable"
        :departments-options="departmentsOptions"
        @delete-department="onClickDeleteDepartment"
        @add-department="addDepartment"
        @delete-location-postal-code="onClickDeleteLocationPostalCode"
        @add-location-postal-code="addLocationPostalCode"
      />
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
          v-for="(elem, elemIndex) in handicaps"
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
        {{ $t('Expérience professionnelle') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in professionalExperiences"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.professionalExperiences"
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
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Permis de conduire') }}
      </oxd-text>
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item
          v-for="(elem, elemIndex) in drivingLicenses"
          :key="`${elemIndex}-${elem}`"
        >
          <oxd-input-field
            v-model="matching.drivingLicenses"
            type="checkbox"
            :label="elem.label"
            :value="elem.label"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
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
import {APIService} from '@/core/util/services/api.service';
import BackButton from '@/core/components/buttons/BackButton';
import JobsAutocomplete from '@/core/components/inputs/JobsAutocomplete';
import CoursesAutocomplete from '@/core/components/inputs/CoursesAutocomplete';
import AgeAutocomplete from '@/core/components/inputs/AgeAutocomplete';
import LocationAutocomplete from '@/core/components/inputs/LocationAutocomplete';
import {
  required,
  numericOnly,
  digitsOnlyWithTwoDecimalPoints,
  shouldNotExceedCharLength,
  beforeDate,
  afterDate,
  validDateFormat,
} from '@/core/util/validation/rules';
import usei18n from '@/core/util/composable/usei18n';

const MatchingModel = {
  id: null,
  isActive: true,
  actor: null,
  isResumeNeeded: false,
  ages: [],
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
    validator: (value) => {
      return (
        (value.dayOfWeek === null || Number.isInteger(value.dayOfWeek)) &&
        (value.hour === null || Number.isInteger(value.hour)) &&
        (value.minutes === null || Number.isInteger(value.minutes))
      );
    },
  },
  startBreakDate: {
    type: Object,
    default: () => ({
      dayOfWeek: null,
      hour: null,
      minutes: null,
    }),
    validator: (value) => {
      return (
        (value.dayOfWeek === null || Number.isInteger(value.dayOfWeek)) &&
        (value.hour === null || Number.isInteger(value.hour)) &&
        (value.minutes === null || Number.isInteger(value.minutes))
      );
    },
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
    'back-button': BackButton,
    'jobs-autocomplete': JobsAutocomplete,
    'courses-autocomplete': CoursesAutocomplete,
    'age-autocomplete': AgeAutocomplete,
    'location-autocomplete': LocationAutocomplete,
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
    isAdding: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    departmentsOptions: {
      type: Array,
      default: () => [],
    },
  },

  emits: ['cancel', 'delete', 'save'],

  setup() {
    const httpJob = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/job/search`,
    );
    const {$t} = usei18n();
    const rules = {
      actor: [required],
      title: [shouldNotExceedCharLength(100)],
      price: [digitsOnlyWithTwoDecimalPoints],
      maxAmountPerDay: [numericOnly],
      maxAmountPerMonth: [numericOnly],
      postalCode: [numericOnly],
    };
    return {
      httpJob,
      rules,
    };
  },
  data() {
    return {
      editable: true,
      matching: {...MatchingModel},
    };
  },
  watch: {
    matchingCurrent() {
      this.fetchMatching();
    },
  },
  beforeMount() {
    if (!this.isAdding) this.fetchMatching();
  },
  methods: {
    onCancel() {
      this.$emit('cancel');
    },
    onSave() {
      if (this.matching.actor) this.matching.actor = this.matching.actor.label;
      this.matching.price = parseFloat(this.matching.price);
      this.matching.maxAmountPerDay = parseInt(this.matching.maxAmountPerDay);
      this.matching.maxAmountPerMonth = parseInt(
        this.matching.maxAmountPerMonth,
      );
      if (this.matching.ages) {
        for (const age of this.matching.ages) {
          age.min = parseInt(age.min);
          age.max = parseInt(age.max);
        }
      }
      this.$emit('save', this.matching);
    },
    onClickDeleteJob(job) {
      this.matching.jobs = this.matching.jobs.filter((j) => j !== job);
    },
    addJobs(newJobs) {
      this.matching.jobs = [...this.matching.jobs, ...newJobs];
    },
    onClickDeleteAge(age) {
      this.matching.ages = this.matching.ages.filter((a) => a !== age);
    },
    addAgeRange(age) {
      this.matching.ages.push(age);
    },
    onClickDeleteDepartment(department) {
      this.matching.departments = this.matching.departments.filter(
        (d) => d !== department,
      );
    },
    addDepartment(department) {
      this.matching.departments.push(department);
    },
    onClickDeleteLocationPostalCode(locationPostalCode) {
      this.matching.locationPostalCodes =
        this.matching.locationPostalCodes.filter(
          (l) => l !== locationPostalCode,
        );
    },
    addLocationPostalCode(postalCode) {
      this.matching.locationPostalCodes.push(postalCode);
    },
    onClickDeleteCourse(course) {
      this.matching.courses = this.matching.courses.filter((c) => c !== course);
    },
    addCourses(courses) {
      this.matching.courses.push(...courses);
    },
    onClickDelete() {
      this.$emit('delete', this.matching.id);
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
    fetchMatching() {
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
      this.matching.jobs = this.matchingCurrent.jobs;
      this.matching.ages = this.matchingCurrent.ages;
      this.matching.departments = [];
      for (const department of this.matchingCurrent.departments) {
        const departmentOption = this.departmentsOptions.find(
          (d) => String(d.id) === String(department),
        );
        this.matching.departments.push({
          id: departmentOption.id,
          label: departmentOption.label,
        });
      }
      this.matching.courses = [];
      for (const [id, label] of Object.entries(this.matchingCurrent.courses)) {
        this.matching.courses.push({
          id: id,
          label: id + ' - ' + label,
        });
      }
      this.matching.locationPostalCodes =
        this.matchingCurrent.locationPostalCodes;
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
