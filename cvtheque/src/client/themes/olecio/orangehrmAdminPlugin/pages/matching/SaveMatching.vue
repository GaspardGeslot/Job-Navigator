<template>
  <div class="orangehrm-background-container">
    <div>
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
        :matching-current="matchingCurrent"
        :is-adding="true"
        :is-duplicating="isDuplicating"
        :departments-options="departments"
        :is-loading="isLoading"
        @cancel="onClickCancel"
        @save="(updatedMatching) => onClickSave(updatedMatching)"
      />
    </div>
  </div>
</template>
<script>
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import MatchingCard from '../../components/MatchingCard.vue';

export default {
  components: {
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
    departments: {
      type: Array,
      default: () => [],
    },
    matchingCurrent: {
      type: Object,
      default: null,
    },
    isDuplicating: {
      type: Boolean,
      default: false,
    },
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/matching`,
    );

    return {
      http,
    };
  },
  data() {
    return {
      isLoading: false,
    };
  },
  // mounted() {
  //   console.log('SaveMatching - matchingCurrent:', this.matchingCurrent);
  //   console.log('SaveMatching - isDuplicating:', this.isDuplicating);
  // },
  methods: {
    onClickCancel() {
      navigate(`/${window.appGlobal.theme}/admin/matching`);
    },
    onClickSave(updatedMatching) {
      this.isLoading = true;
      let matchingData = JSON.parse(JSON.stringify(updatedMatching));
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

      // En mode duplication, on supprime l'ID pour créer un nouveau matching
      if (this.isDuplicating) {
        delete matchingData.id;
      }

      this.http
        .create({...matchingData})
        .then(() => {
          this.$toast.saveSuccess();
          navigate(`/${window.appGlobal.theme}/admin/matching`);
        })
        .catch((error) => {
          // console.log('Error:', error);
          // console.log('Response:', error.response);
          // console.log('Data:', error.response?.data);
          if (matchingData.startBreakDate === null) {
            matchingData.startBreakDate = {
              dayOfWeek: null,
              hour: null,
              minutes: null,
            };
          }

          if (matchingData.endBreakDate === null) {
            matchingData.endBreakDate = {
              dayOfWeek: null,
              hour: null,
              minutes: null,
            };
          }

          matchingData.startBreakDate.dayOfWeek = '';
          matchingData.startBreakDate.hour = '';
          matchingData.startBreakDate.minutes = '';

          matchingData.endBreakDate.dayOfWeek = '';
          matchingData.endBreakDate.hour = '';
          matchingData.endBreakDate.minutes = '';
          return this.$toast.unexpectedError(error.response.data.message);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>
