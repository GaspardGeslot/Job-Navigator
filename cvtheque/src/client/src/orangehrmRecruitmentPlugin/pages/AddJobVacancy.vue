<!--
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
 -->
<template>
  <back-button></back-button>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('recruitment.add_vacancy') }}
      </oxd-text>
      <oxd-divider />

      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="vacancy.name"
              :label="$t('recruitment.vacancy_name')"
              required
              :rules="rules.name"
            />
          </oxd-grid-item>
          <!--<oxd-grid-item>
            <oxd-input-field
              v-model="vacancy.numOfPositions"
              :label="$t('recruitment.num_of_positions')"
              :rules="rules.numOfPositions"
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <jobtitle-dropdown
              v-model="vacancy.jobTitle"
              :rules="rules.jobTitle"
              required
            />
          </oxd-grid-item>-->
        </oxd-grid>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="jobSector"
              type="select"
              :label="$t('recruitment.job_sector')"
              :options="sectors"
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-group :classes="{wrapper: '--status-grouped-field'}">
              <template #label>
                <div class="label-is-entitlement-situational">
                  <oxd-label :label="$t('Métier*')" />
                  <oxd-icon-button
                    style="margin-left: 5px; font-size: 12px"
                    name="exclamation-circle"
                    :with-container="false"
                    @click="onModalOpen"
                  />
                </div>
              </template>
              <oxd-input-field
                v-model="vacancy.jobTitle"
                type="select"
                :options="jobTitlesPerSector"
                required
                :rules="rules.jobTitle"
              />
            </oxd-input-group>
            <oxd-text class="orangehrm-input-hint" tag="p">
              {{
                $t(
                  'Veuillez sélectionner la famille avant de pouvoir choisir le métier.',
                )
              }}
            </oxd-text>
          </oxd-grid-item>
        </oxd-grid>
        <!--<oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="vacancy.countries"
              type="multiselect"
              :label="$t('general.country')"
              :options="countries"
            />
          </oxd-grid-item>
        </oxd-grid>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item class="orangehrm-grid-item-span-2">
            <oxd-input-field
              v-model="vacancy.description"
              type="textarea"
              :label="$t('general.description')"
              :placeholder="$t('general.type_description_here')"
              :rules="rules.description"
            />
          </oxd-grid-item>
        </oxd-grid>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <employee-autocomplete
              v-model="vacancy.hiringManager"
              :params="{
                includeEmployees: 'onlyCurrent',
              }"
              required
              :rules="rules.hiringManager"
              :label="$t('recruitment.hiring_manager')"
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-grid :cols="2" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="vacancy.numOfPositions"
                  :label="$t('recruitment.num_of_positions')"
                  :rules="rules.numOfPositions"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-grid-item>
        </oxd-grid>-->
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item class="orangerhrm-switch-wrapper">
            <oxd-text class="orangehrm-text" tag="p">
              {{ $t('general.active') }}
            </oxd-text>
            <oxd-switch-input v-model="vacancy.status" />
          </oxd-grid-item>
        </oxd-grid>
        <br />

        <oxd-divider />
        <br />
        <oxd-form-row>
          <oxd-icon class="warning-icon" name="exclamation-triangle-fill" />
          <oxd-label
            :label="$t('recruitment.select_only_conditions_for_matching')"
          />
          <oxd-text class="orangehrm-input-hint">
            <i>
              {{
                $t(
                  '(Si vous voulez maximiser les opportunités de candidatures, ne cochez aucun critère, la mise en relation se fera uniquement sur le métier recherché)',
                )
              }}
            </i>
          </oxd-text>
        </oxd-form-row>
        <br />
        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('pim.work_experience_global') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in professionalExperiences"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedProfessionalExperiences"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('general.need_search') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in needs"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedNeeds"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('general.course_start_option') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in courseStarts"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedCourseStarts"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('general.study_level') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in studyLevels"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedStudyLevels"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('pim.license') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in drivingLicenses"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedDrivingLicences"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <br />
        <!--<oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item class="orangerhrm-switch-wrapper">
            <oxd-text class="orangehrm-text" tag="p">
              {{ $t('recruitment.publish_in_rss_feed_and_web_page') }}
            </oxd-text>
            <oxd-switch-input v-model="vacancy.isPublished" />
          </oxd-grid-item>
        </oxd-grid>
        <br />
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item class="orangehrm-grid-item-span-2">
            <div class="orangehrm-vacancy-links">
              <vacancy-link-card
                :label="$t('recruitment.rss_feed_url')"
                :url="rssFeedUrl"
              />
              <vacancy-link-card
                :label="$t('recruitment.web_page_url')"
                :url="webUrl"
              />
            </div>
          </oxd-grid-item>
        </oxd-grid>-->
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
      <job-category-selection-modal
        v-if="showModal"
        @close="onModalClose"
      ></job-category-selection-modal>
    </div>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import {navigate} from '@ohrm/core/util/helper/navigation';
