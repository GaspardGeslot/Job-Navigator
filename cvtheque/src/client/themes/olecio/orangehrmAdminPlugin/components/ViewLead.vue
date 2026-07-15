<template>
  <div
    class="view-lead-overlay"
    :class="{active: isVisible}"
    @click.self="handleClose"
  >
    <div class="view-lead-panel" :class="{active: isVisible}">
      <div class="view-lead-panel-header">
        <oxd-icon-button
          name="box-arrow-up-right"
          class="view-lead-header-btn"
          title="Ouvrir la fiche en page dédiée"
          @click="openLeadDedicatedPage"
        />
        <oxd-icon-button
          name="x-lg"
          class="view-lead-header-btn"
          title="Fermer"
          @click="handleClose"
        />
      </div>
      <div v-if="isLoading" class="view-lead-loading">
        <oxd-loading-spinner />
      </div>
      <lead-profile
        v-else-if="lead"
        class="view-lead-profile"
        :lead="lead"
        :all-statuses="allStatuses"
        :actor-statuses="actorStatuses"
        :contact-log-types="contactLogTypes"
        :default-columns="defaultColumns"
        :all-study-levels="allStudyLevels"
        :actor-study-levels="actorStudyLevels"
        :lead-select-options="leadSelectOptions"
        @update="onLeadUpdate"
      />
    </div>
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import {OxdSpinner} from '@ohrm/oxd';
import LeadProfile from './LeadProfile.vue';

const TRANSITION_DURATION = 300;

export default {
  components: {
    'lead-profile': LeadProfile,
    'oxd-loading-spinner': OxdSpinner,
  },
  props: {
    leadId: {
      type: Number,
      required: true,
    },
    allStatuses: {
      type: Array,
      default: () => [],
    },
    allStudyLevels: {
      type: Array,
      default: () => [],
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    leadSelectOptions: {
      type: Object,
      default: () => ({}),
    },
  },
  emits: ['close', 'open-full-page'],
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/admin/leads`,
    );
    return {http};
  },
  data() {
    return {
      lead: null,
      actorStatuses: [],
      actorStudyLevels: [],
      defaultColumns: null,
      isLoading: false,
      isVisible: false,
    };
  },
  watch: {
    leadId: {
      immediate: true,
      handler() {
        this.onLeadUpdate();
      },
    },
  },
  mounted() {
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        this.isVisible = true;
      });
    });
  },
  methods: {
    openLeadDedicatedPage() {
      this.$emit('open-full-page');
    },
    handleClose() {
      this.isVisible = false;
      setTimeout(() => this.$emit('close'), TRANSITION_DURATION);
    },
    onLeadUpdate() {
      this.isLoading = true;
      this.http
        .get(this.leadId)
        .then(({data: lead}) => {
          if (!lead?.id) {
            this.handleClose();
            return;
          }
          this.lead = lead;
          return this.http
            .request({
              method: 'GET',
              url: `/${window.appGlobal.theme}/api/v2/admin/leads/${this.leadId}/options`,
            })
            .then(({data: options}) => {
              this.actorStatuses = options.actorStatuses || [];
              this.actorStudyLevels = options.actorStudyLevels || [];
              this.defaultColumns = options.defaultColumns || null;
            });
        })
        .catch(() => {
          this.handleClose();
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>

<style scoped>
.view-lead-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0);
  z-index: 280;
  display: flex;
  justify-content: flex-end;
  transition: background-color 0.3s ease;
}

.view-lead-overlay.active {
  background-color: rgba(0, 0, 0, 0.5);
}

.view-lead-panel {
  width: 80%;
  max-width: 1200px;
  height: 100%;
  background-color: #f6f5fb;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  transform: translateX(100%);
  transition: transform 0.3s ease;
}

.view-lead-profile {
  padding: 0rem !important;
}

.view-lead-profile :deep(.orangehrm-card-container) {
  border-radius: 0rem !important;
}

.view-lead-panel.active {
  transform: translateX(0);
}

.view-lead-panel-header {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #ffffff;
  padding: 0.5rem 1rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.25rem;
  border-bottom: 1px solid #eaebee;
}

.view-lead-header-btn {
  cursor: pointer;
}

.view-lead-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  flex: 1;
}
</style>
