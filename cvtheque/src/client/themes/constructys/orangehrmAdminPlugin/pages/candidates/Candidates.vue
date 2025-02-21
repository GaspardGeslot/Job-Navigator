<template>
  <div class="orangehrm-candidate-page">
    <oxd-table-filter :filter-title="$t('Evolution des candidatures')">
      <oxd-form @submit-valid="onSearch" @reset="onReset">
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
    <div class="orangehrm-paper-container">
      <oxd-form :loading="isLoading" style="padding: 25px">
        <div style="width: 100%; height: 400px">
          <canvas ref="chartRef"></canvas>
        </div>
      </oxd-form>
    </div>
    <br />
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
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import {Chart, registerables} from 'chart.js';
Chart.register(...registerables);

export default {
  components: {
    'job-category-selection-modal': JobCategorySelectionModal,
    'oxd-label': OxdLabel,
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

    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/candidates`,
    );

    const state = reactive({
      total: 0,
      offset: 0,
      candidates: [],
      isLoading: false,
    });

    const fetchData = () => {
      state.isLoading = true;
      state.candidates = [];
      http
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
          state.candidates = response.data;
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .finally(() => (state.isLoading = false));
    };

    const chartRef = ref(null);
    let chart = null;

    const normalizeDataForChart = (candidatesData) => {
      if (!candidatesData || Object.keys(candidatesData).length === 0)
        return null;

      // Get all unique dates and statuses
      const dates = Object.keys(candidatesData).sort();
      const statuses = new Set();
      // Format dates and collect all unique statuses
      const formattedDates = dates.map((date) => {
        // Handle both ISO date format and simple date format
        const dateObj = date.includes('T')
          ? new Date(date)
          : parseDate(date, 'yyyy-MM-dd');
        return formatDate(dateObj, 'dd-MM-yyyy');
      });

      // Collect all unique statuses
      dates.forEach((date) => {
        candidatesData[date].forEach((item) => {
          statuses.add(item.status || 'Sans Matching');
        });
      });

      // Create datasets structure
      const datasets = Array.from(statuses).map((status, index) => ({
        label: status,
        data: [],
        backgroundColor: getBackgroundColor(index),
        borderRadius: 5,
      }));

      // Fill datasets with data
      dates.forEach((date, index) => {
        const statusAmounts = {};
        // Initialize amounts for all statuses to 0
        statuses.forEach((status) => {
          statusAmounts[status] = 0;
        });

        // Sum amounts for each status on this date
        candidatesData[date].forEach((item) => {
          const status = item.status || 'Sans Matching';
          statusAmounts[status] += item.amount;
        });

        // Add the amounts to respective datasets
        datasets.forEach((dataset) => {
          dataset.data.push(statusAmounts[dataset.label] || 0);
        });
      });

      return {
        labels: formattedDates,
        datasets,
      };
    };

    const getBackgroundColor = (index) => {
      const colors = [
        'rgba(255, 99, 132, 0.8)', // red
        'rgba(54, 162, 235, 0.8)', // blue
        'rgba(255, 206, 86, 0.8)', // yellow
        'rgba(75, 192, 192, 0.8)', // green
        'rgba(153, 102, 255, 0.8)', // purple
        'rgba(255, 159, 64, 0.8)', // orange
      ];
      return colors[index % colors.length];
    };

    // Calculate the maximum y-axis value that's a multiple of 5
    const calculateYAxisMax = (datasets) => {
      let maxTotal = 0;
      // For each date point, sum all dataset values
      for (let i = 0; i < datasets[0].data.length; i++) {
        const total = datasets.reduce(
          (sum, dataset) => sum + dataset.data[i],
          0,
        );
        maxTotal = Math.max(maxTotal, total);
      }

      // Round up to the next multiple of 5
      return Math.ceil(maxTotal / 5) * 5;
    };

    // Store the maximum y-axis value
    const yAxisMax = ref(0);

    const createChart = () => {
      const ctx = chartRef.value.getContext('2d');
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
          type: 'bar',
          data: chartData,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: {
                stacked: true,
              },
              y: {
                stacked: true,
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
                position: 'bottom',
              },
              title: {
                display: true,
                text: 'Quantité de candidats par statut',
                font: {
                  size: 16,
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
        if (chartRef.value) {
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
      http,
      fetchData,
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
      chartRef,
      yAxisMax,
    };
  },
  data() {
    return {
      showModal: false,
      jobTitlesPerSector: [],
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
    this.fetchData();
  },
  methods: {
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
    onSearch() {
      this.fetchData();
    },
    onReset() {
      this.jobTitleFilter = null;
      this.jobSector = null;
      this.professionalExperienceFilter = null;
      this.needFilter = null;
      this.studyLevelFilter = null;
      this.courseStartFilter = null;
      this.startDateFilter = formatDate(
        new Date(new Date().setMonth(new Date().getMonth() - 1)),
        'dd-MM-yyyy',
      );
      this.endDateFilter = formatDate(new Date(), 'dd-MM-yyyy');
      this.fetchData();
    },
  },
};
</script>
