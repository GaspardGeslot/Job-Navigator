<template>
  <edit-employee-layout :employee-id="empNumber" screen="candidate-and-account">
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <oxd-form :loading="isLoadingStatus" @submit-valid="onSaveSearchStatus">
        <oxd-form-row>
          <oxd-text tag="h6" class="orangehrm-main-title">{{
            $t('Candidature et compte')
          }}</oxd-text>
          <oxd-divider />
        </oxd-form-row>

        <oxd-form-row>
          <oxd-text tag="h6" class="orangehrm-sub-title">{{
            $t('Etat de la candidature')
          }}</oxd-text>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item class="radio-option-item">
              <oxd-input-field
                v-model="candidateSearchStatusDraft"
                type="radio"
                :label="$t(`Je suis à la recherche d'un nouveau poste`)"
                value="searching"
                name="candidate-search-status"
                @update:model-value="onSearchStatusChange"
              />
            </oxd-grid-item>
            <oxd-grid-item class="radio-option-item">
              <oxd-input-field
                v-model="candidateSearchStatusDraft"
                type="radio"
                :label="$t(`Je ne suis plus à la recherche d'un nouveau poste`)"
                value="not-searching"
                name="candidate-search-status"
                @update:model-value="onSearchStatusChange"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-form-actions>
          <oxd-button
            display-type="secondary"
            :label="$t('general.save')"
            type="submit"
            :disabled="!canSaveSearchStatus"
          />
        </oxd-form-actions>
      </oxd-form>

      <oxd-divider />

      <oxd-form
        :loading="isDeletingAccount"
        @submit-valid="openDeleteConfirmDialog"
      >
        <oxd-form-row>
          <oxd-text tag="h6" class="orangehrm-sub-title">{{
            $t('Suppression du compte')
          }}</oxd-text>
        </oxd-form-row>
        <oxd-form-actions>
          <oxd-button
            display-type="label-danger"
            :label="$t('Supprimer mon compte')"
            type="submit"
          />
        </oxd-form-actions>
      </oxd-form>
    </div>

    <teleport to="#app">
      <oxd-dialog
        v-if="showStopSearchDialog"
        class="orangehrm-dialog-popup"
        @update:show="onCancelStopSearchDialog"
      >
        <div class="orangehrm-modal-header">
          <oxd-text type="card-title">{{
            $t("Motif d'arrête de recherche")
          }}</oxd-text>
        </div>
        <oxd-form>
          <oxd-grid-item
            v-for="(option, optionIndex) in stopSearchReasonOptions"
            :key="`${optionIndex}-${option.id}`"
            class="radio-option-item"
          >
            <oxd-input-field
              v-model="stopSearchReason"
              type="radio"
              :label="option.label"
              :value="option.id"
              name="stop-search-reason"
            />
          </oxd-grid-item>
          <oxd-grid-item v-if="showStopSearchDetailField">
            <oxd-input-field
              v-model.trim="stopSearchReasonDetail"
              :label="$t('Preciser')"
              required
              :maxlength="150"
            />
          </oxd-grid-item>
        </oxd-form>
        <div class="orangehrm-modal-footer">
          <oxd-button
            :label="$t('general.cancel')"
            display-type="ghost"
            class="orangehrm-button-margin"
            @click="onCancelStopSearchDialog"
          />
          <oxd-button
            :label="$t('general.confirm')"
            display-type="secondary"
            class="orangehrm-button-margin"
            :disabled="!canConfirmStopSearchReason"
            @click="onConfirmStopSearchReason"
          />
        </div>
      </oxd-dialog>
    </teleport>

    <teleport to="#app">
      <oxd-dialog
        v-if="showDeleteConfirmDialog"
        class="orangehrm-dialog-popup"
        @update:show="onCancelDeleteConfirmDialog"
      >
        <div class="orangehrm-modal-header">
          <oxd-text type="card-title">{{
            $t('Confirmation de suppression')
          }}</oxd-text>
        </div>
        <div class="orangehrm-text-center-align">
          <oxd-text type="card-body">
            {{
              $t(
                'Voulez-vous vraiment supprimer votre compte ? Toutes vos candidatures seront egalement supprimees.',
              )
            }}
          </oxd-text>
        </div>
        <div class="orangehrm-modal-footer">
          <oxd-button
            :label="$t('general.cancel')"
            display-type="ghost"
            class="orangehrm-button-margin"
            @click="onCancelDeleteConfirmDialog"
          />
          <oxd-button
            :label="$t('general.confirm')"
            display-type="label-danger"
            class="orangehrm-button-margin"
            @click="openDeleteFinalDialog"
          />
        </div>
      </oxd-dialog>
    </teleport>

    <teleport to="#app">
      <oxd-dialog
        v-if="showDeleteFinalDialog"
        class="orangehrm-dialog-popup"
        @update:show="onCancelDeleteFinalDialog"
      >
        <div class="orangehrm-modal-header">
          <oxd-text type="card-title">{{
            $t('Derniere confirmation')
          }}</oxd-text>
        </div>
        <div class="orangehrm-text-center-align">
          <oxd-text type="card-body">
            {{
              $t(
                'Cette suppression est definitive. Confirmez-vous la suppression du compte et la deconnexion ?',
              )
            }}
          </oxd-text>
        </div>
        <div class="orangehrm-modal-footer">
          <oxd-button
            :label="$t('general.cancel')"
            display-type="ghost"
            class="orangehrm-button-margin"
            @click="onCancelDeleteFinalDialog"
          />
          <oxd-button
            :label="$t('Supprimer definitivement')"
            display-type="label-danger"
            class="orangehrm-button-margin"
            @click="onConfirmDeleteAccount"
          />
        </div>
      </oxd-dialog>
    </teleport>
  </edit-employee-layout>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';
