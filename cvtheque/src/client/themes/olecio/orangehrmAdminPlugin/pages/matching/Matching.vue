<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('Filtres')">
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
    <!--
          <oxd-text
      v-if="state.matchings.length == 0"
      class="oxd-label"
      :style="{
        fontFamily: 'Nunito Sans, sans-serif',
        fontSize: '18px',
        fontWeight: '600',
        color: 'var(--oxd-interface-gray-darken-1-color, #64728c)',
        marginBottom: '0.5rem',
        marginLeft: '0.5rem',
      }"
    >
      Effectuez une recherche pour consulter les matchings
    </oxd-text>
  -->
    <div class="orangehrm-corporate-directory">
      <div class="orangehrm-paper-container">
        <div
          v-if="!state.isLoading && state.matchings.length == 0"
          class="orangehrm-corporate-directory-nocontent"
          style="display: flex; flex-direction: row; align-items: center"
        >
          <img
            :src="noContentPic"
            alt="No Content"
            style="max-width: 60px; margin: 0.85rem"
          />
          <oxd-text tag="p">
            Effectuez une recherche pour consulter les matchings
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
    <div v-for="(matching, index) in state.matchings" v-else :key="index">
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
    const noContentPic = `${window.appGlobal.publicPath}/images/empty-box.png`;

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
          const allMatchings = response.data;
          state.total = allMatchings.length;

          if (state.total === 0) {
            noRecordsFound();
          } else {
            if (allMatchings.length > 0) {
              state.matchings.push(allMatchings[0]);
            }

            for (let i = 1; i < allMatchings.length; i++) {
              setTimeout(() => {
                state.matchings.push(allMatchings[i]);
              }, i * 30);
            }
          }
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    // onMounted(() => {
    //   fetchData();
    // });

    return {
      http,
      state,
      titleFilter,
      actorFilter,
      jobFilter,
      noContentPic,
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
      if (updatedMatching.courses && updatedMatching.courses.length > 0) {
        matchingData.courses = updatedMatching.courses.reduce((map, course) => {
          const courseId = !isNaN(parseInt(course.id))
            ? parseInt(course.id)
            : null;
          if (courseId !== null) {
            map[courseId] = course.label;
          }
          return map;
        }, {});
      } else {
        matchingData.courses = null;
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
