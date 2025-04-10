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
  <div class="orangehrm-horizontal-padding orangehrm-top-padding">
    <oxd-text
      v-if="type === 'Supervisor'"
      tag="h6"
      class="orangehrm-main-title"
    >
      {{ $t('pim.add_supervisor') }}
    </oxd-text>
    <oxd-text
      v-if="type === 'Subordinate'"
      tag="h6"
      class="orangehrm-main-title"
    >
      {{ $t('pim.add_subordinate') }}
    </oxd-text>
    <oxd-divider />
    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-form-row>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <report-to-employee-autocomplete
              v-model="reportTo.employee"
              :rules="rules.employee"
              :api="api"
              required
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-field
              v-model="reportTo.reportingMethod"
              type="select"
              :label="$t('pim.reporting_method')"
              :rules="rules.reportingMethod"
              :options="reportingMethods"
              required
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>

      <oxd-form-actions>
        <required-text />
        <oxd-button
          type="button"
          display-type="ghost"
          label="Cancel"
          @click="onCancel"
        />
        <submit-button />
      </oxd-form-actions>
    </oxd-form>
    <oxd-divider />
  </div>
</template>

<script>
import ReportToEmployeeAutocomplete from './ReportToEmployeeAutocomplete';
import {
  required,
  shouldNotExceedCharLength,
  validSelection,
} from '@/core/util/validation/rules';

const reportToModel = {
  employee: null,
  reportingMethod: null,
};

export default {
  name: 'SaveEmployeeReportTo',

  components: {
    'report-to-employee-autocomplete': ReportToEmployeeAutocomplete,
  },

  props: {
    http: {
      type: Object,
      required: true,
    },
    reportingMethods: {
      type: Array,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
    empNumber: {
      type: String,
      required: true,
    },
  },

  emits: ['close'],

  setup(props) {
    const api = `/api/v2/pim/employees/${props.empNumber}/report-to/allowed`;
    return {
      api,
    };
  },

  data() {
    return {
      isLoading: false,
      reportTo: {...reportToModel},
      rules: {
        employee: [required, shouldNotExceedCharLength(100), validSelection],
        reportingMethod: [required],
      },
    };
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .create({
          empNumber: this.reportTo.employee?.id,
          reportingMethodId: this.reportTo.reportingMethod?.id,
        })
        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.onCancel();
        });
    },
    onCancel() {
      this.$emit('close', true);
    },
  },
};
</script>
