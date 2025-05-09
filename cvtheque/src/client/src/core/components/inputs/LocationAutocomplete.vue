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
  <oxd-grid :cols="2" class="orangehrm-full-width-grid" style="gap: 1rem">
    <oxd-grid>
      <oxd-grid-item class="orangehrm-job-selection-criteria" style="gap: 1rem">
        <oxd-input-field
          v-model="department"
          :disabled="disabled"
          :label="$t('Département')"
          type="select"
          :options="departmentsOptions"
        />
        <oxd-input-group>
          <oxd-icon-button
            style="margin-bottom: 1rem"
            name="plus"
            @click="addDepartment"
          />
        </oxd-input-group>
      </oxd-grid-item>
      <oxd-grid-item
        v-for="(department, index) in departments"
        :key="index"
        class="orangehrm-job-selection-criteria-selected --offset-column-1"
      >
        <oxd-icon-button
          name="trash-fill"
          @click="onClickDeleteDepartment(department)"
        />
        <oxd-text class="orangehrm-job-selection-criteria-name">
          {{ department.label }}
        </oxd-text>
      </oxd-grid-item>
    </oxd-grid>
    <oxd-grid>
      <oxd-grid-item class="orangehrm-job-selection-criteria" style="gap: 1rem">
        <oxd-input-field
          v-model="locationPostalCode"
          :disabled="disabled"
          :label="$t('Code postal')"
        />
        <oxd-input-group>
          <oxd-icon-button
            style="margin-bottom: 1rem"
            name="plus"
            @click="addLocationPostalCode"
          />
        </oxd-input-group>
      </oxd-grid-item>
      <oxd-grid-item
        v-for="(locationPostalCode, index) in locationPostalCodes"
        :key="index"
        class="orangehrm-job-selection-criteria-selected"
      >
        <oxd-icon-button
          name="trash-fill"
          @click="onClickDeleteLocationPostalCode(locationPostalCode)"
        />
        <oxd-text class="orangehrm-job-selection-criteria-name">
          {{ locationPostalCode }}
        </oxd-text>
      </oxd-grid-item>
    </oxd-grid>
  </oxd-grid>
</template>

<script>
export default {
  name: 'LocationAutocomplete',

  props: {
    departments: {
      type: Array,
      required: true,
    },
    locationPostalCodes: {
      type: Array,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    departmentsOptions: {
      type: Array,
      required: true,
    },
  },

  data() {
    return {
      department: null,
      locationPostalCode: null,
    };
  },

  methods: {
    onClickDeleteDepartment(department) {
      this.$emit('delete-department', department);
    },

    onClickDeleteLocationPostalCode(locationPostalCode) {
      this.$emit('delete-location-postal-code', locationPostalCode);
    },

    addDepartment() {
      if (this.department && !this.departments.includes(this.department)) {
        this.$emit('add-department', this.department);
      }
      this.department = null;
    },

    addLocationPostalCode() {
      if (
        this.locationPostalCode &&
        !this.locationPostalCodes.includes(this.locationPostalCode)
      ) {
        this.$emit('add-location-postal-code', this.locationPostalCode);
      }
      this.locationPostalCode = null;
    },
  },
};
</script>

<style scoped lang="scss">
.orangehrm-job-selection {
  &-criteria {
    display: flex;
    align-items: center;
  }
  &-criteria-selected {
    display: flex;
    align-items: baseline;
  }
  &-criteria-name {
    margin-left: 1rem;
    font-weight: 700;
    font-size: $oxd-input-control-font-size;
    padding: $oxd-input-control-vertical-padding 0rem;
  }
  &-icon {
    margin-left: 1rem;
  }
}
</style>
