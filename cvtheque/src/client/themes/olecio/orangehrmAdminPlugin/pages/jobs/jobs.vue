<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter>
      <oxd-form @submit-valid="filterItems" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.title"
                :label="'Nom'"
                :placeholder="$t('Entrez le nom du job')"
              />
            </oxd-grid-item>
            <!--
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.size"
                :label="'Éléments par page'"
                type="select"
                :options="sizeOptions"
              />
            </oxd-grid-item>
            -->
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-actions>
          <oxd-button
            type="reset"
            display-type="ghost"
            :label="$t('general.reset')"
            :disabled="!canUpdate"
            @click="resetFiltre"
          />
          <submit-button :label="$t('general.search')" :disabled="!canUpdate" />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>
    <div class="orangehrm-paper-container" style="margin-top: 1rem">
      <div class="orangehrm-header-container">
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
    </div>
    <table-header
      :selected="checkedItems.length"
      :total="total"
      :loading="isLoading"
      @delete="onClickDelete"
    ></table-header>
    <div class="orangehrm-container">
      <oxd-card-table
        v-model:selected="checkedItems"
        :loading="isLoading"
        :headers="headers"
        :items="response.data"
        :selectable="true"
        :clickable="false"
        row-decorator="oxd-table-decorator-card"
      />
    </div>
    <div class="orangehrm-pagination-wrapper">
      <oxd-pagination
        v-model:current="paginateCurrentPage"
        :length="totalPages"
        :max="10"
        @update:current="onPageChange"
      />
    </div>
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
    <div
      v-if="showModal"
      class="orangehrm-paper-container"
      style="
        padding: 1.5rem;
        margin-top: 1rem;
        position: fixed;
        top: 50%;
        left: 55%;
        transform: translate(-50%, -50%);
        z-index: 1000;
        background: white;
        width: 80%;
        max-width: 800px;
        border: 0.2rem, grey, solid;
      "
    >
      <div class="orangehrm-header-container">
        <h2>
          {{ isEditing ? $t(`Modifier le job`) : $t('Ajouter un job') }}
        </h2>
      </div>
      <oxd-form @submit="onClickValidate">
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.title"
                :label="$t('title')"
                :placeholder="$t('Entrez le titre')"
                :rules="isCreatingNewOrganisme ? [{required: true}] : []"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.otherTitle"
                :label="$t('otherTitle')"
                :placeholder="$t(`Entrez l'autre titre`)"
                :rules="isCreatingNewOrganisme ? [{required: true}] : []"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.domain"
                :label="$t('domain')"
                :placeholder="$t('Entrez le domain')"
                :rules="isCreatingNewOrganisme ? [{required: true}] : []"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.typeFormTitle"
                :label="$t('typeFormTitle')"
                :placeholder="$t('typeFormTitle')"
                :rules="isCreatingNewOrganisme ? [{required: true}] : []"
              />
            </oxd-grid-item>
            <oxd-grid-item class="orangehrm-switch-wrapper">
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
                Métier olecio
              </oxd-text>
              <oxd-switch-input
                v-model="ofForm.inOlecio"
                :label="$t(`Métier olecio`)"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-actions>
          <oxd-button
            type="button"
            display-type="ghost"
            :label="$t('general.cancel')"
            @click="onClickCancel"
          />
          <oxd-button
            type="submit"
            display-type="secondary"
            :label="$t('general.save')"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {ref, computed, reactive} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@/core/util/composable/usePaginate';
import useToast from '@/core/util/composable/useToast';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {OxdSwitchInput} from '@ohrm/oxd';

