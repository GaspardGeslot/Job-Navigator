<template>
  <div class="orangehrm-candidate-page">
    <oxd-table-filter :filter-title="$t('general.candidates')">
      <oxd-form @submit-valid="filterItems" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.jobSector"
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
                  v-model="filters.jobTitleFilter"
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
                v-model="filters.needFilter"
                type="select"
                :label="$t('general.need')"
                :options="needs"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.studyLevelFilter"
                type="select"
                :label="$t('general.study_level')"
                :options="studyLevels"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.courseStartFilter"
                type="select"
                :label="$t('general.course_start_option')"
                :options="courseStarts"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.professionalExperienceFilter"
                type="select"
                :label="$t('pim.work_experience_global')"
                :options="professionalExperiences"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
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
    <div class="orangehrm-paper-container" style="padding-bottom: 1rem">
      <div v-if="candidates && candidates.length > 0" class="boutonTriBloc">
        <button
          class="boutonTri"
          style="margin: 0.5rem 1rem 0rem 0rem"
          @click="sortByDate"
        >
          Trier par date de candidature ⇅
        </button>
      </div>
      <div class="orangehrm-container">
        <oxd-card-table
          v-model:selected="checkedItems"
          :headers="headers"
          :items="candidates"
          :clickable="false"
          :loading="isLoading"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
      <div
        v-if="!candidates || candidates.length === 0"
        class="orangehrm-text-center-align"
      >
        <br />
        <oxd-text type="card-body" style="color: #64728c; font-style: italic">
          Aucun résultat obtenu pour cette recherche
        </oxd-text>
      </div>
      <div class="orangehrm-pagination-wrapper">
        <oxd-pagination
          v-if="totalPages > 1"
          v-model:current="currentPage"
          :length="totalPages"
          @update:current="onPageChange"
        />
      </div>
    </div>
    <br />
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
    <job-category-selection-modal
      v-if="showModal"
      @close="onModalClose"
    ></job-category-selection-modal>
  </div>
</template>

<script>
import {computed, ref} from 'vue';
import {validSelection} from '@/core/util/validation/rules';
import useLocale from '@/core/util/composable/useLocale';
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';
import {OxdLabel} from '@ohrm/oxd';
import JobCategorySelectionModal from '../components/JobCategorySelectionModal.vue';
import {parseDate, formatDate} from '@/core/util/helper/datefns';
import useDateFormat from '@/core/util/composable/useDateFormat';
import {
  markCandidateAsViewed,
  getCandidateLastViewed,
} from '@/core/util/helper/viewed';

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

