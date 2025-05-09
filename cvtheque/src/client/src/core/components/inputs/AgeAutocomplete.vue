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
  <oxd-grid :cols="2" class="orangehrm-full-width-grid">
    <oxd-grid-item
      class="orangehrm-job-selection-criteria --span-column-2"
      style="gap: 1rem"
    >
      <oxd-input-field
        v-model="ageMin"
        :disabled="disabled"
        :label="$t('Age minimum (inclusif)')"
      />
      <oxd-input-field
        v-model="ageMax"
        :disabled="disabled"
        :label="$t('Age maximum (exclusif)')"
      />
      <oxd-input-group>
        <oxd-icon-button
          style="margin-bottom: 1rem"
          name="plus"
          @click="addAge"
        />
      </oxd-input-group>
    </oxd-grid-item>
  </oxd-grid>
  <oxd-grid :cols="4" class="orangehrm-full-width-grid">
    <oxd-grid-item
      v-for="(age, index) in ages"
      :key="index"
      class="orangehrm-job-selection-criteria-selected"
    >
      <oxd-icon-button name="trash-fill" @click="onClickDeleteAge(age)" />
      <oxd-text class="orangehrm-job-selection-criteria-name">
        {{ age.min }} - {{ age.max }}
      </oxd-text>
    </oxd-grid-item>
  </oxd-grid>
</template>

<script>
export default {
  name: 'AgeAutocomplete',

  props: {
    ages: {
      type: Array,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      ageMin: null,
      ageMax: null,
    };
  },

  methods: {
    onClickDeleteAge(age) {
      this.$emit('delete-age', age);
    },

    addAge() {
      if (this.ageMin === null && this.ageMax === null) return;
      if (this.ageMin === this.ageMax) return;
      if (this.ageMin !== null && isNaN(this.ageMin)) return;
      if (this.ageMax !== null && isNaN(this.ageMax)) return;

      const ageMin = this.ageMin === null ? null : parseInt(this.ageMin);
      const ageMax = this.ageMax === null ? null : parseInt(this.ageMax);

      if (ageMin !== null && ageMax !== null && ageMin > ageMax) return;

      let includes = false;
      for (const age of this.ages) {
        if (age.min === ageMin && age.max === ageMax) {
          includes = true;
          break;
        }
      }

      if (!includes) {
        this.$emit('add-age', {min: ageMin, max: ageMax});
      }

      this.ageMin = null;
      this.ageMax = null;
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
