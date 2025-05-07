<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Besoins en recrutement')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field v-model="titleFilter" :label="$t('Titre')" />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="actorFilter"
                type="select"
                :label="$t('Acteur')"
                :options="actors"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field v-model="jobFilter" :label="$t('Métier')" />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="courseFilter"
                :label="$t('Formation (ID)')"
              />
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
    <div v-for="(matching, index) in state.matchings" :key="index">
      <table-filter
        :active="false"
        :filter-title="
          matching.title
            ? `${matching.title} - ${matching.actor}`
            : `Matching N°${matching.id} - ${matching.actor}`
        "
      >
        <div class="orangehrm-container">
          <matching-card
            :actors="actors"
            :countries="countries"
            :course-starts="courseStarts"
            :fundings="fundings"
            :handicaps="handicaps"
            :study-levels="studyLevels"
            :needs="needs"
            :phone-numbers="phoneNumbers"
            :status="status"
            :training-methods="trainingMethods"
            :professional-experiences="professionalExperiences"
            :driving-licenses="drivingLicenses"
            :matching-current="matching"
            @delete="onClickDelete(matching.id)"
            @save="
              (updatedMatching) => onClickSave(updatedMatching, matching.id)
            "
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
import MatchingCard from '../../components/MatchingCard.vue';
import TableFilter from '@/core/components/dropdown/TableFilter.vue';

const defaultFilters = {
  username: '',
  userRoleId: null,
  empNumber: null,
  status: null,
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'matching-card': MatchingCard,
    'table-filter': TableFilter,
  },
  props: {
    countries: {
      type: Array,
      default: () => [],
    },
    courseStarts: {
      type: Array,
      default: () => [],
    },
    fundings: {
      default: () => [],
      type: Array,
    },
    handicaps: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    phoneNumbers: {
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
    actors: {
      type: Array,
      default: () => [],
    },
    professionalExperiences: {
      type: Array,
      default: () => [],
    },
    drivingLicenses: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/matching`,
    );
    const {noRecordsFound} = useToast();
    const titleFilter = ref(null);
    const actorFilter = ref(null);
    const jobFilter = ref(null);
    const courseFilter = ref(null);

    const state = reactive({
      total: 0,
      offset: 0,
      matchings: [],
      isLoading: false,
    });

    const fetchData = () => {
      state.isLoading = true;
      state.matchings = [];
      http
        .getAll({
          title: titleFilter.value,
          actor: actorFilter.value,
          job: jobFilter.value,
          courseId: courseFilter.value,
        })
        .then((response) => {
          state.matchings = response.data;
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
      titleFilter,
      actorFilter,
      jobFilter,
      courseFilter,
    };
  },
  methods: {
    onClickAdd() {
      navigate(`/${window.appGlobal.theme}/admin/saveMatching`);
    },
    onClickSave(updatedMatching, id) {
      console.log('Matching : ', updatedMatching);
      console.log('ID : ', id);
    },
    onClickDelete(id) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(id);
        }
      });
    },
    deleteItems(id) {
      console.log('ID : ', id);
      if (id) {
        this.isLoading = true;
        this.http
          .delete(id)
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.isLoading = false;
            this.fetchData();
          });
      }
    },
    async filterItems() {
      await this.execQuery();
    },
    onClickReset() {
      this.filters = {...defaultFilters};
      this.filterItems();
    },
  },
};
</script>
