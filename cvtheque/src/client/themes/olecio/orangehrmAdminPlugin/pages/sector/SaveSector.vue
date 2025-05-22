<template>
  <div class="orangehrm-background-container">
    <div>
      <sector-card
        :sector-current="sector"
        :is-adding="true"
        :is-loading="isLoading"
        @cancel="onClickCancel"
        @save="(updatedSector) => onClickSave(updatedSector)"
      />
    </div>
  </div>
</template>
<script>
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import SectorCard from '../../components/SectorCard.vue';

export default {
  components: {
    'sector-card': SectorCard,
  },

  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/sector`,
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
      navigate(`/${window.appGlobal.theme}/admin/viewSectors`);
    },
    onClickSave(updatedSector) {
      this.isLoading = true;
      let sectorData = updatedSector;
      this.http
        .create({...sectorData})
        .then(() => {
          this.$toast.saveSuccess();
          navigate(`/${window.appGlobal.theme}/admin/viewSectors`);
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
