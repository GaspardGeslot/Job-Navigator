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

    <!-- Modal pour ajouter un niveau d'étude -->
    <div v-if="isAdding" class="modal-overlay" @click="onClickCancel">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3>{{ $t("Ajouter un niveau d'étude") }}</h3>
          <span class="close-icon" @click="onClickCancel">&times;</span>
        </div>
        <div class="modal-body">
          <oxd-form>
            <oxd-form-row>
              <oxd-grid :cols="2">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="studyLevel"
                    :placeholder="$t('Nouveau niveau d\'étude')"
                    :label="$t('Nom')"
                    required
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <oxd-input-field
                    :placeholder="$t('Position (optionnel)')"
                    :label="$t('Position')"
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
      `${window.appGlobal.theme}/api/v2/admin/study-level`,
    );
    const state = reactive({
      items: [],
      total: 0,
      isLoading: false,
      isAdding: false,
      studyLevel: '',
      position: '',
    });

    const fetchStudyLevelData = () => {
      state.isLoading = true;
      http
        .getAll()
        .then((response) => {
          state.items = response.data.map((item) => {
            return {
              title: item.name,
              position: item.position || '',
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
            message: "Impossible de récupérer les niveaux d'études",
          });
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    const onClickValidate = () => {
      if (state.studyLevel === '') {
        return;
      } else if (state.items.find((item) => item.title === state.studyLevel)) {
        return error({
          title: 'Conflit',
          message: "Ce niveau d'étude existe déjà",
        });
      }

      // L'API attend une chaîne de caractères simple pour le nom
      http
        .create({name: state.studyLevel})
        .then(() => {
          state.studyLevel = '';
          state.position = '';
          state.isAdding = false;
          fetchStudyLevelData();
        })
        .catch((error) => {
          error({
            title: 'Erreur',
            message: "Impossible d'ajouter le niveau d'étude",
          });
        });
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
      fetchStudyLevelData,
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
          name: 'position',
          title: this.$t('Position'),
          sortField: 'position',
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
          },
        },
      ],
      checkedItems: [],
    };
  },
  beforeMount() {
    this.fetchStudyLevelData();
  },
  methods: {
    onClickCancel() {
      this.isAdding = false;
      this.studyLevel = '';
      this.position = '';
    },
    onClickAdd() {
      this.isAdding = true;
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
          this.deleteItems(item.title);
        }
      });
    },
    deleteItems(item) {
      this.isLoading = true;
      this.http
        .deleteAll({
          name: item,
        })
        .then(() => {
          return this.$toast.deleteSuccess();
        })
        .then(() => {
          this.isLoading = false;
          this.resetDataTable();
        })
        .catch((error) => {
          this.isLoading = false;
        });
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.fetchStudyLevelData();
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
