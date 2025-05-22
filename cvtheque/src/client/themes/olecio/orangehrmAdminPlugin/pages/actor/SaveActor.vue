<template>
  <div class="orangehrm-background-container">
    <div>
      <actor-card
        :countries="countries"
        :fundings="fundings"
        :study-levels="studyLevels"
        :needs="needs"
        :status="status"
        :training-methods="trainingMethods"
        :sources="sources"
        :actor-current="actor"
        :is-adding="true"
        :is-loading="isLoading"
        @cancel="onClickCancel"
        @save="(updatedActor) => onClickSave(updatedActor)"
      />
    </div>
  </div>
</template>
<script>
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import ActorCard from '../../components/ActorCard.vue';

export default {
  components: {
    'actor-card': ActorCard,
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
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/actor`,
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
      navigate(`/${window.appGlobal.theme}/admin/viewActor`);
    },
    onClickSave(updatedActor) {
      this.isLoading = true;
      let actorData = updatedActor;
      this.http
        .create({...actorData})
        .then(() => {
          this.$toast.saveSuccess();
          navigate(`/${window.appGlobal.theme}/admin/viewActor`);
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error.response.data.message);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>
