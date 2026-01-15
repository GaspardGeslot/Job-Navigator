<template>
  <oxd-grid-item>
    <!-- Input STRING -->
    <oxd-input-field
      v-if="columnType === 'STRING'"
      v-model="internalValue"
      :label="title"
      :disabled="!editable"
      :rules="rules.string"
      @update:model-value="onValueChange"
    />

    <!-- Input DOUBLE -->
    <oxd-input-field
      v-else-if="columnType === 'DOUBLE'"
      v-model="internalValue"
      :label="title"
      :disabled="!editable"
      :rules="rules.double"
      @update:model-value="onValueChange"
    />

    <!-- Input SELECT -->
    <oxd-input-field
      v-else-if="columnType === 'SELECT'"
      v-model="internalValue"
      :label="title"
      type="select"
      :options="selectOptions"
      :disabled="!editable"
      @update:model-value="onValueChange"
    />

    <!-- Input BOOLEAN -->
    <div v-else-if="columnType === 'BOOLEAN'" class="orangehrm-switch-wrapper">
      <oxd-text class="orangehrm-text" tag="p">
        {{ title }}
      </oxd-text>
      <oxd-switch-input
        v-model="internalBooleanValue"
        :disabled="!editable"
        @update:model-value="onBooleanChange"
      />
    </div>

    <!-- Input DATE -->
    <date-input
      v-else-if="columnType === 'DATE'"
      v-model="internalValue"
      :label="title"
      :disabled="!editable"
      :rules="rules.date"
      @update:model-value="onValueChange"
    />
  </oxd-grid-item>
</template>

<script>
import DateInput from '@/core/components/inputs/DateInput';
import {OxdSwitchInput} from '@ohrm/oxd';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import {
  digitsOnlyWithDecimalPointAndMinusSign,
  validDateFormat,
} from '@/core/util/validation/rules';

export default {
  name: 'CustomColumnInput',
  components: {
    'date-input': DateInput,
    'oxd-switch-input': OxdSwitchInput,
  },
  props: {
    columnId: {
      type: Number,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    type: {
      type: String,
      required: true,
      validator: (value) =>
        ['STRING', 'DOUBLE', 'SELECT', 'BOOLEAN', 'DATE'].includes(value),
    },
    options: {
      type: String,
      default: null,
    },
    value: {
      type: String,
      default: null,
    },
    editable: {
      type: Boolean,
      default: true,
    },
  },
  emits: ['update:value'],
  setup() {
    const userDateFormat = 'yyyy-MM-dd';
    return {
      userDateFormat,
    };
  },
  data() {
    return {
      internalValue: null,
      internalBooleanValue: false,
      selectOptions: [],
      rules: {
        string: [],
        double: [digitsOnlyWithDecimalPointAndMinusSign],
        date: [validDateFormat(this.userDateFormat)],
      },
    };
  },
  computed: {
    columnType() {
      return this.type.toUpperCase();
    },
  },
  watch: {
    value: {
      immediate: true,
      handler(newValue) {
        this.initializeValue(newValue);
      },
    },
    options: {
      immediate: true,
      handler(newOptions) {
        this.parseSelectOptions(newOptions);
      },
    },
  },
  methods: {
    initializeValue(value) {
      if (value === null || value === undefined || value === '') {
        // Valeur par défaut selon le type
        if (this.columnType === 'BOOLEAN') {
          this.internalBooleanValue = false;
          this.internalValue = 'false';
        } else if (this.columnType === 'DOUBLE') {
          this.internalValue = '0';
        } else {
          this.internalValue = null;
        }
        return;
      }

      // Convertir depuis String selon le type
      if (this.columnType === 'BOOLEAN') {
        this.internalBooleanValue = value === 'true' || value === true;
        this.internalValue = this.internalBooleanValue ? 'true' : 'false';
      } else if (this.columnType === 'DOUBLE') {
        // Convertir String vers number pour l'affichage
        const numValue = parseFloat(value);
        this.internalValue = isNaN(numValue) ? null : numValue.toString();
      } else if (this.columnType === 'DATE') {
        // Convertir de yyyy-MM-dd (API) vers format utilisateur
        if (value && value.trim() !== '') {
          const dateObj = parseDate(value, 'yyyy-MM-dd');
          if (dateObj) {
            this.internalValue = formatDate(dateObj, this.userDateFormat);
          } else {
            this.internalValue = null;
          }
        } else {
          this.internalValue = null;
        }
      } else if (this.columnType === 'SELECT') {
        // SELECT : récupérer l'id du select
        this.parseSelectOptions(this.options);
        const selectOption = this.selectOptions.find(
          (option) => option.label === value,
        );
        this.internalValue = selectOption;
      } else {
        // STRING ou SELECT : utiliser directement
        this.internalValue = value;
      }
    },
    parseSelectOptions(optionsString) {
      if (!optionsString || optionsString.trim() === '') {
        this.selectOptions = [];
        return;
      }

      try {
        // Parser le JSON string (format: "['Option 1', 'Option 2']")
        const parsed = JSON.parse(optionsString);
        if (Array.isArray(parsed)) {
          this.selectOptions = parsed.map((option) => ({
            id: option,
            label: option,
          }));
        } else {
          this.selectOptions = [];
        }
      } catch (error) {
        console.error('Error parsing select options:', error);
        this.selectOptions = [];
      }
    },
    onValueChange(newValue) {
      // Convertir vers String pour l'API
      let stringValue = null;

      if (this.columnType === 'DOUBLE') {
        // Convertir number vers String
        if (newValue !== null && newValue !== undefined && newValue !== '') {
          const numValue = parseFloat(newValue);
          stringValue = isNaN(numValue) ? null : numValue.toString();
        } else {
          stringValue = null;
        }
      } else if (this.columnType === 'DATE') {
        // Convertir du format utilisateur vers yyyy-MM-dd (API)
        if (newValue && newValue.trim() !== '') {
          const dateObj = parseDate(newValue, this.userDateFormat);
          if (dateObj) {
            stringValue = formatDate(dateObj, 'yyyy-MM-dd');
          } else {
            stringValue = null;
          }
        } else {
          stringValue = null;
        }
      } else if (this.columnType === 'SELECT') {
        // SELECT : récupérer le label du select
        stringValue =
          newValue !== null && newValue !== undefined
            ? String(newValue.label)
            : newValue;
      } else {
        // STRING : utiliser directement
        stringValue =
          newValue !== null && newValue !== undefined
            ? String(newValue)
            : newValue;
      }

      this.$emit('update:value', stringValue);
    },
    onBooleanChange(newValue) {
      this.internalBooleanValue = newValue;
      this.internalValue = newValue ? 'true' : 'false';
      this.$emit('update:value', this.internalValue);
    },
  },
};
</script>

<style scoped lang="scss">
.orangehrm-switch-wrapper {
  display: flex;
  flex-direction: row;
  justify-content: start;
  align-items: center;
  gap: 1rem;
  width: 100%;
}

.orangehrm-text {
  font-size: 12px;
  font-weight: 600;
  color: $oxd-interface-gray-darken-1-color;
  margin: 0;
}
</style>
