<template>
  <back-button></back-button>
  <lead-profile
    v-if="lead"
    :lead="lead"
    :statuses="statuses"
    :contact-log-types="contactLogTypes"
    :default-columns="reportingColumns ? reportingColumns.defaultColumns : null"
    @update="onLeadUpdate"
  ></lead-profile>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import BackButton from '@/core/components/buttons/BackButton';
import LeadProfile from '../../components/LeadProfile.vue';

export default {
  components: {
    'lead-profile': LeadProfile,
    'back-button': BackButton,
  },
  props: {
    leadId: {
      type: Number,
      required: true,
    },
    statuses: {
      type: Array,
      required: true,
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/leads`,
    );
    return {
      http,
    };
  },
  data() {
    return {
      lead: null,
      reportingColumns: null,
    };
  },
  beforeMount() {
    this.onLeadUpdate();
  },
  methods: {
    onLeadUpdate() {
      this.http.get(this.leadId).then(({data}) => {
        this.lead = data;
        if (this.lead && this.lead.actor) {
          this.http
            .request({
              method: 'GET',
              url: `/api/v2/actor/reporting-columns/default?actor=${encodeURIComponent(
                this.lead.actor,
              )}`,
            })
            .then(({data: reportingColumns}) => {
              this.reportingColumns = reportingColumns;
            });
        } else {
          this.reportingColumns = null;
        }
      });
    },
  },
};
</script>
