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
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('general.active') }}
          </oxd-text>
          <oxd-switch-input v-model="matching.isActive" :disabled="!editable" />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="matching.maxAmount"
            :label="$t('Quantité maximale')"
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
      <br />
      <oxd-divider />
      <br />
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
            v-model="matching.checkedCountries"
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
            v-model="matching.checkedCourseStarts"
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
            v-model="matching.checkedFundings"
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
            v-model="matching.checkedHandicaps"
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
            v-model="matching.checkedLevels"
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
            v-model="matching.checkedNeeds"
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
            v-model="matching.checkedPhones"
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
            v-model="matching.checkedStatus"
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
            v-model="matching.checkedTrainingMethods"
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

const MatchingModel = {
  id: null,
  isActive: true,
  actor: null,
  ages: [],
  checkedCountries: [],
  checkedCourseStarts: [],
  courses: [],
  departments: [],
  checkedNeeds: [],
  endBreakDate: null,
  startBreakDate: null,
  endDate: null,
  startDate: null,
  checkedFundings: [],
  checkedHandicaps: [],
  title: null,
  jobs: [],
  checkedLevels: [],
  locationPostalCodes: [],
  maxAmount: 0,
  checkedPhones: [],
  price: 0.0,
  resumeNeeded: false,
  checkedStatus: [],
  checkedTrainingMethods: [],
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
  },
  data() {
    return {
      isLoading: false,
      editable: false,
      matching: {...MatchingModel},
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
    fetchMatching() {
      this.isLoading = true;
      this.matching.id = this.matchingCurrent.id;
      this.matching.title = this.matchingCurrent.title;
      this.matching.isActive = this.matchingCurrent.isActive;
      this.matching.maxAmount = this.matchingCurrent.maxAmount;
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
      this.matching.checkedCountries = this.matchingCurrent.checkedCountries;
      this.matching.checkedCourseStarts =
        this.matchingCurrent.checkedCourseStarts;
      this.matching.checkedFundings = this.matchingCurrent.checkedFundings;
      this.matching.checkedHandicaps = this.matchingCurrent.checkedHandicaps;
      this.matching.checkedLevels = this.matchingCurrent.checkedLevels;
      this.matching.checkedNeeds = this.matchingCurrent.checkedNeeds;
      this.matching.checkedPhones = this.matchingCurrent.checkedPhones;
      this.matching.checkedStatus = this.matchingCurrent.checkedStatus;
      this.matching.checkedTrainingMethods =
        this.matchingCurrent.checkedTrainingMethods;
      this.isLoading = false;
    },
  },
};
</script>
<style scoped lang="scss">
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
