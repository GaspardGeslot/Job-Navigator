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
  <div class="orangehrm-card-container">
    <oxd-form :loading="isLoading">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('recruitment.application_stage') }}
      </oxd-text>
      <oxd-divider />
      <oxd-grid :cols="2" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-group :label="$t('general.name')">
            <oxd-text tag="p">
              {{ this.profile.candidateName }}
            </oxd-text>
          </oxd-input-group>
        </oxd-grid-item>
        <oxd-grid-item v-if="this.profile.jobTitle">
          <oxd-input-group :label="$t('general.job_title')">
            <oxd-text tag="p">
              {{ this.profile.jobTitle }}
            </oxd-text>
          </oxd-input-group>
        </oxd-grid-item>
        <!--<oxd-grid-item>
          <oxd-input-group :label="$t('recruitment.hiring_manager')">
            <oxd-text tag="p">
              {{ hiringManagerName ? hiringManagerName : 'N/A' }}
            </oxd-text>
          </oxd-input-group>
        </oxd-grid-item>-->
      </oxd-grid>
      <oxd-divider />
      <oxd-grid
        v-if="candidate.candidatureStatus"
        :cols="2"
        class="orangehrm-full-width-grid"
      >
        <oxd-grid-item>
          <oxd-input-field
            v-model="candidatureStatus"
            type="select"
            :label="$t('general.status')"
            :options="candidatureStatuses"
          />
        </oxd-grid-item>
      </oxd-grid>
      <!--<div class="orangehrm-recruitment">
        <div class="orangehrm-recruitment-actions">
          <oxd-button
            v-if="hasWorkflow(3)"
            :label="$t('general.reject')"
            display-type="danger"
            @click="doWorkflow(3)"
          />
          <oxd-button
            v-if="hasWorkflow(8)"
            :label="$t('recruitment.offer_declined')"
            display-type="danger"
            @click="doWorkflow(8)"
          />
          <oxd-button
            v-if="hasWorkflow(6)"
            :label="$t('recruitment.mark_interview_failed')"
            display-type="danger"
            @click="doWorkflow(6)"
          />
          <oxd-button
            v-if="hasWorkflow(2)"
            :label="$t('recruitment.shortlist')"
            display-type="success"
            @click="doWorkflow(2)"
          />
          <oxd-button
            v-if="hasWorkflow(4)"
            :label="$t('recruitment.schedule_interview')"
            display-type="success"
            @click="doWorkflow(4)"
          />
          <oxd-button
            v-if="hasWorkflow(5)"
            :label="$t('recruitment.mark_interview_passed')"
            display-type="success"
            @click="doWorkflow(5)"
          />
          <oxd-button
            v-if="hasWorkflow(7)"
            :label="$t('recruitment.offer_job')"
            display-type="success"
            @click="doWorkflow(7)"
          />
          <oxd-button
            v-if="hasWorkflow(9)"
            :label="$t('recruitment.hire')"
            display-type="success"
            @click="doWorkflow(9)"
          />
        </div>
      </div>-->
      <oxd-form-actions>
        <oxd-button
          v-if="canUpdate"
          class="orangehrm-right-space"
          display-type="secondary"
          :label="$t('general.update')"
          @click="onClickEdit"
        />
      </oxd-form-actions>
    </oxd-form>
  </div>
</template>

<script>
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import useEmployeeNameTranslate from '@/core/util/composable/useEmployeeNameTranslate';

const CandidateProfileModel = {
  candidateName: '',
  jobTitle: null,
  candidatureStatus: null,
};

