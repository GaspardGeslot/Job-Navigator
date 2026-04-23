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
      class="orangehrm-confirmation-dialog orangehrm-dialog-popup"
      @update:show="onCancel"
    >
      <div class="orangehrm-modal-header">
        <oxd-text type="card-title">{{ title }}</oxd-text>
      </div>
      <div class="orangehrm-text-center-align">
        <oxd-text type="card-body">
          {{ subtitle }}
        </oxd-text>
      </div>
      <div
        v-if="showEmailInput"
        class="orangehrm-confirmation-dialog-input-wrapper"
      >
        <input
          v-model="emailOFModel"
          type="text"
          class="orangehrm-confirmation-dialog-input"
        />
      </div>
      <div class="orangehrm-modal-footer">
        <oxd-button
          :label="cancelLabel"
          :display-type="cancelButtonType"
          class="orangehrm-button-margin"
          @click="onCancelButton"
        />
        <oxd-button
          :icon-name="icon"
          :label="confirmLabel"
          :display-type="confirmButtonType"
          class="orangehrm-button-margin"
          @click="onConfirm"
        />
      </div>
    </oxd-dialog>
  </teleport>
</template>

<script>
import {OxdDialog} from '@ohrm/oxd';

export default {
  components: {
    'oxd-dialog': OxdDialog,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String,
      required: true,
    },
    cancelLabel: {
      type: String,
      required: true,
    },
    confirmLabel: {
      type: String,
      required: true,
    },
    icon: {
      type: String,
      required: false,
      default: '',
    },
    confirmButtonType: {
      type: String,
      required: false,
      default: 'label-danger',
    },
    cancelButtonType: {
      type: String,
      required: false,
      default: 'text',
    },
    cancelButtonSignal: {
      type: String,
      required: false,
      default: 'cancel',
    },
    showEmailInput: {
      type: Boolean,
      required: false,
      default: false,
    },
    emailOF: {
      type: String,
      required: false,
      default: '',
    },
  },
  emits: ['update:emailOF'],
  data() {
    return {
      show: false,
      reject: null,
      resolve: null,
    };
  },
  computed: {
    emailOFModel: {
      get() {
        return this.emailOF ?? '';
      },
      set(value) {
        this.$emit('update:emailOF', value);
      },
    },
  },
  methods: {
    showDialog() {
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
        this.show = true;
      });
    },
    onConfirm() {
      this.show = false;
      this.resolve && this.resolve('ok');
    },
    onCancel() {
      this.show = false;
      this.resolve && this.resolve('cancel');
    },
    onCancelButton() {
      this.show = false;
      this.resolve && this.resolve(this.cancelButtonSignal);
    },
  },
};
</script>

<style src="./dialog.scss" lang="scss" scoped></style>
<style lang="scss" scoped>
.orangehrm-confirmation-dialog-input-wrapper {
  box-sizing: border-box;
  margin: 1rem auto 0;
  width: 100%;
}

.orangehrm-confirmation-dialog-input {
  box-sizing: border-box;
  width: 100%;
  min-height: 2.5rem;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d6dde5;
  border-radius: 0.65rem;
  font-size: 0.95rem;
  outline: none;
}

.orangehrm-confirmation-dialog-input:focus {
  border-color: #64728c;
}
</style>
