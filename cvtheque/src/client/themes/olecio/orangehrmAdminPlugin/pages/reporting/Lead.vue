<template>
  <back-button></back-button>
  <lead-profile
    v-if="lead"
    :lead="lead"
    :all-statuses="allStatuses"
    :actor-statuses="actorStatuses"
    :contact-log-types="contactLogTypes"
    :default-columns="defaultColumns"
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
    allStatuses: {
      type: Array,
      required: true,
    },
    actorStatuses: {
      type: Array,
      required: true,
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    defaultColumns: {
      type: Object,
      required: false,
      default: null,
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
    };
  },
  beforeMount() {
    this.onLeadUpdate();
  },
  methods: {
    onLeadUpdate() {
      this.http.get(this.leadId).then(({data}) => {
        this.lead = data;
      });
    },
  },
};
</script>
