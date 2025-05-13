<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter>
      <oxd-form @submit-valid="filterItems" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.titre"
                :label="'Titre'"
                :placeholder="$t('Entrez le titre')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.organisme"
                :label="'Organisme'"
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

    <!-- Modal pour l'ajout/édition -->
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
              ? $t('Modifier la formation')
              : $t('Ajouter une formation')
          }}
        </h2>
      </div>
      <oxd-form @submit="onClickValidate">
        <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.id"
                :label="$t('Id')"
                :placeholder="$t('Entrez l\'id')"
                :rules="[{required: true}]"
                :disabled="isEditing"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.title"
                :label="$t('Titre')"
                :placeholder="$t('Entrez le titre')"
                :rules="[{required: true}]"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.code"
                :label="$t('Code')"
                :placeholder="$t('Entrez le code')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.organisme"
                :label="$t('Organisme')"
                :placeholder="$t('Entrez l\'organisme')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.actor"
                :label="$t('Acteur')"
                type="select"
                :options="actorOptions"
                :rules="[]"
                :disabled="isEditing"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.actorCourseId"
                :label="$t('Acteur Id')"
                :placeholder="$t('Entrez l\'id')"
                :rules="[]"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.trainingCode"
                :label="$t('trainingCode')"
                :placeholder="$t('Entrez le code')"
                :rules="[]"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.trainingId"
                :label="$t('trainingId')"
                :placeholder="$t('Entrez l\'id')"
                :rules="[]"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.utmCampaign"
                :label="$t('utmCampaign')"
                :placeholder="$t('Entrez l\'utm')"
                :rules="[]"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseForm.thematic"
                :label="$t('thematic')"
                :placeholder="$t('Entrez la thématique')"
                :rules="[]"
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
      `${window.appGlobal.theme}/api/v2/admin/course`,
    );
    const actorHttp = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/actor/options`,
    );

    const filters = ref({
      titre: '',
      organisme: '',
      actor: null,
    });

    const actorOptions = ref([]);
    const showModal = ref(false);
    const isEditing = ref(false);
    const deleteDialog = ref(null);
    const courseForm = reactive({
      id: null,
      title: '',
      code: '',
      organisme: '',
      actor: null,
      actorCourseId: null,
      trainingCode: null,
      trainingId: null,
      utmCampaign: null,
      thematic: null,
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

      if (filters.value.titre) {
        filterParams['title'] = filters.value.titre;
      }

      if (filters.value.organisme) {
        filterParams['of'] = filters.value.organisme;
      }

      if (filters.value.actor) {
        filterParams['actor'] = filters.value.actor.label;
      }

      return filterParams;
    });

    const canUpdate = computed(() => {
      return Object.keys(serializedFilters.value).length > 0;
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
        return data.map((course) => ({
          id: course.id,
          name: course.name,
          code: course.code || '',
          of: course.of || '',
          actorCourseTitle: course.actorCourseTitle || '',
          actorCourseId: course.actorCourseId || null,
          trainingCode: course.trainingCode || '',
          trainingId: course.trainingId || null,
          utmCampaign: course.utmCampaign || '',
          thematic: course.thematic || ''
        }));
      },
    });

    const resetFiltre = async () => {
      filters.value.titre = '';
      filters.value.organisme = '';
      filters.value.actor = null;
      await execQuery();
    };

    const resetForm = () => {
      courseForm.id = null;
      courseForm.title = '';
      courseForm.code = '';
      courseForm.organisme = '';
      courseForm.actor = null;
      courseForm.actorCourseId = null;
      courseForm.trainingCode = null;
      courseForm.trainingId = null;
      courseForm.utmCampaign = null;
      courseForm.thematic = null;
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
      courseForm.id = item.id;
      courseForm.title = item.name;
      courseForm.code = item.code || '';
      courseForm.organisme = item.of || '';
      
      const actorCourseTitle = item.actorCourseTitle;
      const matchingActor = actorOptions.value.find(opt => opt.label === actorCourseTitle);
      courseForm.actor = matchingActor || null;
      
      courseForm.actorCourseId = item.actorCourseId || null;
      courseForm.trainingCode = item.trainingCode || null;
      courseForm.trainingId = item.trainingId || null;
      courseForm.utmCampaign = item.utmCampaign || null;
      courseForm.thematic = item.thematic || null;
      showModal.value = true;
    };

    const onClickValidate = async (event) => {
      event.preventDefault();
      if (!courseForm.title) {
        return error({title: 'Erreur', message: 'Le titre est requis'});
      }
      if (!isEditing.value && !courseForm.id) {
        return error({title: 'Erreur', message: `L'ID est requis`});
      }

      try {
        const data = {
          title: courseForm.title,
          code: courseForm.code,
          of: {
            name: courseForm.organisme,
          },
          actorCourseTitle: courseForm.actor ? courseForm.actor.label : null,
          actorCourseId: courseForm.actorCourseId,
          thematic: courseForm.thematic,
          trainingCode: courseForm.trainingCode,
          trainingId: courseForm.trainingId,
          utmCampaign: courseForm.utmCampaign,
        };

        if (isEditing.value) {
          await http.update(courseForm.id, data);
        } else {
          data.id = courseForm.id;
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
      courseForm,
      onClickCancel,
      onClickAdd,
      onClickEdit,
      onClickValidate,
      onClickDelete,
      deleteDialog,
    };
  },
  data() {
    return {
      checkedItems: [],
      headers: [
        {
          name: 'id',
          title: 'ID',
          sortField: 'id',
          style: {flex: 1},
        },
        {
          name: 'name',
          title: 'Titre',
          sortField: 'title',
          style: {flex: 1},
        },
        {
          name: 'code',
          title: 'Code',
          sortField: 'code',
          style: {flex: 1},
        },
        {
          name: 'of',
          title: 'Organisme',
          sortField: 'of',
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
