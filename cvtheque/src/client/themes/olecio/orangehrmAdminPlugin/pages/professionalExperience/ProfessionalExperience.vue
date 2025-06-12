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

    <!-- Modal pour ajouter/modifier une expérience professionnelle -->
    <div v-if="isAdding" class="modal-overlay" @click="onClickCancel">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3>
            {{
              isEditing
                ? $t("Modifier l'expérience professionnelle")
                : $t('Ajouter une expérience professionnelle')
            }}
          </h3>
          <span class="close-icon" @click="onClickCancel">&times;</span>
        </div>
        <div class="modal-body">
          <oxd-form>
            <oxd-form-row>
              <oxd-grid :cols="2">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="name"
                    :placeholder="$t('Nouvelle expérience professionnelle')"
                    :label="$t('Nom')"
                    required
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="quantity"
                    :placeholder="$t('quantité (optionnel)')"
                    :label="$t('quantité')"
                  />
                </oxd-grid-item>
              </oxd-grid>
            </oxd-form-row>
          </oxd-form>
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
            @click="onClickValidate"
          />
        </div>
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
    const {noRecordsFound, error} = useToast();
    const {sortDefinition, sortField, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/professional-experience`,
    );
    const state = reactive({
      items: [],
      total: 0,
      isLoading: false,
      isAdding: false,
      isEditing: false,
      name: '',
      quantity: 0,
      currentId: null,
    });

    const fetchExperiences = () => {
      state.isLoading = true;
      http
        .getAll()
        .then((response) => {
          state.items = response.data.map((item) => {
            return {
              id: item.id,
              title: item.name,
              quantity: item.quantity || '',
            };
          });
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .catch((err) => {
          error({
            title: 'Erreur',
            message: 'Impossible de récupérer les expériences professionnelles',
          });
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    const resetForm = () => {
      state.name = '';
      state.quantity = 0;
      state.isAdding = false;
      state.isEditing = false;
      state.currentId = null;
    };

    const onClickCancel = () => {
      resetForm();
    };

    const onClickAdd = () => {
      state.isAdding = true;
      state.isEditing = false;
    };

    const onClickEdit = (item) => {
      state.isAdding = true;
      state.isEditing = true;
      state.currentId = item.id;
      state.name = item.title;
      state.quantity = item.quantity;
    };

    const onClickValidate = () => {
      if (state.name === '') {
        return;
      }

      const data = {
        name: state.name,
        quantity: state.quantity,
      };

      if (state.isEditing) {
        http
          .update(state.currentId, data)
          .then(() => {
            resetForm();
            fetchExperiences();
          })
          .catch((error) => {
            error({
              title: 'Erreur',
              message: "Impossible de modifier l'expérience professionnelle",
            });
          });
      } else {
        if (state.items.find((item) => item.title === state.name)) {
          return error({
            title: 'Conflit',
            message: 'Cette expérience professionnelle existe déjà',
          });
        }

        http
          .create(data)
          .then(() => {
            resetForm();
            fetchExperiences();
          })
          .catch((error) => {
            error({
              title: 'Erreur',
              message: "Impossible d'ajouter l'expérience professionnelle",
            });
          });
      }
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
      fetchExperiences,
      onClickCancel,
      onClickAdd,
      onClickEdit,
      resetForm,
      ...toRefs(state),
      sortDefinition,
    };
  },

  data() {
    return {
      headers: [
        {
          name: 'title',
          title: this.$t('Titre'),
          sortField: 'title',
          style: {flex: 1},
        },
        {
          name: 'quantity',
          title: this.$t('quantité'),
          sortField: 'quantity',
          style: {flex: 0.5},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            delete: {
              onClick: this.onClickDelete,
              props: {
                name: 'trash',
              },
            },
            edit: {
              onClick: (item) => this.onClickEdit(item),
              props: {
                name: 'pencil-fill',
              },
            },
          },
        },
      ],
      checkedItems: [],
    };
  },
  beforeMount() {
    this.fetchExperiences();
  },
  methods: {
    onClickDelete(item) {
      const isSelectable = this.unselectableIds.findIndex(
        (id) => id == item.id,
      );
      if (isSelectable > -1) {
        return this.$toast.cannotDelete();
      }
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItem(item.id);
        }
      });
    },
    async deleteItem(id) {
      try {
        await this.http
          .delete(id)
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.isLoading = false;
            this.resetDataTable();
          });
      } catch (error) {
        console.error('Erreur lors de la suppression du cours:', error);
        error({
          title: 'Erreur',
          message: 'Erreur lors de la suppression du cours',
        });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.fetchExperiences();
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
}

.modal-container {
  background-color: #ffffff;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  width: 700px;
  max-width: 90%;
  z-index: 1001;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem;
  border-bottom: 1px solid var(--oxd-border-light-color);

  h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
  }

  .close-icon {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    color: #666;
    &:hover {
      color: #333;
    }
  }
}

.modal-body {
  padding: 2rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 2rem;
  border-top: 1px solid var(--oxd-border-light-color);
}
</style>
