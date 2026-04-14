<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-paper-container">
      <div class="orangehrm-left-header-container">
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <div class="orangehrm-container">
        <oxd-card-table
          v-model:selected="checkedItems"
          v-model:order="sortDefinition"
          :headers="headers"
          :items="items"
          :selectable="false"
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

    <!-- Modal pour ajouter/modifier une colonne personnalisée -->
    <div v-if="isModalOpen" class="modal-overlay" @click="onClickCancel">
      <div class="modal-container" @click.stop>
        <oxd-form
          :loading="isSaving"
          class="modal-form"
          @submit-valid="onClickValidate"
        >
          <div class="modal-header">
            <h3>
              {{
                isEditing
                  ? $t('Modifier une colonne personnalisée')
                  : $t('Ajouter une colonne personnalisée')
              }}
            </h3>
            <span class="close-icon" @click="onClickCancel">&times;</span>
          </div>
          <div class="modal-body">
            <oxd-form-row>
              <oxd-grid :cols="1">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="columnTitle"
                    :placeholder="$t('Titre de la colonne')"
                    :label="$t('Titre')"
                    required
                    :rules="rules.title"
                    :disabled="isEditing"
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="columnType"
                    type="select"
                    :label="$t('Type')"
                    :options="typeOptionsFormatted"
                    required
                    :rules="rules.type"
                    :disabled="isEditing"
                  />
                </oxd-grid-item>
                <oxd-grid-item v-if="!selectedTypeIsNumber">
                  <div class="orangehrm-switch-wrapper">
                    <oxd-text class="oxd-label">
                      {{ $t('Afficher dans les filtres ?') }}
                    </oxd-text>
                    <oxd-switch-input v-model="hasFilter" />
                  </div>
                </oxd-grid-item>
                <oxd-grid-item>
                  <div class="orangehrm-switch-wrapper">
                    <div class="external-set-label-row">
                      <oxd-text class="oxd-label">
                        {{ $t('Autoriser paramètre externe') }}
                      </oxd-text>
                      <div class="external-set-help">
                        <button
                          type="button"
                          class="external-set-help-icon"
                          aria-label="Aide paramètre externe"
                        >
                          ?
                        </button>
                        <span class="external-set-help-tooltip">
                          {{
                            $t(
                              'determine si le champs est définissable par les accès API externes',
                            )
                          }}
                        </span>
                      </div>
                    </div>
                    <oxd-switch-input v-model="allowExternalSet" />
                  </div>
                </oxd-grid-item>
                <oxd-grid-item v-if="selectedTypeIsSelect">
                  <oxd-text class="orangehrm-text">
                    {{ $t('Options') }}
                  </oxd-text>
                  <div class="options-list">
                    <div
                      v-for="(option, index) in selectOptions"
                      :key="index"
                      class="option-item"
                    >
                      <oxd-input-field
                        v-model="selectOptions[index]"
                        :label="$t('Option ' + (index + 1))"
                        :rules="rules.option"
                        :disabled="isEditing"
                      />
                      <oxd-icon-button
                        name="trash"
                        display-type="danger"
                        class="option-delete-button"
                        :disabled="isEditing"
                        @click="removeOption(index)"
                      />
                    </div>
                    <oxd-button
                      :label="$t('Ajouter une option')"
                      icon-name="plus"
                      display-type="secondary"
                      :disabled="isEditing"
                      @click="addOption"
                    />
                  </div>
                </oxd-grid-item>
              </oxd-grid>
            </oxd-form-row>
          </div>
          <div class="modal-footer">
            <oxd-button
              :label="$t('Annuler')"
              display-type="ghost"
              @click="onClickCancel"
            />
            <oxd-button
              :label="$t('Enregistrer')"
              display-type="secondary"
              :loading="isSaving"
              type="submit"
            />
          </div>
        </oxd-form>
      </div>
    </div>
  </div>
</template>

