<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Filtres')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid
            v-if="actorsFilter.length === 0 || !isARelancerSelected"
            :cols="2"
            class="orangehrm-full-width-grid"
          >
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
                v-model="departmentCodesFilter"
                type="multiselect"
                :label="$t('Département')"
                :options="departmentCodeOptions"
                :multiple="true"
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
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="matchingStatusFilter"
                type="multiselect"
                :label="$t('Etat du matching')"
                :options="matchingStatusFilters"
                :multiple="true"
              />
            </oxd-grid-item>
            <oxd-grid :cols="2" class="orangehrm-full-width-grid"
              ><oxd-grid-item
                class="orangehrm-switch-wrapper"
                style="display: flex; flex-direction: row; margin-top: 0.5rem"
              >
                <oxd-switch-input
                  v-model="hideTests"
                  :label="$t(`Masquer les tests`)"
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
                  Masquer les tests
                </oxd-text>
              </oxd-grid-item>
            </oxd-grid>
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
                <div
                  v-if="
                    isEditingCell(index, headerIndex) &&
                    getEditableFieldType(header.key) === 'date'
                  "
                  class="inline-cell-editor inline-cell-editor--date"
                  @click.stop
                >
                  <input
                    type="date"
                    class="inline-cell-editor__date-input"
                    :value="editingDateValue"
                    @click.stop
                    @input="onEditableDateInput"
                    @change="onEditableDateInput"
                    @keydown.enter.prevent="commitDateEditIfNeeded"
                    @keydown.escape.prevent="closeCellEditor"
                  />
                </div>
                <template
                  v-else-if="
                    isEditingCell(index, headerIndex) &&
                    getEditableFieldType(header.key) === 'select'
                  "
                >
                  <div class="editable-cell-content">
                    <span class="editable-cell-content__value">{{
                      getCellValue(item, header.key)
                    }}</span>
                    <oxd-icon
                      name="caret-down-fill"
                      class="editable-cell-content__icon"
                    />
                  </div>
                  <teleport to="#app">
                    <ul
                      class="inline-cell-editor inline-cell-editor--select-dropdown"
                      :style="selectEditorStyle"
                      @click.stop
                    >
                      <li
                        v-for="option in getEditableOptions(header.key)"
                        :key="`${header.key}-${option.id ?? 'empty'}`"
                        :class="{
                          'inline-cell-editor__option': true,
                          'inline-cell-editor__option--active': isCurrentOption(
                            item,
                            header.key,
                            option,
                          ),
                          'inline-cell-editor__option--empty':
                            option.id === null,
                        }"
                        @click.stop="
                          onSelectEditableOption(item, header.key, option)
                        "
                      >
                        {{ option.id === null ? '—' : option.label }}
                      </li>
                    </ul>
                  </teleport>
                </template>
                <div
                  v-else-if="isEditableColumn(header.key)"
                  class="editable-cell-content"
                >
                  <span class="editable-cell-content__value">{{
                    getCellValue(item, header.key)
                  }}</span>
                  <oxd-icon
                    :name="
                      getEditableFieldType(header.key) === 'date'
                        ? 'calendar3'
                        : 'caret-down-fill'
                    "
                    class="editable-cell-content__icon"
                  />
                </div>
                <template v-else>
                  {{ getCellValue(item, header.key) }}
                </template>
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
    <view-lead
      v-if="selectedLeadId"
      :lead-id="selectedLeadId"
      :all-statuses="allStatuses"
      :all-study-levels="allStudyLevels"
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
  nextTick,
  onMounted,
  onBeforeUnmount,
  watch,
  getCurrentInstance,
} from 'vue';
import usei18n from '@/core/util/composable/usei18n';
import {
  required,
  validDateFormat,
  startDateShouldBeBeforeEndDate,
  endDateShouldBeAfterStartDate,
} from '@/core/util/validation/rules';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import {navigate} from '@/core/util/helper/navigation';
import useToast from '@/core/util/composable/useToast';
import JobAutocomplete from '@/core/components/inputs/JobAutocomplete.vue';
import {APIService} from '@/core/util/services/api.service';
import {OxdSpinner, OxdSwitchInput} from '@ohrm/oxd';
import * as XLSX from 'xlsx';
import ConfirmationDialog from '@/core/components/dialogs/ConfirmationDialog.vue';
import DateInput from '@/core/components/inputs/DateInput';
import ViewLead from '../../components/ViewLead.vue';

