<template>
  <div
    class="view-lead-overlay"
    :class="{active: isVisible}"
    @click.self="handleClose"
  >
    <div class="view-lead-panel" :class="{active: isVisible}">
      <div class="view-lead-panel-header">
        <oxd-icon-button
          name="x-lg"
          class="view-lead-header-btn"
          title="Fermer"
          @click="handleClose"
        />
      </div>
      <lead-profile
        class="view-lead-profile"
        is-create-mode
        :lead="emptyLead"
        :default-columns="defaultColumns"
        :custom-columns="customColumns"
        :contact-log-types="contactLogTypes"
        :scope-options="scopeOptions"
        :lead-select-options="leadSelectOptions"
        :of-options="ofOptions"
        @created="onCreated"
      />
    </div>
    <confirmation-dialog
      ref="confirmCloseDialog"
      :title="$t('Fermer la création')"
      :subtitle="
        $t(
          'Les informations saisies ne seront pas enregistrées. Voulez-vous vraiment fermer ?',
        )
      "
      :cancel-label="$t('general.no_cancel')"
      :confirm-label="$t('Oui, fermer')"
      confirm-button-type="secondary"
    />
  </div>
</template>

<script>
import LeadProfile from './LeadProfile.vue';
import ConfirmationDialog from '@/core/components/dialogs/ConfirmationDialog';

const TRANSITION_DURATION = 300;

export default {
  components: {
    'lead-profile': LeadProfile,
    'confirmation-dialog': ConfirmationDialog,
  },
  props: {
    defaultColumns: {
      type: Object,
      default: () => ({}),
    },
    customColumns: {
      type: Array,
      default: () => [],
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    scopeOptions: {
      type: Array,
      default: () => [],
    },
    leadSelectOptions: {
      type: Object,
      default: () => ({
        needs: [],
        courseStarts: [],
        studyLevels: [],
        countries: [],
        fundings: [],
        handicaps: [],
        status: [],
        trainingMethods: [],
        sources: [],
        timeSlots: [],
        professionalExperiences: [],
      }),
    },
    ofOptions: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['close', 'created'],
  data() {
    return {
      isVisible: false,
      emptyLead: {id: 0},
    };
  },
  mounted() {
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        this.isVisible = true;
      });
    });
  },
  methods: {
    handleClose() {
      this.$refs.confirmCloseDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.closePanel();
        }
      });
    },
    closePanel() {
      this.isVisible = false;
      setTimeout(() => this.$emit('close'), TRANSITION_DURATION);
    },
    onCreated() {
      this.$emit('created');
      this.closePanel();
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

.view-lead-profile {
  padding: 0rem !important;
}

.view-lead-profile :deep(.orangehrm-card-container) {
  border-radius: 0rem !important;
}
</style>
