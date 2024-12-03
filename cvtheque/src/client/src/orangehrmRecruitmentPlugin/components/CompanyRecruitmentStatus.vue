<template>
  <div class="orangehrm-card-container">
    <oxd-form :loading="isLoading">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('recruitment.application_stage') }}
      </oxd-text>
      <oxd-divider />
      <oxd-grid :cols="2" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="profile.companyName"
            :label="$t('company.name')"
            :disabled="true"
          />
        </oxd-grid-item>
        <oxd-grid-item v-if="profile.candidatureStatus">
          <oxd-input-field
            v-model="profile.candidatureStatus"
            :label="$t('general.status')"
            :disabled="true"
          />
        </oxd-grid-item>
      </oxd-grid>
    </oxd-form>
  </div>
</template>

<script>
const CompanyProfileModel = {
  companyName: '',
  candidatureStatus: '',
};

export default {
  name: 'CompanyRecruitmentStatus',
  props: {
    matchingId: {
      type: Number,
      required: true,
    },
    company: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      profile: {...CompanyProfileModel},
      isLoading: false,
    };
  },
  beforeMount() {
    this.fetchCompany();
  },
  methods: {
    fetchCompany() {
      this.profile.companyName = this.company.companyName;
      this.profile.candidatureStatus = this.company.candidatureStatus;
    },
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-recruitment {
  display: flex;
  justify-content: space-between;
  &-actions {
    gap: 0.4rem;
    display: flex;
    flex-wrap: wrap;
    max-width: 120px;
    margin-left: 60px;
    justify-content: flex-end;
    ::v-deep(.oxd-button--medium) {
      width: 100%;
    }
    @include oxd-respond-to('md') {
      margin-left: unset;
      max-width: unset;
      ::v-deep(.oxd-button--medium) {
        width: unset;
      }
    }
  }
}
::v-deep(.oxd-input-group) {
  margin-bottom: 1rem;
  @include oxd-respond-to('md') {
    margin-bottom: 0;
  }
}
</style>
