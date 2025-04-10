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
  <oxd-sheet :gutters="false" class="orangehrm-directory-card">
    <div v-show="showBackButton" class="orangehrm-directory-card-top">
      <oxd-icon
        name="arrow-left"
        class="icon-left"
        @click="$emit('hide-details', false)"
      >
      </oxd-icon>
      <oxd-icon
        name="eye-fill"
        class="icon-right"
        @click="$emit('see-details', false)"
      >
      </oxd-icon>
    </div>
    <oxd-text tag="p" :class="cardTitleClasses">
      {{ companyName }}
    </oxd-text>
    <profile-picture :company-siret="companySiret"></profile-picture>
    <br
      v-if="!companyMatchingJobTitle || companyMatchingJobTitle.length <= 0"
    />
    <oxd-text
      v-show="companyMatchingJobTitle"
      tag="p"
      :class="cardSubTitleClasses"
    >
      {{ companyMatchingJobTitle }}
    </oxd-text>
    <div
      v-show="employeeSubUnit || companyLocation"
      class="orangehrm-directory-card-body"
    >
      <oxd-icon
        class="orangehrm-directory-card-icon"
        name="geo-alt-fill"
      ></oxd-icon>
      <div>
        <oxd-text
          v-show="employeeSubUnit"
          tag="p"
          :class="cardDescriptionClasses"
        >
          {{ employeeSubUnit }}
        </oxd-text>
        <oxd-text
          v-show="companyLocation"
          tag="p"
          :class="cardDescriptionClasses"
        >
          {{ companyLocation }}
        </oxd-text>
      </div>
    </div>
    <oxd-text v-show="candidatureStatus" tag="p" :class="cardSubTitleClasses">
      {{ $t('general.status') }}: {{ candidatureStatus }}
    </oxd-text>
    <slot></slot>
  </oxd-sheet>
</template>

<script>
import ProfilePicture from './ProfilePicture';
import {OxdIcon, OxdSheet} from '@ohrm/oxd';

export default {
  name: 'SummaryCard',
  components: {
    'oxd-icon': OxdIcon,
    'oxd-sheet': OxdSheet,
    'profile-picture': ProfilePicture,
  },
  props: {
    companyId: {
      type: Number,
      required: true,
    },
    companySiret: {
      type: String,
      required: false,
      default: null,
    },
    companyName: {
      type: String,
      required: true,
    },
    companyMatchingJobTitle: {
      type: String,
      required: false,
      default: null,
    },
    employeeSubUnit: {
      type: String,
      default: '',
    },
    companyLocation: {
      type: String,
      default: '',
    },
    showBackButton: {
      type: Boolean,
      default: false,
    },
    candidatureStatus: {
      type: String,
      default: null,
    },
  },
  emits: ['hide-details', 'see-details'],
  computed: {
    hasDefaultSlot() {
      return !!this.$slots.default;
    },
    cardTitleClasses() {
      return {
        'orangehrm-directory-card-header': true,
        '--break-words': !this.hasDefaultSlot,
      };
    },
    cardSubTitleClasses() {
      return {
        'orangehrm-directory-card-subtitle': true,
        '--break-words': !this.hasDefaultSlot,
      };
    },
    cardDescriptionClasses() {
      return {
        'orangehrm-directory-card-description': true,
        '--break-words': !this.hasDefaultSlot,
      };
    },
  },
};
</script>

<style lang="scss" scoped>
.icon-left {
  display: inline-block;
}
.icon-right {
  float: right;
}
.orangehrm-directory-card {
  height: auto;
  cursor: pointer;
  overflow: hidden;
  padding: 0.5rem 1rem;

  &-header {
    font-size: 14px;
    min-height: 28px;
    font-weight: 700;
    text-align: center;
    margin-top: 1rem;
    margin-bottom: 0.75rem;
    word-break: break-word;
    &.--break-words {
      @include truncate(2, 1, #fff);
    }
  }

  &-subtitle {
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    margin-top: 1rem;
    margin-bottom: 0.75rem;
    word-break: break-word;
    &.--break-words {
      @include truncate(1, 1, #fff);
    }
  }

  &-description {
    font-size: 12px;
    text-align: left;
    word-break: break-word;
    &.--break-words {
      @include truncate(1, 1, #fff);
    }
    &:first-of-type {
      margin-bottom: 0.25rem;
    }
  }

  &-body {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 0.5rem;
    background-color: $oxd-background-white-shadow-color;
  }

  &-icon {
    font-size: 24px;
    margin-right: 0.5rem;
    color: $oxd-interface-gray-darken-1-color;
  }

  @include oxd-respond-to('md') {
    min-height: 260px;
  }
}
</style>
