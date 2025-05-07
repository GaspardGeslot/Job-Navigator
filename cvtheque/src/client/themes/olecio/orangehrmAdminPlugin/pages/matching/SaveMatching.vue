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
        :matching-current="matching"
        :is-adding="true"
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
  methods: {
    onClickCancel() {
      navigate(`/${window.appGlobal.theme}/admin/matching`);
    },
    onClickSave(updatedMatching) {
      this.isLoading = true;
      if (
        !updatedMatching.startBreakDate ||
        !updatedMatching.startBreakDate.dayOfWeek ||
        updatedMatching.startBreakDate.dayOfWeek === null ||
        !updatedMatching.startBreakDate.hour ||
        updatedMatching.startBreakDate.hour === null ||
        !updatedMatching.startBreakDate.minutes ||
        updatedMatching.startBreakDate.minutes === null
      )
        updatedMatching.startBreakDate = null;
      if (
        !updatedMatching.endBreakDate ||
        !updatedMatching.endBreakDate.dayOfWeek ||
        updatedMatching.endBreakDate.dayOfWeek === null ||
        !updatedMatching.endBreakDate.hour ||
        updatedMatching.endBreakDate.hour === null ||
        !updatedMatching.endBreakDate.minutes ||
        updatedMatching.endBreakDate.minutes === null
      )
        updatedMatching.endBreakDate = null;
      this.http.create({...updatedMatching}).then((response) => {
        this.isLoading = false;
        this.$toast.saveSuccess();
        navigate(`/${window.appGlobal.theme}/admin/matching`);
      });
    },
  },
};
</script>
