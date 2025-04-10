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
  <oxd-dialog
    :style="{width: '90%', maxWidth: '450px'}"
    @update:show="onCancel"
  >
    <div class="orangehrm-modal-header">
      <oxd-text type="card-title">
        {{ $t('general.comment') }}
      </oxd-text>
    </div>
    <oxd-divider />
    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-form-row>
        <oxd-grid :cols="2" class="orangehrm-timesheet-grid">
          <oxd-text tag="p" class="orangehrm-timesheet-title">
            {{ $t('time.project') }}:
          </oxd-text>
          <oxd-text tag="p" class="orangehrm-timesheet-text">
            {{ projectName }}
          </oxd-text>
          <oxd-text tag="p" class="orangehrm-timesheet-title">
            {{ $t('time.activity') }}:
          </oxd-text>
          <oxd-text tag="p" class="orangehrm-timesheet-text">
            {{ data.activity.name }}
          </oxd-text>
          <oxd-text tag="p" class="orangehrm-timesheet-title">
            {{ $t('general.date') }}:
          </oxd-text>
          <oxd-text tag="p" class="orangehrm-timesheet-text">
            {{ commentDate }}
          </oxd-text>
        </oxd-grid>
      </oxd-form-row>
      <oxd-form-row>
        <oxd-input-field
          v-model="comment"
          type="textarea"
          :placeholder="$t('general.comment_here')"
          :rules="rules.comment"
          :disabled="!editable"
        />
      </oxd-form-row>
      <oxd-divider />
      <oxd-form-actions>
        <oxd-button
          type="button"
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <submit-button v-show="editable" />
      </oxd-form-actions>
    </oxd-form>
  </oxd-dialog>
</template>

<script>
import {shouldNotExceedCharLength} from '@/core/util/validation/rules';
import {APIService} from '@/core/util/services/api.service';
import {OxdDialog} from '@ohrm/oxd';
import useLocale from '@/core/util/composable/useLocale';
import useDateFormat from '@/core/util/composable/useDateFormat';
import {formatDate, parseDate} from '@/core/util/helper/datefns';

export default {
  name: 'TimesheetCommentModal',
  components: {
    'oxd-dialog': OxdDialog,
  },
  props: {
    data: {
      type: Object,
      required: true,
    },
    editable: {
      type: Boolean,
      required: true,
    },
    timesheetId: {
      type: Number,
      required: true,
    },
  },
  emits: ['close'],
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/time/timesheets`,
    );
    const {locale} = useLocale();
    const {jsDateFormat} = useDateFormat();
    return {
      http,
      locale,
      jsDateFormat,
    };
  },
  data() {
    return {
      isLoading: false,
      comment: null,
      rules: {
        comment: [shouldNotExceedCharLength(2000)],
      },
    };
  },
  computed: {
    projectName() {
      const {project, customer} = this.data;
      return project?.label
        ? project.label
        : `${customer?.name} - ${project?.name}`;
    },
    commentDate() {
      const {date} = this.data;
      return date
        ? formatDate(parseDate(date), this.jsDateFormat, {
            locale: this.locale,
          })
        : null;
    },
  },
  beforeMount() {
    if (this.data?.id) {
      this.isLoading = true;
      this.http
        .request({
          method: 'GET',
          url: `/api/v2/time/timesheets/${this.timesheetId}/entries/${this.data.id}/comment`,
        })
        .then((response) => {
          const {data} = response.data;
          this.comment = data?.comment;
        })
        .finally(() => {
          this.isLoading = false;
        });
    }
  },
  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          url: `/api/v2/time/timesheets/${this.timesheetId}/entries/comment`,
          data: {
            date: this.data.date,
            comment: this.comment,
            projectId: this.data.project.id,
            activityId: this.data.activity.id,
          },
        })
        .then((response) => {
          const {data} = response.data;
          this.$toast.saveSuccess();
          this.$emit('close', data);
        });
    },
    onCancel() {
      this.comment = null;
      this.$emit('close');
    },
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-timesheet {
  &-grid {
    width: 100%;
    padding: 0 0.625rem;
    grid-template-columns: 100px 1fr;
    margin-bottom: 1rem;
  }
  &-title,
  &-text {
    word-break: break-word;
    font-size: $oxd-input-control-font-size;
  }
  &-title {
    font-weight: 700;
  }
}
</style>