export default {
  components: {
    'oxd-loading-spinner': OxdSpinner,
    'job-autocomplete': JobAutocomplete,
    'confirmation-dialog': ConfirmationDialog,
    'oxd-switch-input': OxdSwitchInput,
    'date-input': DateInput,
    'view-lead': ViewLead,
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
    departmentCodes: {
      type: Array,
      default: () => [],
    },
  },
  setup(props) {
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
            matchingStatusFilter: filters.matchingStatusFilter || [],
            actorsFilter: filters.actorsFilter || [],
            departmentCodesFilter: filters.departmentCodesFilter || [],
            jobsFilter: filters.jobsFilter || [],
            courseOnly: filters.courseOnly || false,
            hideTests:
              typeof filters.hideTests === 'boolean'
                ? filters.hideTests
                : false,
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
        matchingStatusFilter: [],
        actorsFilter: [],
        departmentCodesFilter: [],
        jobsFilter: [],
        courseOnly: false,
        hideTests: false,
      };
    };

    // Fonction pour sauvegarder les filtres dans localStorage
    const saveFiltersToLocalStorage = (
      startDate,
      endDate,
      matchingStatusFilter,
      actors,
      departmentCodes,
      jobs,
      course,
      hide,
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
          departmentCodesFilter: departmentCodes,
          jobsFilter: jobs,
          courseOnly: course,
          hideTests: hide,
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
    const departmentCodesFilter = ref(loadedFilters.departmentCodesFilter);
    const jobsFilter = ref(loadedFilters.jobsFilter);
    const courseOnly = ref(loadedFilters.courseOnly);
    const hideTests = ref(loadedFilters.hideTests);
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
    const selectedLeadId = ref(null);
    const editingCell = ref(null);
    const editingDateValue = ref('');
    const selectEditorStyle = ref({});
    const leadSelectOptions = ref({
      needs: [],
      courseStarts: [],
      studyLevels: [],
      countries: [],
      fundings: [],
      handicaps: [],
      status: [],
      trainingMethods: [],
      sources: [],
      timeSlots: [],
      professionalExperiences: [],
    });
    const contactLogTypes = ref([]);
    const departmentCodeOptions = computed(() =>
      (props.departmentCodes || []).map((departmentCode, index) => ({
        id: index,
        label: departmentCode.label,
        code: departmentCode.id,
      })),
    );

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
        label: 'Relancer à partir de',
        key: 'callBackDate',
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
      civility: (cols) => !!cols.gender,
      address: (cols) => !!cols.address,
      locationPostalCodeEmail: (cols) => !!cols.postalCode,
      zipCode: (cols) => !!cols.postalCode,
      city: (cols) => !!cols.city,
      country: (cols) => !!cols.country,
      birthDate: (cols) => !!cols.birthDate,
      age: (cols) => !!cols.age,
      resume: (cols) => !!cols.resume,
      isNEET: (cols) => !!cols.isNeet,
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
      utmCampaign: (cols) => !!cols.utms,
      utmGroup: (cols) => !!cols.utms,
      utmSource: (cols) => !!cols.utms,
      source: (cols) => !!cols.source,
      timeSlotEmail: (cols) => !!cols.timeSlot,
      timeSlot: (cols) => !!cols.timeSlot,
      complement: (cols) => !!cols.complement,
      lastContact: (cols) => !!cols.lastContact,
      callBackDate: (cols) => !!cols.callBackDate,
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

    const isEditableColumn = (headerKey) =>
      Object.prototype.hasOwnProperty.call(EDITABLE_FIELDS, headerKey);

    const getEditableFieldType = (headerKey) =>
      EDITABLE_FIELDS[headerKey]?.type ?? null;

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

    const isEmptyCellValue = (value) =>
      value === null || value === undefined || value === '';

    const getEditableOptions = (headerKey) => {
      const config = EDITABLE_FIELDS[headerKey];
      if (!config || config.type !== 'select') {
        return [];
      }

      let options = [];
      if (config.getOptions) {
        options = config.getOptions();
      } else if (config.optionsKey) {
        options = sortOptionsByLabel(
          leadSelectOptions.value[config.optionsKey] || [],
        );
      }

      return [EMPTY_SELECT_OPTION, ...options];
    };

    const isCurrentOption = (item, headerKey, option) => {
      const current = getCellValue(item, headerKey);
      if (option.id === null) {
        return isEmptyCellValue(current);
      }
      return current === option.label;
    };

    const applyEditableFieldValue = (item, headerKey, value) => {
      if (headerKey === 'status') {
        item.status = value;
        if (Object.prototype.hasOwnProperty.call(item, 'currentSituation')) {
          item.currentSituation = value;
        }
        return;
      }
      item[headerKey] = value;
    };

    const closeCellEditor = () => {
      editingCell.value = null;
      editingDateValue.value = '';
      selectEditorStyle.value = {};
    };

    const commitDateEditIfNeeded = () => {
      if (
        !editingCell.value ||
        getEditableFieldType(editingCell.value.fieldKey) !== 'date'
      ) {
        return;
      }

      const {leadId, fieldKey} = editingCell.value;
      const item = tableData.value.find((row) => row.id === leadId);
      if (!item) {
        closeCellEditor();
        return;
      }

      const apiValue = editingDateValue.value ?? '';
      const currentApiValue = toNativeDateInputValue(
        getCellValue(item, fieldKey),
      );
      if (apiValue !== currentApiValue) {
        persistInlineFieldEdit(item, fieldKey, apiValue);
      }
      closeCellEditor();
    };

    const updateSelectEditorPosition = () => {
      if (
        !editingCell.value ||
        getEditableFieldType(editingCell.value.fieldKey) !== 'select'
      ) {
        return;
      }

      const cell = document.querySelector('.editing-cell--select');
      if (!cell) {
        return;
      }

      const rect = cell.getBoundingClientRect();
      const preferredMaxHeight = 240;
      const spaceBelow = window.innerHeight - rect.bottom - 8;
      const spaceAbove = rect.top - 8;
      const openUpward =
        spaceBelow < Math.min(preferredMaxHeight, 140) &&
        spaceAbove > spaceBelow;
      const maxHeight = Math.max(
        120,
        Math.min(preferredMaxHeight, openUpward ? spaceAbove : spaceBelow),
      );

      selectEditorStyle.value = {
        position: 'fixed',
        top: openUpward ? 'auto' : `${rect.bottom}px`,
        bottom: openUpward ? `${window.innerHeight - rect.top}px` : 'auto',
        left: `${rect.left}px`,
        minWidth: `${Math.max(rect.width, 140)}px`,
        maxHeight: `${maxHeight}px`,
        zIndex: 2000,
      };
    };

    const onSelectEditorReposition = () => {
      if (
        editingCell.value &&
        getEditableFieldType(editingCell.value.fieldKey) === 'select'
      ) {
        updateSelectEditorPosition();
      }
    };

    const getPreviousEditableValue = (item, headerKey) => {
      if (headerKey === 'status') {
        return item.status ?? item.currentSituation ?? null;
      }
      return item[headerKey] ?? null;
    };

    const persistInlineFieldEdit = (item, headerKey, value) => {
      const config = EDITABLE_FIELDS[headerKey];
      if (!config || typeof value !== 'string') {
        return Promise.resolve();
      }

      const previousValue = getPreviousEditableValue(item, headerKey);
      applyEditableFieldValue(item, headerKey, value);

      return http
        .request({
          method: 'PUT',
          url: `/${window.appGlobal.theme}/api/v2/admin/lead/${item.id}`,
          data: {
            apiField: config.apiField,
            value,
          },
        })
        .then(() => {
          updateSuccess();
        })
        .catch((error) => {
          applyEditableFieldValue(item, headerKey, previousValue);
          instance?.proxy?.$toast?.unexpectedError(
            error?.response?.data?.message,
          );
        });
    };

    const onCellClick = (rowIndex, colIndex, item, header) => {
      if (
        editingCell.value &&
        getEditableFieldType(editingCell.value.fieldKey) === 'date' &&
        (editingCell.value.row !== rowIndex ||
          editingCell.value.col !== colIndex)
      ) {
        commitDateEditIfNeeded();
      }

      selectCell(rowIndex, colIndex);
      if (isEditableColumn(header.key)) {
        editingCell.value = {
          row: rowIndex,
          col: colIndex,
          leadId: item.id,
          fieldKey: header.key,
        };
        editingDateValue.value =
          getEditableFieldType(header.key) === 'date'
            ? toNativeDateInputValue(getCellValue(item, header.key))
            : '';
        if (getEditableFieldType(header.key) === 'select') {
          nextTick(() => {
            updateSelectEditorPosition();
          });
        } else if (getEditableFieldType(header.key) === 'date') {
          nextTick(() => {
            const input = document.querySelector(
              '.inline-cell-editor__date-input',
            );
            if (input) {
              input.focus();
              if (typeof input.showPicker === 'function') {
                try {
                  input.showPicker();
                } catch (e) {
                  // showPicker may throw if not triggered by a user gesture
                }
              }
            }
          });
        }
        return;
      }
      closeCellEditor();
    };

    const onEditableDateInput = (event) => {
      editingDateValue.value = event.target.value ?? '';
    };

    const onSelectEditableOption = (item, headerKey, option) => {
      if (isCurrentOption(item, headerKey, option)) {
        return;
      }
      const value = option.id === null ? '' : String(option.label);
      persistInlineFieldEdit(item, headerKey, value);
      closeCellEditor();
    };

    const onDocumentClick = (event) => {
      if (!editingCell.value) {
        return;
      }
      if (event.target.closest('.inline-cell-editor')) {
        return;
      }
      if (getEditableFieldType(editingCell.value.fieldKey) === 'date') {
        // Ne pas fermer pendant l'interaction avec le date picker natif
        // (scroll d'années, etc.) : le blur/focus hors input est normal.
        // On ne commit que sur un vrai clic hors éditeur.
        commitDateEditIfNeeded();
        return;
      }
      closeCellEditor();
    };

    const totalPages = computed(() => {
      return Math.ceil(totalRecords.value / itemsPerPage);
    });

    const isARelancerSelected = computed(() => {
      const selectedMatchingStatuses = matchingStatusFilter.value || [];
      return selectedMatchingStatuses.some(
        (status) => status?.label === 'A relancer',
      );
    });

    const fetchData = async () => {
      isLoading.value = true;

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
        matchingStatuses: matchingStatusFilter.value
          ? matchingStatusFilter.value.map((status) => status.label)
          : [],
        actors: actorsFilter.value
          ? actorsFilter.value.map((actor) => actor.label)
          : [],
        departmentCodes: departmentCodesFilter.value
          ? departmentCodesFilter.value.map(
              (departmentCode) => departmentCode.code,
            )
          : [],
        jobs: jobsFilter.value ? jobsFilter.value.map((job) => job.label) : [],
        courseOnly: courseOnly.value,
        hideTests: hideTests.value,
      };

      const leadsPromise = http.getAll(params).then((response) => {
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
      });

      const reportingColumnsPromise = (() => {
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
        return Promise.resolve();
      })();

      return Promise.all([leadsPromise, reportingColumnsPromise]).finally(
        () => {
          isLoading.value = false;
        },
      );
    };

    const filterItems = () => {
      currentPage.value = 1;
      // Sauvegarder les filtres avant de filtrer
      saveFiltersToLocalStorage(
        startDateFilter.value,
        endDateFilter.value,
        matchingStatusFilter.value,
        actorsFilter.value,
        departmentCodesFilter.value,
        jobsFilter.value,
        courseOnly.value,
        hideTests.value,
      );
      fetchData();
    };

    const onClickReset = () => {
      const defaultStartDate = new Date();
      defaultStartDate.setDate(defaultStartDate.getDate() - 2);
      const defaultEndDate = new Date();
      startDateFilter.value = formatDate(defaultStartDate, userDateFormat);
      endDateFilter.value = formatDate(defaultEndDate, userDateFormat);
      matchingStatusFilter.value = [];
      actorsFilter.value = [];
      departmentCodesFilter.value = [];
      jobsFilter.value = [];
      courseOnly.value = false;
      hideTests.value = false;
      currentPage.value = 1;
      // Sauvegarder les filtres réinitialisés
      saveFiltersToLocalStorage(
        startDateFilter.value,
        endDateFilter.value,
        matchingStatusFilter.value,
        actorsFilter.value,
        departmentCodesFilter.value,
        jobsFilter.value,
        courseOnly.value,
        hideTests.value,
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
        departmentCodesFilter.value,
        jobsFilter.value,
        courseOnly.value,
        hideTests.value,
      );
    };

    // Watch pour sauvegarder automatiquement les changements de filtres
    watch(
      [
        startDateFilter,
        endDateFilter,
        matchingStatusFilter,
        actorsFilter,
        departmentCodesFilter,
        jobsFilter,
        courseOnly,
        hideTests,
      ],
      () => {
        saveFiltersToLocalStorage(
          startDateFilter.value,
          endDateFilter.value,
          matchingStatusFilter.value,
          actorsFilter.value,
          departmentCodesFilter.value,
          jobsFilter.value,
          courseOnly.value,
          hideTests.value,
        );
      },
    );

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
      window.addEventListener('resize', onSelectEditorReposition);
      window.addEventListener('scroll', onSelectEditorReposition, true);
      openLeadFromUrl();
      // Restaurer les jobs dans jobAutocomplete si nécessaire
      if (jobAutocomplete.value && jobsFilter.value.length > 0) {
        // Le jobAutocomplete devrait se mettre à jour automatiquement via v-model
        // mais on peut forcer une mise à jour si nécessaire
      }
      fetchData();
      http
        .request({
          method: 'GET',
          url: `/${window.appGlobal.theme}/api/v2/admin/leads/global-options`,
        })
        .then(({data}) => {
          allStatuses.value = data.allStatuses || [];
          allStudyLevels.value = data.allStudyLevels || [];
          contactLogTypes.value = data.contactLogTypes || [];
        });
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', onDocumentClick);
      window.removeEventListener('resize', onSelectEditorReposition);
      window.removeEventListener('scroll', onSelectEditorReposition, true);
    });

    return {
      http,
      jobAutocomplete,
      startDateFilter,
      endDateFilter,
      matchingStatusFilter,
      isARelancerSelected,
      actorsFilter,
      departmentCodesFilter,
      jobsFilter,
      courseOnly,
      hideTests,
      tableData,
      tableHeaders,
      totalRecords,
      currentPage,
      totalPages,
      selectedCell,
      selectedRow,
      selectedLeadId,
      editingCell,
      editingDateValue,
      selectEditorStyle,
      leadSelectOptions,
      contactLogTypes,
      filterItems,
      onClickReset,
      selectCell,
      onCellClick,
      isEditableColumn,
      getEditableFieldType,
      isEditingCell,
      getEditableOptions,
      onEditableDateInput,
      commitDateEditIfNeeded,
      closeCellEditor,
      isCurrentOption,
      onSelectEditableOption,
      getCellValue,
      rules,
      departmentCodeOptions,
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
      this.selectedLeadId = leadId;
    },
    openLeadInFullPage() {
      const leadId = this.selectedLeadId;
      if (leadId == null) {
        return;
      }
      this.saveFiltersToLocalStorage(
        this.startDateFilter,
        this.endDateFilter,
        this.matchingStatusFilter,
        this.actorsFilter,
        this.departmentCodesFilter,
        this.jobsFilter,
        this.courseOnly,
        this.hideTests,
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
      &.editable-cell {
        cursor: pointer;
      }
      &.editing-cell {
        z-index: 10;

        &--select {
          vertical-align: middle;
          overflow: visible;
        }

        &--date {
          padding: 0;
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

.editable-cell-content {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  max-width: 100%;

  &__value {
    min-width: 0;
  }

  &__icon {
    flex-shrink: 0;
    font-size: 11px;
    color: var(--oxd-primary-one-color);
    opacity: 0.75;
  }
}

.inline-cell-editor {
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

  &--select-dropdown {
    list-style: none;
    margin: 0;
    padding: 0.25rem 0;
    overflow-x: hidden;
    overflow-y: auto;
    box-sizing: border-box;
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

.inline-cell-editor__option {
  padding: 0.5rem 1rem;
  cursor: pointer;
  white-space: nowrap;
  font-family: 'Nunito Sans', sans-serif;
  font-size: 12px;
  color: #64728c;

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
