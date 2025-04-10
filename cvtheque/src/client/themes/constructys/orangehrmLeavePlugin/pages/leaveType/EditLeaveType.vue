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
        {{ $t('leave.edit_leave_type') }}
      </oxd-text>

      <oxd-divider />

      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-form-row>
          <oxd-input-field
            v-model="leaveType.name"
            :label="$t('general.name')"
            :rules="rules.name"
            required
          />
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-group :classes="{wrapper: '--status-grouped-field'}">
                <template #label>
                  <div class="label-is-entitlement-situational">
                    <oxd-label
                      :label="$t('leave.is_entitlement_situational')"
                    />
                    <oxd-icon-button
                      class="--help"
                      name="exclamation-circle"
                      :with-container="false"
                      @click="onModalOpen"
                    />
                  </div>
                </template>
                <oxd-input-field
                  v-model="leaveType.situational"
                  type="radio"
                  :option-label="$t('general.yes')"
                  :value="true"
                />
                <oxd-input-field
                  v-model="leaveType.situational"
                  type="radio"
                  :option-label="$t('general.no')"
                  :value="false"
                />
              </oxd-input-group>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

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
      <entitlement-situational-modal
        v-if="showModal"
        @close="onModalClose"
      ></entitlement-situational-modal>
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
import EntitlementSituationalModal from '../../components/EntitlementSituationalModal';
import {OxdLabel} from '@ohrm/oxd';
import useServerValidation from '@/core/util/composable/useServerValidation';

const leaveTypeModel = {
  id: '',
  name: '',
  situational: '',
};

export default {
  components: {
    'oxd-label': OxdLabel,
    'entitlement-situational-modal': EntitlementSituationalModal,
  },
  props: {
    leaveTypeId: {
      type: Number,
      required: true,
    },
  },

  setup(props) {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/leave/leave-types',
    );
    const {createUniqueValidator} = useServerValidation(http);
    const leaveTypeUniqueValidation = createUniqueValidator(
      'LeaveType',
      'name',
      {entityId: props.leaveTypeId},
    );
    return {
      http,
      leaveTypeUniqueValidation,
    };
  },

  data() {
    return {
      showModal: false,
      isLoading: false,
      leaveType: {...leaveTypeModel},
      rules: {
        name: [
          required,
          this.leaveTypeUniqueValidation,
          shouldNotExceedCharLength(50),
        ],
      },
    };
  },
  created() {
    this.isLoading = true;
    this.http
      .get(this.leaveTypeId)
      .then((response) => {
        const {data} = response.data;
        this.leaveType.id = data.id;
        this.leaveType.name = data.name;
        this.leaveType.situational = data.situational;
      })
      .finally(() => {
        this.isLoading = false;
      });
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .update(this.leaveTypeId, {
          name: this.leaveType.name,
          situational: this.leaveType.situational,
        })
        .then(() => {
          return this.$toast.updateSuccess();
        })
        .then(() => {
          this.onCancel();
        });
    },
    onCancel() {
      navigate('/leave/leaveTypeList');
    },
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
  },
};
</script>

<style src="./leave-type.scss" lang="scss" scoped></style>
