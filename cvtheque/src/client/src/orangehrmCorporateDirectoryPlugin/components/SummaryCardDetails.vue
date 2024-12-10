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
  <summary-card
    :company-id="companyId"
    :company-siret="companySiret"
    :company-name="companyName"
    :company-location="companyLocation"
    :company-matching-job-title="companyMatchingJobTitle"
    :company-phone-number-contact="companyPhoneNumberContact"
    :company-email-contact="companyEmailContact"
    :show-back-button="true"
    :candidature-status="candidatureStatus"
    @see-details="$emit('see-details', false)"
  >
    <company-details
      :company-id="companyId"
      :company-name="companyName"
      :company-location="companyLocation"
      :company-matching-job-title="companyMatchingJobTitle"
      :company-phone-number-contact="companyPhoneNumberContact"
      :company-email-contact="companyEmailContact"
      :candidature-status="candidatureStatus"
      @see-details="$emit('see-details', false)"
    >
    </company-details>
  </summary-card>
</template>

<script>
import SummaryCard from '@/orangehrmCorporateDirectoryPlugin/components/SummaryCard';
import CompanyDetails from '@/orangehrmCorporateDirectoryPlugin/components/CompanyDetails';

export default {
  name: 'SummaryCardDetails',
  components: {
    'company-details': CompanyDetails,
    'summary-card': SummaryCard,
  },
  emits: ['see-details'],
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
    companyLocation: {
      type: String,
      default: '',
    },
    companyPhoneNumberContact: {
      type: String,
      default: '',
    },
    companyEmailContact: {
      type: String,
      default: '',
    },
    candidatureStatus: {
      type: String,
      default: null,
    },
  },
};
</script>