export default {
  name: 'Jobs',
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'oxd-switch-input': OxdSwitchInput,
  },
  setup() {
    const {error, deleteSuccess} = useToast();
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/job`,
    );

    const initialFilters = {
      title: '',
      page: 0,
      size: 25,
    };

    const filters = ref({
      title: '',
      page: 0,
      size: 25,
    });

    const showModal = ref(false);
    const isEditing = ref(false);
    const deleteDialog = ref(null);
    const ofForm = reactive({
      title: '',
      inOlecio: true,
      domain: null,
      typeFormTitle: null,
      otherTitle: null,
    });
    const isCreatingNewOrganisme = ref(false);

    // const sizeOptions = [
    //   {id: 25, label: '25'},
    //   {id: 50, label: '50'},
    //   {id: 100, label: '100'},
    // ];

    const pageOptions = computed(() => {
      const totalPages = response.value?.meta?.totalPages || 0;
      return Array.from({length: totalPages}, (_, i) => ({
        id: i,
        label: (i + 1).toString(),
      }));
    });

    const serializedFilters = computed(() => {
      const filterParams = {};
      if (filters.value.title) {
        filterParams['title'] = filters.value.title;
      }
      if (filters.value.size) {
        filterParams['size'] = filters.value.size;
      }
      if (filters.value.page) {
        filterParams['page'] = filters.value.page;
      }
      return filterParams;
    });

    const canUpdate = computed(() => {
      return (
        filters.value.title !== initialFilters.title ||
        filters.value.page !== initialFilters.page ||
        filters.value.size !== initialFilters.size
      );
    });

    const {
      showPaginator,
      paginateCurrentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
    } = usePaginate(http, {
      query: serializedFilters,
      normalizer: (data) => {
        data.map((job) => ({
          title: job.title,
          domain: job.domain,
          typeFormTitle: job.typeFormTitle,
          otherTitle: job.otherTitle,
          inOlecio: job.inOlecio,
        }));
        return data;
      },
    });

    const totalPages = computed(() => {
      return response.value?.meta?.totalPages || 0;
    });

    const onPageChange = (page) => {
      filters.value.page = page - 1;
      execQuery();
    };

    const resetFiltre = async () => {
      filters.value = {...initialFilters};
      await execQuery();
    };

    const resetForm = () => {
      ofForm.id = null;
      ofForm.title = '';
      ofForm.domain = '';
      ofForm.typeFormTitle = '';
      ofForm.otherTitle = '';
      ofForm.inOlecio = true;
      isCreatingNewOrganisme.value = false;
    };

    const onClickCancel = () => {
      showModal.value = false;
      resetForm();
    };

    const onClickAdd = () => {
      isEditing.value = false;
      resetForm();
      showModal.value = true;
    };

    const onClickEdit = (item) => {
      isEditing.value = true;
      ofForm.id = item.id;
      ofForm.title = item.title;
      ofForm.typeFormTitle = item.typeFormTitle;
      ofForm.otherTitle = item.otherTitle;
      ofForm.domain = item.domain;
      ofForm.inOlecio = item.inOlecio;
      showModal.value = true;
    };

    const onClickValidate = async (event) => {
      event.preventDefault();

      try {
        const data = {
          title: ofForm.title,
          domain: ofForm.domain !== '' ? ofForm.domain : null,
          typeFormTitle:
            ofForm.typeFormTitle !== '' ? ofForm.typeFormTitle : null,
          otherTitle: ofForm.otherTitle !== '' ? ofForm.otherTitle : null,
          inOlecio: ofForm.inOlecio,
        };

        // console.log('data to send ', data);
        if (isEditing.value) {
          await http.update(ofForm.id, data);
        } else {
          await http.create(data);
        }
        onClickCancel();
        await execQuery();
      } catch (error) {
        console.error('Erreur lors de la sauvegarde du cours:', error);
        error({
          title: 'Erreur',
          message: 'Erreur lors de la sauvegarde du cours',
        });
      }
    };

    const onClickDelete = (item) => {
      deleteDialog.value.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          deleteCourse(item.id);
        }
      });
    };

    const deleteCourse = async (id) => {
      try {
        await http.delete(id);
        await deleteSuccess();
        await execQuery();
      } catch (error) {
        console.error('Erreur lors de la suppression du cours:', error);
        error({
          title: 'Erreur',
          message: 'Erreur lors de la suppression du cours',
        });
      }
    };

    return {
      http,
      filters,
      canUpdate,
      showPaginator,
      paginateCurrentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
      resetFiltre,
      showModal,
      isEditing,
      ofForm,
      onClickCancel,
      onClickAdd,
      onClickEdit,
      onClickValidate,
      onClickDelete,
      deleteDialog,
      // sizeOptions,
      pageOptions,
      isCreatingNewOrganisme,
      totalPages,
      onPageChange,
    };
  },
  data() {
    return {
      checkedItems: [],
      headers: [
        {
          name: 'title',
          title: 'Titre',
          sortField: 'title',
          style: {flex: 1},
        },
        {
          name: 'inOlecio',
          title: 'inOlecio',
          sortField: 'inOlecio',
          style: {flex: 1},
        },
        {
          name: 'domain',
          title: 'domain',
          sortField: 'domain',
          style: {flex: 1},
        },
        {
          name: 'typeFormTitle',
          title: 'Typeform',
          sortField: 'typeFormTitle',
          style: {flex: 1},
        },
        {
          name: 'otherTitle',
          title: 'OtherTitle',
          sortField: 'otherTitle',
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
              onClick: (item) => this.onClickDelete(item),
              component: 'oxd-icon-button',
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
    };
  },
  //   created() {},
  methods: {
    async filterItems() {
      await this.execQuery();
    },
    onReset() {
      this.resetFiltre();
    },
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-dialog-modal {
  z-index: 1000;
}

.orangehrm-pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
  padding: 1rem;
}
</style>
