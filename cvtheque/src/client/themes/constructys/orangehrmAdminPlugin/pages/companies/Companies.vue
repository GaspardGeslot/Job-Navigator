<template>
  <div class="orangehrm-candidate-page">
    <oxd-table-filter :filter-title="$t('Evolution des entreprises')">
      <oxd-form @submit-valid="onSearchCompanies" @reset="onResetCompanies">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="startDateFilter"
                :label="$t('general.start_date')"
                :rules="rules.fromDate"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="endDateFilter"
                :label="$t('general.end_date')"
                :rules="rules.toDate"
                required
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
            @click="resetFiltre"
          />
          <submit-button :label="$t('general.search')" />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>
    <br />
    <div class="orangehrm-paper-container">
      <oxd-form :loading="isLoadingCompanies" style="padding: 25px">
        <div style="width: 100%; height: 400px">
          <canvas ref="companiesChartRef"></canvas>
        </div>
      </oxd-form>
    </div>
    <br />
    <oxd-table-filter :filter-title="$t('Besoins en recrutement')">
      <oxd-form @submit-valid="onSearchMatching" @reset="onResetMatching">
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
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            type="reset"
            display-type="ghost"
            :label="$t('general.reset')"
            @click="resetFiltre"
          />
          <submit-button :label="$t('general.search')" />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>
    <br />
    <table-filter
      :filter-title="$t('Quantité de besoin en recrutement par entreprise')"
    >
      <div class="orangehrm-container">
        <oxd-card-table
          :headers="headers"
          :items="matchings"
          :selectable="false"
          :clickable="false"
          :loading="isLoadingMatching"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
    </table-filter>
    <div class="orangehrm-bottom-container" v-if="showPaginator">
      <oxd-pagination v-model:current="currentPage" :length="pages" />
    </div>
    <br v-if="!showPaginator" />
    <job-category-selection-modal
      v-if="showModal"
      @close="onModalClose"
    ></job-category-selection-modal>
  </div>
</template>
<script>
import {reactive, toRefs, ref, onMounted, watch} from 'vue';
import usei18n from '@/core/util/composable/usei18n';
import useToast from '@/core/util/composable/useToast';
import {APIService} from '@/core/util/services/api.service';
import {
  required,
  validDateFormatFrench,
  startDateShouldBeBeforeEndDate,
  endDateShouldBeAfterStartDate,
} from '@/core/util/validation/rules';
import {OxdLabel} from '@ohrm/oxd';
import JobCategorySelectionModal from '../../../orangehrmRecruitmentPlugin/components/JobCategorySelectionModal.vue';
import TableFilter from '@/core/components/dropdown/TableFilter.vue';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import {Chart, registerables} from 'chart.js';
Chart.register(...registerables);

