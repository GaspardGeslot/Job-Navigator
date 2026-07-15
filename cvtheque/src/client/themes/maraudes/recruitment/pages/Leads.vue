<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Filtres')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <date-input
                v-model="startDateFilter"
                :label="$t('Date de réception (début)')"
                :rules="rules.fromDate"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <date-input
                v-model="endDateFilter"
                :label="$t('Date de réception (fin)')"
                :rules="rules.toDate"
                required
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row v-if="showContactStatusFilter">
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="contactStatusFilter"
                type="select"
                :label="$t('Etat de contact')"
                :options="contactStatusOptions"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <!-- Standard filter rows for STRING, SELECT, BOOLEAN types -->
        <oxd-form-row
          v-if="filterableColumns.some((col) => col.type !== 'DATE')"
        >
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <template v-for="col in filterableColumns" :key="col.id">
              <oxd-grid-item v-if="col.type === 'STRING'">
                <oxd-input-field
                  v-model="customColumnFilters[col.id]"
                  :label="col.title"
                />
              </oxd-grid-item>
              <oxd-grid-item v-else-if="col.type === 'SELECT'">
                <oxd-input-field
                  v-model="customColumnFilters[col.id]"
                  type="multiselect"
                  :multiple="true"
                  :label="col.title"
                  :options="
                    col.options
                      ? (typeof col.options === 'string'
                          ? JSON.parse(col.options)
                          : col.options
                        ).map((o) => ({id: o, label: o}))
                      : []
                  "
                />
              </oxd-grid-item>
              <oxd-grid-item v-else-if="col.type === 'BOOLEAN'">
                <oxd-input-field
                  v-model="customColumnFilters[col.id]"
                  type="select"
                  :label="col.title"
                  :options="[
                    {id: true, label: $t('Oui')},
                    {id: false, label: $t('Non')},
                  ]"
                />
              </oxd-grid-item>
            </template>
          </oxd-grid>
        </oxd-form-row>

        <!-- Dedicated form-rows for each DATE type filter -->
        <template v-for="col in filterableColumns" :key="col.id + '_dateRow'">
          <oxd-form-row v-if="col.type === 'DATE'">
            <oxd-grid :cols="2" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <date-input
                  v-model="customColumnFilters[col.id].from"
                  :label="`${col.title} (début)`"
                  :rules="[
                    validDateFormat(userDateFormat),
                    startDateShouldBeBeforeEndDate(
                      () => customColumnFilters[col.id].to,
                      $t('general.from_date_should_be_before_to_date'),
                      {allowSameDate: true, dateFormat: userDateFormat},
                    ),
                  ]"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <date-input
                  v-model="customColumnFilters[col.id].to"
                  :label="`${col.title} (fin)`"
                  :rules="[
                    validDateFormat(userDateFormat),
                    endDateShouldBeAfterStartDate(
                      () => customColumnFilters[col.id].from,
                      $t('general.to_date_should_be_after_from_date'),
                      {allowSameDate: true, dateFormat: userDateFormat},
                    ),
                  ]"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </template>
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
          <oxd-pagination
            v-model:current="currentPage"
            :length="paginationLength"
          />
        </div>
      </div>
      <div
        class="orangehrm-horizontal-scroll-container"
        :class="{'--cell-editing': editingCell}"
      >
        <table class="orangehrm-custom-table">
          <thead>
            <tr>
              <th class="action-column"></th>
              <th
                v-for="(header, index) in tableHeaders"
                :key="index"
                :class="{
                  'editable-column-header': isEditableColumn(header),
                }"
              >
                <span class="column-header">
                  <span class="column-header__label">{{ header.label }}</span>
                  <oxd-icon
                    v-if="isEditableColumn(header)"
                    name="pencil-fill"
                    class="column-header__edit-icon"
                    title="Modifiable — cliquez sur une cellule"
                  />
                </span>
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
                  'editable-cell': isEditableColumn(header),
                  'editing-cell': isEditingCell(index, headerIndex),
                  'editing-cell--date':
                    isEditingCell(index, headerIndex) &&
                    getEditableFieldType(header) === 'date',
                  'editing-cell--select':
                    isEditingCell(index, headerIndex) &&
                    getEditableFieldType(header) === 'select',
                }"
                @click.stop="onCellClick(index, headerIndex, item, header)"
              >
                <div
                  v-if="isEditingCell(index, headerIndex)"
                  class="inline-cell-editor"
                  :class="{
                    'inline-cell-editor--date':
                      getEditableFieldType(header) === 'date',
                  }"
                  @click.stop
                >
                  <input
                    v-if="getEditableFieldType(header) === 'date'"
                    type="date"
                    class="inline-cell-editor__date-input"
                    :value="editingDateValue"
                    @click.stop
                    @change="onEditableDateChange(item, header, $event)"
                    @blur="onEditableDateBlur"
                  />
                  <ul v-else class="inline-cell-editor__options">
                    <li
                      v-for="option in getEditableOptions(header)"
                      :key="`${header.customColumnId}-${option.id ?? 'empty'}`"
                      :class="{
                        'inline-cell-editor__option': true,
                        'inline-cell-editor__option--active': isCurrentOption(
                          item,
                          header,
                          option,
                        ),
                        'inline-cell-editor__option--empty': option.id === null,
                      }"
                      @click.stop="onSelectEditableOption(item, header, option)"
                    >
                      {{ option.id === null ? '—' : option.label }}
                    </li>
                  </ul>
                </div>
                <template v-else>
                  {{ getCellValue(item, header.key, header) }}
                </template>
              </td>
            </tr>
            <tr v-if="tableData.length === 0">
              <td colspan="38">{{ $t('general.no_records_found') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <view-lead
      v-if="selectedLeadId"
      :lead-id="selectedLeadId"
      :default-columns="reportingDefaultColumns"
      :custom-columns="customColumns"
      :contact-log-types="contactLogTypes"
      @close="selectedLeadId = null"
      @open-full-page="openLeadInFullPage"
    />
  </div>
</template>
<script>
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
  watch,
  reactive,
  getCurrentInstance,
} from 'vue';
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
import {OxdIcon, OxdSpinner} from '@ohrm/oxd';
import * as XLSX from 'xlsx';
import DateInput from '@/core/components/inputs/DateInput';
import ViewLead from '../components/ViewLead.vue';

const EMPTY_SELECT_OPTION = {id: null, label: ''};

const parseCustomColumnOptions = (options) => {
  if (!options) {
    return [];
  }
  try {
    const parsed = typeof options === 'string' ? JSON.parse(options) : options;
    if (!Array.isArray(parsed)) {
      return [];
    }
    return parsed.map((option) => ({id: option, label: option}));
  } catch {
    return [];
  }
};

export default {
  components: {
    'oxd-icon': OxdIcon,
    'oxd-loading-spinner': OxdSpinner,
    'date-input': DateInput,
    'view-lead': ViewLead,
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
    matchingStatusFilters: {
      type: Array,
      default: () => [],
    },
    otherMatchings: {
      type: Boolean,
      default: null,
    },
  },
  setup(props) {
    const {$t} = usei18n();
    const instance = getCurrentInstance();
    const userDateFormat = 'yyyy-MM-dd';

    // Filtres dynamiques pour les colonnes personnalisées avec hasFilter: true
    const customColumnFilters = reactive({});

    // PHP peut retourner 1 au lieu de true pour les booléens
    const filterableColumns = computed(() =>
      (props.customColumns || []).filter((col) => !!col.hasFilter),
    );

    // Initialiser les clés dès que filterableColumns change
    watch(
      filterableColumns,
      (cols) => {
        cols.forEach((col) => {
          if (!(col.id in customColumnFilters)) {
            if (col.type === 'SELECT') customColumnFilters[col.id] = [];
            else if (col.type === 'DATE')
              customColumnFilters[col.id] = {from: null, to: null};
            else customColumnFilters[col.id] = null;
          }
        });
      },
      {immediate: true},
    );

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
    const contactStatusOptions = computed(() => props.matchingStatusFilters);
    const contactStatusFilter = ref(null);
    const tableData = ref([]);
    const leads = ref([]);
    const isLoading = ref(false);
    const {noRecordsFound, updateSuccess} = useToast();
    const totalRecords = ref(0);
    const itemsPerPage = 50;
    const currentPage = ref(1);
    const selectedCell = ref({row: null, col: null});
    const selectedRow = ref(null);
    const selectedLeadId = ref(null);
    const editingCell = ref(null);
    const editingDateValue = ref('');
    const contactLogTypes = ref([]);
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

    const reportingDefaultColumns = ref(props.defaultColumns);

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
      {
        label: 'Périmètre',
        key: 'otherActors',
        condition: () => props.otherMatchings === true,
      },
    ];

    // Compute table headers efficiently with conditions
    const tableHeaders = [];
    for (const col of COLUMN_CONFIG) {
      if (col.utms) {
        if (col.condition(reportingDefaultColumns.value)) {
          tableHeaders.push(...col.utms);
        }
      } else if (
        !col.condition ||
        col.condition(reportingDefaultColumns.value)
      ) {
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
          customColumnOptions: customCol.options,
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

    const getRawCustomColumnValue = (item, headerConfig) => {
      if (!headerConfig?.isCustom || !item.customColumns) {
        return null;
      }
      const customColumn = item.customColumns.find(
        (cc) => cc.id === headerConfig.customColumnId,
      );
      if (
        !customColumn ||
        customColumn.value === null ||
        customColumn.value === undefined ||
        customColumn.value === ''
      ) {
        return null;
      }
      return String(customColumn.value);
    };

    const isEditableColumn = (header) =>
      header?.isCustom && (header.type === 'SELECT' || header.type === 'DATE');

    const getEditableFieldType = (header) => {
      if (header?.type === 'DATE') {
        return 'date';
      }
      if (header?.type === 'SELECT') {
        return 'select';
      }
      return null;
    };

    const toNativeDateInputValue = (value) => {
      if (!value) {
        return '';
      }
      if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return value;
      }
      const dateObj = parseDate(value, userDateFormat);
      return dateObj ? formatDate(dateObj, 'yyyy-MM-dd') : '';
    };

    const isEditingCell = (rowIndex, colIndex) =>
      editingCell.value?.row === rowIndex &&
      editingCell.value?.col === colIndex;

    const sortOptionsByLabel = (options) =>
      [...options].sort((a, b) =>
        (a.label || '').localeCompare(b.label || '', 'fr', {
          sensitivity: 'base',
        }),
      );

    const getEditableOptions = (header) => {
      if (getEditableFieldType(header) !== 'select') {
        return [];
      }
      return [
        EMPTY_SELECT_OPTION,
        ...sortOptionsByLabel(
          parseCustomColumnOptions(header.customColumnOptions),
        ),
      ];
    };

    const isCurrentOption = (item, header, option) => {
      const current = getRawCustomColumnValue(item, header);
      if (option.id === null) {
        return current === null;
      }
      return current === option.label;
    };

    const applyCustomColumnValue = (item, header, value) => {
      if (!item.customColumns) {
        item.customColumns = [];
      }
      const customColumn = item.customColumns.find(
        (cc) => cc.id === header.customColumnId,
      );
      if (customColumn) {
        customColumn.value = value;
        return;
      }
      item.customColumns.push({
        id: header.customColumnId,
        title: header.key,
        value,
      });
    };

    const closeCellEditor = () => {
      editingCell.value = null;
      editingDateValue.value = '';
    };

    const serializeCustomColumnValue = (value) =>
      value === null || value === undefined || value === ''
        ? null
        : String(value);

    const persistInlineCustomColumnEdit = (item, header, value) => {
      const previousValue = getRawCustomColumnValue(item, header);
      const normalizedValue = serializeCustomColumnValue(value);
      applyCustomColumnValue(item, header, normalizedValue);

      return http
        .request({
          method: 'PUT',
          url: `/api/v2/admin/lead/${item.id}/custom-column`,
          data: {
            id: header.customColumnId,
            value: normalizedValue,
          },
        })
        .then(() => {
          updateSuccess();
        })
        .catch((error) => {
          applyCustomColumnValue(item, header, previousValue);
          instance?.proxy?.$toast?.unexpectedError(
            error?.response?.data?.message,
          );
        });
    };

    const onCellClick = (rowIndex, colIndex, item, header) => {
      selectCell(rowIndex, colIndex);
      if (isEditableColumn(header)) {
        editingCell.value = {
          row: rowIndex,
          col: colIndex,
          leadId: item.id,
          header,
        };
        editingDateValue.value =
          getEditableFieldType(header) === 'date'
            ? toNativeDateInputValue(getRawCustomColumnValue(item, header))
            : '';
        return;
      }
      closeCellEditor();
    };

    const onEditableDateChange = (item, header, event) => {
      const apiValue = event.target.value ?? '';
      const currentApiValue = toNativeDateInputValue(
        getRawCustomColumnValue(item, header),
      );
      if (apiValue === currentApiValue) {
        closeCellEditor();
        return;
      }
      persistInlineCustomColumnEdit(item, header, apiValue || null);
      closeCellEditor();
    };

    const onSelectEditableOption = (item, header, option) => {
      if (isCurrentOption(item, header, option)) {
        return;
      }
      const value = option.id === null ? null : String(option.label);
      persistInlineCustomColumnEdit(item, header, value);
      closeCellEditor();
    };

    const onDocumentClick = (event) => {
      if (!editingCell.value) {
        return;
      }
      if (event.target.closest('.inline-cell-editor')) {
        return;
      }
      closeCellEditor();
    };

    const onEditableDateBlur = () => {
      const editingSnapshot = editingCell.value ? {...editingCell.value} : null;
      window.setTimeout(() => {
        if (
          !editingSnapshot ||
          editingCell.value?.row !== editingSnapshot.row ||
          editingCell.value?.col !== editingSnapshot.col ||
          getEditableFieldType(editingSnapshot.header) !== 'date'
        ) {
          return;
        }
        closeCellEditor();
      }, 150);
    };

    const totalPages = computed(() => {
      return Math.ceil(totalRecords.value / itemsPerPage);
    });

    const paginationLength = computed(() => Math.max(1, totalPages.value || 1));
    const showContactStatusFilter = computed(() => {
      const cols = reportingDefaultColumns.value || {};
      return !!cols.callBackDate || !!cols.contactLogs;
    });

    // OXD pagination n'accepte pas current < 1 ou current > length
    watch([totalRecords, currentPage], () => {
      const length = paginationLength.value;
      if (currentPage.value < 1) currentPage.value = 1;
      if (currentPage.value > length) currentPage.value = length;
    });

    const fetchData = async () => {
      isLoading.value = true;
      // Construire les params des filtres de colonnes personnalisées
      const customFiltersParams = {};
      Object.entries(customColumnFilters).forEach(([id, value]) => {
        if (
          value &&
          typeof value === 'object' &&
          !Array.isArray(value) &&
          filterableColumns.value.find((c) => String(c.id) === String(id))
            ?.type === 'BOOLEAN' &&
          'id' in value
        ) {
          customFiltersParams[`customFilter[${id}]`] = value.id;
          return;
        }

        if (value === null || value === undefined || value === '') return;
        if (Array.isArray(value)) {
          // multiselect : tableau d'objets {id, label}
          if (value.length === 0) return;
          customFiltersParams[`customFilter[${id}]`] = value.map((v) => v.id);
        } else if (
          typeof value === 'object' &&
          ('from' in value || 'to' in value)
        ) {
          // plage de dates
          if (value.from)
            customFiltersParams[`customFilter[${id}][from]`] = value.from;
          if (value.to)
            customFiltersParams[`customFilter[${id}][to]`] = value.to;
        } else {
          customFiltersParams[`customFilter[${id}]`] = value;
        }
      });

      // Convertir du format utilisateur vers le format API (yyyy-MM-dd)
      const params = {
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
        matchingStatus: contactStatusFilter.value
          ? contactStatusFilter.value.label
          : null,
        ...customFiltersParams,
      };

      http
        .getAll(params)
        .then((response) => {
          leads.value = Array.isArray(response.data) ? response.data : [];

          if (leads.value.length > 0) {
            leads.value.sort((a, b) => {
              return new Date(b.receivedAt) - new Date(a.receivedAt);
            });
          }

          tableData.value =
            leads.value.length > itemsPerPage
              ? leads.value.slice(
                  (currentPage.value - 1) * itemsPerPage,
                  currentPage.value * itemsPerPage,
                )
              : leads.value;

          totalRecords.value = leads.value.length;
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
      // Réinitialiser les filtres des colonnes personnalisées
      filterableColumns.value.forEach((col) => {
        if (col.type === 'SELECT') customColumnFilters[col.id] = [];
        else if (col.type === 'DATE')
          customColumnFilters[col.id] = {from: null, to: null};
        else customColumnFilters[col.id] = null;
      });
      contactStatusFilter.value = null;
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

    const updateUrlLeadId = (leadId) => {
      const url = new URL(window.location.href);
      if (leadId != null) {
        url.searchParams.set('id', String(leadId));
      } else {
        url.searchParams.delete('id');
      }
      window.history.replaceState({}, '', url.toString());
    };

    const openLeadFromUrl = () => {
      const leadIdParam = new URLSearchParams(window.location.search).get('id');
      if (!leadIdParam) {
        return;
      }
      const parsedId = parseInt(leadIdParam, 10);
      if (!isNaN(parsedId) && parsedId > 0) {
        selectedLeadId.value = parsedId;
      }
    };

    watch(selectedLeadId, (leadId) => {
      updateUrlLeadId(leadId);
    });

    onMounted(() => {
      document.addEventListener('click', onDocumentClick);
      openLeadFromUrl();
      fetchData();
      http
        .request({
          method: 'GET',
          url: '/api/v2/admin/leads/global-options',
        })
        .then(({data}) => {
          contactLogTypes.value = data.contactLogTypes || [];
        });
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', onDocumentClick);
    });

    return {
      http,
      reportingDefaultColumns,
      contactStatusOptions,
      showContactStatusFilter,
      contactStatusFilter,
      contactLogTypes,
      startDateFilter,
      endDateFilter,
      tableData,
      tableHeaders,
      totalRecords,
      currentPage,
      totalPages,
      paginationLength,
      selectedCell,
      selectedRow,
      selectedLeadId,
      editingCell,
      editingDateValue,
      filterItems,
      onClickReset,
      selectCell,
      onCellClick,
      isEditableColumn,
      getEditableFieldType,
      isEditingCell,
      getEditableOptions,
      onEditableDateChange,
      onEditableDateBlur,
      isCurrentOption,
      onSelectEditableOption,
      getCellValue,
      rules,
      isLoading,
      exportToExcel,
      fetchData,
      saveFiltersToLocalStorage,
      userDateFormat,
      customColumnFilters,
      filterableColumns,
      validDateFormat,
      startDateShouldBeBeforeEndDate,
      endDateShouldBeAfterStartDate,
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
      this.selectedLeadId = leadId;
    },
    openLeadInFullPage() {
      const leadId = this.selectedLeadId;
      if (leadId == null) {
        return;
      }
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

  &.--cell-editing {
    overflow-y: visible;
  }
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

  .column-header {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 100%;
  }

  .column-header__label {
    min-width: 0;
  }

  .column-header__edit-icon {
    flex-shrink: 0;
    font-size: 12px;
    color: var(--oxd-primary-one-color);
    opacity: 0.85;
  }

  th.editable-column-header {
    background-color: #eef2ff;
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
      &.editable-cell {
        cursor: pointer;
      }
      &.editing-cell {
        padding: 0;
        z-index: 10;

        &--select {
          vertical-align: top;
          overflow: visible;
        }

        &--date {
          vertical-align: middle;
          overflow: hidden;
        }
      }
    }

    &:has(.editing-cell) {
      position: relative;
      z-index: 10;
    }
  }
}

.inline-cell-editor {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 30;
  min-width: 100%;
  max-height: 100px;
  overflow-x: hidden;
  overflow-y: auto;
  background-color: #ffffff;
  border: 1px solid #d8dadf;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);

  &--date {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    min-width: 0;
    max-height: none;
    overflow: hidden;
    padding: 0.25rem;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    box-shadow: none;
    border-radius: 0;
    border: none;
    background-color: #ffffff;
  }
}

.inline-cell-editor__date-input {
  display: block;
  flex: 1;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  box-sizing: border-box;
  border: 1px solid #d8dadf;
  border-radius: 4px;
  padding: 0.35rem 0.5rem;
  font-family: 'Nunito Sans', sans-serif;
  font-size: 12px;
  color: #64728c;
  background-color: #ffffff;

  &:focus {
    outline: none;
    border-color: var(--oxd-primary-one-color);
    box-shadow: inset 0 0 0 1px var(--oxd-primary-one-color);
  }
}

.inline-cell-editor__options {
  list-style: none;
  margin: 0;
  padding: 0.25rem 0;
}

.inline-cell-editor__option {
  padding: 0.5rem 1rem;
  cursor: pointer;
  white-space: nowrap;

  &:hover {
    background-color: #f5f6f7;
  }

  &--active {
    background-color: #eef2ff;
    color: var(--oxd-primary-one-color);
    font-weight: 600;
    cursor: default;

    &:hover {
      background-color: #eef2ff;
    }
  }

  &--empty {
    color: #9aa5b8;
    font-style: italic;
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
