<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Filtres')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field v-model="nameFilter" :label="$t('Nom')" />
            </oxd-grid-item>
            <oxd-grid-item>
              <job-autocomplete v-model="jobFilter" />
            </oxd-grid-item>
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
    <div class="orangehrm-paper-container">
      <div class="orangehrm-header-container">
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
    </div>
    <br />
    <div class="orangehrm-corporate-directory">
      <div class="orangehrm-paper-container">
        <div
          v-if="!state.isLoading && state.actors.length == 0"
          class="orangehrm-corporate-directory-nocontent"
          style="
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem 0;
          "
        >
          <img
            :src="noContentPic"
            alt="No Content"
            style="max-width: 60px; margin: 0 0 0.85rem 0"
          />
          <oxd-text tag="p">
            Effectuez une recherche pour consulter les acteurs
          </oxd-text>
        </div>
      </div>
    </div>
    <div
      v-if="state.isLoading"
      class="orangehrm-header-container"
      style="justify-content: center"
    >
      <oxd-loading-spinner class="orangehrm-container-loader" />
    </div>
    <div v-for="(actor, index) in state.actors" v-else :key="index">
      <table-filter
        :active="false"
        :filter-title="actor.name ? `${actor.name}` : `Actor N°${actor.id}`"
      >
        <div class="orangehrm-container">
          <actor-card
            :countries="countries"
            :fundings="fundings"
            :study-levels="studyLevels"
            :needs="needs"
            :status="status"
            :training-methods="trainingMethods"
            :sources="sources"
            :actor-current="actor"
            :time-slots="timeSlots"
            :themes="themes"
            @delete="onClickDelete(actor.id)"
            @save="(updatedActor) => onClickSave(updatedActor, actor.id)"
            @update="onUpdate()"
          />
        </div>
      </table-filter>
      <br />
    </div>
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
  </div>
</template>
<script>
import {ref, reactive} from 'vue';
import useToast from '@/core/util/composable/useToast';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import ActorCard from '../../components/ActorCard.vue';
import TableFilter from '@/core/components/dropdown/TableFilter.vue';
import JobAutocomplete from '@/core/components/inputs/JobAutocomplete.vue';
import {OxdSpinner} from '@ohrm/oxd';

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'actor-card': ActorCard,
    'table-filter': TableFilter,
    'oxd-loading-spinner': OxdSpinner,
    'job-autocomplete': JobAutocomplete,
  },
  props: {
    countries: {
      type: Array,
      default: () => [],
    },
    fundings: {
      default: () => [],
      type: Array,
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    status: {
      type: Array,
      default: () => [],
    },
    trainingMethods: {
      type: Array,
      default: () => [],
    },
    sources: {
      type: Array,
      default: () => [],
    },
    timeSlots: {
      type: Array,
      default: () => [],
    },
    themes: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/actor`,
    );
    const {noRecordsFound} = useToast();
    const nameFilter = ref(null);
    const jobFilter = ref(null);
    const noContentPic = `${window.appGlobal.publicPath}/images/empty-box.png`;

    const state = reactive({
      total: 0,
      offset: 0,
      actors: [],
      isLoading: false,
    });

    const fetchData = () => {
      state.isLoading = true;
      state.actors = [];
      http
        .getAll({
          name: nameFilter.value,
          job: jobFilter.value?.label,
        })
        .then((response) => {
          const allActors = response.data;
          state.total = allActors.length;

          if (state.total === 0) {
            noRecordsFound();
          } else {
            if (allActors.length > 0) {
              state.actors.push(allActors[0]);
            }

            for (let i = 1; i < allActors.length; i++) {
              setTimeout(() => {
                state.actors.push(allActors[i]);
              }, i * 30);
            }
          }
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    return {
      http,
      state,
      nameFilter,
      jobFilter,
      noContentPic,
      fetchData,
    };
  },
  methods: {
    onClickAdd() {
      navigate(`/${window.appGlobal.theme}/admin/saveActor`);
    },
    onUpdate() {
      this.fetchData();
    },
    onClickSave(updatedActor, id) {
      this.state.isLoading = true;
      let actorData = updatedActor;
      this.http
        .update(id, {...actorData})
        .then(() => {
          this.$toast.saveSuccess();
          this.fetchData();
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error.response.data.message);
        })
        .finally(() => {
          this.state.isLoading = false;
        });
    },
    onClickDelete(id) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(id);
        }
      });
    },
    deleteItems(id) {
      if (id) {
        this.state.isLoading = true;
        this.http
          .delete(id)
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.state.actors = this.state.actors.filter(
              (actor) => actor.id !== id,
            );
            this.state.isLoading = false;
          });
      }
    },
    async filterItems() {
      this.fetchData();
    },
    onClickReset() {
      this.nameFilter = null;
      this.jobFilter = null;
      this.filterItems();
    },
  },
};
</script>
