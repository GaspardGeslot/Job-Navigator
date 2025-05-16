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
  <oxd-divider v-show="companyEmailContact || companyPhoneNumberContact">
  </oxd-divider>
  <div
    v-show="companyEmailContact || companyPhoneNumberContact"
    class="orangehrm-directory-card-rounded-body"
  >
    <div
      v-show="companyPhoneNumberContact"
      class="orangehrm-directory-card-icon"
    >
      <oxd-icon-button
        display-type="success"
        name="telephone-fill"
        @click.stop="openClientTelephone"
      ></oxd-icon-button>
    </div>
    <div v-show="companyEmailContact" class="orangehrm-directory-card-icon">
      <oxd-icon-button
        display-type="danger"
        name="envelope-fill"
        @click.stop="openClientEmail"
      ></oxd-icon-button>
    </div>
  </div>
  <div
    v-show="companyPhoneNumberContact"
    class="orangehrm-directory-card-hover"
    @mouseleave="showTelephoneClip = false"
    @mouseover="showTelephoneClip = true"
  >
    <div class="orangehrm-directory-card-hover-body">
      <oxd-text type="toast-message">{{ $t('pim.work_telephone') }}</oxd-text>
      <oxd-text ref="cloneTelephone" type="toast-title">
        {{ companyPhoneNumberContact }}
      </oxd-text>
    </div>
    <div
      class="orangehrm-directory-card-hover-body orangehrm-directory-card-hover-icon"
    >
      <oxd-icon-button
        v-show="showTelephoneClip || isMobile"
        name="files"
        @click.stop="copyTelephone"
      ></oxd-icon-button>
    </div>
  </div>
  <oxd-divider v-show="companyPhoneNumberContact"></oxd-divider>
  <div
    v-show="companyEmailContact"
    class="orangehrm-directory-card-hover"
    @mouseleave="showEmailClip = false"
    @mouseover="showEmailClip = true"
  >
    <div class="orangehrm-directory-card-hover-body">
      <oxd-text type="toast-message">{{ $t('general.other_email') }}</oxd-text>
      <oxd-text ref="cloneEmail" type="toast-title">
        {{ companyEmailContact }}
      </oxd-text>
    </div>
    <div
      class="orangehrm-directory-card-hover-body orangehrm-directory-card-hover-icon"
    >
      <oxd-icon-button
        v-show="showEmailClip || isMobile"
        name="files"
        @click.stop="copyEmail"
      ></oxd-icon-button>
    </div>
  </div>
  <oxd-divider v-show="companyEmailContact"></oxd-divider>
  <qr-code
    v-if="qrPayload && (companyPhoneNumberContact || companyEmailContact)"
    :value="qrPayload"
  ></qr-code>
  <oxd-divider v-show="companyEmailContact"></oxd-divider>
  <div style="text-align: center">
    <oxd-button
      display-type="secondary"
      :label="$t('general.show_more')"
      @click="$emit('see-details', false)"
    />
  </div>
</template>

<script>
import {OxdDivider} from '@ohrm/oxd';
import {APIService} from '@/core/util/services/api.service';
import QRCode from './QRCode';

export default {
  name: 'CompanyDetails',
  components: {
    'qr-code': QRCode,
    'oxd-divider': OxdDivider,
  },
  props: {
    companyId: {
      type: Number,
      required: true,
    },
    companyName: {
      type: String,
      required: true,
    },
    companyPhoneNumberContact: {
      type: String,
      default: '',
    },
    companyEmailContact: {
      type: String,
      default: '',
    },
    isMobile: {
      type: Boolean,
      default: false,
    },
    candidatureStatus: {
      type: String,
      default: null,
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/directory/employees',
    );
    return {
      http,
    };
  },
  data() {
    return {
      phoneNumberContact: null,
      emailContact: null,
      showTelephoneClip: false,
      showEmailClip: false,
      toGoEmail: null,
      qrPayload: null,
      name: null,
    };
  },
  watch: {
    companyId: function () {
      this.generateQrPayload();
    },
  },
  beforeMount() {
    this.generateQrPayload();
  },
  emits: ['see-details'],
  methods: {
    openClientTelephone() {
      window.location.href = 'tel:' + this.companyPhoneNumberContact;
    },
    openClientEmail() {
      window.location.href = 'mailto:' + this.companyEmailContact;
    },
    copyEmail() {
      navigator.clipboard?.writeText(this.companyEmailContact);
    },
    copyTelephone() {
      navigator.clipboard?.writeText(this.companyPhoneNumberContact);
    },
    /*createCompanyDetails() {
      this.http.get(this.companyId, {model: 'detailed'}).then((response) => {
        const {data} = response.data;
        this.name = data.name;
        this.emailContact = data.contactInfo?.workEmail;
        this.phoneNumberContact = data.contactInfo?.workTelephone;
        this.generateQrPayload();
      });
    },*/
    generateQrPayload() {
      let content = '';
      content += `N:${this.companyName || ''};\n`;
      if (this.companyPhoneNumberContact)
        content += `TEL;CELL:${this.companyPhoneNumberContact}\n`;
      if (this.companyEmailContact)
        content += `EMAIL;WORK;INTERNET:${this.companyEmailContact}\n`;

      this.qrPayload = `BEGIN:VCARD\nVERSION:3.0\n${content}END:VCARD\n`;
    },
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-directory-card {
  height: auto;
  overflow: hidden;
  padding: 0.5rem 1rem;
  @include oxd-respond-to('md') {
    min-height: 280px;
  }

  &-rounded-body {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 1rem;
    margin: 1rem 8px 1rem 0;
    border-radius: 1.2rem;
    width: auto;
    height: 64px;
    box-shadow: 5px 5px 5px 5px $oxd-background-white-shadow-color;
  }

  &-hover {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0.75rem;
    border-radius: 0.5rem;
    margin: auto;
    @include oxd-respond-to('md') {
      width: auto;
    }

    &-body {
      display: block;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      word-wrap: break-word;
    }

    &-icon {
      color: $oxd-interface-gray-darken-1-color;
      font-size: 14px;
      display: flex;
      flex-shrink: 0;
      align-items: center;
      justify-content: center;
    }

    &:hover {
      background-color: $oxd-background-white-shadow-color;
    }
  }

  &-icon {
    margin: 0 0.5rem 0 0;
    color: $oxd-interface-gray-darken-1-color;
    font-size: 24px;
    display: flex;
    justify-content: center;
  }
}
</style>
