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
  <oxd-grid-item class="orangehrm-report-criteria --offset-column-1">
    <oxd-icon-button name="trash-fill" @click="onClickDelete" />
    <oxd-text class="orangehrm-report-criteria-name">
      {{ criterion.label }}
    </oxd-text>
  </oxd-grid-item>
  <component
    v-bind="$attrs"
    :is="field.component"
    :api="field.api"
    :label="field.name"
    :options="field.options"
  ></component>
</template>

<script>
import {ref} from 'vue';
import ReportCriterionAutocomplete from './ReportCriterionAutocomplete';
import ReportCriterionSelect from './ReportCriterionSelect';
import ReportCriterionRange from './ReportCriterionRange';
import ReportCriterionDateRange from './ReportCriterionDateRange';

export default {
  name: 'ReportCriterion',

  components: {
    'report-criterion-autocomplete': ReportCriterionAutocomplete,
    'report-criterion-select': ReportCriterionSelect,
    'report-criterion-range': ReportCriterionRange,
    'report-criterion-date-range': ReportCriterionDateRange,
  },
  inheritAttrs: false,

  props: {
    criterion: {
      type: Object,
      required: true,
    },
  },

  emits: ['delete'],

  setup(props, context) {
    const field = ref(null);

    // map the field type according to criterion
    switch (props.criterion.key) {
      case 'employee_name':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-autocomplete',
          api: null,
          options: [],
        };
        break;

      case 'pay_grade':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/pay-grades',
          options: [],
        };
        break;

      case 'education':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/educations',
          options: [],
        };
        break;

      case 'employment_status':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/employment-statuses',
          options: [],
        };
        break;

      case 'service_period':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-range',
          api: null,
          options: [],
        };
        break;

      case 'joined_date':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-date-range',
          api: null,
          options: [],
        };
        break;

      case 'job_title':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/job-titles',
          options: [],
        };
        break;

      case 'language':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/languages',
          options: [],
        };
        break;

      case 'skill':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/skills',
          options: [],
        };
        break;

      case 'age_group':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-range',
          api: null,
          options: [],
        };
        break;

      case 'sub_unit':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/subunits',
          options: [],
        };
        break;

      case 'location':
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: '/api/v2/admin/locations',
          options: [],
        };
        break;

      default:
        // gender
        field.value = {
          name: props.criterion.label,
          component: 'report-criterion-select',
          api: null,
          options: [
            {id: 1, label: 'Male'},
            {id: 2, label: 'Female'},
          ],
        };
    }

    const onClickDelete = ($event) => {
      context.emit('delete', $event);
    };

    return {
      field,
      onClickDelete,
    };
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-report {
  &-criteria {
    display: flex;
    align-items: baseline;
  }
  &-criteria-name {
    margin-left: 1rem;
    font-weight: 700;
    font-size: $oxd-input-control-font-size;
    padding: $oxd-input-control-vertical-padding 0rem;
  }
}
</style>
