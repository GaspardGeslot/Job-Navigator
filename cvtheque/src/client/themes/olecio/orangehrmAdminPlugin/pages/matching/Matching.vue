<template>
  <div class="orangehrm-background-container">
    <!--<oxd-table-filter :filter-title="$t('admin.system_users')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="4" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.username"
                :label="$t('general.username')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.userRoleId"
                type="select"
                :label="$t('general.user_role')"
                :options="userRoles"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <employee-autocomplete
                v-model="filters.empNumber"
                :rules="rules.employee"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.status"
                type="select"
                :label="$t('general.status')"
                :options="userStatuses"
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
    <br />-->
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
            :countries="countries"
            :course-starts="courseStarts"
            :fundings="fundings"
            :handicaps="handicaps"
            :study-levels="studyLevels"
            :needs="needs"
            :phone-numbers="phoneNumbers"
            :status="status"
            :training-methods="trainingMethods"
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
      type: Array,
      default: () => [],
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
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/matchings`,
    );
    return {
      http,
    };
  },

  data() {
    return {
      isLoading: false,
      matchings: [],
    };
  },
  created() {
    this.isLoading = true;

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
        checkedCountries: ['Brasil'],
        checkedCourseStarts: ['Immédiatement'],
        courses: [],
        departments: ['75'],
        checkedNeeds: ['CDI'],
        endBreakDate: {
          dayOfWeek: 5,
          hour: 12,
          minutes: 0,
        },
        startBreakDate: {
          dayOfWeek: 7,
          hour: 23,
          minutes: 0,
        },
        endDate: '2021-09-30',
        startDate: '2021-09-01',
        checkedFundings: ['Moi-même'],
        checkedHandicaps: ['Aucun'],
        title: 'Matching 1',
        jobs: ['Développeur'],
        checkedLevels: ['Bac +5', 'Bac +3', 'Bac +1'],
        locationPostalCodes: ['75015'],
        maxAmount: 42,
        checkedPhones: ['+33'],
        price: 15.27,
        resumeNeeded: false,
        checkedStatus: ['En formation'],
        checkedTrainingMethods: ['Présentiel'],
      },
      {
        id: 1,
        isActive: false,
        actor: 'Actor 2',
        ages: [],
        checkedCountries: [],
        checkedCourseStarts: [],
        courses: [],
        departments: [],
        checkedNeeds: [],
        endBreakDate: null,
        startBreakDate: null,
        endDate: null,
        startDate: null,
        checkedFundings: [],
        checkedHandicaps: [],
        title: null,
        jobs: [],
        checkedLevels: [],
        locationPostalCodes: [],
        maxAmount: 0,
        checkedPhones: [],
        price: 15,
        resumeNeeded: true,
        checkedStatus: [],
        checkedTrainingMethods: [],
      },
    ];
    /*this.http
      .get()
      .then((response) => {
        this.matchings = response.data;
      })
      .finally(() => {
        this.isLoading = false;
      });*/
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
