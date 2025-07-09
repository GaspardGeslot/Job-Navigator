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
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
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
      <oxd-grid
        v-if="matching.startBreakDate"
        :cols="4"
        class="orangehrm-full-width-grid"
      >
        <oxd-grid-item>
          <oxd-input-field
            v-model="startBreakDayOfWeek"
            :label="$t('Jour de la semaine')"
            :disabled="!editable"
            type="select"
            :options="dayOfWeeks"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="startBreakTime"
            :label="$t('Heure')"
            :disabled="!editable"
            type="time"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t(`Date d'arrêt - Fin`) }}
      </oxd-text>
      <oxd-grid
        v-if="matching.endBreakDate"
        :cols="3"
        class="orangehrm-full-width-grid"
      >
        <oxd-grid-item>
          <oxd-input-field
            v-model="endBreakDayOfWeek"
            :label="$t('Jour de la semaine')"
            :disabled="!editable"
            type="select"
            :options="dayOfWeeks"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="endBreakTime"
            :label="$t('Heure')"
            :disabled="!editable"
            type="time"
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
        :is-all-departments-selected="isAllDepartmentsSelected"
        @delete-department="onClickDeleteDepartment"
        @add-department="addDepartment"
        @delete-location-postal-code="onClickDeleteLocationPostalCode"
        @add-location-postal-code="addLocationPostalCode"
        @toggle-all-departments="toggleAllDepartments"
      />
      <oxd-divider />
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Pays') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('countries')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('countries')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Début de formation') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('courseStarts')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('courseStarts')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Financement') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('fundings')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('fundings')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Handicap') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('handicaps')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('handicaps')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t(`Niveau d'étude`) }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('studyLevels')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('studyLevels')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Besoin') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('needs')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('needs')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Préfixe téléphonique') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('phones')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('phones')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Expérience professionnelle') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('professionalExperiences')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('professionalExperiences')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Situation actuelle') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('status')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('status')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Modalité de formation') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('trainingMethods')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('trainingMethods')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
      <oxd-grid :cols="4" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Permis de conduire') }}
          </oxd-text>
        </oxd-grid-item>
        <oxd-grid-item class="orangehrm-select-all">
          <oxd-input-field
            :model-value="isAllSelected('drivingLicenses')"
            type="checkbox"
            :label="$t('Tout sélectionner')"
            :disabled="!editable"
            @input="toggleAll('drivingLicenses')"
          />
        </oxd-grid-item>
      </oxd-grid>
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
} from '@/core/util/validation/rules';

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
  endBreakDate: defaultBreakTime(),
  startBreakDate: defaultBreakTime(),
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
function defaultBreakTime() {
  return {
    dayOfWeek: null,
    hour: null,
    minutes: null,
  };
}
function transformBreakTimeToIntegers(breakTime) {
  if (breakTime == null) {
    return null;
  }

  const dayOfWeek =
    breakTime.dayOfWeek !== null ? parseInt(breakTime.dayOfWeek, 10) : null;
  const hour = breakTime.hour !== null ? parseInt(breakTime.hour, 10) : null;
  const minutes =
    breakTime.minutes !== null ? parseInt(breakTime.minutes, 10) : null;

  // Si toutes les valeurs sont null, retourner null
  if (dayOfWeek === null && hour === null && minutes === null) {
    return null;
  }

  const transformedBreakTime = {
    dayOfWeek,
    hour,
    minutes,
  };

  return transformedBreakTime;
}
function isValidBreakTime(breakTime) {
  return (
    breakTime &&
    (breakTime.dayOfWeek === null || !isNaN(parseInt(breakTime.dayOfWeek))) &&
    (breakTime.hour === null || !isNaN(parseInt(breakTime.hour))) &&
    (breakTime.minutes === null || !isNaN(parseInt(breakTime.minutes)))
  );
}
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
    const rules = {
      actor: [required],
      title: [shouldNotExceedCharLength(100)],
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
      dayOfWeeks: [
        {id: 1, label: 'Lundi'},
        {id: 2, label: 'Mardi'},
        {id: 3, label: 'Mercredi'},
        {id: 4, label: 'Jeudi'},
        {id: 5, label: 'Vendredi'},
        {id: 6, label: 'Samedi'},
        {id: 7, label: 'Dimanche'},
      ],
      matching: {
        ...MatchingModel,
        startBreakDate: {
          dayOfWeek: null,
          hour: null,
          minutes: null,
        },
        endBreakDate: {
          dayOfWeek: null,
          hour: null,
          minutes: null,
        },
      },
    };
  },
  computed: {
    startBreakTime: {
      get() {
        if (
          this.matching.startBreakDate.hour !== null &&
          this.matching.startBreakDate.minutes !== null
        ) {
          const hour = String(this.matching.startBreakDate.hour).padStart(
            2,
            '0',
          );
          const minutes = String(this.matching.startBreakDate.minutes).padStart(
            2,
            '0',
          );
          const timeString = `${hour}:${minutes}`;
          return timeString;
        }
        return '';
      },
      set(value) {
        if (value) {
          const [hour, minutes] = value.split(':').map(Number);
          this.matching.startBreakDate.hour = hour;
          this.matching.startBreakDate.minutes = minutes;
        } else {
          this.matching.startBreakDate.hour = null;
          this.matching.startBreakDate.minutes = null;
        }
      },
    },
    startBreakDayOfWeek: {
      get() {
        if (this.matching.startBreakDate.dayOfWeek !== null) {
          if (
            typeof this.matching.startBreakDate.dayOfWeek === 'object' &&
            this.matching.startBreakDate.dayOfWeek.id
          ) {
            return this.matching.startBreakDate.dayOfWeek;
          }
          const dayOption = this.dayOfWeeks.find(
            (day) => day.id === this.matching.startBreakDate.dayOfWeek,
          );
          return dayOption || null;
        }
        return null;
      },
      set(value) {
        if (value && value.id) {
          this.matching.startBreakDate.dayOfWeek = value.id;
        } else {
          this.matching.startBreakDate.dayOfWeek = null;
        }
      },
    },
    endBreakDayOfWeek: {
      get() {
        if (this.matching.endBreakDate.dayOfWeek !== null) {
          if (
            typeof this.matching.endBreakDate.dayOfWeek === 'object' &&
            this.matching.endBreakDate.dayOfWeek.id
          ) {
            return this.matching.endBreakDate.dayOfWeek;
          }
          const dayOption = this.dayOfWeeks.find(
            (day) => day.id === this.matching.endBreakDate.dayOfWeek,
          );
          return dayOption || null;
        }
        return null;
      },
      set(value) {
        if (value && value.id) {
          this.matching.endBreakDate.dayOfWeek = value.id;
        } else {
          this.matching.endBreakDate.dayOfWeek = null;
        }
      },
    },
    endBreakTime: {
      get() {
        if (
          this.matching.endBreakDate.hour !== null &&
          this.matching.endBreakDate.minutes !== null
        ) {
          const hour = String(this.matching.endBreakDate.hour).padStart(2, '0');
          const minutes = String(this.matching.endBreakDate.minutes).padStart(
            2,
            '0',
          );
          const timeString = `${hour}:${minutes}`;
          return timeString;
        }
        return '';
      },
      set(value) {
        if (value) {
          const [hour, minutes] = value.split(':').map(Number);
          this.matching.endBreakDate.hour = hour;
          this.matching.endBreakDate.minutes = minutes;
        } else {
          this.matching.endBreakDate.hour = null;
          this.matching.endBreakDate.minutes = null;
        }
      },
    },
    isAllSelected() {
      return (field) => {
        let optionsList, selectedList;
        if (field === 'phones') {
          optionsList = this.phoneNumbers;
          selectedList = this.matching.phones;
        } else if (field === 'studyLevels') {
          optionsList = this.studyLevels;
          selectedList = this.matching.levels;
        } else {
          optionsList = this[field] || [];
          selectedList = this.matching[field] || [];
        }

        if (optionsList.length === 0) return false;
        if (selectedList.length === 0) return false;

        return selectedList.length === optionsList.length;
      };
    },
    isAllDepartmentsSelected() {
      return (
        this.departmentsOptions.length > 0 &&
        this.matching.departments.length === this.departmentsOptions.length
      );
    },
  },
  watch: {
    matchingCurrent: {
      handler() {
        this.fetchMatching();
      },
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
      const updatedMatching = JSON.parse(JSON.stringify(this.matching));

      updatedMatching.startBreakDate = transformBreakTimeToIntegers(
        this.matching.startBreakDate,
      );
      updatedMatching.endBreakDate = transformBreakTimeToIntegers(
        this.matching.endBreakDate,
      );

      if (
        !isValidBreakTime(updatedMatching.startBreakDate) ||
        !isValidBreakTime(updatedMatching.endBreakDate)
      ) {
        alert('Les horaires ne sont pas valides.');
        return;
      }

      if (updatedMatching.actor)
        updatedMatching.actor = updatedMatching.actor.label;
      updatedMatching.price = parseFloat(updatedMatching.price);
      updatedMatching.maxAmountPerDay = parseInt(
        updatedMatching.maxAmountPerDay,
      );
      updatedMatching.maxAmountPerMonth = parseInt(
        updatedMatching.maxAmountPerMonth,
      );

      if (updatedMatching.ages) {
        for (const age of updatedMatching.ages) {
          age.min = parseInt(age.min);
          age.max = parseInt(age.max);
        }
      }
      this.$emit('save', updatedMatching);
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
      this.matching.departments.sort((a, b) => a.label.localeCompare(b.label));
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
    fetchMatching() {
      this.matching.id = this.matchingCurrent.id;
      this.matching.title = this.matchingCurrent.title;
      this.matching.isActive = this.matchingCurrent.isActive;
      this.matching.maxAmountPerDay = this.matchingCurrent.maxAmountPerDay;
      this.matching.maxAmountPerMonth = this.matchingCurrent.maxAmountPerMonth;
      this.matching.price = this.matchingCurrent.price;
      this.matching.startDate = this.matchingCurrent.startDate;
      this.matching.endDate = this.matchingCurrent.endDate;
      this.matching.startBreakDate = isValidBreakTime(
        this.matchingCurrent.startBreakDate,
      )
        ? this.matchingCurrent.startBreakDate
        : defaultBreakTime();

      this.matching.endBreakDate = isValidBreakTime(
        this.matchingCurrent.endBreakDate,
      )
        ? this.matchingCurrent.endBreakDate
        : defaultBreakTime();
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
      this.matching.ages.sort((a, b) => a.min - b.min);

      const departmentMap = new Map();
      this.departmentsOptions.forEach((dept) => {
        departmentMap.set(String(dept.id), dept);
      });

      this.matching.departments = [];
      for (const department of this.matchingCurrent.departments) {
        const departmentOption = departmentMap.get(String(department));
        if (departmentOption) {
          this.matching.departments.push({
            id: departmentOption.id,
            label: departmentOption.label,
          });
        }
      }

      this.matching.courses = [];
      if (
        this.matchingCurrent.courses &&
        typeof this.matchingCurrent.courses === 'object'
      ) {
        this.matching.courses = Object.entries(
          this.matchingCurrent.courses,
        ).map(([id, label]) => ({
          id: id,
          label: id + ' - ' + label,
        }));
      }

      this.matching.locationPostalCodes =
        this.matchingCurrent.locationPostalCodes;
    },
    toggleAll(field) {
      let optionsList, selectedList;
      if (field === 'phones') {
        optionsList = this.phoneNumbers;
        selectedList = this.matching.phones;
      } else if (field === 'studyLevels') {
        optionsList = this.studyLevels;
        selectedList = this.matching.levels;
      } else {
        optionsList = this[field] || [];
        selectedList = this.matching[field] || [];
      }
      if (selectedList.length === optionsList.length) {
        if (field === 'studyLevels') {
          this.matching.levels = [];
        } else {
          this.matching[field] = [];
        }
      } else {
        if (field === 'studyLevels') {
          this.matching.levels = optionsList.map((elem) => elem.label);
        } else {
          this.matching[field] = optionsList.map((elem) => elem.label);
        }
      }
    },
    toggleAllDepartments(selected) {
      if (selected) {
        this.departmentsOptions.forEach((dept) => {
          if (!this.matching.departments.some((d) => d.id === dept.id)) {
            this.addDepartment(dept);
          }
        });
        this.matching.departments.sort((a, b) =>
          a.label.localeCompare(b.label),
        );
      } else {
        const departmentsToRemove = [...this.matching.departments];
        departmentsToRemove.forEach((dept) => {
          this.onClickDeleteDepartment(dept);
        });
      }
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

.orangehrm-section-header {
  margin-bottom: 1rem;
}

.orangehrm-select-all {
  display: flex;
  justify-content: flex-end;
}
</style>
