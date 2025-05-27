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
        :rules="rules.ageMin"
      />
      <oxd-input-field
        v-model="ageMax"
        :disabled="disabled"
        :label="$t('Age maximum (exclusif)')"
        :rules="rules.ageMax"
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
  <oxd-grid v-if="!isActorSpecific" :cols="4" class="orangehrm-full-width-grid">
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
  <oxd-grid
    v-else
    v-for="(age, index) in ages"
    :key="index"
    :cols="3"
    class="orangehrm-full-width-grid"
  >
    <oxd-grid-item class="orangehrm-job-selection-criteria-selected">
      <oxd-icon-button name="trash-fill" @click="onClickDeleteAge(age)" />
      <oxd-text class="orangehrm-job-selection-criteria-name">
        {{ age.min }} - {{ age.max }}
      </oxd-text>
    </oxd-grid-item>
    <oxd-grid-item>
      <oxd-input-field
        v-model="age.custom"
        :label="$t('Dénomination spécifique')"
        :rules="rules.custom"
        required
      />
    </oxd-grid-item>
  </oxd-grid>
</template>

<script>
import {
  numericOnly,
  shouldNotExceedCharLength,
  required,
} from '@/core/util/validation/rules';

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
    isActorSpecific: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      ageMin: '',
      ageMax: '',
    };
  },

  setup() {
    const rules = {
      ageMin: [numericOnly],
      ageMax: [numericOnly],
      custom: [shouldNotExceedCharLength(100), required],
    };
    return {
      rules,
    };
  },

  emits: ['delete-age', 'add-age'],

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
      if (!includes) this.$emit('add-age', {min: ageMin, max: ageMax});

      this.ageMin = '';
      this.ageMax = '';
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