export default {
  components: {
    'job-category-selection-modal': JobCategorySelectionModal,
    'oxd-label': OxdLabel,
    'table-filter': TableFilter,
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
  setup() {
    const jobSector = ref('');
    const professionalExperienceFilter = ref(null);
    const jobTitleFilter = ref(null);
    const needFilter = ref(null);
    const studyLevelFilter = ref(null);
    const courseStartFilter = ref(null);
    const startDateFilter = ref(
      formatDate(
        new Date(new Date().setMonth(new Date().getMonth() - 1)),
        'dd-MM-yyyy',
      ),
    );
    const endDateFilter = ref(formatDate(new Date(), 'dd-MM-yyyy'));
    const {$t} = usei18n();
    const {noRecordsFound} = useToast();
    const rules = {
      fromDate: [
        required,
        validDateFormatFrench('dd-MM-yyyy'),
        startDateShouldBeBeforeEndDate(
          () => endDateFilter.value,
          $t('general.from_date_should_be_before_to_date'),
          {allowSameDate: true, dateFormat: 'dd-MM-yyyy'},
        ),
      ],
      toDate: [
        required,
        validDateFormatFrench('dd-MM-yyyy'),
        endDateShouldBeAfterStartDate(
          () => startDateFilter.value,
          $t('general.to_date_should_be_after_from_date'),
          {allowSameDate: true, dateFormat: 'dd-MM-yyyy'},
        ),
      ],
    };

    const httpCompanies = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/companies`,
    );

    const httpMatching = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/companies/matchings`,
    );

    const state = reactive({
      total: 0,
      offset: 0,
      candidates: [],
      matchings: [],
      isLoadingCompanies: false,
      isLoadingMatching: false,
    });

    const fetchCompaniesData = () => {
      state.isLoadingCompanies = true;
      state.candidates = [];
      httpCompanies
        .getAll({
          startDate: formatDate(
            parseDate(startDateFilter.value, 'dd-MM-yyyy'),
            'yyyy-MM-dd',
          ),
          toDate: endDateFilter.value
            ? formatDate(
                parseDate(endDateFilter.value, 'dd-MM-yyyy'),
                'yyyy-MM-dd',
              )
            : undefined,
        })
        .then((response) => {
          state.candidates = response.data;
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .finally(() => (state.isLoadingCompanies = false));
    };

    const fetchMatchingData = () => {
      state.isLoadingMatching = true;
      state.matchings = [];
      httpMatching
        .getAll({
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
        })
        .then((response) => {
          console.log('response data : ', response.data);
          state.matchings = response.data.map((item) => {
            return {
              siret: item.siret,
              name: item.companyName,
              amount: item.matchingAmount,
            };
          });
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .finally(() => (state.isLoadingMatching = false));
    };

    const companiesChartRef = ref(null);
    let chart = null;

    const normalizeDataForChart = (companiesData) => {
      if (!companiesData || Object.keys(companiesData).length === 0)
        return null;

      // Get all unique dates
      const dates = Object.keys(companiesData).sort();
      // Format dates
      const formattedDates = dates.map((date) => {
        // Handle both ISO date format and simple date format
        const dateObj = date.includes('T')
          ? new Date(date)
          : parseDate(date, 'yyyy-MM-dd');
        return formatDate(dateObj, 'dd-MM-yyyy');
      });

      // Calculate percentage of recently active companies
      const lastDate = dates[dates.length - 1];
      const allCompanies = companiesData[lastDate] || [];
      const oneMonthAgo = new Date();
      oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);

      const recentlyActiveCompanies = allCompanies.filter((company) => {
        const lastLogged = company.lastLogged
          ? new Date(company.lastLogged)
          : null;
        return lastLogged && lastLogged >= oneMonthAgo;
      });

      const activePercentage =
        allCompanies.length > 0
          ? Math.round(
              (recentlyActiveCompanies.length / allCompanies.length) * 100,
            )
          : 0;

      // Create single dataset with company counts
      const dataset = {
        label: "Nombre d'entreprises",
        data: dates.map((date) => companiesData[date]?.length || 0),
        borderColor: 'rgb(75, 192, 192)',
        backgroundColor: 'rgba(75, 192, 192, 0.5)',
        tension: 0.3,
        fill: true,
      };

      return {
        labels: formattedDates,
        datasets: [dataset],
        activePercentage, // Add the percentage to the return object
      };
    };

    // Calculate the maximum y-axis value that's a multiple of 5
    const calculateYAxisMax = (datasets) => {
      const maxValue = Math.max(...datasets[0].data);
      return Math.ceil(maxValue / 5) * 5;
    };

    // Store the maximum y-axis value
    const yAxisMax = ref(0);

    const createChart = () => {
      const ctx = companiesChartRef.value.getContext('2d');
      const chartData = normalizeDataForChart(state.candidates);

      if (chart) {
        chart.destroy();
      }

      if (chartData) {
        // Only update yAxisMax if it hasn't been set yet or if it's 0
        if (yAxisMax.value === 0) {
          yAxisMax.value = calculateYAxisMax(chartData.datasets);
        }

        chart = new Chart(ctx, {
          type: 'line',
          data: chartData,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                max: yAxisMax.value,
                ticks: {
                  stepSize: 1,
                  callback: function (value) {
                    if (Math.floor(value) === value) {
                      return value;
                    }
                  },
                },
              },
            },
            plugins: {
              legend: {
                display: false,
              },
              title: {
                display: true,
                text: "Quantité d'entreprises actives",
                font: {
                  size: 16,
                },
              },
              subtitle: {
                display: true,
                text: `${chartData.activePercentage}% des entreprises se sont connectées les 30 derniers jours`,
                font: {
                  size: 14,
                  style: 'italic',
                },
                padding: {
                  bottom: 20,
                },
              },
            },
          },
        });
      }
    };

    // Reset yAxisMax when date filters change
    watch([startDateFilter, endDateFilter], () => {
      yAxisMax.value = 0;
    });

    watch(
      () => state.candidates,
      () => {
        if (companiesChartRef.value) {
          createChart();
        }
      },
      {deep: true},
    );

    onMounted(() => {
      if (state.candidates && Object.keys(state.candidates).length > 0) {
        createChart();
      }
    });

    return {
      httpCompanies,
      httpMatching,
      fetchCompaniesData,
      fetchMatchingData,
      ...toRefs(state),
      rules,
      startDateFilter,
      endDateFilter,
      needFilter,
      jobSector,
      professionalExperienceFilter,
      jobTitleFilter,
      studyLevelFilter,
      courseStartFilter,
      companiesChartRef,
      yAxisMax,
    };
  },
  data() {
    return {
      showModal: false,
      jobTitlesPerSector: [],
      headers: [
        {
          name: 'siret',
          title: this.$t('company.siret'),
          style: {flex: 1},
        },
        {
          name: 'name',
          title: this.$t('company.name'),
          style: {flex: 1},
        },
        {
          name: 'amount',
          title: this.$t('Quantité de besoin en recrutement'),
          style: {flex: 1},
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
  },
  beforeMount() {
    this.fetchCompaniesData();
    this.fetchMatchingData();
  },
  methods: {
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
    onSearchCompanies() {
      this.fetchCompaniesData();
    },
    onResetCompanies() {
      this.startDateFilter = formatDate(
        new Date(new Date().setMonth(new Date().getMonth() - 1)),
        'dd-MM-yyyy',
      );
      this.endDateFilter = formatDate(new Date(), 'dd-MM-yyyy');
      this.fetchCompaniesData();
    },
    onSearchMatching() {
      this.fetchMatchingData();
    },
    onResetMatching() {
      this.jobTitleFilter = null;
      this.jobSector = null;
      this.professionalExperienceFilter = null;
      this.needFilter = null;
      this.studyLevelFilter = null;
      this.courseStartFilter = null;
      this.fetchMatchingData();
    },
  },
};
</script>