import BackButton from '@/core/components/buttons/BackButton';
import {
  required,
  numericOnly,
  validSelection,
  shouldNotExceedCharLength,
  numberShouldBeBetweenMinAndMaxValue,
} from '@ohrm/core/util/validation/rules';
/*import EmployeeAutocomplete from '@/core/components/inputs/EmployeeAutocomplete';
import JobtitleDropdown from '@/orangehrmPimPlugin/components/JobtitleDropdown';
import VacancyLinkCard from '../components/VacancyLinkCard.vue';*/
import {OxdSwitchInput} from '@ohrm/oxd';
import {OxdLabel} from '@ohrm/oxd';
import {OxdIcon} from '@ohrm/oxd';
import useServerValidation from '@/core/util/composable/useServerValidation';
import JobCategorySelectionModal from '../components/JobCategorySelectionModal.vue';

const vacancyModel = {
  jobTitle: null,
  name: '',
  hiringManager: null,
  checkedProfessionalExperiences: [],
  checkedDrivingLicences: [],
  checkedNeeds: [],
  checkedCourseStarts: [],
  checkedStudyLevels: [],
  numOfPositions: '',
  description: '',
  status: true,
  isPublished: true,
};

const basePath = `${window.location.protocol}//${window.location.host}${window.appGlobal.baseUrl}`;

export default {
  components: {
    'oxd-switch-input': OxdSwitchInput,
    'back-button': BackButton,
    'oxd-label': OxdLabel,
    'oxd-icon': OxdIcon,
    'job-category-selection-modal': JobCategorySelectionModal,
    /*'employee-autocomplete': EmployeeAutocomplete,
    'jobtitle-dropdown': JobtitleDropdown,
    'vacancy-link-card': VacancyLinkCard,*/
  },

  props: {
    professionalExperiences: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    drivingLicenses: {
      type: Array,
      default: () => [],
    },
    courseStarts: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    sectors: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/vacancies',
    );
    const {createUniqueValidator} = useServerValidation(http);
    const vacancyNameUniqueValidation = createUniqueValidator(
      'Vacancy',
      'name',
    );
    return {
      http,
      vacancyNameUniqueValidation,
    };
  },
  data() {
    return {
      showModal: false,
      jobSector: '',
      jobTitlesPerSector: [],
      isLoading: false,
      vacancy: {...vacancyModel},
      rules: {
        jobTitle: [required],
        name: [
          required,
          //this.vacancyNameUniqueValidation,
          shouldNotExceedCharLength(50),
        ],
        hiringManager: [required, validSelection],
        numOfPositions: [
          (value) => {
            if (value === null || value === '') return true;
            return typeof numericOnly(value) === 'string'
              ? numericOnly(value)
              : numberShouldBeBetweenMinAndMaxValue(1, 99)(value);
          },
        ],
        description: [],
        status: [required],
        isPublished: [required],
      },
      rssFeedUrl: `${basePath}/recruitmentApply/jobs.rss`,
      webUrl: `${basePath}/recruitmentApply/jobs.html`,
    };
  },
  watch: {
    jobSector(newVal) {
      if (newVal) {
        const selectedSector = this.sectors.find(
          (sector) => sector.label === newVal.label,
        );
        this.jobTitlesPerSector = selectedSector
          ? selectedSector.jobs.map((job, index) => {
              return {id: index, label: job};
            })
          : [];
      } else this.jobTitlesPerSector = [];
    },
  },
  methods: {
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
    onCancel() {
      navigate('/recruitment/viewJobVacancy');
    },
    onSave() {
      this.isLoading = true;
      const vacancy = {
        name: this.vacancy.name,
        //jobTitleId: this.vacancy.jobTitle?.id,
        jobTitle: this.vacancy.jobTitle?.label,
        /*countries: JSON.stringify(
          this.vacancy.countries?.map((country) => {
            return country.label;
          }),
        ),*/
        professionalExperiences: JSON.stringify(
          this.vacancy.checkedProfessionalExperiences,
        ),
        drivingLicenses: JSON.stringify(this.vacancy.checkedDrivingLicences),
        needs: JSON.stringify(this.vacancy.checkedNeeds),
        courseStarts: JSON.stringify(this.vacancy.checkedCourseStarts),
        studyLevels: JSON.stringify(this.vacancy.checkedStudyLevels),
        //employeeId: this.vacancy.hiringManager?.id,
        numOfPositions: this.vacancy.numOfPositions
          ? parseInt(this.vacancy.numOfPositions)
          : null,
        //description: this.vacancy.description,
        status: this.vacancy.status,
        //isPublished: this.vacancy.isPublished,
      };
      this.http.create({...vacancy}).then((response) => {
        const {data} = response.data;
        this.$toast.saveSuccess();
        if (data.id !== null)
          navigate(`/recruitment/viewJobVacancy/${data.id}`);
        else navigate('/recruitment/viewJobVacancy');
      });
    },
  },
};
</script>

<style src="./vacancy.scss" lang="scss" scoped></style>
