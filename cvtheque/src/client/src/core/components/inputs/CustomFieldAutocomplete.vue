<template>
  <oxd-grid :cols="2" class="orangehrm-full-width-grid" style="gap: 1rem">
    <oxd-grid-item class="orangehrm-job-selection-criteria" style="gap: 1rem">
      <oxd-input-field
        v-model="newCustomField"
        :disabled="disabled"
        :label="label"
        type="select"
        :options="customFieldOptions"
      />
      <oxd-input-group>
        <oxd-icon-button
          style="margin-bottom: 1rem"
          name="plus"
          @click="addCustomField"
        />
      </oxd-input-group>
    </oxd-grid-item>
  </oxd-grid>
  <oxd-grid
    v-for="(c, index) in customFields"
    :key="index"
    :cols="3"
    class="orangehrm-full-width-grid"
  >
    <oxd-grid-item class="orangehrm-job-selection-criteria-selected">
      <oxd-icon-button name="trash-fill" @click="onClickDeleteCustomField(c)" />
      <oxd-text class="orangehrm-job-selection-criteria-name">
        {{ c.title }}
      </oxd-text>
    </oxd-grid-item>
    <oxd-grid-item>
      <oxd-input-field
        v-model="c.custom"
        :label="customFieldLabel"
        :rules="rules.custom"
      />
    </oxd-grid-item>
  </oxd-grid>
</template>

<script>
import {shouldNotExceedCharLength} from '@/core/util/validation/rules';

export default {
  name: 'CustomFieldAutocomplete',

  props: {
    label: {
      type: String,
      required: true,
    },
    customFields: {
      type: Array,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    customFieldOptions: {
      type: Array,
      required: true,
    },
    customFieldLabel: {
      type: String,
      default: 'Dénomination spécifique',
    },
  },

  data() {
    return {
      newCustomField: null,
    };
  },

  setup() {
    const rules = {
      custom: [shouldNotExceedCharLength(100)],
    };
    return {
      rules,
    };
  },

  emits: ['delete-custom-field', 'add-custom-field'],
  methods: {
    onClickDeleteCustomField(customField) {
      this.$emit('delete-custom-field', customField);
    },

    addCustomField() {
      if (
        this.newCustomField &&
        !this.customFields.some((c) => c.title === this.newCustomField.label)
      ) {
        this.$emit('add-custom-field', this.newCustomField.label);
      }
      this.newCustomField = null;
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
