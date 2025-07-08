<!--
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
 -->

<template>
  <teleport to="#app">
    <oxd-dialog
      v-if="show"
      class="orangehrm-dialog-popup"
      @update:show="onCancel"
    >
      <div class="orangehrm-modal-header">
        <oxd-text type="card-title">Motif de suppression</oxd-text>
      </div>
      <oxd-form>
        <oxd-grid-item
          v-for="(elem, elemIndex) in reasonOptions"
          :key="`${elemIndex}-${elem}`"
          class="radio-option-item"
        >
          <oxd-input-field
            v-model="reason"
            type="radio"
            :label="elem.label"
            :value="elem.label"
            name="delete-reason"
          />
        </oxd-grid-item>
        <oxd-grid-item v-if="showOtherPlatformField">
          <oxd-input-field
            v-model="otherPlatform"
            :label="$t('Préciser la plateforme')"
            :placeholder="$t('Ex: LinkedIn, Indeed, etc.')"
            :maxlength="50"
          />
        </oxd-grid-item>
        <oxd-grid-item v-if="showOtherField">
          <oxd-input-field
            v-model="otherReason"
            :label="$t('Préciser la raison')"
            :placeholder="$t('Décrivez la raison de cette suppression')"
            required
            :maxlength="150"
          />
        </oxd-grid-item>
      </oxd-form>

      <!-- Affichage des erreurs -->
      <div v-if="showError" class="orangehrm-text-center-align">
        <oxd-text
          type="card-body"
          style="color: #e74c3c; font-size: 14px; margin-bottom: 0.5rem"
        >
          {{ errorMessage }}
        </oxd-text>
      </div>
      <div class="orangehrm-text-center-align">
        <oxd-text type="card-body" style="color: #64728c">
          {{ message || $t('general.delete_confirmation_message') }}
        </oxd-text>
      </div>
      <div class="orangehrm-modal-footer">
        <oxd-button
          :label="$t('general.no_cancel')"
          display-type="ghost"
          class="orangehrm-button-margin"
          @click="onCancel"
        />
        <oxd-button
          :label="$t('general.yes_delete')"
          icon-name="trash"
          display-type="label-danger"
          class="orangehrm-button-margin"
          @click="onDelete"
        />
      </div>
    </oxd-dialog>
  </teleport>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';
import {ref} from 'vue';
export default {
  components: {
    'oxd-dialog': OxdDialog,
  },
  props: {
    message: {
      type: String,
      default: null,
      required: false,
    },
  },
  data() {
    const reason = ref('');
    const otherPlatform = ref('');
    const otherReason = ref('');
    const reasonOptions = [
      {
        id: 1,
        label: "J'ai recruté un candidat via Job Navigator (cette plateforme)",
      },
      {
        id: 2,
        label:
          "J'ai recruté un candidat à travers une autre plateforme (Préciser)",
      },
      {
        id: 3,
        label: "Je n'ai plus besoin d'un candidat pour ce poste",
      },
      {
        id: 4,
        label: 'Autre (Préciser)',
      },
    ];
    return {
      show: false,
      reject: null,
      resolve: null,
      reason,
      otherPlatform,
      otherReason,
      reasonOptions,
      showError: false,
      errorMessage: '',
    };
  },
  computed: {
    showOtherPlatformField() {
      return (
        this.reason ===
        "J'ai recruté un candidat à travers une autre plateforme (Préciser)"
      );
    },
    showOtherField() {
      return this.reason === 'Autre (Préciser)';
    },
  },
  watch: {
    reason(newVal) {
      if (
        newVal !==
        "J'ai recruté un candidat à travers une autre plateforme (Préciser)"
      ) {
        this.otherPlatform = '';
      }

      if (newVal !== 'Autre (Préciser)') {
        this.otherReason = '';
      }
      this.showError = false;
      this.errorMessage = '';
    },
  },
  methods: {
    showDialog() {
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
        this.show = true;
        this.reason = '';
        this.otherPlatform = '';
        this.otherReason = '';
        this.showError = false;
        this.errorMessage = '';
      });
    },
    onDelete() {
      this.showError = false;
      this.errorMessage = '';
      if (!this.reason) {
        this.showError = true;
        this.errorMessage = 'Veuillez indiquer la raison';
        return;
      }
      if (this.showOtherField) {
        if (!this.otherReason.trim()) {
          this.showError = true;
          this.errorMessage =
            'Veuillez préciser la raison de cette suppression.';
          return;
        }

        if (this.otherReason.length > 150) {
          this.showError = true;
          this.errorMessage =
            'Le paragraphe ne peut pas dépasser 150 caractères.';
          return;
        }
      }

      this.show = false;

      let finalReason = this.reason;

      if (this.showOtherPlatformField && this.otherPlatform) {
        finalReason = `${this.reason} : ${this.otherPlatform}`;
      } else if (this.showOtherField && this.otherReason) {
        finalReason = `${this.reason} : ${this.otherReason}`;
      }

      this.resolve &&
        this.resolve({
          action: 'ok',
          reason: finalReason,
        });
    },
    onCancel() {
      this.show = false;
      this.resolve && this.resolve('cancel');
    },
  },
};
</script>

<style src="./dialog.scss" lang="scss"></style>
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
.radio-option-item .oxd-input-group {
  display: flex !important;
  flex-direction: row-reverse !important;
  align-items: flex-end !important;
  justify-content: flex-end !important;
  gap: 0 !important;
}

.radio-option-item .oxd-input-group input[type='radio'] {
  margin-right: 0.5em !important;
  margin-left: 0 !important;
}

.oxd-input-group__label-wrapper {
  margin-top: 0.25rem !important;
}
.oxd-label {
  margin-top: 0.25rem !important;
}
</style>
