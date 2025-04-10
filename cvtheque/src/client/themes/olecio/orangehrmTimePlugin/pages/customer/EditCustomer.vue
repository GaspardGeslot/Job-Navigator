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
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('time.edit_customer') }}
      </oxd-text>
      <oxd-divider />
      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-form-row>
          <oxd-input-field
            v-model="customer.name"
            :label="$t('general.name')"
            :rules="rules.name"
            required
          />
        </oxd-form-row>
        <oxd-form-row>
          <oxd-input-field
            v-model="customer.description"
            type="textarea"
            :label="$t('general.description')"
            :placeholder="$t('general.type_description_here')"
            :rules="rules.description"
          />
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-actions>
          <required-text />
          <oxd-button
            display-type="ghost"
            :label="$t('general.cancel')"
            @click="onCancel"
          />
          <submit-button />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import {
  required,
  shouldNotExceedCharLength,
} from '@/core/util/validation/rules';
import {promiseDebounce} from '@ohrm/oxd';

const customerModel = {
  id: '',
  name: '',
  description: '',
};
export default {
  props: {
    customerId: {
      type: String,
      required: true,
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/time/customers',
    );
    http.setIgnorePath('/api/v2/time/validation/customer-name');
    return {
      http,
    };
  },
  data() {
    return {
      isLoading: false,
      customer: {...customerModel},
      rules: {
        name: [
          required,
          shouldNotExceedCharLength(50),
          promiseDebounce(this.validateCustomerName, 500),
        ],
        description: [shouldNotExceedCharLength(255)],
      },
    };
  },
  created() {
    this.isLoading = true;
    this.http
      .get(this.customerId)
      .then((response) => {
        const {data} = response.data;
        this.customer.id = data.id;
        this.customer.name = data.name;
        this.customer.description = data.description;
      })
      .finally(() => {
        this.isLoading = false;
      });
  },
  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .update(this.customerId, {
          name: this.customer.name,
          description: this.customer.description,
        })
        .then(() => {
          return this.$toast.updateSuccess();
        })
        .then(() => {
          this.onCancel();
        });
    },
    onCancel() {
      navigate('/time/viewCustomers');
    },
    validateCustomerName(customer) {
      return new Promise((resolve) => {
        if (customer) {
          this.http
            .request({
              method: 'GET',
              url: `/api/v2/time/validation/customer-name`,
              params: {
                customerName: this.customer.name.trim(),
                customerId: this.customerId,
              },
            })
            .then((response) => {
              const {data} = response.data;
              return data.valid === true
                ? resolve(true)
                : resolve(this.$t('general.already_exists'));
            });
        } else {
          resolve(true);
        }
      });
    },
  },
};
</script>