<script>
import {reactive, toRefs, computed} from 'vue';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {APIService} from '@/core/util/services/api.service';
import useSort from '@/core/util/composable/useSort';
import useToast from '@/core/util/composable/useToast';
import {
  required,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';
import {OxdSwitchInput} from '@ohrm/oxd';

const defaultSortOrder = {
  title: 'ASC',
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'oxd-switch-input': OxdSwitchInput,
  },
  props: {
    typeOptions: {
      type: Array,
      required: true,
      default: () => [],
    },
    apiAccessLimit: {
      type: Number,
      default: 0,
    },
  },

  setup(props) {
    const {noRecordsFound, error, success} = useToast();
    const {sortDefinition, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/admin/custom-column`,
    );
    const state = reactive({
      items: [],
      total: 0,
      isLoading: false,
      isModalOpen: false,
      isEditing: false,
      isSaving: false,
      columnTitle: '',
      columnType: null,
      selectOptions: [],
      hasFilter: false,
      allowExternalSet: false,
      editingItem: null,
    });

    const fetchCustomColumnData = () => {
      state.isLoading = true;
      http
        .getAll()
        .then((response) => {
          state.items = response.data.map((item) => {
            // Parser les options si elles existent
            let optionsDisplay = '';
            if (item.options && item.options.trim() !== '') {
              try {
                const parsedOptions = JSON.parse(item.options);
                if (Array.isArray(parsedOptions)) {
                  optionsDisplay = parsedOptions.join(' | ');
                }
              } catch (e) {
                console.error('Error parsing options:', e);
              }
            }

            // Déterminer si la colonne est de type "Nombre"
            const numberType = props.typeOptions
              ? props.typeOptions.find((type) => type.label === 'Nombre')
              : null;
            const isNumberType =
              numberType && String(numberType.id) === String(item.typeOrdinal);

            // Valeur affichée pour la colonne "Filtre"
            let filterDisplay = '';
            if (
              !isNumberType &&
              item.hasFilter !== null &&
              typeof item.hasFilter !== 'undefined'
            ) {
              filterDisplay = item.hasFilter ? 'Oui' : 'Non';
            }

            const allowExternalSet =
              typeof item.allowExternalSet === 'boolean'
                ? item.allowExternalSet
                : false;

            return {
              id: item.id,
              title: item.title,
              type: item.type,
              typeOrdinal: item.typeOrdinal,
              options: optionsDisplay,
              rawOptions: item.options,
              hasFilter: item.hasFilter ?? false,
              filter: filterDisplay,
              allowExternalSet,
              externalSet: allowExternalSet ? 'Oui' : 'Non',
            };
          });
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: 'Impossible de récupérer les colonnes personnalisées',
          });
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    const onClickValidate = () => {
      if (!state.columnTitle || state.columnTitle.length < 2) {
        return error({
          title: 'Erreur de validation',
          message: 'Le titre doit contenir au moins 2 caractères',
        });
      }

      if (!state.columnType) {
        return error({
          title: 'Erreur de validation',
          message: 'Le type est requis',
        });
      }

      // Vérifier si le titre existe déjà (sauf si on est en mode édition du même item)
      const existingItem = state.items.find(
        (item) => item.title === state.columnTitle,
      );
      if (
        existingItem &&
        (!state.isEditing || existingItem.id !== state.editingItem?.id)
      ) {
        return error({
          title: 'Conflit',
          message: 'Cette colonne personnalisée existe déjà',
        });
      }

      // Préparer les options pour le type SELECT
      let optionsString = null;
      const selectedTypeOption = props.typeOptions.find(
        (type) => type.id === state.columnType.id,
      );
      if (selectedTypeOption && selectedTypeOption.label === 'Choix multiple') {
        // Filtrer les options vides et créer le JSON string
        const validOptions = state.selectOptions.filter(
          (opt) => opt && opt.trim() !== '',
        );
        // Vérifier qu'il y a au moins une option valide
        if (validOptions.length === 0) {
          return error({
            title: 'Erreur de validation',
            message: 'Au moins une option est requise',
          });
        }
        optionsString = JSON.stringify(validOptions);
      }

      state.isSaving = true;

      let requestPromise;

      if (state.isEditing && state.editingItem) {
        requestPromise = http.update(state.editingItem.id, {
          hasFilter: state.hasFilter,
          allowExternalSet: state.allowExternalSet,
        });
      } else {
        const payload = {
          title: state.columnTitle,
          typeOrdinal: state.columnType.id,
          options: optionsString,
          hasFilter: state.hasFilter,
          allowExternalSet: state.allowExternalSet,
        };
        requestPromise = http.create(payload);
      }

      requestPromise
        .then(() => {
          success({
            title: 'Succès',
            message: state.isEditing
              ? 'Colonne personnalisée mise à jour avec succès'
              : 'Colonne personnalisée ajoutée avec succès',
          });
          resetForm();
          fetchCustomColumnData();
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: "Impossible d'ajouter la colonne personnalisée",
          });
        })
        .finally(() => {
          state.isSaving = false;
        });
    };

    const resetForm = () => {
      state.columnTitle = '';
      state.columnType = null;
      state.selectOptions = [];
      state.hasFilter = false;
      state.allowExternalSet = false;
      state.isModalOpen = false;
      state.isEditing = false;
      state.editingItem = null;
    };

    const addOption = () => {
      state.selectOptions.push('');
    };

    const removeOption = (index) => {
      state.selectOptions.splice(index, 1);
    };

    const sort = () => {
      if (sortOrder.value === 'ASC')
        state.items.sort((a, b) => a.title.localeCompare(b.title));
      else state.items.sort((a, b) => b.title.localeCompare(a.title));
    };

    // Computed pour vérifier si le type sélectionné est SELECT
    const selectedTypeIsSelect = computed(() => {
      if (!state.columnType) return false;
      const selectedTypeOption = props.typeOptions.find(
        (type) => type.id === state.columnType.id,
      );
      return (
        selectedTypeOption && selectedTypeOption.label === 'Choix multiple'
      );
    });

    const selectedTypeIsNumber = computed(() => {
      if (!state.columnType) return false;
      const selectedTypeOption = props.typeOptions.find(
        (type) => type.id === state.columnType.id,
      );
      return selectedTypeOption && selectedTypeOption.label === 'Nombre';
    });

    // Formater les options pour le select
    const typeOptionsFormatted = computed(() => {
      return props.typeOptions.map((type) => ({
        id: type.id,
        label: type.label,
      }));
    });

    onSort(sort);

    return {
      http,
      onClickValidate,
      fetchCustomColumnData,
      resetForm,
      addOption,
      removeOption,
      selectedTypeIsSelect,
      selectedTypeIsNumber,
      typeOptionsFormatted,
      ...toRefs(state),
      sortDefinition,
    };
  },

  data() {
    return {
      rules: {
        title: [required, shouldNotExceedCharLength(100)],
        type: [required],
        option: [shouldNotExceedCharLength(100)],
      },
      headers: [
        {
          name: 'title',
          title: this.$t('Titre'),
          sortField: 'title',
          style: {flex: 1},
        },
        {
          name: 'type',
          title: this.$t('Type'),
          sortField: 'type',
          style: {flex: 0.5},
        },
        {
          name: 'options',
          title: this.$t('Options'),
          sortField: 'options',
          style: {flex: 1},
          cellRenderer: (value) => {
            return value || '-';
          },
        },
        {
          name: 'filter',
          title: this.$t('Filtre'),
          sortField: 'filter',
          style: {flex: 0.5},
          cellRenderer: (value) => {
            return value || '';
          },
        },
        {
          name: 'externalSet',
          title: this.$t('Autoriser paramètre externe'),
          sortField: 'externalSet',
          style: {flex: 0.5},
          cellRenderer: (value) => {
            return value || '';
          },
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.cellRenderer,
        },
      ],
      checkedItems: [],
    };
  },
  beforeMount() {
    this.fetchCustomColumnData();
  },
  methods: {
    cellRenderer(...[, , , row]) {
      const cellConfig = {};

      // On veut l'ordre: delete puis edit visuellement
      cellConfig.delete = {
        onClick: this.onClickDelete,
        props: {
          name: 'trash',
        },
      };

      // Pas de bouton d'édition pour les colonnes de type "Nombre"
      if (row.type !== 'Nombre') {
        cellConfig.edit = {
          onClick: this.onClickEdit,
          props: {
            name: 'pencil',
          },
        };
      }

      return {
        props: {
          header: {
            cellConfig,
          },
        },
      };
    },
    onClickCancel() {
      this.resetForm();
    },
    onClickAdd() {
      this.isModalOpen = true;
      this.isEditing = false;
      this.editingItem = null;
      this.columnTitle = '';
      this.columnType = null;
      this.selectOptions = [];
      this.hasFilter = false;
      this.allowExternalSet = false;
    },
    onClickEdit(item) {
      this.isModalOpen = true;
      this.isEditing = true;
      this.editingItem = item;
      this.columnTitle = item.title;
      this.columnType = {
        id: item.typeOrdinal,
        label: item.type,
      };

      if (item.rawOptions && item.rawOptions.trim() !== '') {
        try {
          const parsed = JSON.parse(item.rawOptions);
          this.selectOptions = Array.isArray(parsed) ? parsed : [];
        } catch (e) {
          this.selectOptions = [];
        }
      } else {
        this.selectOptions = [];
      }

      this.hasFilter = item.hasFilter ?? false;
      this.allowExternalSet = item.allowExternalSet ?? false;
    },
    onClickDelete(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(item.id);
        }
      });
    },
    deleteItems(id) {
      this.isLoading = true;
      this.http
        .delete(id)
        .then(() => {
          return this.$toast.deleteSuccess();
        })
        .then(() => {
          this.isLoading = false;
          this.resetDataTable();
        })
        .catch(() => {
          this.isLoading = false;
        });
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.fetchCustomColumnData();
    },
  },
};
</script>

<style lang="scss" scoped>
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
  width: 500px;
  height: 90vh;
  max-width: 90%;
  z-index: 1001;
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

  .close-icon {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    color: #6c757d;
    transition: color 0.2s ease;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;

    &:hover {
      color: #dc3545;
      background-color: rgba(220, 53, 69, 0.1);
    }
  }
}

.modal-body {
  padding: 2rem;
  background-color: #ffffff;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--oxd-border-light-color);
  background-color: #f8f9fa;
  border-radius: 0 0 0.75rem 0.75rem;
  flex-shrink: 0;
}

.modal-form {
  display: flex;
  flex-direction: column;
  height: 100%;
}

// Amélioration des champs de formulaire
:deep(.oxd-input-field) {
  .oxd-input {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.2s ease;

    &:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
  }

  .oxd-label {
    font-weight: 500;
    color: #495057;
  }
}

// Amélioration des boutons
:deep(.oxd-button) {
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;

  &.oxd-button--secondary {
    background: linear-gradient(135deg, #a7df73 0%, #76bc21 100%);
    border: none;

    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 0.5rem 1rem rgba(118, 188, 33, 0.3);
    }
  }

  &.oxd-button--ghost {
    &:hover {
      background-color: rgba(108, 117, 125, 0.1);
    }
  }
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.option-item {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  width: 100%;
}

.option-item .oxd-input-field {
  flex: 1;
}

.option-delete-button {
  flex-shrink: 0;
  margin-bottom: 1.25rem;
}

.external-set-label-row {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.external-set-help {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.external-set-help-icon {
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  background: #e9ecef;
  color: #495057;
  flex-shrink: 0;
  border: none;
  cursor: pointer;
  padding: 0;
}

.external-set-help-tooltip {
  position: absolute;
  left: 50%;
  top: calc(100% + 0.35rem);
  transform: translateX(-50%);
  width: max-content;
  max-width: min(80vw, 320px);
  background: #1f2d3d;
  color: #fff;
  border-radius: 0.35rem;
  padding: 0.45rem 0.6rem;
  font-size: 0.75rem;
  line-height: 1.3;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.15s ease;
  z-index: 3;
}

.external-set-help:hover .external-set-help-tooltip,
.external-set-help:focus-within .external-set-help-tooltip {
  opacity: 1;
  visibility: visible;
}

@media (max-width: 768px) {
  .external-set-label-row {
    position: relative;
  }

  .external-set-help {
    position: static;
  }

  .external-set-help-tooltip {
    top: auto;
    bottom: calc(100% + 0.35rem);
    left: 50%;
    transform: translateX(-50%);
    max-width: min(90vw, 320px);
  }
}
</style>