export default {
  name: 'RecruitmentStatus',
  props: {
    matchingId: {
      type: Number,
      required: true,
    },
    candidate: {
      type: Object,
      required: true,
    },
    candidatureStatuses: {
      type: Array,
      default: () => [],
    },
  },
  setup() {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/candidates',
    );
    const {$tEmpName} = useEmployeeNameTranslate();

    return {
      http,
      translateEmpName: $tEmpName,
    };
  },
  data() {
    return {
      profile: {...CandidateProfileModel},
      canUpdate: false,
      candidatureStatus: null,
      isLoading: false,
      statuses: [
        {id: 1, label: this.$t('recruitment.application_initiated')},
        {id: 2, label: this.$t('recruitment.shortlisted')},
        {id: 3, label: this.$t('leave.rejected')},
        {id: 4, label: this.$t('recruitment.interview_scheduled')},
        {id: 5, label: this.$t('recruitment.interview_passed')},
        {id: 6, label: this.$t('recruitment.interview_failed')},
        {id: 7, label: this.$t('recruitment.job_offered')},
        {id: 8, label: this.$t('recruitment.offer_declined')},
        {id: 9, label: this.$t('recruitment.hired')},
      ],
      actions: [],
    };
  },
  computed: {
    /*recruitmentStatus() {
      return (
        this.statuses.find((item) => item.id === this.candidate.status?.id)
          ?.label || null
      );
    },
    candidateName() {
      return `${this.candidate.firstName} ${this.candidate?.middleName || ''} ${
        this.candidate.lastName
      }`;
    },
    vacancyName() {
      const {vacancy} = this.candidate;
      if (!vacancy) return null;
      return vacancy.status === false
        ? vacancy.name + ` (${this.$t('general.closed')})`
        : vacancy.name;
    },
    hiringManagerName() {
      return this.candidate.vacancy?.hiringManager
        ? this.translateEmpName(this.candidate.vacancy.hiringManager, {
            includeMiddle: true,
            excludePastEmpTag: false,
          })
        : undefined;
    },*/
  },
  watch: {
    candidate() {
      this.candidatureStatus = this.candidatureStatuses.find(
        (item) => item.label === this.profile.candidatureStatus,
      );
    },
    candidatureStatus(newVal) {
      this.canUpdate =
        newVal && newVal.label !== this.profile.candidatureStatus;
    },
  },
  beforeMount() {
    this.fetchCandidate();
  },
  methods: {
    hasWorkflow(actionId) {
      return this.actions.findIndex((actions) => actions.id == actionId) > -1;
    },
    doWorkflow(actionId) {
      navigate(
        '/recruitment/changeCandidateVacancyStatus',
        {},
        {
          candidateId: this.candidate?.id,
          selectedAction: actionId,
        },
      );
    },
    getAllowedActions() {
      this.isLoading = true;
      this.http
        .request({
          method: 'GET',
          url: `/api/v2/recruitment/candidates/${this.candidate?.id}/actions/allowed`,
        })
        .then((response) => {
          const {data} = response.data;
          this.actions = [...data];
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    fetchCandidate() {
      this.profile.candidateName = `${this.candidate.firstName} ${
        this.candidate?.middleName || ''
      } ${this.candidate.lastName}`;
      this.profile.jobTitle = this.candidate.jobTitle;
      this.profile.candidatureStatus = this.candidate.candidatureStatus;
      this.candidatureStatus = this.candidatureStatuses.find(
        (item) => item.label === this.candidate.candidatureStatus,
      );
    },
    onClickEdit() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          url: `/api/v2/recruitment/candidates/${this.candidate?.id}/matching/${this.matchingId}/status/${this.candidatureStatus?.id}`,
        })
        .finally(() => {
          this.profile.candidatureStatus = this.candidatureStatus?.label;
          this.isLoading = false;
          this.canUpdate = false;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-recruitment {
  display: flex;
  justify-content: space-between;
  &-actions {
    gap: 0.4rem;
    display: flex;
    flex-wrap: wrap;
    max-width: 120px;
    margin-left: 60px;
    justify-content: flex-end;
    ::v-deep(.oxd-button--medium) {
      width: 100%;
    }
    @include oxd-respond-to('md') {
      margin-left: unset;
      max-width: unset;
      ::v-deep(.oxd-button--medium) {
        width: unset;
      }
    }
  }
}
::v-deep(.oxd-input-group) {
  margin-bottom: 1rem;
  @include oxd-respond-to('md') {
    margin-bottom: 0;
  }
}
</style>
