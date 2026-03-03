<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Filtres')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <date-input
                v-model="startDateFilter"
                :label="$t('Date de début')"
                :rules="rules.fromDate"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <date-input
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
                v-model="actorsFilter"
                type="multiselect"
                :label="$t('Acteur')"
                :options="actors"
                :multiple="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <job-autocomplete
                ref="jobAutocomplete"
                v-model="jobsFilter"
                :multiple="true"
                @update-jobs="updateJobs"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="matchingStatusFilter"
                type="select"
                :label="$t('Etat du matching')"
                :options="matchingStatusFilters"
              />
            </oxd-grid-item>
            <oxd-grid-item
              class="orangehrm-switch-wrapper"
              style="display: flex; flex-direction: row; margin-top: 0.5rem"
            >
              <oxd-switch-input
                v-model="courseOnly"
                :label="$t(`Uniquement avec formation`)"
              />
              <oxd-text
                class="oxd-label"
                :style="{
                  fontFamily: 'Nunito Sans, sans-serif',
                  fontSize: '12px',
                  fontWeight: '600',
                  color: 'var(--oxd-interface-gray-darken-1-color, #64728c)',
                  marginBottom: '0.5rem',
                }"
              >
                Uniquement avec formation
              </oxd-text>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-actions>
          <oxd-button
            display-type="ghost"
            :label="$t('general.reset')"
            @click="onClickReset"
          />
          <oxd-button
            class="orangehrm-left-space"
            display-type="secondary"
            :label="$t('general.search')"
            type="submit"
          />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>
    <br />
    <div
      v-if="isLoading"
      class="orangehrm-header-container"
      style="justify-content: center"
    >
      <oxd-loading-spinner class="orangehrm-container-loader" />
    </div>
    <div v-else class="orangehrm-paper-container">
      <div class="orangehrm-header-container">
        <div class="orangehrm-header-left">
          <oxd-button
            display-type="secondary"
            :label="$t('Exporter en Excel')"
            class="export-button"
            icon-name="download"
            @click="exportToExcel"
          />
          <span class="orangehrm-text">
            {{
              totalRecords > 1
                ? totalRecords + ' leads trouvés'
                : totalRecords + ' lead trouvé'
            }}
          </span>
        </div>
        <div class="orangehrm-pagination-wrapper">
          <oxd-pagination v-model:current="currentPage" :length="totalPages" />
        </div>
      </div>
      <div class="orangehrm-horizontal-scroll-container">
        <table class="orangehrm-custom-table">
          <thead>
            <tr>
              <th class="action-column"></th>
              <th v-for="(header, index) in tableHeaders" :key="index">
                {{ header.label }}
              </th>
              <th class="action-column"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in tableData"
              :key="index"
              :class="{'highlighted-row': selectedRow === index}"
            >
              <td class="action-column-values">
                <oxd-icon-button
                  name="arrow-clockwise"
                  class="action-button"
                  @click.stop="reloadLead(item.id)"
                />
                <oxd-icon-button
                  name="eye-fill"
                  class="action-button"
                  @click.stop="viewLead(item.id)"
                />
              </td>
              <td
                v-for="(header, headerIndex) in tableHeaders"
                :key="headerIndex"
                :class="{
                  'selected-cell':
                    selectedCell.row === index &&
                    selectedCell.col === headerIndex,
                  'selected-row': selectedCell.row === index,
                }"
                @click="selectCell(index, headerIndex)"
              >
                {{ getCellValue(item, header.key) }}
              </td>
              <td class="action-column-values">
                <oxd-icon-button
                  name="eye-fill"
                  class="action-button"
                  @click.stop="viewLead(item.id)"
                />
              </td>
            </tr>
            <tr v-if="tableData.length === 0">
              <td colspan="38">{{ $t('general.no_records_found') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <confirmation-dialog
      ref="confirmationDialog"
      :title="$t('Confirmer le retraitement')"
      :subtitle="$t('Êtes-vous sûrs de vouloir retraiter ce lead ?')"
      :confirm-label="$t('Confirmer')"
      :cancel-label="$t('Annuler')"
      :confirm-button-type="'secondary'"
    />
  </div>
</template>
<script>
import {ref, computed, onMounted, watch} from 'vue';
import {navigate} from '@/core/util/helper/navigation';
import usei18n from '@/core/util/composable/usei18n';
import {
  required,
  validDateFormat,
  startDateShouldBeBeforeEndDate,
  endDateShouldBeAfterStartDate,
} from '@/core/util/validation/rules';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import useToast from '@/core/util/composable/useToast';
import JobAutocomplete from '@/core/components/inputs/JobAutocomplete.vue';
import {APIService} from '@/core/util/services/api.service';
import {OxdSpinner, OxdSwitchInput} from '@ohrm/oxd';
import * as XLSX from 'xlsx';
import ConfirmationDialog from '@/core/components/dialogs/ConfirmationDialog.vue';
import DateInput from '@/core/components/inputs/DateInput';

export default {
  components: {
    'oxd-loading-spinner': OxdSpinner,
    'job-autocomplete': JobAutocomplete,
    'confirmation-dialog': ConfirmationDialog,
    'oxd-switch-input': OxdSwitchInput,
    'date-input': DateInput,
  },
  props: {
    actors: {
      type: Array,
      default: () => [],
    },
    matchingStatusFilters: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const {$t} = usei18n();
    const jobAutocomplete = ref(null);

    const userDateFormat = 'yyyy-MM-dd';

    // Clé pour le localStorage
    const STORAGE_KEY = 'leadsFilters';

    // Fonction pour charger les filtres depuis localStorage
    const loadFiltersFromLocalStorage = () => {
      try {
        const savedFilters = localStorage.getItem(STORAGE_KEY);
        if (savedFilters) {
          const filters = JSON.parse(savedFilters);

          // Convertir les dates sauvegardées du format API (yyyy-MM-dd) vers le format utilisateur
          const defaultStartDate = new Date();
          defaultStartDate.setDate(defaultStartDate.getDate() - 2);
          const defaultEndDate = new Date();

          const convertDateFromStorage = (dateValue) => {
            if (!dateValue) return null;
            if (
              dateValue.includes('-') &&
              dateValue.length === 10 &&
              dateValue.match(/^\d{4}-\d{2}-\d{2}$/)
            ) {
              // Format API (yyyy-MM-dd), convertir vers format utilisateur
              return formatDate(
                parseDate(dateValue, 'yyyy-MM-dd'),
                userDateFormat,
              );
            }
            // Déjà au format utilisateur
            return dateValue;
          };

          return {
            startDateFilter:
              convertDateFromStorage(filters.startDateFilter) ||
              formatDate(defaultStartDate, userDateFormat),
            endDateFilter:
              convertDateFromStorage(filters.endDateFilter) ||
              formatDate(defaultEndDate, userDateFormat),
            matchingStatusFilter: filters.matchingStatusFilter || null,
            actorsFilter: filters.actorsFilter || [],
            jobsFilter: filters.jobsFilter || [],
            courseOnly: filters.courseOnly || false,
          };
        }
      } catch (error) {
        console.error('Error loading filters from localStorage:', error);
      }
      // Valeurs par défaut si rien n'est sauvegardé
      const defaultStartDate = new Date();
      defaultStartDate.setDate(defaultStartDate.getDate() - 2);
      const defaultEndDate = new Date();
      // Valeurs par défaut si rien n'est sauvegardé
      return {
        startDateFilter: formatDate(defaultStartDate, userDateFormat),
        endDateFilter: formatDate(defaultEndDate, userDateFormat),
        matchingStatusFilter: null,
        actorsFilter: [],
        jobsFilter: [],
        courseOnly: false,
      };
    };

    // Fonction pour sauvegarder les filtres dans localStorage
    const saveFiltersToLocalStorage = (
      startDate,
      endDate,
      matchingStatusFilter,
      actors,
      jobs,
      course,
    ) => {
      try {
        // Convertir du format utilisateur vers le format API pour le stockage
        const startDateApi = startDate
          ? formatDate(parseDate(startDate, userDateFormat), 'yyyy-MM-dd')
          : null;
        const endDateApi = endDate
          ? formatDate(parseDate(endDate, userDateFormat), 'yyyy-MM-dd')
          : null;
        const filters = {
          startDateFilter: startDateApi,
          endDateFilter: endDateApi,
          matchingStatusFilter: matchingStatusFilter,
          actorsFilter: actors,
          jobsFilter: jobs,
          courseOnly: course,
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
      } catch (error) {
        console.error('Error saving filters to localStorage:', error);
      }
    };

    // Charger les filtres depuis localStorage
    const loadedFilters = loadFiltersFromLocalStorage();

    // S'assurer que les dates sont initialisées avec des valeurs par défaut si elles sont null/undefined
    const defaultStartDate = new Date();
    defaultStartDate.setDate(defaultStartDate.getDate() - 2);
    const defaultEndDate = new Date();

    const startDateFilter = ref(
      loadedFilters?.startDateFilter ||
        formatDate(defaultStartDate, userDateFormat),
    );
    const endDateFilter = ref(
      loadedFilters?.endDateFilter ||
        formatDate(defaultEndDate, userDateFormat),
    );
    const matchingStatusFilter = ref(loadedFilters.matchingStatusFilter);
    const actorsFilter = ref(loadedFilters.actorsFilter);
    const jobsFilter = ref(loadedFilters.jobsFilter);
    const courseOnly = ref(loadedFilters.courseOnly);
    const tableData = ref([]);
    const leads = ref([]);
    const defaultColumns = ref(null);
    const isLoading = ref(false);
    const {noRecordsFound} = useToast();
    const totalRecords = ref(0);
    const itemsPerPage = 50;
    const currentPage = ref(1);
    const selectedCell = ref({row: null, col: null});
    const selectedRow = ref(null);
    const rules = {
      fromDate: [
        required,
        validDateFormat(userDateFormat),
        startDateShouldBeBeforeEndDate(
          () => endDateFilter.value,
          $t('general.from_date_should_be_before_to_date'),
          {allowSameDate: true, dateFormat: userDateFormat},
        ),
      ],
      toDate: [
        required,
        validDateFormat(userDateFormat),
        endDateShouldBeAfterStartDate(
          () => startDateFilter.value,
          $t('general.to_date_should_be_after_from_date'),
          {allowSameDate: true, dateFormat: userDateFormat},
        ),
      ],
    };
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/leads`,
    );

    const ALL_TABLE_HEADERS = [
      {
        label: 'ID',
        key: 'id',
      },
      {
        label: 'Date de réception',
        key: 'receivedAt',
      },
      {
        label: 'Civilité',
        key: 'civility',
      },
      {
        label: 'Prénom',
        key: 'firstName',
      },
      {
        label: 'Nom',
        key: 'lastName',
      },
      {
        label: 'Email',
        key: 'email',
      },
      {
        label: 'Téléphone',
        key: 'phoneNumberEmail',
      },
      {
        label: 'Adresse',
        key: 'address',
      },
      {
        label: 'Code postal',
        key: 'locationPostalCodeEmail',
      },
      {
        label: 'Code postal identifié',
        key: 'zipCode',
      },
      {
        label: 'Ville',
        key: 'city',
      },
      {
        label: 'Pays',
        key: 'country',
      },
      {
        label: 'Date de naissance',
        key: 'birthDate',
      },
      {
        label: 'Âge',
        key: 'age',
      },
      {
        label: 'CV',
        key: 'resume',
      },
      {
        label: 'Est NEET ?',
        key: 'isNEET',
      },
      {
        label: 'Métiers',
        key: 'jobs',
      },
      {
        label: 'Secteur',
        key: 'sector',
      },
      {
        label: 'Formation',
        key: 'course',
      },
      {
        label: 'OF',
        key: 'of',
      },
      {
        label: 'Situation actuelle',
        key: 'status',
      },
      {
        label: "Niveau d'études",
        key: 'studyLevel',
      },
      {
        label: 'Modalité de formation',
        key: 'trainingMethod',
      },
      {
        label: 'Besoin',
        key: 'need',
      },
      {
        label: 'Handicap',
        key: 'handicap',
      },
      {
        label: 'Début de formation',
        key: 'courseStart',
      },
      {
        label: 'Financement',
        key: 'funding',
      },
      {
        label: 'Campagne UTM',
        key: 'utmCampaign',
      },
      {
        label: 'Groupe UTM',
        key: 'utmGroup',
      },
      {
        label: 'Source UTM',
        key: 'utmSource',
      },
      {
        label: 'Source',
        key: 'source',
      },
      {
        label: 'Disponibilité - Email',
        key: 'timeSlotEmail',
      },
      {
        label: 'Disponibilité identifiée',
        key: 'timeSlot',
      },
      {
        label: 'Complément',
        key: 'complement',
      },
      {
        label: 'Dernier contact',
        key: 'lastContact',
      },
      {
        label: "Date d'envoi",
        key: 'sentTime',
      },
      {
        label: 'Partenaire',
        key: 'actor',
      },
      {
        label: "Etat d'envoi",
        key: 'matchingState',
      },
      {
        label: "Message d'erreur / API",
        key: 'matchingStatus',
      },
      {
        label: 'Partenaire - Autres matchings',
        key: 'otherActors',
      },
    ];

    const tableHeaders = ref([...ALL_TABLE_HEADERS]);

    const ALWAYS_VISIBLE_KEYS = [
      'id',
      'receivedAt',
      'firstName',
      'lastName',
      'email',
      'phoneNumberEmail',
      'sentTime',
      'actor',
      'matchingState',
      'matchingStatus',
      'otherActors',
    ];

    const CONDITIONAL_COLUMNS = {
      civility: (cols) => !!cols.civility,
      address: (cols) => !!cols.address,
      locationPostalCodeEmail: (cols) => !!cols.postalCode,
      zipCode: (cols) => !!cols.postalCode,
      city: (cols) => !!cols.city,
      country: (cols) => !!cols.country,
      birthDate: (cols) => !!cols.birthDate,
      age: (cols) => !!cols.age,
      resume: (cols) => !!cols.resume,
      isNEET: (cols) => !!cols.neet,
      jobs: (cols) => !!cols.job,
      sector: (cols) => !!cols.sector,
      course: (cols) => !!cols.course,
      of: (cols) => !!cols.course,
      status: (cols) => !!cols.status,
      studyLevel: (cols) => !!cols.studyLevel,
      trainingMethod: (cols) => !!cols.trainingMethod,
      need: (cols) => !!cols.need,
      handicap: (cols) => !!cols.handicap,
      courseStart: (cols) => !!cols.courseStart,
      funding: (cols) => !!cols.funding,
      utmCampaign: (cols) => !!cols.utmCampaign,
      utmGroup: (cols) => !!cols.utmGroup,
      utmSource: (cols) => !!cols.utmSource,
      source: (cols) => !!cols.source,
      timeSlotEmail: (cols) => !!cols.timeSlot,
      timeSlot: (cols) => !!cols.timeSlot,
      complement: (cols) => !!cols.complement,
      lastContact: () => true,
    };

    const updateTableHeadersForDefaultColumns = (cols) => {
      if (!cols) {
        tableHeaders.value = [...ALL_TABLE_HEADERS];
        return;
      }
      tableHeaders.value = ALL_TABLE_HEADERS.filter((col) => {
        if (ALWAYS_VISIBLE_KEYS.includes(col.key)) {
          return true;
        }
        const condition = CONDITIONAL_COLUMNS[col.key];
        if (!condition) {
          return true;
        }
        return !!condition(cols);
      });
    };

    const getCellValue = (item, headerKey) => {
      return item[headerKey];
    };

    const totalPages = computed(() => {
      return Math.ceil(totalRecords.value / itemsPerPage);
    });

    const fetchData = async () => {
      isLoading.value = true;
      http
        .getAll({
          from: startDateFilter.value
            ? formatDate(
                parseDate(startDateFilter.value, userDateFormat),
                'yyyy-MM-dd',
              )
            : undefined,
          to: endDateFilter.value
            ? formatDate(
                parseDate(endDateFilter.value, userDateFormat),
                'yyyy-MM-dd',
              )
            : undefined,
          matchingStatus: matchingStatusFilter.value
            ? matchingStatusFilter.value.label
            : null,
          actors: actorsFilter.value
            ? actorsFilter.value.map((actor) => actor.label)
            : [],
          jobs: jobsFilter.value
            ? jobsFilter.value.map((job) => job.label)
            : [],
          courseOnly: courseOnly.value,
        })
        .then((response) => {
          leads.value = response.data;
          if (leads.value && leads.value.length > 0)
            leads.value.sort((a, b) => {
              return new Date(b.receivedAt) - new Date(a.receivedAt);
            });
          tableData.value = leads.value
            ? leads.value.length > itemsPerPage
              ? leads.value.slice(
                  (currentPage.value - 1) * itemsPerPage,
                  currentPage.value * itemsPerPage,
                )
              : leads.value
            : [];
          totalRecords.value = leads.value ? leads.value.length : 0;
          if (totalRecords.value === 0) noRecordsFound();

          const selectedActors = actorsFilter.value || [];
          if (selectedActors.length === 1) {
            const actorLabel = selectedActors[0]?.label;
            if (actorLabel) {
              return http
                .request({
                  method: 'GET',
                  url: `/api/v2/actor/reporting-columns/default?actor=${encodeURIComponent(
                    actorLabel,
                  )}`,
                })
                .then(({data}) => {
                  defaultColumns.value = data?.defaultColumns || null;
                  updateTableHeadersForDefaultColumns(defaultColumns.value);
                })
                .catch(() => {
                  defaultColumns.value = null;
                  updateTableHeadersForDefaultColumns(null);
                });
            }
          }
          defaultColumns.value = null;
          updateTableHeadersForDefaultColumns(null);
          return null;
        })
        .finally(() => {
          isLoading.value = false;
        });
    };

    const filterItems = () => {
      currentPage.value = 1;
      // Sauvegarder les filtres avant de filtrer
      saveFiltersToLocalStorage(
        startDateFilter.value,
        endDateFilter.value,
        matchingStatusFilter.value,
        actorsFilter.value,
        jobsFilter.value,
        courseOnly.value,
      );
      fetchData();
    };

    const onClickReset = () => {
      const defaultStartDate = new Date();
      defaultStartDate.setDate(defaultStartDate.getDate() - 2);
      const defaultEndDate = new Date();
      startDateFilter.value = formatDate(defaultStartDate, userDateFormat);
      endDateFilter.value = formatDate(defaultEndDate, userDateFormat);
      matchingStatusFilter.value = null;
      actorsFilter.value = [];
      jobsFilter.value = [];
      currentPage.value = 1;
      // Sauvegarder les filtres réinitialisés
      saveFiltersToLocalStorage(
        startDateFilter.value,
        endDateFilter.value,
        matchingStatusFilter.value,
        actorsFilter.value,
        jobsFilter.value,
        courseOnly.value,
      );
      if (jobAutocomplete.value) {
        jobAutocomplete.value.reset();
      }
      fetchData();
    };

    const selectCell = (rowIndex, colIndex) => {
      selectedCell.value = {row: rowIndex, col: colIndex};
      selectedRow.value = rowIndex;
    };

    const exportToExcel = () => {
      // Create a worksheet from the leads data
      const worksheet = XLSX.utils.json_to_sheet(
        leads.value.map((item) => {
          const row = {};
          tableHeaders.value.forEach((header) => {
            row[header.label] = item[header.key];
          });
          return row;
        }),
      );

      const columnWidths = tableHeaders.value.map(() => ({wch: 15}));
      worksheet['!cols'] = columnWidths;

      // Create a workbook
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Leads');

      // Generate the file and trigger download
      const fileName = `leads_${startDateFilter.value}_${endDateFilter.value}.xlsx`;
      XLSX.writeFile(workbook, fileName);
    };

    watch(currentPage, (newPage, oldPage) => {
      if (newPage !== oldPage)
        tableData.value = leads.value.slice(
          (newPage - 1) * itemsPerPage,
          newPage * itemsPerPage,
        );
    });

    const updateJobs = (jobs) => {
      jobsFilter.value = jobs;
      // Sauvegarder les filtres quand les jobs changent
      saveFiltersToLocalStorage(
        startDateFilter.value,
        endDateFilter.value,
        matchingStatusFilter.value,
        actorsFilter.value,
        jobsFilter.value,
        courseOnly.value,
      );
    };

    // Watch pour sauvegarder automatiquement les changements de filtres
    watch(
      [
        startDateFilter,
        endDateFilter,
        matchingStatusFilter,
        actorsFilter,
        jobsFilter,
        courseOnly,
      ],
      () => {
        saveFiltersToLocalStorage(
          startDateFilter.value,
          endDateFilter.value,
          matchingStatusFilter.value,
          actorsFilter.value,
          jobsFilter.value,
          courseOnly.value,
        );
      },
    );

    onMounted(() => {
      // Restaurer les jobs dans jobAutocomplete si nécessaire
      if (jobAutocomplete.value && jobsFilter.value.length > 0) {
        // Le jobAutocomplete devrait se mettre à jour automatiquement via v-model
        // mais on peut forcer une mise à jour si nécessaire
      }
      fetchData();
    });

    return {
      http,
      jobAutocomplete,
      startDateFilter,
      endDateFilter,
      matchingStatusFilter,
      actorsFilter,
      jobsFilter,
      courseOnly,
      tableData,
      tableHeaders,
      totalRecords,
      currentPage,
      totalPages,
      selectedCell,
      selectedRow,
      filterItems,
      onClickReset,
      selectCell,
      getCellValue,
      rules,
      isLoading,
      exportToExcel,
      updateJobs,
      fetchData,
      saveFiltersToLocalStorage,
    };
  },
  methods: {
    reloadLead(leadId) {
      this.$refs.confirmationDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.reprocessLead(leadId);
        }
      });
    },
    viewLead(leadId) {
      // Sauvegarder les filtres avant de naviguer
      this.saveFiltersToLocalStorage(
        this.startDateFilter,
        this.endDateFilter,
        this.matchingStatusFilter,
        this.actorsFilter,
        this.jobsFilter,
        this.courseOnly,
      );
      navigate(`/${window.appGlobal.theme}/admin/viewLeads/{id}`, {
        id: leadId,
      });
    },
    reprocessLead(leadId) {
      this.isLoading = true;
      this.http
        .update(leadId, {})
        .then(() => {
          this.$toast.saveSuccess();
          this.fetchData();
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error?.response?.data?.message);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>
<style lang="scss" scoped>
.orangehrm-horizontal-scroll-container {
  overflow-x: auto;
  width: 100%;
}

.orangehrm-text,
.orangehrm-custom-table th,
.orangehrm-custom-table td {
  font-family: 'Nunito Sans', sans-serif;
  font-size: 12px;
  font-weight: 400;
  color: #64728c;
}

.orangehrm-custom-table {
  width: 100%;
  border-collapse: collapse;

  th,
  td {
    padding: 0.5rem 1rem;
    text-align: left;
    border: 1px solid #eaebee;
    background-color: white;
    white-space: nowrap;
    position: relative;
  }

  th {
    font-weight: bold;
    color: #38455d;
    font-size: 14px;
    padding: 0.75rem 1rem;
    background-color: #f5f6f7;
    position: sticky;
    top: 0;
    z-index: 1;
    border-bottom: 2px solid #d8dadf;
  }

  tbody tr {
    td {
      background-color: white;
      cursor: pointer;
      transition: background-color 0.2s ease;

      &.selected-cell {
        background-color: #f5f6f7;
        box-shadow: inset 0 0 0 1px var(--oxd-primary-one-color);
      }
      &.selected-row {
        background-color: #f5f6f7;
      }
    }
  }
}

.orangehrm-header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
}

.orangehrm-header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.export-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 12px;
  height: 32px;
}

.export-icon {
  margin-right: 4px;
}

.orangehrm-pagination-wrapper {
  display: flex;
  align-items: center;
}

.records-count {
  font-size: 0.9rem;
  color: var(--oxd-interface-gray-color);
}

.action-column {
  width: auto;
  min-width: 60px;
  padding: 0.25rem !important;
  text-align: center !important;
}

.action-column-values {
  width: auto;
  min-width: 60px;
  padding: 0.25rem !important;
  text-align: center !important;
  display: flex;
  flex-direction: row;
  gap: 0.25rem;
  justify-content: center;
  align-items: center;
}

.action-button {
  margin: 0 auto;

  &:hover {
    color: var(--oxd-primary-one-color);
  }
}
</style>
