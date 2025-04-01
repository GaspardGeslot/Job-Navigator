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
  <div class="orangehrm-candidate-page">
    <oxd-table-filter :filter-title="$t('general.candidates')">
      <oxd-form @submit-valid="filterItems" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
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
                    <oxd-label :label="$t('general.job_title')" />
                    <oxd-icon-button
                      style="margin-left: 5px; font-size: 12px"
                      name="exclamation-circle"
                      :with-container="false"
                      @click="onModalOpen"
                    />
                  </div>
                </template>
                <oxd-input-field
                  v-model="jobTitleFilter"
                  type="select"
                  :options="jobTitlesPerSector"
                  required
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
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="needFilter"
                type="select"
                :label="$t('general.need')"
                :options="needs"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="studyLevelFilter"
                type="select"
                :label="$t('general.study_level')"
                :options="studyLevels"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseStartFilter"
                type="select"
                :label="$t('general.course_start_option')"
                :options="courseStarts"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="professionalExperienceFilter"
                type="select"
                :label="$t('pim.work_experience_global')"
                :options="professionalExperiences"
              />
            </oxd-grid-item>
            <!-- <oxd-grid-item>
              <vacancy-dropdown v-model="filters.vacancy"></vacancy-dropdown>
            </oxd-grid-item>
            <oxd-grid-item>
              <hiring-manager-dropdown
                v-model="filters.hiringManager"
              ></hiring-manager-dropdown>
            </oxd-grid-item>
            <oxd-grid-item>
              <candidate-status-dropdown v-model="filters.status" />
            </oxd-grid-item> -->
          </oxd-grid>
        </oxd-form-row>
        <!-- <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <candidate-autocomplete
                v-model="filters.candidate"
                :rules="rules.candidate"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.keywords"
                :label="$t('recruitment.keywords')"
                :placeholder="`${$t(
                  'recruitment.enter_comma_seperated_words',
                )}...`"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <date-input
                v-model="filters.fromDate"
                :label="$t('recruitment.date_of_application')"
                :placeholder="$t('general.from')"
                :rules="rules.fromDate"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <date-input
                v-model="filters.toDate"
                label="&nbsp"
                :placeholder="$t('general.to')"
                :rules="rules.toDate"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.methodOfApplication"
                :label="$t('recruitment.method_of_application')"
                type="select"
                :options="applications"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row> -->
        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            type="reset"
            display-type="ghost"
            :label="$t('general.reset')"
            :disabled="!canUpdate"
            @click="resetFiltre"
          />
          <submit-button :label="$t('general.search')" :disabled="!canUpdate" />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>
    <br />
    <div class="orangehrm-paper-container">
      <!--<div
        v-if="$can.create('recruitment_candidates')"
        class="orangehrm-header-container"
      >
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <table-header
        :selected="checkedItems.length"
        :total="total"
        :loading="isLoading"
        :show-divider="$can.create('recruitment_candidates')"
        @delete="onClickDeleteSelected"
      ></table-header>-->
      <div class="boutonTriBloc">
        <button class="boutonTri" @click="sortByDate">Trier par date ⇅</button>
      </div>
      <div class="orangehrm-container">
        <oxd-card-table
          v-model:selected="checkedItems"
          v-model:order="sortDefinition"
          :headers="headers"
          :items="items?.data"
          :clickable="false"
          :loading="isLoading"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
      <div class="orangehrm-bottom-container">
        <oxd-pagination
          v-if="showPaginator"
          v-model:current="currentPage"
          :length="pages"
        />
      </div>
    </div>
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
    <job-category-selection-modal
      v-if="showModal"
      @close="onModalClose"
    ></job-category-selection-modal>
  </div>
</template>

<script>
import {computed, ref} from 'vue';
import {
  validSelection,
  validDateFormat,
  endDateShouldBeAfterStartDate,
  startDateShouldBeBeforeEndDate,
} from '@/core/util/validation/rules';
import usei18n from '@/core/util/composable/usei18n';
import useSort from '@/core/util/composable/useSort';
import useLocale from '@/core/util/composable/useLocale';
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import useDateFormat from '@/core/util/composable/useDateFormat';
import usePaginate from '@/core/util/composable/usePaginate';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
//import JobtitleDropdown from '@/orangehrmPimPlugin/components/JobtitleDropdown';
//import VacancyDropdown from '../components/VacancyDropdown';
import useEmployeeNameTranslate from '@/core/util/composable/useEmployeeNameTranslate';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';
//import CandidateAutocomplete from '../components/CandidateAutocomplete';
//import HiringManagerDropdown from '../components/HiringManagerDropdown';
//import CandidateStatusDropdown from '../components/CandidateStatusDropdown';
import {OxdLabel} from '@ohrm/oxd';
import JobCategorySelectionModal from '../components/JobCategorySelectionModal.vue';

const defaultFilters = {
  jobTitle: null,
  vacancy: null,
  hiringManager: null,
  status: null,
  keywords: null,
  application: null,
  candidate: null,
  fromDate: null,
  toDate: null,
};

const defaultSortOrder = {
  'vacancy.name': 'DEFAULT',
  'candidate.lastName': 'DEFAULT',
  'hiringManager.lastName': 'DEFAULT',
  'candidate.dateOfApplication': 'DESC',
  'candidateVacancy.status': 'DEFAULT',
};

export default {
  components: {
    //'vacancy-dropdown': VacancyDropdown,
    //'jobtitle-dropdown': JobtitleDropdown,
    'oxd-label': OxdLabel,
    'job-category-selection-modal': JobCategorySelectionModal,
    'delete-confirmation': DeleteConfirmationDialog,
    //'candidate-autocomplete': CandidateAutocomplete,
    //'hiring-manager-dropdown': HiringManagerDropdown,
    //'candidate-status-dropdown': CandidateStatusDropdown,
  },

  props: {
    status: {
      type: Object,
      required: false,
      default: null,
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    courseStarts: {
      type: Array,
      default: () => [],
    },
    professionalExperiences: {
      type: Array,
      default: () => [],
    },
    sectors: {
      type: Array,
      default: () => [],
    },
  },
  setup(props) {
    const jobSector = ref('');
    const professionalExperienceFilter = ref(null);
    const jobTitleFilter = ref(null);
    const needFilter = ref(null);
    const studyLevelFilter = ref(null);
    const courseStartFilter = ref(null);
    const {$t} = usei18n();
    const {locale} = useLocale();
    const {jsDateFormat, userDateFormat} = useDateFormat();
    const {$tEmpName} = useEmployeeNameTranslate();
    const statuses = [
      {id: 1, label: $t('recruitment.application_initiated')},
      {id: 2, label: $t('recruitment.shortlisted')},
      {id: 3, label: $t('leave.rejected')},
      {id: 4, label: $t('recruitment.interview_scheduled')},
      {id: 5, label: $t('recruitment.interview_passed')},
      {id: 6, label: $t('recruitment.interview_failed')},
      {id: 7, label: $t('recruitment.job_offered')},
      {id: 8, label: $t('recruitment.offer_declined')},
      {id: 9, label: $t('recruitment.hired')},
    ];
    const candidateDataNormalizer = (data) => {
      return data.map((item) => {
        return {
          id: item.id,
          jobTitle: JSON.parse(item.jobs).join(', '),
          candidate: `${item.firstName} ${item.middleName || ''} ${
            item.lastName
          }`,
          dateOfApplication: formatDate(
            parseDate(item.dateOfApplication),
            jsDateFormat,
            {locale},
          ),
          email: item.email,
          status:
            statuses.find((status) => status.id === item.status?.id)?.label ||
            '',
          resume: item.hasAttachment,
          isSelectable: item.deletable,
        };
      });
    };
    const filters = ref({
      ...defaultFilters,
      ...(props.status && {status: props.status}),
    });
    const rules = {
      candidate: [validSelection],
      fromDate: [
        validDateFormat(userDateFormat),
        startDateShouldBeBeforeEndDate(
          () => filters.value.toDate,
          $t('general.from_date_should_be_before_to_date'),
          {allowSameDate: true},
        ),
      ],
      toDate: [
        validDateFormat(userDateFormat),
        endDateShouldBeAfterStartDate(
          () => filters.value.fromDate,
          $t('general.to_date_should_be_after_from_date'),
          {allowSameDate: true},
        ),
      ],
    };
    const {sortDefinition, sortField, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const serializedFilters = computed(() => {
      return {
        jobTitleId: filters.value.jobTitle?.id,
        vacancyId: filters.value.vacancy?.id,
        hiringManagerId: filters.value.hiringManager?.id,
        keywords: filters.value.keywords,
        candidateId: filters.value.candidate?.id,
        fromDate: filters.value.fromDate,
        toDate: filters.value.toDate,
        status: filters.value.status?.id,
        methodOfApplication: filters.value.methodOfApplication?.id,
        model: 'detailed',
        sortField: sortField.value,
        sortOrder: sortOrder.value,
        allLeads: 'candidat',
        ...(jobSector.value ? {jobSector: jobSector.value.label} : {}),
        ...(professionalExperienceFilter.value
          ? {
              professionalExperienceFilter:
                professionalExperienceFilter.value.label,
            }
          : {}),
        ...(jobTitleFilter.value
          ? {jobTitleFilter: jobTitleFilter.value.label}
          : {}),
        ...(needFilter.value ? {needFilter: needFilter.value.label} : {}),
        ...(studyLevelFilter.value
          ? {studyLevelFilter: studyLevelFilter.value.label}
          : {}),
        ...(courseStartFilter.value
          ? {courseStartFilter: courseStartFilter.value.label}
          : {}),
      };
    });

    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/recruitment/candidates`,
    );

    const {
      showPaginator,
      currentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
    } = usePaginate(http, {
      query: serializedFilters,
      normalizer: candidateDataNormalizer,
    });

    onSort(execQuery);

    return {
      http,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      pageSize,
      execQuery,
      items: response,
      filters,
      sortDefinition,
      rules,
      needFilter,
      jobSector,
      professionalExperienceFilter,
      jobTitleFilter,
      studyLevelFilter,
      courseStartFilter,
    };
  },
  data() {
    return {
      showModal: false,
      jobTitlesPerSector: [],
      checkedItems: [],
      canUpdate: false,
      headers: [
        {
          name: 'jobTitle',
          title: this.$t('general.job_title'),
          style: {flex: 1},
        },
        {
          name: 'candidate',
          slot: 'title',
          title: this.$t('recruitment.candidate'),
          style: {flex: 1},
        },
        {
          name: 'dateOfApplication',
          title: this.$t('recruitment.date_of_application'),
          style: {flex: 1},
        },
        {
          name: 'email',
          title: this.$t('general.other_email'),
          style: {flex: 1},
        },
        /*{
          name: 'status',
          title: this.$t('general.status'),
          style: {flex: 1},
        },*/
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.cellRenderer,
        },
      ],
      applications: [
        {
          id: 1,
          label: this.$t('recruitment.manual'),
        },
        {
          id: 2,
          label: this.$t('recruitment.online'),
        },
      ],
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
      } else {
        this.jobTitlesPerSector = [];
        this.jobTitleFilter = null;
      }
    },
    'items.data': function (newData) {
      if (newData && newData.length > 0) {
        // Vérifie que les données sont chargées
        this.sortByDate();
      }
    },
    jobTitleFilter(newVal) {
      if (
        newVal === null &&
        this.needFilter == null &&
        this.courseStartFilter == null &&
        this.professionalExperienceFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    needFilter(newVal) {
      if (
        newVal === null &&
        this.studyLevelFilter == null &&
        this.courseStartFilter == null &&
        this.professionalExperienceFilter == null &&
        this.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    studyLevelFilter(newVal) {
      if (
        newVal === null &&
        this.needFilter == null &&
        this.courseStartFilter == null &&
        this.professionalExperienceFilter == null &&
        this.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    courseStartFilter(newVal) {
      if (
        newVal === null &&
        this.needFilter == null &&
        this.studyLevelFilter == null &&
        this.professionalExperienceFilter == null &&
        this.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    professionalExperienceFilter(newVal) {
      if (
        newVal === null &&
        this.needFilter == null &&
        this.studyLevelFilter == null &&
        this.courseStartFilter == null &&
        this.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
  },
  methods: {
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
    async resetFiltre() {
      await this.resetFiltreValeur();
      await this.execQuery();
      this.canUpdate = false;
    },
    resetFiltreValeur() {
      this.jobSector = '';
      this.professionalExperienceFilter = null;
      this.jobTitleFilter = null;
      this.needFilter = null;
      this.studyLevelFilter = null;
      this.courseStartFilter = null;
    },
    sortByDate() {
      // Change l'ordre de tri
      this.isDateAscending = !this.isDateAscending;
      // Trie les éléments en fonction de l'ordre défini
      this.items.data.sort((a, b) => {
        const dateA = parseDate(a.dateOfApplication, 'dd-MM-yyyy');
        const dateB = parseDate(b.dateOfApplication, 'dd-MM-yyyy');

        return this.isDateAscending ? dateA - dateB : dateB - dateA;
      });
    },
    cellRenderer(...[, , , row]) {
      const cellConfig = {
        view: {
          onClick: this.onClickEdit,
          props: {
            name: 'eye-fill',
          },
        },
      };
      /*if (row.isSelectable) {
        cellConfig.delete = {
          onClick: this.onClickDelete,
          component: 'oxd-icon-button',
          props: {
            name: 'trash',
          },
        };
      }
      if (row.resume) {
        cellConfig.download = {
          onClick: this.onDownload,
          props: {
            name: 'download',
          },
        };
      }*/
      return {
        props: {
          header: {
            cellConfig,
          },
        },
      };
    },
    onClickAdd() {
      navigate(`/${window.appGlobal.theme}/recruitment/addCandidate`);
    },
    onClickEdit(item) {
      navigate(`/${window.appGlobal.theme}/recruitment/viewCandidate/{id}`, {
        id: item.id,
      });
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items?.data[index].id;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(ids);
        }
      });
    },
    onClickDelete(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems([item.id]);
        }
      });
    },
    onDownload(item) {
      if (!item?.id) return;
      const fileUrl = 'recruitment/viewCandidateAttachment/candidateId';
      const downUrl = `${window.appGlobal.baseUrl}/${fileUrl}/${item.id}`;
      window.open(downUrl, '_blank');
    },
    deleteItems(items) {
      if (items instanceof Array) {
        this.isLoading = true;
        this.http
          .deleteAll({
            ids: items,
          })
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.isLoading = false;
            this.resetDataTable();
          })
          .catch(() => {
            this.isLoading = false;
            this.resetDataTable();
          });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    async filterItems() {
      await this.execQuery();
    },
    onReset() {
      this.filters = {...defaultFilters};
      this.filterItems();
    },
  },
};
</script>
<style lang="scss" scoped>
.boutonTri {
  border-radius: 2rem;
  height: 2rem;
  border: 1px solid rgb(190, 190, 190);
  cursor: pointer;
  background-color: white;
  width: 10rem;
  margin: 1rem 1rem;
}
</style>
