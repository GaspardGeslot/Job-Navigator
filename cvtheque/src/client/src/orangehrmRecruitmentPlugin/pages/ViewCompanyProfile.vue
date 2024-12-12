<template>
  <back-button></back-button>
  <company-recruitment-status
    style="margin-top: 1.5rem"
    v-if="company && company.candidatureStatus"
    :company="company"
    :matching-id="matchingId"
  >
  </company-recruitment-status>
  <company-profile
    v-if="company"
    :company="company"
    :allowed-file-types="allowedFileTypes"
    :max-file-size="maxFileSize"
    :updatable="updatable"
    @update="onCandidsateUpdate"
  ></company-profile>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import BackButton from '@/core/components/buttons/BackButton';
import CompanyRecruitmentStatus from '@/orangehrmRecruitmentPlugin/components/CompanyRecruitmentStatus';
import CompanyProfile from '@/orangehrmRecruitmentPlugin/components/CompanyProfile';

export default {
  components: {
    'company-profile': CompanyProfile,
    'company-recruitment-status': CompanyRecruitmentStatus,
    'back-button': BackButton,
  },
  props: {
    companyId: {
      type: Number,
      required: true,
    },
    matchingId: {
      type: Number,
      required: true,
    },
    maxFileSize: {
      type: Number,
      required: true,
    },
    allowedFileTypes: {
      type: Array,
      required: true,
    },
    updatable: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/companies',
    );
    return {
      http,
    };
  },
  data() {
    return {
      company: null,
    };
  },
  beforeMount() {
    this.onCandidateUpdate();
  },
  methods: {
    onCandidateUpdate() {
      this.matchingId
        ? this.http
            .get(this.companyId, {
              matchingId: this.matchingId,
            })
            .then(({data: {data}}) => {
              this.company = data;
            })
        : this.http.get(this.companyId).then(({data: {data}}) => {
            this.company = data;
          });
    },
  },
};
</script>
