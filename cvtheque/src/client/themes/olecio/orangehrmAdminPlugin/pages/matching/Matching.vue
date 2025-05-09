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
              <job-autocomplete v-model="jobFilter" />
            </oxd-grid-item>
            <oxd-grid-item>
              <course-autocomplete v-model="courseFilter" />
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
    <div v-else v-for="(matching, index) in state.matchings" :key="index">
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
            :departments-options="departments"
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
import JobAutocomplete from '@/core/components/inputs/JobAutocomplete.vue';
import CourseAutocomplete from '@/core/components/inputs/CourseAutocomplete.vue';
import {OxdSpinner} from '@ohrm/oxd';

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'matching-card': MatchingCard,
    'table-filter': TableFilter,
    'oxd-loading-spinner': OxdSpinner,
    'job-autocomplete': JobAutocomplete,
    'course-autocomplete': CourseAutocomplete,
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
    departments: {
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
          actor: actorFilter.value?.label,
          job: jobFilter.value?.label,
          courseId: courseFilter.value?.id,
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
      fetchData,
    };
  },
  methods: {
    onClickAdd() {
      navigate(`/${window.appGlobal.theme}/admin/saveMatching`);
    },
    onClickSave(updatedMatching, id) {
      this.state.isLoading = true;
      let matchingData = updatedMatching;
      if (
        !updatedMatching.startBreakDate ||
        !updatedMatching.startBreakDate.dayOfWeek ||
        updatedMatching.startBreakDate.dayOfWeek === null ||
        !updatedMatching.startBreakDate.hour ||
        updatedMatching.startBreakDate.hour === null ||
        !updatedMatching.startBreakDate.minutes ||
        updatedMatching.startBreakDate.minutes === null
      )
        matchingData.startBreakDate = null;
      if (
        !updatedMatching.endBreakDate ||
        !updatedMatching.endBreakDate.dayOfWeek ||
        updatedMatching.endBreakDate.dayOfWeek === null ||
        !updatedMatching.endBreakDate.hour ||
        updatedMatching.endBreakDate.hour === null ||
        !updatedMatching.endBreakDate.minutes ||
        updatedMatching.endBreakDate.minutes === null
      )
        matchingData.endBreakDate = null;
      if (updatedMatching.departments) {
        matchingData.departments = updatedMatching.departments.map(
          (department) => department.id,
        );
      }
      if (updatedMatching.courses) {
        matchingData.courses = updatedMatching.courses.reduce((map, course) => {
          const courseId = !isNaN(parseInt(course.id))
            ? parseInt(course.id)
            : null;
          if (courseId !== null) {
            map[courseId] = course.label;
          }
          return map;
        }, {});
      }
      this.http
        .update(id, {...matchingData})
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
            this.state.matchings = this.state.matchings.filter(
              (matching) => matching.id !== id,
            );
            this.state.isLoading = false;
          });
      }
    },
    async filterItems() {
      this.fetchData();
    },
    onClickReset() {
      this.titleFilter = null;
      this.actorFilter = null;
      this.jobFilter = null;
      this.courseFilter = null;
      this.filterItems();
    },
  },
};
</script>
