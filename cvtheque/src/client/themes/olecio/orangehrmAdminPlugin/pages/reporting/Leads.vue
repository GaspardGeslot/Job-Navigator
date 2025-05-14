<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Leads')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="startDateFilter"
                :label="$t('Date de début')"
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
                v-model="actorFilter"
                type="select"
                :label="$t('Acteur')"
                :options="actors"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="statusFilter"
                type="radio"
                :option-label="statusOptions.billable.label"
                :value="statusOptions.billable.value"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="statusFilter"
                type="radio"
                :option-label="statusOptions.duplicate.label"
                :value="statusOptions.duplicate.value"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="statusFilter"
                type="radio"
                :option-label="statusOptions.matchingNotAvailable.label"
                :value="statusOptions.matchingNotAvailable.value"
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
            @click="exportToExcel"
            class="export-button"
            icon-name="download"
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
              <th v-for="(header, index) in tableHeaders" :key="index">
                {{ header.label }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in tableData" :key="index">
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
            </tr>
            <tr v-if="tableData.length === 0">
              <td colspan="37">{{ $t('general.no_records_found') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script>
import {ref, computed, onMounted, watch} from 'vue';
import usei18n from '@/core/util/composable/usei18n';
import {
  required,
  validDateFormatFrench,
  startDateShouldBeBeforeEndDate,
  endDateShouldBeAfterStartDate,
} from '@/core/util/validation/rules';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import useToast from '@/core/util/composable/useToast';
import {APIService} from '@/core/util/services/api.service';
import {OxdSpinner} from '@ohrm/oxd';
import * as XLSX from 'xlsx';

export default {
  components: {
    'oxd-loading-spinner': OxdSpinner,
  },
  props: {
    actors: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const {$t} = usei18n();
    const startDateFilter = ref(
      formatDate(
        new Date(new Date().setMonth(new Date().getMonth() - 1)),
        'dd-MM-yyyy',
      ),
    );
    const endDateFilter = ref(formatDate(new Date(), 'dd-MM-yyyy'));
    const statusFilter = ref(null);
    const actorFilter = ref(null);
    const tableData = ref([]);
    const leads = ref([]);
    const isLoading = ref(false);
    const {noRecordsFound} = useToast();
    const totalRecords = ref(0);
    const itemsPerPage = 50;
    const currentPage = ref(1);
    const selectedCell = ref({row: null, col: null});
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
      `${window.appGlobal.theme}/api/v2/admin/leads`,
    );

    const tableHeaders = [
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
        label: 'Complément',
        key: 'complement',
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

    const statusOptions = {
      billable: {
        label: 'Facturable',
        value: 'billable',
      },
      duplicate: {
        label: 'Doublon',
        value: 'duplicate',
      },
      matchingNotAvailable: {
        label: 'Matching non disponible',
        value: 'matchingNotAvailable',
      },
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
                parseDate(startDateFilter.value, 'dd-MM-yyyy'),
                'yyyy-MM-dd',
              )
            : undefined,
          to: endDateFilter.value
            ? formatDate(
                parseDate(endDateFilter.value, 'dd-MM-yyyy'),
                'yyyy-MM-dd',
              )
            : undefined,
          onlyBillable: statusFilter.value === statusOptions.billable.value,
          onlyDuplicate: statusFilter.value === statusOptions.duplicate.value,
          onlyMatchingNotAvailable:
            statusFilter.value === statusOptions.matchingNotAvailable.value,
          actor: actorFilter.value?.label,
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
      fetchData();
    };

    const onClickReset = () => {
      startDateFilter.value = formatDate(
        new Date(new Date().setMonth(new Date().getMonth() - 1)),
        'dd-MM-yyyy',
      );
      endDateFilter.value = formatDate(new Date(), 'dd-MM-yyyy');
      statusFilter.value = null;
      actorFilter.value = null;
      currentPage.value = 1;
      fetchData();
    };

    const selectCell = (rowIndex, colIndex) => {
      selectedCell.value = {row: rowIndex, col: colIndex};
    };

    const exportToExcel = () => {
      // Create a worksheet from the leads data
      const worksheet = XLSX.utils.json_to_sheet(
        leads.value.map((item) => {
          const row = {};
          tableHeaders.forEach((header) => {
            row[header.label] = item[header.key];
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

    onMounted(() => {
      fetchData();
    });

    return {
      actorFilter,
      startDateFilter,
      endDateFilter,
      statusFilter,
      tableData,
      tableHeaders,
      totalRecords,
      currentPage,
      totalPages,
      selectedCell,
      filterItems,
      onClickReset,
      selectCell,
      getCellValue,
      rules,
      statusOptions,
      isLoading,
      exportToExcel,
    };
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
</style>
