<template>
  <oxd-grid :cols="5" class="orangehrm-full-width-grid">
    <oxd-grid-item>
      <oxd-input-field
        v-model="timeSlotMin"
        :disabled="disabled"
        :label="$t('Heure minimum')"
        :rules="rules.timeSlotMin"
      />
    </oxd-grid-item>
    <oxd-grid-item>
      <oxd-input-field
        v-model="timeSlotMax"
        :disabled="disabled"
        :label="$t('Heure maximum')"
        :rules="rules.timeSlotMax"
      />
    </oxd-grid-item>
    <oxd-grid-item
      class="orangehrm-job-selection-criteria --span-column-2"
      style="gap: 1rem"
    >
      <oxd-text style="text-align: center; align-content: center">
        {{ $t('ou') }}
      </oxd-text>
      <oxd-input-field
        v-model="timeSlotName"
        :disabled="disabled"
        :label="$t('Titre')"
        type="select"
        :options="timeSlotOptions"
      />
    </oxd-grid-item>
    <oxd-grid-item>
      <oxd-input-group>
        <oxd-icon-button
          style="margin-bottom: 1rem"
          name="plus"
          @click="addTimeSlot"
        />
      </oxd-input-group>
    </oxd-grid-item>
  </oxd-grid>
  <oxd-grid
    v-for="(timeSlot, index) in timeSlots"
    :key="index"
    :cols="3"
    class="orangehrm-full-width-grid"
  >
    <oxd-grid-item class="orangehrm-job-selection-criteria-selected">
      <oxd-icon-button
        name="trash-fill"
        @click="onClickDeleteTimeSlot(timeSlot)"
      />
      <oxd-text
        v-if="timeSlot.name"
        class="orangehrm-job-selection-criteria-name"
      >
        {{ timeSlot.name }}
      </oxd-text>
      <oxd-text v-else class="orangehrm-job-selection-criteria-name">
        {{ timeSlot.min }} - {{ timeSlot.max }}
      </oxd-text>
    </oxd-grid-item>
    <oxd-grid-item>
      <oxd-input-field
        v-model="timeSlot.custom"
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
  name: 'TimeSlotAutocomplete',

  props: {
    timeSlots: {
      type: Array,
      required: true,
    },
    timeSlotOptions: {
      type: Array,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['delete-time-slot', 'add-time-slot'],

  setup() {
    const rules = {
      timeSlotMin: [numericOnly],
      timeSlotMax: [numericOnly],
      custom: [shouldNotExceedCharLength(100), required],
    };
    return {
      rules,
    };
  },

  data() {
    return {
      timeSlotMin: '',
      timeSlotMax: '',
      timeSlotName: '',
    };
  },

  methods: {
    onClickDeleteTimeSlot(timeSlot) {
      this.$emit('delete-time-slot', timeSlot);
    },

    addTimeSlot() {
      if (this.timeSlotName && this.timeSlotName.label) {
        if (
          !this.timeSlots.some(
            (timeSlot) => timeSlot.name === this.timeSlotName.label,
          )
        ) {
          this.$emit('add-time-slot', {
            name: this.timeSlotName.label,
          });
          return;
        }
      } else {
        if (this.timeSlotMin === null && this.timeSlotMax === null) return;
        if (this.timeSlotMin === this.timeSlotMax) return;
        if (this.timeSlotMin !== null && isNaN(this.timeSlotMin)) return;
        if (this.timeSlotMax !== null && isNaN(this.timeSlotMax)) return;

        const timeSlotMin =
          this.timeSlotMin === null ? null : parseInt(this.timeSlotMin);
        const timeSlotMax =
          this.timeSlotMax === null ? null : parseInt(this.timeSlotMax);

        if (
          timeSlotMin !== null &&
          timeSlotMax !== null &&
          timeSlotMin > timeSlotMax
        )
          return;

        let includes = false;
        for (const timeSlot of this.timeSlots) {
          if (timeSlot.min === timeSlotMin && timeSlot.max === timeSlotMax) {
            includes = true;
            break;
          }
        }
        if (!includes)
          this.$emit('add-time-slot', {
            min: timeSlotMin,
            max: timeSlotMax,
          });
      }

      this.timeSlotMin = '';
      this.timeSlotMax = '';
      this.timeSlotName = '';
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