export default {
  components: {
    'oxd-label': OxdLabel,
    'job-category-selection-modal': JobCategorySelectionModal,
    'delete-confirmation': DeleteConfirmationDialog,
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
    const {locale} = useLocale();
    const {jsDateFormat, jsDateTimeFormat} = useDateFormat();
    const candidates = ref([]);
    const totalPages = ref(0);
    const isLoading = ref(false);
    const currentPage = ref(1);
    const jobTitlesPerSector = ref([]);

    // Filtres initiaux pour la comparaison
    const initialFilters = {
      jobSector: '',
      professionalExperienceFilter: null,
      jobTitleFilter: null,
      needFilter: null,
      studyLevelFilter: null,
      courseStartFilter: null,
      page: 0,
      size: 20,
    };

    // Filtres réactifs
    const filters = ref({
      ...initialFilters,
      ...(props.status && {status: props.status}),
    });

    // Add sort order state
    const isDateAscending = ref(false); // Default to descending (false)

    const rules = {
      candidate: [validSelection],
    };
    const serializedFilters = computed(() => {
      return {
        page: currentPage.value - 1,
        size: filters.value.size,
        fromDate: filters.value.fromDate,
        toDate: filters.value.toDate,
        status: filters.value.status?.id,
        allLeads: 'candidat',
        sortDirection: isDateAscending.value ? 'ASC' : 'DESC',
        ...(filters.value.jobSector
          ? {jobSector: filters.value.jobSector.label}
          : {}),
        ...(filters.value.professionalExperienceFilter
          ? {
              professionalExperienceFilter:
                filters.value.professionalExperienceFilter.label,
            }
          : {}),
        ...(filters.value.jobTitleFilter
          ? {jobTitleFilter: filters.value.jobTitleFilter.label}
          : {}),
        ...(filters.value.needFilter
          ? {needFilter: filters.value.needFilter.label}
          : {}),
        ...(filters.value.studyLevelFilter
          ? {studyLevelFilter: filters.value.studyLevelFilter.label}
          : {}),
        ...(filters.value.courseStartFilter
          ? {courseStartFilter: filters.value.courseStartFilter.label}
          : {}),
      };
    });

    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/recruitment/candidates`,
    );

    const execQuery = async () => {
      isLoading.value = true;
      http
        .getAll({
          ...serializedFilters.value,
        })
        .then(({data: {data, meta}}) => {
          candidates.value = data
            .sort((a, b) => {
              const dateA = parseDate(a.date);
              const dateB = parseDate(b.date);
              return isDateAscending.value ? dateA - dateB : dateB - dateA;
            })
            .map((candidate) => ({
              id: candidate.id,
              jobTitle: candidate.jobs.join(', '),
              dateOfApplication: formatDate(
                parseDate(candidate.date),
                jsDateFormat,
                {locale},
              ),
              candidate: `${candidate.firstName} ${candidate.lastName}`,
              email: candidate.email,
              // Check if candidate has been viewed before for visual indication
              lastViewed: formatDate(
                getCandidateLastViewed(candidate.email),
                jsDateTimeFormat,
                {locale},
              ),
            }));
          totalPages.value = calculateTotalPages(meta);
        })
        .catch((error) => {
          console.error(
            'Erreur lors de la récupération des candidats :',
            error,
          );
          candidates.value = [];
          totalPages.value = 0;
        })
        .finally(() => {
          isLoading.value = false;
        });
    };

    // Helper function to calculate total pages
    const calculateTotalPages = (meta) => {
      if (typeof meta?.totalPages === 'number') return meta.totalPages;
      if (typeof meta?.total === 'number' && meta?.pageSize > 0)
        return Math.ceil(meta.total / meta.pageSize);
      return 0;
    };

    // Computed pour vérifier si les filtres ont changé
    const canUpdate = computed(() => {
      return (
        filters.value.professionalExperienceFilter !==
          initialFilters.professionalExperienceFilter ||
        filters.value.jobTitleFilter !== initialFilters.jobTitleFilter ||
        filters.value.needFilter !== initialFilters.needFilter ||
        filters.value.studyLevelFilter !== initialFilters.studyLevelFilter ||
        filters.value.courseStartFilter !== initialFilters.courseStartFilter
      );
    });

    // Gestionnaire de changement de page
    const onPageChange = (page) => {
      // Mise à jour de la page courante
      currentPage.value = page;
      // Appel direct de l'API avec les nouveaux paramètres
      execQuery();
    };

    if (localStorage.getItem('candidateFilters')) {
      const filterParams = JSON.parse(localStorage.getItem('candidateFilters'));
      Object.keys(filterParams).forEach((filterKey) => {
        const filter = filterParams[filterKey];
        switch (filterKey) {
          case 'job': {
            const selectedSector = props.sectors.find((sector) => {
              return sector.jobs.find((job) => job === filter);
            });
            filters.value.jobSector = selectedSector;
            jobTitlesPerSector.value = selectedSector
              ? selectedSector.jobs.map((job, index) => {
                  return {id: index, label: job};
                })
              : [];
            filters.value.jobTitleFilter = jobTitlesPerSector.value.find(
              (job) => job.label === filter,
            );
            break;
          }
          case 'professionalExperience':
            filters.value.professionalExperienceFilter =
              props.professionalExperiences.find(
                (experience) => experience.label === filter,
              );
            break;
          case 'need':
            filters.value.needFilter = props.needs.find(
              (need) => need.label === filter,
            );
            break;
          case 'studyLevel':
            filters.value.studyLevelFilter = props.studyLevels.find(
              (level) => level.label === filter,
            );
            break;
          case 'courseStart':
            filters.value.courseStartFilter = props.courseStarts.find(
              (start) => start.label === filter,
            );
            break;
          default:
            break;
        }
      });
      localStorage.removeItem('candidateFilters');
    }

    execQuery();

    return {
      http,
      isLoading,
      filters,
      rules,
      totalPages,
      onPageChange,
      canUpdate,
      isDateAscending,
      candidates,
      execQuery,
      currentPage,
      jobTitlesPerSector,
    };
  },
  data() {
    return {
      showModal: false,
      checkedItems: [],
      headers: [
        {
          name: 'jobTitle',
          slot: 'title',
          title: this.$t('general.job_title'),
          style: {flex: 1},
        },
        {
          name: 'candidate',
          slot: 'candidateCell',
          title: this.$t('recruitment.candidate'),
          style: {flex: 1},
        },
        {
          name: 'dateOfApplication',
          title: this.$t('recruitment.date_of_application'),
          style: {flex: 0.5},
        },
        {
          name: 'email',
          title: this.$t('general.other_email'),
          style: {flex: 1},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.25},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.cellRenderer,
        },
        {
          name: 'lastViewed',
          title: 'Dernière consultation',
          style: {flex: 0.5},
        },
      ],
    };
  },
  watch: {
    'filters.jobSector'(newVal) {
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
        this.filters.jobTitleFilter = null;
      }
    },
    'filters.jobTitleFilter'(newVal) {
      if (
        newVal === null &&
        this.filters.needFilter == null &&
        this.filters.courseStartFilter == null &&
        this.filters.professionalExperienceFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    'filters.needFilter'(newVal) {
      if (
        newVal === null &&
        this.filters.studyLevelFilter == null &&
        this.filters.courseStartFilter == null &&
        this.filters.professionalExperienceFilter == null &&
        this.filters.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    'filters.studyLevelFilter'(newVal) {
      if (
        newVal === null &&
        this.filters.needFilter == null &&
        this.filters.courseStartFilter == null &&
        this.filters.professionalExperienceFilter == null &&
        this.filters.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    'filters.courseStartFilter'(newVal) {
      if (
        newVal === null &&
        this.filters.needFilter == null &&
        this.filters.studyLevelFilter == null &&
        this.filters.professionalExperienceFilter == null &&
        this.filters.jobTitleFilter == null
      ) {
        this.canUpdate = false;
      } else {
        this.canUpdate = true;
      }
    },
    'filters.professionalExperienceFilter'(newVal) {
      if (
        newVal === null &&
        this.filters.needFilter == null &&
        this.filters.studyLevelFilter == null &&
        this.filters.courseStartFilter == null &&
        this.filters.jobTitleFilter == null
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
    resetFiltre() {
      this.resetFiltreValeur();
      this.canUpdate = false;
      this.execQuery();
    },
    resetFiltreValeur() {
      this.filters.jobSector = '';
      this.filters.professionalExperienceFilter = null;
      this.filters.jobTitleFilter = null;
      this.filters.needFilter = null;
      this.filters.studyLevelFilter = null;
      this.filters.courseStartFilter = null;
      this.currentPage = 1;
    },
    sortByDate() {
      // Change l'ordre de tri
      this.isDateAscending = !this.isDateAscending;
      // Reload data with new sort order
      this.execQuery();
    },
    cellRenderer() {
      const cellConfig = {
        view: {
          onClick: this.onClickView,
          props: {
            name: 'eye-fill',
          },
        },
      };
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
    onClickView(item) {
      // Mark candidate as viewed using email as identifier
      // This will be stored in localStorage for visual indication
      if (item.email) {
        markCandidateAsViewed(item.email);
      }

      let params = '';
      if (
        this.filters.jobTitleFilter ||
        this.filters.professionalExperienceFilter ||
        this.filters.needFilter ||
        this.filters.studyLevelFilter ||
        this.filters.courseStartFilter
      ) {
        const filterData = {
          job: this.filters.jobTitleFilter?.label,
          professionalExperience:
            this.filters.professionalExperienceFilter?.label,
          need: this.filters.needFilter?.label,
          studyLevel: this.filters.studyLevelFilter?.label,
          courseStart: this.filters.courseStartFilter?.label,
        };

        localStorage.setItem('candidateFilters', JSON.stringify(filterData));
      } else localStorage.removeItem('candidateFilters');
      navigate(
        `/${window.appGlobal.theme}/recruitment/viewCandidate/{id}${params}`,
        {
          id: item.id,
        },
      );
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.candidates[index].id;
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
      // Réinitialiser la pagination lors de l'application des filtres
      this.currentPage = 1;
      await this.execQuery();
    },
    onReset() {
      this.filters = {...defaultFilters};
      this.filterItems();
    },
  },
};
</script>
<style src="./vacancy.scss" lang="scss" scoped></style>
<style lang="scss" scoped>
.orangehrm-pagination-wrapper {
  margin: 1rem 0rem 0rem 1rem;
}
</style>
