<!--
/*
 * This file is part of OrangeHRM Inc
 *
 * Copyright (C) 2020 onwards OrangeHRM Inc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see  http://www.gnu.org/licenses
 */
-->
<template>
  <div class="oxd-table-filter">
    <div class="oxd-table-filter-header">
      <div class="oxd-table-filter-header-title">
        <oxd-text class="oxd-table-filter-title" tag="h5">
          {{ filterTitle }}
        </oxd-text>
      </div>
      <div class="oxd-table-filter-header-options">
        <div class="--toggle">
          <slot name="toggleOptions"></slot>
        </div>
        <div class="--export">
          <slot name="exportOptions"></slot>
        </div>
        <div class="--toggle">
          <oxd-icon-button
            :name="isActive ? 'caret-up-fill' : 'caret-down-fill'"
            @click="toggleFilters"
          />
        </div>
      </div>
    </div>
    <oxd-divider v-show="isActive" />
    <div v-show="isActive" class="oxd-table-filter-area">
      <slot></slot>
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent, ref} from 'vue';

export default defineComponent({
  name: 'OxdTableFilter',

  props: {
    filterTitle: {
      type: String,
      required: true,
    },
  },

  setup() {
    const isActive = ref(true);

    const toggleFilters = () => {
      isActive.value = !isActive.value;
    };

    return {
      isActive,
      toggleFilters,
    };
  },
});
</script>

<style lang="scss" scoped>
.oxd-table-filter {
  background-color: $oxd-white-color;
  border-radius: 1.2rem;
  padding: 25px;
  &-title {
    font-size: 14px;
    font-weight: 800;
  }
  &-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: space-between;
    &-options {
      display: flex;
      & .--toggle,
      .--export {
        display: flex;
      }
    }
  }
}
.oxd-table-filter-header-options {
  & .oxd-icon-button {
    font-size: 12px;
    width: 24px !important;
    height: 24px !important;
    min-width: unset;
    min-height: unset;
    margin-right: 5px;
  }
  & .oxd-button {
    padding-right: 5px;
    padding-left: 5px;
    min-width: unset;
    margin-right: 5px;
  }
}
</style>
