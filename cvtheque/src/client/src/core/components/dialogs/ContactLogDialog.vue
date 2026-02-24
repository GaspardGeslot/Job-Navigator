<template>
  <oxd-dialog
    v-if="modelValue"
    :show="modelValue"
    :style="{width: '90%', maxWidth: '600px'}"
    @update:show="onClose"
  >
    <div class="orangehrm-modal-header">
      <oxd-text type="card-title">
        {{
          isEditing
            ? $t('Modifier la prise de contact')
            : $t('Ajouter une prise de contact')
        }}
      </oxd-text>
    </div>
    <oxd-divider />
    <oxd-form
      :loading="loading"
      @submit-valid="onSubmit"
    >
      <oxd-form-row>
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <date-input
              v-model="form.date"
              :label="$t('general.date')"
              :rules="rules.telephoneContactDate"
              :disabled="isEditing"
              required
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <time-input
              v-model="form.time"
              :label="$t('Heure')"
              :rules="rules.telephoneContactTime"
              :disabled="isEditing"
              :step="1"
              required
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-form-row v-if="hasContactLogTypes">
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="form.type"
              :label="$t('Type de prise de contact')"
              type="select"
              :options="contactLogTypes"
              option-value="id"
              option-label="label"
              :disabled="isEditing"
              required
            />
          </oxd-grid-item>
          <oxd-grid-item v-if="contactLogTypeOrdinal !== null">
            <oxd-text class="orangehrm-input-title" tag="h6">
              {{
                isContactLogTypeTelephone
                  ? $t('Contact abouti avec succès')
                  : $t('A répondu')
              }}
            </oxd-text>
            <oxd-switch-input
              v-model="form.successful"
              :disabled="isEditing"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-form-row v-if="hasContactLogTypes && isContactLogTypePhone">
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="form.phoneNumber"
              :label="$t('recruitment.contact_number')"
              :rules="isContactLogTypePhone ? rules.telephoneContactPhoneNumber : []"
              :disabled="isEditing"
              required
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-form-row v-if="!hasContactLogTypes">
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="form.phoneNumber"
              :label="$t('recruitment.contact_number')"
              :rules="rules.telephoneContactPhoneNumber"
              :disabled="isEditing"
              required
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-text class="orangehrm-input-title" tag="h6">
              {{ $t('Appel abouti avec succès') }}
            </oxd-text>
            <oxd-switch-input
              v-model="form.successful"
              :disabled="isEditing"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-form-row>
        <oxd-grid :cols="1" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="form.comment"
              type="textarea"
              :label="$t('Commentaire')"
              :rules="rules.telephoneContactComment"
              :disabled="isEditing && !canEditComment"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-divider />
      <oxd-form-actions class="orangehrm-form-action">
        <required-text />
        <oxd-button
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onClose"
        />
        <oxd-button
          display-type="secondary"
          :label="$t('general.save')"
          type="submit"
        />
      </oxd-form-actions>
    </oxd-form>
  </oxd-dialog>
</template>

<script>
import {
  validPhoneNumberFormat,
  shouldNotExceedCharLength,
  required,
  validDateFormat,
  validTimeFormat,
  shouldBeCurrentOrPreviousDate,
} from '@/core/util/validation/rules';
import DateInput from '@/core/components/inputs/DateInput';
import TimeInput from '@/core/components/inputs/TimeInput';
import RequiredText from '@/core/components/labels/RequiredText';
import {OxdSwitchInput, OxdDialog} from '@ohrm/oxd';
import {formatDate} from '@/core/util/helper/datefns';

const defaultForm = () => ({
  date: null,
  time: null,
  phoneNumber: '',
  successful: false,
  comment: '',
  type: null,
});

export default {
  name: 'ContactLogDialog',
  components: {
    DateInput,
    TimeInput,
    RequiredText,
    'oxd-switch-input': OxdSwitchInput,
    'oxd-dialog': OxdDialog,
  },
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
    /** Données initiales en mode édition */
    initialForm: {
      type: Object,
      default: null,
    },
    /** Types de prise de contact (si fournis, affiche le select Type) */
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    defaultPhoneNumber: {
      type: String,
      default: '',
    },
    defaultEmail: {
      type: String,
      default: '',
    },
    isEditing: {
      type: Boolean,
      default: false,
    },
    userDateFormat: {
      type: String,
      default: 'yyyy-MM-dd',
    },
    loading: {
      type: Boolean,
      default: false,
    },
    canEditComment: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue', 'save'],
  data() {
    return {
      form: defaultForm(),
    };
  },
  computed: {
    hasContactLogTypes() {
      return this.contactLogTypes && this.contactLogTypes.length > 0;
    },
    contactLogTypeOrdinal() {
      const t = this.form.type;
      if (t == null) return null;
      if (typeof t === 'object') return t.value ?? t.id ?? null;
      const n = Number(t);
      return Number.isInteger(n) ? n : null;
    },
    isContactLogTypePhone() {
      const o = this.contactLogTypeOrdinal;
      return o !== null && o !== 1;
    },
    isContactLogTypeTelephone() {
      return this.contactLogTypeOrdinal !== 1;
    },
    rules() {
      return {
        telephoneContactDate: [
          required,
          validDateFormat(this.userDateFormat),
          shouldBeCurrentOrPreviousDate(),
        ],
        telephoneContactTime: [required, validTimeFormat],
        telephoneContactPhoneNumber: [
          required,
          validPhoneNumberFormat,
          shouldNotExceedCharLength(25),
        ],
        telephoneContactComment: [shouldNotExceedCharLength(1000)],
      };
    },
  },
  watch: {
    modelValue: {
      handler(visible) {
        if (visible) {
          this.$nextTick(() => this.resetForm());
        }
      },
    },
  },
  methods: {
    resetForm() {
      if (this.initialForm) {
        this.form = {
          date: this.initialForm.date ?? null,
          time: this.initialForm.time ?? null,
          phoneNumber: this.initialForm.phoneNumber ?? '',
          successful: this.initialForm.successful ?? false,
          comment: this.initialForm.comment ?? '',
          type: this.initialForm.type ?? null,
        };
      } else {
        const now = new Date();
        this.form = {
          date: formatDate(now, this.userDateFormat),
          time: formatDate(now, 'HH:mm'),
          phoneNumber: this.defaultPhoneNumber || '',
          successful: false,
          comment: '',
          type: this.hasContactLogTypes ? null : null,
        };
      }
    },
    onSubmit() {
      this.$emit('save', {...this.form});
    },
    onClose() {
      this.$emit('update:modelValue', false);
    },
  },
};
</script>
