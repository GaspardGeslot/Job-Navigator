<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Filtres')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
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
    <div
      v-if="state.isLoading"
      class="orangehrm-header-container"
      style="justify-content: center"
    >
      <oxd-loading-spinner class="orangehrm-container-loader" />
    </div>
    <div v-else v-for="(sector, index) in state.sectors" :key="index">
      <table-filter
        :active="false"
        :filter-title="
          sector.title ? `${sector.title}` : `Secteur N°${sector.id}`
        "
      >
        <div class="orangehrm-container">
          <sector-card
            :sector-current="sector"
            @delete="onClickDelete(sector.id)"
            @save="(updatedSector) => onClickSave(updatedSector, sector.id)"
          />
        </div>
      </table-filter>
      <br />
    </div>
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
  </div>
</template>
<script>
import {ref, reactive, onMounted} from 'vue';
import useToast from '@/core/util/composable/useToast';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import SectorCard from '../../components/SectorCard.vue';
import TableFilter from '@/core/components/dropdown/TableFilter.vue';
import JobAutocomplete from '@/core/components/inputs/JobAutocomplete.vue';
import {OxdSpinner} from '@ohrm/oxd';

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'sector-card': SectorCard,
    'table-filter': TableFilter,
    'oxd-loading-spinner': OxdSpinner,
    'job-autocomplete': JobAutocomplete,
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/sector`,
    );
    const {noRecordsFound} = useToast();
    const jobFilter = ref(null);

    const state = reactive({
      total: 0,
      offset: 0,
      sectors: [],
      isLoading: false,
    });

    const fetchData = () => {
      state.isLoading = true;
      state.sectors = [];
      http
        .getAll({
          job: jobFilter.value?.label,
        })
        .then((response) => {
          state.sectors = response.data;
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    onMounted(() => {
      fetchData();
    });

    return {
      http,
      state,
      jobFilter,
      fetchData,
    };
  },
  methods: {
    onClickAdd() {
      navigate(`/${window.appGlobal.theme}/admin/saveSector`);
    },
    onClickSave(updatedSector, id) {
      this.state.isLoading = true;
      let sectorData = updatedSector;
      this.http
        .update(id, {...sectorData})
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
            this.state.sectors = this.state.sectors.filter(
              (sector) => sector.id !== id,
            );
            this.state.isLoading = false;
          });
      }
    },
    async filterItems() {
      this.fetchData();
    },
    onClickReset() {
      this.jobFilter = null;
      this.filterItems();
    },
  },
};
</script>
