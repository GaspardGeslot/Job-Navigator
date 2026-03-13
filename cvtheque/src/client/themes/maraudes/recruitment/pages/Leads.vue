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
                ? totalRecords + ' contacts trouvés'
                : totalRecords + ' contact trouvé'
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
                {{ getCellValue(item, header.key, header) }}
              </td>
            </tr>
            <tr v-if="tableData.length === 0">
              <td colspan="38">{{ $t('general.no_records_found') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
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
import {APIService} from '@/core/util/services/api.service';
import {OxdSpinner} from '@ohrm/oxd';
import * as XLSX from 'xlsx';
import DateInput from '@/core/components/inputs/DateInput';

export default {
  components: {
    'oxd-loading-spinner': OxdSpinner,
    'date-input': DateInput,
  },
  props: {
    defaultColumns: {
      type: Object,
      default: () => ({}),
    },
    customColumns: {
      type: Array,
      default: () => [],
    },
  },
  setup(props) {
    const {$t} = usei18n();
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
          };
        }
      } catch (error) {
        console.error('Error loading filters from localStorage:', error);
      }
      // Valeurs par défaut si rien n'est sauvegardé
      const defaultStartDate = new Date();
      defaultStartDate.setDate(defaultStartDate.getDate() - 2);
      const defaultEndDate = new Date();
      return {
        startDateFilter: formatDate(defaultStartDate, userDateFormat),
        endDateFilter: formatDate(defaultEndDate, userDateFormat),
      };
    };

    // Fonction pour sauvegarder les filtres dans localStorage
    // Sauvegarder au format API (yyyy-MM-dd) pour la compatibilité
    const saveFiltersToLocalStorage = (startDate, endDate) => {
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
    const tableData = ref([]);
    const leads = ref([]);
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
      `/api/v2/actor/leads`,
    );

    const defaultColumns = ref(props.defaultColumns);

    const COLUMN_CONFIG = [
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
        condition: (cols) => !!cols.gender,
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
        condition: (cols) => !!cols.address,
      },
      {
        label: 'Code postal',
        key: 'locationPostalCodeEmail',
        condition: (cols) => !!cols.postalCode,
      },
      {
        label: 'Ville',
        key: 'city',
        condition: (cols) => !!cols.city,
      },
      {
        label: 'Pays',
        key: 'country',
        condition: (cols) => !!cols.country,
      },
      {
        label: 'Date de naissance',
        key: 'birthDate',
        condition: (cols) => !!cols.birthDate,
      },
      {
        label: 'Âge',
        key: 'age',
        condition: (cols) => !!cols.age,
      },
      {
        label: 'CV',
        key: 'resume',
        condition: (cols) => !!cols.resume,
      },
      {
        label: 'Est NEET ?',
        key: 'isNEET',
        condition: (cols) => !!cols.neet,
      },
      {
        label: 'Métiers',
        key: 'jobs',
        condition: (cols) => !!cols.job,
      },
      {
        label: 'Secteur',
        key: 'sector',
        condition: (cols) => !!cols.sector,
      },
      {
        label: 'Formation',
        key: 'course',
        condition: (cols) => !!cols.course,
      },
      {
        label: 'OF',
        key: 'of',
        condition: (cols) => !!cols.course,
      },
      {
        label: 'Situation actuelle',
        key: 'status',
        condition: (cols) => !!cols.status,
      },
      {
        label: "Niveau d'études",
        key: 'studyLevel',
        condition: (cols) => !!cols.studyLevel,
      },
      {
        label: 'Modalité de formation',
        key: 'trainingMethod',
        condition: (cols) => !!cols.trainingMethod,
      },
      {
        label: 'Besoin',
        key: 'need',
        condition: (cols) => !!cols.need,
      },
      {
        label: 'Handicap',
        key: 'handicap',
        condition: (cols) => !!cols.handicap,
      },
      {
        label: 'Début de formation',
        key: 'courseStart',
        condition: (cols) => !!cols.courseStart,
      },
      {
        label: 'Financement',
        key: 'funding',
        condition: (cols) => !!cols.funding,
      },
      /*{
        utms: [
          {
            label: 'Campagne UTM',
            key: 'utmCampaign',
            condition: (cols) => !!cols.utmCampaign,
          },
          {
            label: 'Groupe UTM',
            key: 'utmGroup',
            condition: (cols) => !!cols.utmGroup,
          },
          {
            label: 'Source UTM',
            key: 'utmSource',
            condition: (cols) => !!cols.utmSource,
          },
        ],
        condition: (cols) => !!cols.utms,
      },*/
      {
        label: 'Source',
        key: 'source',
        condition: (cols) => !!cols.source,
      },
      {
        label: 'Disponibilité',
        key: 'timeSlot',
        condition: (cols) => !!cols.timeSlot,
      },
      {
        label: 'Complément',
        key: 'complement',
        condition: (cols) => !!cols.complement,
      },
    ];

    // Compute table headers efficiently with conditions
    const tableHeaders = [];
    for (const col of COLUMN_CONFIG) {
      if (col.utms) {
        if (col.condition(defaultColumns.value)) {
          tableHeaders.push(...col.utms);
        }
      } else if (!col.condition || col.condition(defaultColumns.value)) {
        tableHeaders.push({
          label: col.label,
          key: col.key,
        });
      }
    }

    // Ajouter les colonnes personnalisées à la fin
    if (props.customColumns && Array.isArray(props.customColumns)) {
      props.customColumns.forEach((customCol) => {
        tableHeaders.push({
          label: customCol.title,
          key: customCol.title,
          isCustom: true,
          type: customCol.type,
          customColumnId: customCol.id,
        });
      });
    }

    const getCellValue = (item, headerKey, headerConfig) => {
      // Si c'est une colonne personnalisée, chercher dans customColumns
      if (headerConfig && headerConfig.isCustom) {
        if (item.customColumns && Array.isArray(item.customColumns)) {
          const customColumn = item.customColumns.find(
            (cc) =>
              cc.title === headerKey || cc.id === headerConfig.customColumnId,
          );
          if (
            customColumn &&
            customColumn.value !== null &&
            customColumn.value !== undefined
          ) {
            if (headerConfig.type === 'DATE') {
              return formatDate(
                parseDate(customColumn.value, 'yyyy-MM-dd'),
                'dd-MM-yyyy',
              );
            } else if (headerConfig.type === 'BOOLEAN') {
              return customColumn.value === 'true' ? 'Oui' : 'Non';
            }
            // Retourner la valeur en String
            return String(customColumn.value);
          }
        }
        return '';
      }
      // Colonne standard
      return item[headerKey];
    };

    const totalPages = computed(() => {
      return Math.ceil(totalRecords.value / itemsPerPage);
    });

    const fetchData = async () => {
      isLoading.value = true;
      // Convertir du format utilisateur vers le format API (yyyy-MM-dd)
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
        })
        .finally(() => {
          isLoading.value = false;
        });
    };

    const filterItems = () => {
      currentPage.value = 1;
      // Sauvegarder les filtres avant de filtrer
      saveFiltersToLocalStorage(startDateFilter.value, endDateFilter.value);
      fetchData();
    };

    const onClickReset = () => {
      const defaultStartDate = new Date();
      defaultStartDate.setDate(defaultStartDate.getDate() - 2);
      const defaultEndDate = new Date();
      startDateFilter.value = formatDate(defaultStartDate, userDateFormat);
      endDateFilter.value = formatDate(defaultEndDate, userDateFormat);
      currentPage.value = 1;
      // Sauvegarder les filtres réinitialisés
      saveFiltersToLocalStorage(startDateFilter.value, endDateFilter.value);
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
          tableHeaders.forEach((header) => {
            row[header.label] = getCellValue(item, header.key, header);
          });
          return row;
        }),
      );

      const columnWidths = tableHeaders.map(() => ({wch: 15}));
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

    // Watch pour sauvegarder automatiquement les changements de filtres
    watch([startDateFilter, endDateFilter], () => {
      saveFiltersToLocalStorage(startDateFilter.value, endDateFilter.value);
    });

    onMounted(() => {
      fetchData();
    });

    return {
      http,
      startDateFilter,
      endDateFilter,
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
      fetchData,
      saveFiltersToLocalStorage,
      userDateFormat,
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
      this.saveFiltersToLocalStorage(this.startDateFilter, this.endDateFilter);
      navigate(`/recruitment/viewLeads/{id}`, {
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
  min-width: 30px;
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
