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

    <!-- Modal pour ajouter/modifier une source -->
    <div v-if="isModalOpen" class="modal-overlay" @click="onClickCancel">
      <div class="modal-container" @click.stop>
        <oxd-form :loading="isSaving" @submit-valid="onClickValidate">
          <div class="modal-header">
            <h3>
              {{
                isEditing ? $t('Modifier la source') : $t('Ajouter une source')
              }}
            </h3>
            <span class="close-icon" @click="onClickCancel">&times;</span>
          </div>
          <div class="modal-body">
            <oxd-form-row>
              <oxd-grid :cols="1">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="sourceName"
                    :placeholder="$t('Nom de la source')"
                    :label="$t('Nom')"
                    required
                    :rules="rules.title"
                    :disabled="isEditing"
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="sourcePrice"
                    :placeholder="$t('Prix (optionnel)')"
                    :label="$t('Prix')"
                    :rules="rules.price"
                  />
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
              :label="$t(isEditing ? 'Modifier' : 'Enregistrer')"
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
import {reactive, toRefs} from 'vue';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {APIService} from '@/core/util/services/api.service';
import useSort from '@/core/util/composable/useSort';
import useToast from '@/core/util/composable/useToast';
import {
  required,
  shouldNotExceedCharLength,
  digitsOnlyWithTwoDecimalPoints,
} from '@/core/util/validation/rules';

const defaultSortOrder = {
  title: 'ASC',
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
  },
  props: {
    unselectableIds: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const {noRecordsFound, error, success} = useToast();
    const {sortDefinition, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/source`,
    );
    const state = reactive({
      items: [],
      total: 0,
      isLoading: false,
      isModalOpen: false,
      isEditing: false,
      isSaving: false,
      sourceName: '',
      sourcePrice: '',
      editingItem: null,
    });

    const fetchSourceData = () => {
      state.isLoading = true;
      http
        .getAll()
        .then((response) => {
          state.items = response.data.map((item) => {
            return {
              title: item.name,
              price:
                item.price !== null
                  ? parseFloat(item.price).toFixed(2) + ' €'
                  : item.price === 0
                  ? '0 €'
                  : '-',
              rawPrice: item.price,
              name: item.name,
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
            message: 'Impossible de récupérer les sources',
          });
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    const onClickValidate = () => {
      if (!state.sourceName || state.sourceName.length < 2) {
        return error({
          title: 'Erreur de validation',
          message: 'Le nom doit contenir au moins 2 caractères',
        });
      }

      // Validation du prix si fourni
      let price = null;
      if (state.sourcePrice && state.sourcePrice.trim() !== '') {
        const parsedPrice = parseFloat(state.sourcePrice);
        if (isNaN(parsedPrice) || parsedPrice < 0) {
          return error({
            title: 'Erreur de validation',
            message: 'Le prix doit être un nombre positif valide',
          });
        }
        price = parsedPrice;
      }

      // Vérifier si le nom existe déjà (sauf si on est en mode édition du même item)
      const existingItem = state.items.find(
        (item) => item.name === state.sourceName,
      );
      if (
        existingItem &&
        (!state.isEditing || existingItem.name !== state.editingItem?.name)
      ) {
        return error({
          title: 'Conflit',
          message: 'Cette source existe déjà',
        });
      }

      state.isSaving = true;

      const requestPromise = state.isEditing
        ? http.update(encodeURIComponent(state.editingItem.name), {
            price: price,
          })
        : http.create({name: state.sourceName, price: price});

      requestPromise
        .then(() => {
          success({
            title: 'Succès',
            message: state.isEditing
              ? 'Source modifiée avec succès'
              : 'Source ajoutée avec succès',
          });
          resetForm();
          fetchSourceData();
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: state.isEditing
              ? 'Impossible de modifier la source'
              : "Impossible d'ajouter la source",
          });
        })
        .finally(() => {
          state.isSaving = false;
        });
    };

    const resetForm = () => {
      state.sourceName = '';
      state.sourcePrice = '';
      state.isModalOpen = false;
      state.isEditing = false;
      state.editingItem = null;
    };

    const sort = () => {
      if (sortOrder.value === 'ASC')
        state.items.sort((a, b) => a.title.localeCompare(b.title));
      else state.items.sort((a, b) => b.title.localeCompare(a.title));
    };

    onSort(sort);

    return {
      http,
      onClickValidate,
      fetchSourceData,
      resetForm,
      ...toRefs(state),
      sortDefinition,
    };
  },

  data() {
    return {
      rules: {
        title: [required, shouldNotExceedCharLength(100)],
        price: [digitsOnlyWithTwoDecimalPoints],
      },
      headers: [
        {
          name: 'title',
          title: this.$t('Nom'),
          sortField: 'title',
          style: {flex: 1},
        },
        {
          name: 'price',
          title: this.$t('Prix'),
          sortField: 'price',
          style: {flex: 0.5},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            edit: {
              onClick: this.onClickEdit,
              props: {
                name: 'pencil',
              },
            },
            delete: {
              onClick: this.onClickDelete,
              props: {
                name: 'trash',
              },
            },
          },
        },
      ],
      checkedItems: [],
    };
  },
  beforeMount() {
    this.fetchSourceData();
  },
  methods: {
    onClickCancel() {
      this.resetForm();
    },
    onClickAdd() {
      this.isModalOpen = true;
      this.isEditing = false;
      this.editingItem = null;
    },
    onClickEdit(item) {
      this.isModalOpen = true;
      this.isEditing = true;
      this.editingItem = item;
      this.sourceName = item.name;
      this.sourcePrice = item.rawPrice !== null ? item.rawPrice.toString() : '';
    },
    onClickDelete(item) {
      const isSelectable = this.unselectableIds.findIndex(
        (id) => id == item.id,
      );
      if (isSelectable > -1) {
        return this.$toast.cannotDelete();
      }
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(item.name);
        }
      });
    },
    deleteItems(name) {
      this.isLoading = true;
      this.http
        .deleteAll({
          name: name,
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
        });
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.fetchSourceData();
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
</style>
