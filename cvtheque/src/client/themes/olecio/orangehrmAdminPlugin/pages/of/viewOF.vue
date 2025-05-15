<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter>
      <oxd-form @submit-valid="filterItems" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.organisme"
                :label="'Nom'"
                :placeholder="$t('Entrez le nom de l\'organisme')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.actor"
                :label="'Acteur'"
                type="select"
                :options="actorOptions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.page"
                :label="'Page'"
                type="select"
                :options="pageOptions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.size"
                :label="'Éléments par page'"
                type="select"
                :options="sizeOptions"
              />
            </oxd-grid-item>
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
          {{
            isEditing
              ? $t(`Modifier l'organisme de formation`)
              : $t('Ajouter un organisme de formation')
          }}
        </h2>
      </div>
      <oxd-form @submit="onClickValidate">
        <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.organisme"
                :label="$t('Organisme')"
                :placeholder="$t('Entrez l\'organisme')"
                :rules="isCreatingNewOrganisme ? [{required: true}] : []"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.organismeContact"
                :label="$t('Contact de l\'organisme')"
                :placeholder="$t('Entrez le contact')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="ofForm.organismeActor"
                :label="$t('Acteur de l\'organisme')"
                type="select"
                :options="actorOptions"
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

export default {
  name: 'MaVue',
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
  },
  setup() {
    const {error, deleteSuccess} = useToast();
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/of`,
    );
    const actorHttp = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/actor/options`,
    );

    const initialFilters = {
      organisme: '',
      actor: null,
      page: 0,
      size: 25,
    };

    const filters = ref({
      organisme: '',
      actor: null,
      page: 0,
      size: 25,
    });

    const actorOptions = ref([]);
    const showModal = ref(false);
    const isEditing = ref(false);
    const deleteDialog = ref(null);
    const ofForm = reactive({
      id: null,
      organisme: '',
      organismeContact: '',
      organismeActor: null,
      actor: null,
    });

    const isCreatingNewOrganisme = ref(false);

    const sizeOptions = [
      {id: 2, label: '2'},
      {id: 25, label: '25'},
      {id: 50, label: '50'},
      {id: 100, label: '100'},
    ];

    const pageOptions = computed(() => {
      const totalPages = response.value?.meta?.totalPages || 0;
      console.log('totalPages here ', totalPages);
      return Array.from({length: totalPages}, (_, i) => ({
        id: i,
        label: (i + 1).toString(),
      }));
    });

    const fetchActorOptions = async () => {
      try {
        const response = await actorHttp.getAll();
        if (response.data) {
          actorOptions.value = response.data.map((option) => ({
            id: option.id,
            label: option.label,
          }));
        }
      } catch (error) {
        console.error(
          "Erreur lors de la récupération des options d'acteurs:",
          error,
        );
      }
    };

    const serializedFilters = computed(() => {
      const filterParams = {};

      if (filters.value.organisme) {
        filterParams['name'] = filters.value.organisme;
      }

      if (filters.value.actor) {
        filterParams['actor'] = filters.value.actor.label;
      }

      if (filters.value.size) {
        filterParams['size'] = filters.value.size.id;
      }
      if (filters.value.page) {
        filterParams['page'] = filters.value.page.id;
      }
      return filterParams;
    });

    const canUpdate = computed(() => {
      return (
        filters.value.organisme !== initialFilters.organisme ||
        filters.value.actor !== initialFilters.actor ||
        filters.value.page !== initialFilters.page ||
        filters.value.size !== initialFilters.size
      );
    });

    const {
      showPaginator,
      currentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
    } = usePaginate(http, {
      query: serializedFilters,
      normalizer: (data) => {
        return data.map((of) => ({
          id: of.id,
          name: of.name,
          contact: of.contact || '',
          actor: of.actor || '',
        }));
      },
    });

    const resetFiltre = async () => {
      filters.value = {...initialFilters};
      await execQuery();
    };

    const resetForm = () => {
      ofForm.id = null;
      ofForm.title = '';
      ofForm.code = '';
      ofForm.organisme = '';
      ofForm.organismeContact = '';
      ofForm.organismeActor = null;
      ofForm.actor = null;
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
      ofForm.organisme = item.name;
      ofForm.organismeContact = item.contact;
      const matchingActor = actorOptions.value.find(
        (opt) => opt.label === item.actor,
      );
      ofForm.organismeActor = matchingActor || null;
      showModal.value = true;
    };

    const onClickValidate = async (event) => {
      event.preventDefault();

      try {
        const data = {
          name: ofForm.organisme,
          contact: ofForm.organismeContact,
          actor: ofForm.organismeActor ? ofForm.organismeActor.label : null,
        };

        if (isEditing.value) {
          await http.update(ofForm.id, data);
        } else {
          //   data.id = ofForm.id;
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
      actorOptions,
      canUpdate,
      showPaginator,
      currentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
      fetchActorOptions,
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
      sizeOptions,
      pageOptions,
      isCreatingNewOrganisme,
    };
  },
  data() {
    return {
      checkedItems: [],
      headers: [
        {
          name: 'name',
          title: 'Titre',
          sortField: 'title',
          style: {flex: 1},
        },
        {
          name: 'contact',
          title: 'Contact',
          sortField: 'contact',
          style: {flex: 1},
        },
        {
          name: 'actor',
          title: 'Actor',
          sortField: 'actor',
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
  created() {
    this.fetchActorOptions();
  },
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
</style>
