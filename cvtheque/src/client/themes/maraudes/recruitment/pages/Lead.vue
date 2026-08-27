<template>
  <back-button></back-button>
  <lead-profile
    v-if="lead"
    :lead="lead"
    :default-columns="defaultColumns"
    :custom-columns="customColumns"
    :contact-log-types="contactLogTypes"
    :scope-options="scopeOptions"
    @update="onLeadUpdate"
  ></lead-profile>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import BackButton from '@/core/components/buttons/BackButton';
import LeadProfile from '../components/LeadProfile.vue';

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
    defaultColumns: {
      type: Object,
      required: true,
    },
    customColumns: {
      type: Array,
      required: true,
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    scopeOptions: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/admin/leads`,
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
