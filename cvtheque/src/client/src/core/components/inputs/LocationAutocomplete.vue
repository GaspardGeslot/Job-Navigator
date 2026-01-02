<template>
  <oxd-grid class="orangehrm-full-width-grid" style="gap: 1rem">
    <oxd-grid :cols="2">
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
      <oxd-grid-item>
        <oxd-input-field
          :model-value="isAllDepartmentsSelected"
          type="checkbox"
          :label="$t('Tout sélectionner')"
          :disabled="disabled"
          @change="(event) => toggleAllDepartments(event.target.checked)"
        />
      </oxd-grid-item>
    </oxd-grid>
    <oxd-grid :cols="4" class="orangehrm-full-width-grid">
      <oxd-grid-item
        v-for="(department, index) in departments"
        :key="index"
        class="orangehrm-job-selection-criteria-selected"
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
    <oxd-grid :cols="4">
      <oxd-grid-item
        class="orangehrm-job-selection-criteria --span-column-2"
        style="gap: 1rem"
      >
        <oxd-input-field
          v-model="locationPostalCode"
          :disabled="disabled"
          :label="$t('Code postal')"
        />
        <oxd-input-group>
          <oxd-icon-button name="plus" @click="addLocationPostalCode" />
        </oxd-input-group>
      </oxd-grid-item>
      <oxd-grid-item
        class="orangehrm-job-selection-criteria --span-column-2"
        style="gap: 1rem"
      >
        <oxd-input-field
          ref="csvFileInput"
          type="file"
          :label="$t('Importer des codes postaux')"
          accept=".csv"
          :disabled="disabled"
          :placeholder="$t('Choisir un fichier CSV')"
          @change="onFileChange"
        />
        <oxd-input-group>
          <oxd-icon-button
            name="trash-fill"
            :title="$t('Tout supprimer')"
            @click="onClickDeleteAllLocationPostalCode()"
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
    isAllDepartmentsSelected: {
      type: Boolean,
      default: false,
    },
  },

  emits: [
    'delete-department',
    'delete-location-postal-code',
    'add-department',
    'add-location-postal-code',
    'toggle-all-departments',
    'import-csv',
    'delete-all-location-postal-code',
  ],

  data() {
    return {
      department: null,
      locationPostalCode: null,
      postalCodesFile: null,
    };
  },

  methods: {
    onClickDeleteDepartment(department) {
      this.$emit('delete-department', department);
    },

    onClickDeleteLocationPostalCode(locationPostalCode) {
      this.$emit('delete-location-postal-code', locationPostalCode);
    },

    onClickDeleteAllLocationPostalCode() {
      this.$emit('delete-all-location-postal-code');
    },

    onFileChange(event) {
      const file = event.target.files[0];
      if (file) this.$emit('import-csv', file);
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

    toggleAllDepartments(selected) {
      this.$emit('toggle-all-departments', selected);
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
    align-items: center;
    margin-bottom: 0.5rem;
  }
  &-criteria-name {
    margin-left: 0.5rem;
    font-weight: 700;
    font-size: $oxd-input-control-font-size;
    padding: $oxd-input-control-vertical-padding 0rem;
  }
  &-icon {
    margin-left: 1rem;
  }
}
</style>
