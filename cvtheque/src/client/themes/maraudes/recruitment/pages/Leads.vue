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
        <div class="leads-header-row leads-header-row--actions">
          <div v-if="!showContactAddMenu" class="leads-contact-actions">
            <oxd-button
              display-type="secondary"
              label="Ajouter un contact"
              icon-name="plus"
              @click="showContactAddMenu = true"
            />
          </div>
          <div v-else class="leads-contact-actions">
            <oxd-button
              display-type="secondary"
              label="Individuellement"
              @click="openCreateContact"
            />
            <oxd-button
              display-type="ghost"
              label="En masse (Excel)"
              @click="openMassiveImportModal"
            />
            <oxd-icon-button
              name="x-lg"
              title="Annuler"
              @click="showContactAddMenu = false"
            />
          </div>
        </div>
        <div class="leads-header-row leads-header-row--tools">
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
                  v-if="
                    isEditingCell(index, headerIndex) &&
                    getEditableFieldType(header) === 'date'
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
                    getEditableFieldType(header) === 'select'
                  "
                >
                  <div class="editable-cell-content">
                    <span class="editable-cell-content__value">{{
                      getCellValue(item, header.key, header)
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
                        v-for="option in getEditableOptions(header)"
                        :key="`${header.customColumnId}-${
                          option.id ?? 'empty'
                        }`"
                        :class="{
                          'inline-cell-editor__option': true,
                          'inline-cell-editor__option--active': isCurrentOption(
                            item,
                            header,
                            option,
                          ),
                          'inline-cell-editor__option--empty':
                            option.id === null,
                        }"
                        @click.stop="
                          onSelectEditableOption(item, header, option)
                        "
                      >
                        {{ option.id === null ? '—' : option.label }}
                      </li>
                    </ul>
                  </teleport>
                </template>
                <div
                  v-else-if="isEditableColumn(header)"
                  class="editable-cell-content"
                >
                  <span class="editable-cell-content__value">{{
                    getCellValue(item, header.key, header)
                  }}</span>
                  <oxd-icon
                    :name="
                      getEditableFieldType(header) === 'date'
                        ? 'calendar3'
                        : 'caret-down-fill'
                    "
                    class="editable-cell-content__icon"
                  />
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
    <div
      v-if="showMassiveImportModal"
      class="modal-overlay"
      @click="closeMassiveImportModal"
    >
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <oxd-icon-button
            v-if="massiveImportStep === 'upload' && !isUploadingMassiveImport"
            name="chevron-left"
            title="Revenir aux explications"
            class="modal-header__back"
            @click="backToMassiveImportIntro"
          />
          <h3>
            {{
              massiveImportStep === 'upload'
                ? 'Charger vos nouveaux contacts'
                : 'Ajouter des contacts en masse'
            }}
          </h3>
        </div>

        <template v-if="massiveImportStep === 'intro'">
          <div class="modal-body">
            <oxd-text tag="p" class="massive-import-text">
              L'ajout en masse se fait à partir d'un fichier Excel
              (<b>.xlsx</b>). Le format de votre fichier doit correspondre
              <b>exactement</b> à celui du modèle indiqué ci-dessous : mêmes
              colonnes, sans en ajouter ni en retirer, avec 1 contact par ligne.
            </oxd-text>
            <oxd-text tag="p" class="massive-import-text">
              Le modèle se télécharge sous forme d'archive contenant deux
              fichiers :
            </oxd-text>
            <ul class="massive-import-list">
              <li>
                un <b>fichier Excel</b> dont les colonnes sont déjà
                pré-remplies, qu'il vous suffit de compléter avec vos contacts ;
              </li>
              <li>
                un <b>fichier PDF</b> qui détaille, pour chacune de ces
                colonnes, les options que vous pouvez y renseigner.
              </li>
            </ul>
          </div>
          <div class="modal-footer">
            <oxd-button
              display-type="ghost"
              icon-name="download"
              :label="
                isDownloadingMassiveImport
                  ? 'Téléchargement...'
                  : 'Télécharger le modèle'
              "
              :disabled="isDownloadingMassiveImport"
              type="button"
              @click="downloadMassiveImportDocumentation"
            />
            <oxd-button
              display-type="secondary"
              label="Charger les nouveaux contacts"
              type="button"
              @click="goToMassiveImportUpload"
            />
          </div>
        </template>

        <template v-else>
          <div class="modal-body">
            <div
              v-if="isUploadingMassiveImport"
              class="massive-import-uploading"
            >
              <oxd-loading-spinner />
              <oxd-text tag="p" class="massive-import-text">
                Import de vos contacts en cours, merci de ne pas fermer cette
                fenêtre...
              </oxd-text>
            </div>
            <template v-else>
              <oxd-text tag="p" class="massive-import-text">
                Sélectionnez le fichier Excel (<b>.xlsx</b>) que vous avez
                complété à partir du modèle. Ses colonnes doivent être restées
                identiques à celles du modèle, sinon l'import sera refusé.
              </oxd-text>
              <div
                class="massive-import-dropzone"
                :class="{
                  '--dragging': isDraggingMassiveImportFile,
                  '--filled': massiveImportFile !== null,
                  '--error': !!massiveImportError,
                }"
                @click="openMassiveImportFilePicker"
                @dragover.prevent="isDraggingMassiveImportFile = true"
                @dragleave.prevent="isDraggingMassiveImportFile = false"
                @drop.prevent="onMassiveImportFileDropped"
              >
                <template v-if="massiveImportFile">
                  <oxd-icon
                    name="file-earmark-excel"
                    class="massive-import-dropzone__icon"
                  />
                  <div class="massive-import-dropzone__details">
                    <oxd-text tag="p" class="massive-import-dropzone__name">
                      {{ massiveImportFile.name }}
                    </oxd-text>
                    <oxd-text tag="p" class="massive-import-dropzone__hint">
                      {{ massiveImportFileSize }} — cliquez pour choisir un
                      autre fichier
                    </oxd-text>
                  </div>
                  <oxd-icon-button
                    name="x-lg"
                    title="Retirer ce fichier"
                    @click.stop="clearMassiveImportFile"
                  />
                </template>
                <template v-else>
                  <oxd-icon
                    name="upload"
                    class="massive-import-dropzone__icon"
                  />
                  <div class="massive-import-dropzone__details">
                    <oxd-text tag="p" class="massive-import-dropzone__name">
                      Cliquez pour choisir un fichier
                    </oxd-text>
                    <oxd-text tag="p" class="massive-import-dropzone__hint">
                      ou glissez-le directement dans cette zone
                    </oxd-text>
                  </div>
                </template>
              </div>
              <div v-if="massiveImportError" class="massive-import-error">
                <oxd-icon
                  name="exclamation-triangle-fill"
                  class="massive-import-error__icon"
                />
                <div class="massive-import-error__content">
                  <oxd-text tag="p" class="massive-import-error__title">
                    Import impossible
                  </oxd-text>
                  <oxd-text tag="p" class="massive-import-error__message">
                    {{ massiveImportError }}
                  </oxd-text>
                </div>
              </div>
            </template>
          </div>
          <div class="modal-footer">
            <oxd-button
              display-type="ghost"
              label="Annuler"
              type="button"
              :disabled="isUploadingMassiveImport"
              @click="closeMassiveImportModal"
            />
            <oxd-button
              display-type="secondary"
              label="Envoyer le fichier"
              type="button"
              :disabled="massiveImportFile === null"
              :loading="isUploadingMassiveImport"
              @click="submitMassiveImportFile"
            />
          </div>
        </template>
      </div>
    </div>
    <input
      ref="massiveImportFileInput"
      type="file"
      accept=".xlsx"
      class="massive-import-file-input"
      @change="onMassiveImportFileSelected"
    />
    <create-contact
      v-if="showCreateContact"
      :default-columns="reportingDefaultColumns"
      :custom-columns="customColumns"
      :contact-log-types="contactLogTypes"
      :scope-options="scopeOptions"
      :lead-select-options="leadSelectOptions"
      :of-options="ofOptions"
      @close="showCreateContact = false"
      @created="onContactCreated"
    />
    <view-lead
      v-if="selectedLeadId"
      :lead-id="selectedLeadId"
      :default-columns="reportingDefaultColumns"
      :custom-columns="customColumns"
      :contact-log-types="contactLogTypes"
      :scope-options="scopeOptions"
      @close="selectedLeadId = null"
      @open-full-page="openLeadInFullPage"
      @update="onViewLeadUpdate"
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
import CreateContact from '../components/CreateContact.vue';

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
    'create-contact': CreateContact,
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
    scopeOptions: {
      type: Array,
      default: () => [],
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
    const {
      noRecordsFound,
      updateSuccess,
      error: toastError,
      success: toastSuccess,
      clearAll: clearAllToasts,
    } = useToast();
    const totalRecords = ref(0);
    const itemsPerPage = 50;
    const currentPage = ref(1);
    const selectedCell = ref({row: null, col: null});
    const selectedRow = ref(null);
    const selectedLeadId = ref(null);
    const showContactAddMenu = ref(false);
    const showCreateContact = ref(false);
    const showMassiveImportModal = ref(false);
    const massiveImportStep = ref('intro');
    const isDownloadingMassiveImport = ref(false);
    const isUploadingMassiveImport = ref(false);
    const isDraggingMassiveImportFile = ref(false);
    const massiveImportFile = ref(null);
    const massiveImportFileInput = ref(null);
    const massiveImportError = ref('');
    const editingCell = ref(null);
    const editingDateValue = ref('');
    const selectEditorStyle = ref({});
    const contactLogTypes = ref([]);
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
    const ofOptions = ref([]);
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
    http.setIgnorePath('api/v2/admin/leads/massive-import');

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

    const asLabel = (value) =>
      value && typeof value === 'object' ? value.label ?? null : value;

    const mapProfileToTableRow = (profile, existingLead) => {
      const row = {
        civility: asLabel(profile.civility),
        firstName: profile.firstName,
        lastName: profile.lastName,
        email: profile.email,
        phoneNumberEmail: profile.phoneNumber,
        address: profile.address,
        locationPostalCodeEmail: profile.postalCode,
        city: profile.city,
        country: asLabel(profile.country),
        birthDate: profile.birthDate,
        age: profile.age,
        jobs: Array.isArray(profile.jobs)
          ? profile.jobs.filter(Boolean).join(' - ')
          : profile.jobs,
        sector: profile.sector,
        course: profile.course,
        of: profile.of,
        status: asLabel(profile.currentSituation) ?? profile.status,
        studyLevel: asLabel(profile.studyLevel),
        trainingMethod: asLabel(profile.trainingMethod),
        need: asLabel(profile.need),
        handicap: asLabel(profile.handicap),
        courseStart: asLabel(profile.courseStart),
        funding: asLabel(profile.funding),
        source: asLabel(profile.source),
        timeSlot: asLabel(profile.timeSlot),
        complement: profile.complement,
        callBackDate: profile.callBackDate,
      };

      if (Array.isArray(profile.customColumns)) {
        const existingCustomColumns = existingLead?.customColumns || [];
        row.customColumns = existingCustomColumns.map((column) => {
          const updated = profile.customColumns.find(
            (item) => Number(item.id) === Number(column.id),
          );
          return updated ? {...column, value: updated.value} : column;
        });
      }

      return row;
    };

    const refreshTablePage = () => {
      const source = leads.value || [];
      const start = (currentPage.value - 1) * itemsPerPage;
      tableData.value = source.slice(start, start + itemsPerPage);
    };

    const onViewLeadUpdate = (updatedLead) => {
      if (updatedLead?.id == null || !Array.isArray(leads.value)) {
        return;
      }
      const leadId = Number(updatedLead.id);
      const index = leads.value.findIndex((item) => Number(item.id) === leadId);
      if (index === -1) {
        return;
      }
      const existingLead = leads.value[index];
      const nextLeads = [...leads.value];
      nextLeads[index] = {
        ...existingLead,
        ...mapProfileToTableRow(updatedLead, existingLead),
      };
      leads.value = nextLeads;
      refreshTablePage();
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
      selectEditorStyle.value = {};
    };

    const commitDateEditIfNeeded = () => {
      if (
        !editingCell.value ||
        getEditableFieldType(editingCell.value.header) !== 'date'
      ) {
        return;
      }

      const {leadId, header} = editingCell.value;
      const item = tableData.value.find((row) => row.id === leadId);
      if (!item) {
        closeCellEditor();
        return;
      }

      const apiValue = editingDateValue.value ?? '';
      const currentApiValue = toNativeDateInputValue(
        getRawCustomColumnValue(item, header),
      );
      if (apiValue !== currentApiValue) {
        persistInlineCustomColumnEdit(item, header, apiValue || null);
      }
      closeCellEditor();
    };

    const updateSelectEditorPosition = () => {
      if (
        !editingCell.value ||
        getEditableFieldType(editingCell.value.header) !== 'select'
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
        getEditableFieldType(editingCell.value.header) === 'select'
      ) {
        updateSelectEditorPosition();
      }
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
      if (
        editingCell.value &&
        getEditableFieldType(editingCell.value.header) === 'date' &&
        (editingCell.value.row !== rowIndex ||
          editingCell.value.col !== colIndex)
      ) {
        commitDateEditIfNeeded();
      }

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
        if (getEditableFieldType(header) === 'select') {
          nextTick(() => {
            updateSelectEditorPosition();
          });
        } else if (getEditableFieldType(header) === 'date') {
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
      if (getEditableFieldType(editingCell.value.header) === 'date') {
        commitDateEditIfNeeded();
        return;
      }
      closeCellEditor();
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

    const openCreateContact = () => {
      showContactAddMenu.value = false;
      showCreateContact.value = true;
    };

    const onContactCreated = () => {
      fetchData();
    };

    const massiveImportFileSize = computed(() => {
      const size = massiveImportFile.value?.size ?? 0;
      return size < 1024 * 1024
        ? `${Math.max(1, Math.round(size / 1024))} Ko`
        : `${(size / (1024 * 1024)).toFixed(1)} Mo`;
    });

    const clearMassiveImportFile = () => {
      massiveImportFile.value = null;
      isDraggingMassiveImportFile.value = false;
      massiveImportError.value = '';
    };

    const openMassiveImportModal = () => {
      showContactAddMenu.value = false;
      massiveImportStep.value = 'intro';
      clearMassiveImportFile();
      massiveImportError.value = '';
      showMassiveImportModal.value = true;
    };

    const closeMassiveImportModal = () => {
      if (isDownloadingMassiveImport.value || isUploadingMassiveImport.value) {
        return;
      }
      showMassiveImportModal.value = false;
      massiveImportError.value = '';
    };

    const goToMassiveImportUpload = () => {
      massiveImportError.value = '';
      massiveImportStep.value = 'upload';
    };

    const backToMassiveImportIntro = () => {
      if (isUploadingMassiveImport.value) return;
      massiveImportError.value = '';
      massiveImportStep.value = 'intro';
    };

    const openMassiveImportFilePicker = () => {
      massiveImportFileInput.value?.click();
    };

    const setMassiveImportFile = (file) => {
      isDraggingMassiveImportFile.value = false;
      if (!file) return;
      if (!file.name.toLowerCase().endsWith('.xlsx')) {
        massiveImportError.value =
          'Seuls les fichiers Excel au format .xlsx sont acceptés.';
        return;
      }
      massiveImportError.value = '';
      massiveImportFile.value = file;
    };

    const onMassiveImportFileSelected = (event) => {
      const file = event.target.files?.[0];
      // Permet de re-sélectionner le même fichier après l'avoir retiré.
      event.target.value = '';
      setMassiveImportFile(file);
    };

    const onMassiveImportFileDropped = (event) => {
      setMassiveImportFile(event.dataTransfer?.files?.[0]);
    };

    const submitMassiveImportFile = () => {
      if (!massiveImportFile.value || isUploadingMassiveImport.value) return;
      isUploadingMassiveImport.value = true;
      massiveImportError.value = '';

      const formData = new FormData();
      formData.append('file', massiveImportFile.value);

      http
        .request({
          method: 'POST',
          url: '/api/v2/admin/leads/massive-import',
          data: formData,
          // Laisse axios positionner le boundary multipart.
          headers: {},
        })
        .then(() => {
          showMassiveImportModal.value = false;
          clearMassiveImportFile();
          toastSuccess({
            title: 'Import terminé',
            message: 'Vos contacts ont bien été importés.',
          });
          fetchData();
        })
        .catch((err) => {
          // Avec setIgnorePath, l'intercepteur rejecte parfois
          // directement la response (err.data) plutôt qu'AxiosError.
          massiveImportError.value =
            err?.data?.message ||
            err?.response?.data?.message ||
            "Le fichier n'a pas pu être importé. Vérifiez qu'il respecte bien le modèle.";
          clearAllToasts();
          toastError({
            title: 'Import impossible',
            message:
              "Une erreur est survenue lors de l'import. Consultez le détail dans la fenêtre.",
          });
        })
        .finally(() => {
          isUploadingMassiveImport.value = false;
        });
    };

    const downloadMassiveImportDocumentation = () => {
      if (isDownloadingMassiveImport.value) return;
      isDownloadingMassiveImport.value = true;
      http
        .request({
          method: 'GET',
          url: '/api/v2/admin/leads/documentation/massive-import',
          responseType: 'blob',
        })
        .then(({data, headers}) => {
          const contentType = headers?.['content-type'] || 'application/zip';
          const disposition = headers?.['content-disposition'] || '';
          const match = disposition.match(/filename="?([^";]+)"?/);
          const fileName = match ? match[1] : 'import-en-masse.zip';

          const objectUrl = URL.createObjectURL(
            new Blob([data], {type: contentType}),
          );
          const link = document.createElement('a');
          link.href = objectUrl;
          link.download = fileName;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          setTimeout(() => URL.revokeObjectURL(objectUrl), 60000);
        })
        .catch(() => {
          toastError({
            title: 'Erreur',
            message:
              "Impossible de télécharger le modèle d'import en masse. Veuillez réessayer.",
          });
        })
        .finally(() => {
          isDownloadingMassiveImport.value = false;
        });
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
      window.addEventListener('resize', onSelectEditorReposition);
      window.addEventListener('scroll', onSelectEditorReposition, true);
      openLeadFromUrl();
      fetchData();
      http
        .request({
          method: 'GET',
          url: '/api/v2/admin/leads/global-options',
        })
        .then(({data}) => {
          leadSelectOptions.value = {
            needs: data.needs || [],
            courseStarts: data.courseStarts || [],
            studyLevels: data.studyLevels || [],
            countries: data.countries || [],
            fundings: data.fundings || [],
            handicaps: data.handicaps || [],
            status: data.status || [],
            trainingMethods: data.trainingMethods || [],
            sources: data.sources || [],
            timeSlots: data.timeSlots || [],
            professionalExperiences: data.professionalExperiences || [],
          };
          contactLogTypes.value = data.contactLogTypes || [];
        });
      http
        .request({
          method: 'GET',
          url: '/api/v2/admin/of/actor',
        })
        .then(({data}) => {
          ofOptions.value = (data?.data || []).map((of) => ({
            id: of.id,
            label: of.name,
          }));
        })
        .catch(() => {
          ofOptions.value = [];
        });
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', onDocumentClick);
      window.removeEventListener('resize', onSelectEditorReposition);
      window.removeEventListener('scroll', onSelectEditorReposition, true);
    });

    return {
      http,
      reportingDefaultColumns,
      contactStatusOptions,
      showContactStatusFilter,
      contactStatusFilter,
      contactLogTypes,
      leadSelectOptions,
      ofOptions,
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
      showContactAddMenu,
      showCreateContact,
      openCreateContact,
      onContactCreated,
      showMassiveImportModal,
      massiveImportStep,
      isDownloadingMassiveImport,
      isUploadingMassiveImport,
      isDraggingMassiveImportFile,
      massiveImportFile,
      massiveImportFileInput,
      massiveImportFileSize,
      massiveImportError,
      openMassiveImportModal,
      closeMassiveImportModal,
      goToMassiveImportUpload,
      backToMassiveImportIntro,
      openMassiveImportFilePicker,
      onMassiveImportFileSelected,
      onMassiveImportFileDropped,
      clearMassiveImportFile,
      submitMassiveImportFile,
      downloadMassiveImportDocumentation,
      editingCell,
      editingDateValue,
      selectEditorStyle,
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
      isLoading,
      exportToExcel,
      fetchData,
      saveFiltersToLocalStorage,
      onViewLeadUpdate,
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
  flex-direction: column;
  align-items: stretch;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  width: 100%;
  box-sizing: border-box;
}

.leads-header-row--actions {
  display: flex;
  justify-content: flex-start;
  width: 100%;
}

.leads-header-row--tools {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  width: 100%;
}

.leads-contact-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .leads-header-row--tools {
    flex-direction: column;
    align-items: stretch;
  }

  .leads-header-row--actions {
    justify-content: center;
  }

  .orangehrm-header-left {
    flex-direction: column;
    align-items: stretch;
  }

  .orangehrm-pagination-wrapper {
    justify-content: center;
  }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.modal-container {
  background-color: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
  width: 560px;
  max-width: 90%;
  z-index: 1001;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--oxd-border-light-color);
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 0.75rem 0.75rem 0 0;

  h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
    font-family: Nunito Sans, sans-serif;
  }

  &__back {
    flex-shrink: 0;
    margin-left: -0.5rem;
  }
}

.modal-body {
  padding: 2rem;
  background-color: #ffffff;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--oxd-border-light-color);
  background-color: #f8f9fa;
  border-radius: 0 0 0.75rem 0.75rem;
}

.massive-import-text {
  margin: 0 0 0.75rem;
  font-size: 0.85rem;
  line-height: 1.5;
  color: #495057;
}

.massive-import-list {
  margin: 0;
  padding-left: 1.25rem;
  font-size: 0.85rem;
  line-height: 1.5;
  color: #495057;

  li + li {
    margin-top: 0.5rem;
  }
}

.massive-import-file-input {
  display: none;
}

.massive-import-dropzone {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1.25rem;
  padding: 1.25rem;
  border: 2px dashed #ced4da;
  border-radius: 0.75rem;
  background-color: #f8f9fa;
  cursor: pointer;
  transition: border-color 0.2s ease, background-color 0.2s ease;

  &:hover,
  &.--dragging {
    border-color: var(--oxd-primary-one-color, #ff7b00);
    background-color: #fff8f1;
  }

  &.--filled {
    border-style: solid;
    border-color: #ced4da;
    background-color: #ffffff;
  }

  &.--error {
    border-color: #dd2735;
  }

  &__icon {
    flex-shrink: 0;
    font-size: 1.75rem;
    color: #6c757d;
  }

  &__details {
    flex: 1;
    min-width: 0;
  }

  &__name {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__hint {
    margin: 0.15rem 0 0;
    font-size: 0.75rem;
    color: #6c757d;
  }
}

.massive-import-error {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-top: 1rem;
  padding: 0.85rem 1rem;
  border: 1px solid #f5c2c7;
  border-radius: 0.5rem;
  background-color: #f8d7da;
  max-height: 8.5rem;
  overflow-y: auto;

  &__icon {
    flex-shrink: 0;
    margin-top: 0.1rem;
    font-size: 1rem;
    color: #842029;
  }

  &__content {
    flex: 1;
    min-width: 0;
  }

  &__title {
    margin: 0 0 0.25rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: #842029;
  }

  &__message {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.45;
    color: #842029;
    white-space: pre-wrap;
    word-break: break-word;
  }
}

.massive-import-uploading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 2rem 0;
  text-align: center;
}

.orangehrm-header-left {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 1rem;
  flex: 1 1 auto;
  min-width: 0;
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
  justify-content: flex-end;
  flex: 0 0 auto;
  margin-left: auto;
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
