<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-paper-container">
      <div v-if="!isAdding" class="orangehrm-left-header-container">
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <div v-else class="orangehrm-left-header-container">
        <oxd-button
          :label="$t('Annuler')"
          display-type="ghost"
          @click="onClickCancel"
        />
        <oxd-form>
          <oxd-form-row>
            <oxd-grid>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="drivingLicense"
                  :placeholder="$t('Nouveau permis de conduire')"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </oxd-form>
        <oxd-button
          :label="$t('Enregistrer')"
          display-type="secondary"
          @click="onClickValidate"
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
      `${window.appGlobal.theme}/api/v2/admin/driving-licenses`,
    );
    const state = reactive({
      items: [],
      total: 0,
      isLoading: false,
      isAdding: false,
      drivingLicense: '',
    });

    const fetchDrivingLicenseData = () => {
      state.isLoading = true;
      http
        .getAll()
        .then((response) => {
          console.log("Réponse de l'API:", response);
          state.items = response.data.data.map((item) => {
            return {title: item};
          });
          state.total = response.data.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .catch((err) => {
          error({
            title: 'Erreur',
            message: 'Impossible de récupérer les permis de conduire',
          });
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    const onClickValidate = () => {
      if (state.drivingLicense === '') {
        return;
      } else if (
        state.items.find((item) => item.title === state.drivingLicense)
      ) {
        return error({
          title: 'Conflit',
          message: 'Ce permis de conduire existe déjà',
        });
      }
      http
        .create({name: state.drivingLicense})
        .then(() => {
          state.drivingLicense = '';
          state.isAdding = false;
          fetchDrivingLicenseData();
        })
        .catch((error) => {
          error({
            title: 'Erreur',
            message: "Impossible d'ajouter le permis de conduire",
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
      fetchDrivingLicenseData,
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
    this.fetchDrivingLicenseData();
  },
  methods: {
    onClickCancel() {
      this.isAdding = false;
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
      await this.fetchDrivingLicenseData();
    },
  },
};
</script>