import EditEmployeeLayout from '../../components/EditEmployeeLayout';
import {APIService} from '@/core/util/services/api.service';

const STOP_SEARCH_REASON_OPTIONS = [
  {
    id: 'found-via-job-navigator',
    label: "J'ai trouvé un emploi via Job Navigator (cette plateforme)",
  },
  {
    id: 'found-via-other-platform',
    label: "J'ai trouvé un emploi à travers une autre plateforme (Préciser)",
  },
  {
    id: 'no-longer-looking',
    label: "Je ne recherche plus d'emploi",
  },
  {
    id: 'other',
    label: 'Autre (Préciser)',
  },
];

export default {
  components: {
    'edit-employee-layout': EditEmployeeLayout,
    'oxd-dialog': OxdDialog,
  },

  props: {
    empNumber: {
      type: String,
      required: true,
    },
  },

  setup() {
    const applicationStatusHttp = new APIService(
      window.appGlobal.baseUrl,
      `/${window.appGlobal.theme}/api/v2/user/application-status`,
    );
    const userHttp = new APIService(
      window.appGlobal.baseUrl,
      `/${window.appGlobal.theme}/api/v2/user`,
    );

    return {
      applicationStatusHttp,
      userHttp,
    };
  },

  data() {
    return {
      isLoadingStatus: false,
      isDeletingAccount: false,
      showStopSearchDialog: false,
      showDeleteConfirmDialog: false,
      showDeleteFinalDialog: false,
      candidateSearchStatusInitial: 'searching',
      candidateSearchStatusDraft: 'searching',
      stopSearchReason: '',
      stopSearchReasonDetail: '',
      selectedStopSearchReasonPayload: null,
      stopSearchReasonOptions: STOP_SEARCH_REASON_OPTIONS,
    };
  },

  computed: {
    canSaveSearchStatus() {
      return (
        this.candidateSearchStatusDraft !== this.candidateSearchStatusInitial
      );
    },
    showStopSearchDetailField() {
      return ['found-via-other-platform', 'other'].includes(
        this.stopSearchReason,
      );
    },
    canConfirmStopSearchReason() {
      if (!this.stopSearchReason) {
        return false;
      }
      if (this.showStopSearchDetailField) {
        return !!this.stopSearchReasonDetail?.trim();
      }
      return true;
    },
  },

  beforeMount() {
    this.fetchInitialCandidateStatus();
  },

  methods: {
    async fetchInitialCandidateStatus() {
      const {data} = await this.applicationStatusHttp.getAll();
      const isSearching = data?.data?.isSearching;
      this.candidateSearchStatusInitial = isSearching
        ? 'searching'
        : 'not-searching';
      this.candidateSearchStatusDraft = this.candidateSearchStatusInitial;
    },
    onSearchStatusChange(nextValue) {
      if (
        this.candidateSearchStatusInitial === 'searching' &&
        nextValue === 'not-searching'
      ) {
        this.openStopSearchDialog();
      } else {
        this.selectedStopSearchReasonPayload = null;
      }
    },
    openStopSearchDialog() {
      this.stopSearchReason = '';
      this.stopSearchReasonDetail = '';
      this.showStopSearchDialog = true;
    },
    onCancelStopSearchDialog() {
      this.showStopSearchDialog = false;
      this.candidateSearchStatusDraft = this.candidateSearchStatusInitial;
      this.stopSearchReason = '';
      this.stopSearchReasonDetail = '';
      this.selectedStopSearchReasonPayload = null;
    },
    onConfirmStopSearchReason() {
      if (!this.canConfirmStopSearchReason) {
        return;
      }
      this.selectedStopSearchReasonPayload = {
        reason: this.stopSearchReason,
        detail: this.showStopSearchDetailField
          ? this.stopSearchReasonDetail.trim()
          : null,
      };
      this.showStopSearchDialog = false;
    },
    async onSaveSearchStatus() {
      this.isLoadingStatus = true;
      try {
        const isSearching = this.candidateSearchStatusDraft === 'searching';
        const reason =
          !isSearching && this.selectedStopSearchReasonPayload
            ? this.buildInactiveReasonString(
                this.selectedStopSearchReasonPayload,
              )
            : null;
        await this.applicationStatusHttp.request({
          method: 'PUT',
          data: {reason},
        });
        this.candidateSearchStatusInitial = this.candidateSearchStatusDraft;
        this.selectedStopSearchReasonPayload = null;
        await this.$toast.updateSuccess();
      } finally {
        this.isLoadingStatus = false;
      }
    },
    openDeleteConfirmDialog() {
      this.showDeleteConfirmDialog = true;
    },
    onCancelDeleteConfirmDialog() {
      this.showDeleteConfirmDialog = false;
    },
    openDeleteFinalDialog() {
      this.showDeleteConfirmDialog = false;
      this.showDeleteFinalDialog = true;
    },
    onCancelDeleteFinalDialog() {
      this.showDeleteFinalDialog = false;
    },
    async onConfirmDeleteAccount() {
      this.isDeletingAccount = true;
      this.showDeleteFinalDialog = false;
      try {
        await this.userHttp.request({
          method: 'DELETE',
        });
        await this.$toast.deleteSuccess();
        const appBaseUrl = `${window.location.origin}/cvtheque/web/index.php`;
        const logoutUrl = `${appBaseUrl}/${window.appGlobal.theme}/auth/logout`;
        const homeUrl = `${appBaseUrl}/${window.appGlobal.theme}/candidature/index`;
        await fetch(logoutUrl, {
          method: 'GET',
          credentials: 'include',
        });
        window.location.href = homeUrl;
      } finally {
        this.isDeletingAccount = false;
      }
    },
    buildInactiveReasonString(payload) {
      const option = this.stopSearchReasonOptions.find(
        (opt) => opt.id === payload.reason,
      );
      const baseLabel = option ? option.label : '';
      if (payload.detail) {
        return `${baseLabel} : ${payload.detail}`;
      }
      return baseLabel;
    },
  },
};
</script>

<style src="./employee.scss" lang="scss" scoped></style>

<style scoped>
.radio-option-item .oxd-input-group {
  display: flex !important;
  flex-direction: row-reverse !important;
  justify-content: flex-end !important;
  gap: 0 !important;
}

.radio-option-item .oxd-input-group input[type='radio'] {
  margin-right: 0.5em !important;
  margin-left: 0 !important;
}

.radio-option-item .oxd-input-group label {
  margin: 0 !important;
}
</style>
