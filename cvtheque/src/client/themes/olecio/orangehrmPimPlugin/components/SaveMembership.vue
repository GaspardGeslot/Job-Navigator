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
  <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
    <oxd-text tag="h6" class="orangehrm-main-title">
      {{ $t('general.add_experience') }}
    </oxd-text>
    <oxd-divider />
    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-form-row>
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="membership.title"
              :label="$t('Poste occupé')"
              :rules="rules.title"
              required
            />
            <oxd-text
              v-if="titleErrorMessage"
              class="error-text orangehrm-vertical-padding"
              style="color: red"
              >{{ titleErrorMessage }}</oxd-text
            >
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-field
              v-model="membership.employer"
              :label="$t('Employeur')"
            />
          </oxd-grid-item>
        </oxd-grid>
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="membership.year"
              :label="$t('Période')"
              placeholder="01/22 - 05/22"
              :rules="rules.year"
              required
            />
          </oxd-grid-item>
        </oxd-grid>
        <oxd-grid :cols="1" class="orangehrm-full-width-grid">
          <oxd-grid-item class="orangehrm-save-candidate-page --span-column-2">
            <oxd-input-field
              v-model="membership.description"
              :label="$t('Description')"
              type="textarea"
              :rules="rules.description"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>

      <oxd-form-actions>
        <required-text />
        <oxd-button
          type="button"
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <submit-button />
      </oxd-form-actions>
    </oxd-form>
  </div>
  <oxd-divider />
</template>

<script>
// eslint-disable-next-line prettier/prettier
// import
// required,
// validDateFormat,
// endDateShouldBeAfterStartDate,
// digitsOnlyWithDecimalPoint,
// maxCurrency,
// '@/core/util/validation/rules';
// import {yearRange} from '@/core/util/helper/year-range';
import {
  required,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';
import useDateFormat from '@/core/util/composable/useDateFormat';

// const membershipModel = {
//   membershipId: [],
//   subscriptionFee: '',
//   subscriptionPaidBy: null,
//   currencyTypeId: [],
//   subscriptionCommenceDate: '',
//   subscriptionRenewalDate: '',
// };
const proXPModel = {
  title: '',
  year: '',
  description: '',
  employer: '',
};

export default {
  name: 'SaveMembership',
  // directives: {
  //   numericOnly: {
  //     mounted(el) {
  //       el.addEventListener('input', (event) => {
  //         const value = event.target.value;
  //         if (!/^\d*$/.test(value)) {
  //           event.target.value = value.replace(/\D/g, '');
  //         }
  //       });
  //     },
  //   },
  // },

  props: {
    http: {
      type: Object,
      required: true,
    },
    currencies: {
      type: Array,
      default: () => [],
    },
    paidBy: {
      type: Array,
      default: () => [],
    },
    memberships: {
      type: Array,
      default: () => [],
    },
    skills: {
      type: Array,
      default: () => [],
    },
  },

  emits: ['close'],

  setup() {
    const {userDateFormat} = useDateFormat();

    return {
      userDateFormat,
    };
  },

  data() {
    return {
      isLoading: false,
      membership: {...proXPModel},
      titleErrorMessage: '',
      // yearArray: [...yearRange()],
      rules: {
        description: [shouldNotExceedCharLength(300)],
        title: [required],
        year: [required],
        // membership: [required],
        // subscriptionCommenceDate: [validDateFormat(this.userDateFormat)],
        // subscriptionRenewalDate: [
        //   validDateFormat(this.userDateFormat),
        //   endDateShouldBeAfterStartDate(
        //     () => this.membership.subscriptionCommenceDate,
        //     this.$t('pim.renewal_date_should_be_after_the_commencing_date'),
        //   ),
        // ],
        // subscriptionFee: [digitsOnlyWithDecimalPoint, maxCurrency(1000000000)],
      },
    };
  },

  methods: {
    onSave() {
      // const data = {
      //   title: String(this.membership.title),
      //   year: Number(this.membership.year),
      //   description: String(this.membership.description),
      // };
      this.titleErrorMessage = '';
      const existingSkill = this.skills.find(
        (skill) =>
          skill.title.toLowerCase() ===
          this.membership.title.trim().toLowerCase(),
      );
      if (existingSkill) {
        this.titleErrorMessage = this.$t(
          'Une expérience avec ce titre existe déjà. Veuillez utiliser un autre titre.',
        );
        this.isLoading = false;
        return;
      }
      this.isLoading = true;
      this.http
        .create({
          title: String(this.membership.title),
          year: String(this.membership.year),
          description: String(this.membership.description),
          employer: String(this.membership.employer),
        })
        // subscriptionFee: this.membership.subscriptionFee,
        // subscriptionCommenceDate: this.membership.subscriptionCommenceDate,
        // subscriptionRenewalDate: this.membership.subscriptionRenewalDate,
        // membershipId: this.membership.membership.id,
        // subscriptionPaidBy: this.membership.subscriptionPaidBy?.id,
        // currencyTypeId: this.membership.currencyType?.id,

        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.membership = {...proXPModel};
          this.onCancel();
        })
        .catch((error) => {
          if (error.response) {
            console.error('Erreur lors de la requête:', error.response.data);
          } else if (error.request) {
            console.error('Aucune réponse du serveur:', error.request);
          } else {
            console.error(
              'Erreur lors de la configuration de la requête:',
              error.message || 'Erreur inconnue',
            );
          }
        });
    },
    onCancel() {
      this.$emit('close', true);
    },
  },
};
</script>
