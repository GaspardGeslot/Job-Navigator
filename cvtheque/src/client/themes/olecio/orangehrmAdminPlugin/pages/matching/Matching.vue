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
    <div v-for="(matching, index) in matchings" :key="index">
      <oxd-table-filter
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
            @edit="onClickEdit(matching)"
            @delete="onClickDelete(matching)"
          />
        </div>
      </oxd-table-filter>
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
      `${window.appGlobal.theme}/api/v2/admin/matchings`,
    );
    const {noRecordsFound} = useToast();
    const titleFilter = ref(null);
    const actorFilter = ref(null);
    const jobFilter = ref(null);
    const courseFilter = ref(null);

    const state = reactive({
      total: 0,
      offset: 0,
      //matchings: [],
      isLoading: false,
    });

    const fetchData = () => {
      state.isLoading = true;
      //state.matchings = [];
      http
        .getAll({
          title: titleFilter.value,
          actor: actorFilter.value,
          job: jobFilter.value,
          courseId: courseFilter.value,
        })
        .then((response) => {
          //state.matchings = response.data;
          //console.log('Matchings : ', state.matchings);
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
      state,
      titleFilter,
      actorFilter,
      jobFilter,
      courseFilter,
    };
  },
  created() {
    this.matchings = [
      {
        id: 0,
        isActive: true,
        actor: 'Actor 1',
        ages: [
          {
            min: 18,
            max: 25,
          },
        ],
        countries: ['Belgique'],
        courseStarts: ['Dans plus de 6 mois'],
        courses: [],
        departments: ['75'],
        needs: ["J'ai arrêté ou je souhaite arrêter l'école", 'Temp 3, Temp 4'],
        endBreakDate: {
          dayOfWeek: 5,
          hour: 12,
          minutes: 5,
        },
        startBreakDate: {
          dayOfWeek: 7,
          hour: 23,
          minutes: 0,
        },
        endDate: '2021-09-30',
        startDate: '2021-09-01',
        fundings: ['Un mix de tout ça'],
        handicaps: ['Aucun'],
        title: 'Matching 1',
        jobs: ['Développeur'],
        levels: ['Bac +5', 'Bac +3', 'Bac +1'],
        locationPostalCodes: ['75015'],
        maxAmountPerDay: 42,
        phones: ['+33'],
        price: 15.27,
        resumeNeeded: false,
        status: ['En formation'],
        trainingMethods: ['En présentiel'],
        isResumeNeeded: true,
      },
      {
        id: 1,
        isActive: false,
        actor: 'Actor 2',
        ages: [],
        countries: [],
        courseStarts: [],
        courses: [],
        departments: [],
        needs: [],
        endBreakDate: null,
        startBreakDate: null,
        endDate: null,
        startDate: null,
        fundings: [],
        handicaps: [],
        title: null,
        jobs: [],
        levels: [],
        locationPostalCodes: [],
        maxAmountPerDay: 0,
        phones: [],
        price: 15,
        resumeNeeded: true,
        status: [],
        trainingMethods: [],
      },
    ];
  },
  methods: {
    onClickAdd() {
      navigate('/admin/saveSystemUser');
    },
    onClickEdit(item) {
      navigate('/admin/saveSystemUser/{id}', {id: item.id});
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items?.data[index].id;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(ids);
        }
      });
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
          this.deleteItems([item.id]);
        }
      });
    },
    deleteItems(items) {
      if (items instanceof Array) {
        this.isLoading = true;
        this.http
          .deleteAll({
            ids: items,
          })
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.isLoading = false;
            this.resetDataTable();
          });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
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
