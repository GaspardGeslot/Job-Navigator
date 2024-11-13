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
  <edit-employee-layout screen="job" :employee-id="empNumber">
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('pim.job_details') }}
      </oxd-text>
      <oxd-divider />
      <oxd-form
        v-if="jobsFetched.length"
        :loading="isLoading"
        @submit-valid="onSave"
      >
        <oxd-form-row
          v-for="(item, itemIndex) in sectors"
          :key="itemIndex"
          style="flex-direction: row-reverse"
        >
          <oxd-text tag="h7" class="orangehrm-main-title">
            {{ item.title }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item
              v-for="(elem, elemIndex) in item.jobs"
              :key="`${itemIndex}-${elemIndex}`"
              style="flex-direction: row-reverse"
            >
              <oxd-input-field
                type="checkbox"
                :disabled="
                  checkedJobs.length >= 3 && !checkedJobs.includes(elem)
                "
                :label="elem"
                :value="elem"
                :checked="checkedJobs.includes(elem)"
                style="flex-direction: row-reverse"
                @change="toggleCheckedJob(elem)"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-divider />
        </oxd-form-row>
        <!--<oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <date-input
                v-model="job.joinedDate"
                :label="$t('general.joined_date')"
                :rules="rules.joinedDate"
                :disabled="!hasUpdatePermissions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="job.jobTitleId"
                type="select"
                :label="$t('general.job_title')"
                :options="normalizedJobTitles"
                :disabled="!hasUpdatePermissions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <job-spec-download
                :key="`jobspec-${selectedJobTitleId}`"
                :resource-id="selectedJobTitleId"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="job.jobCategoryId"
                type="select"
                :label="$t('general.job_category')"
                :options="jobCategories"
                :disabled="!hasUpdatePermissions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="job.subunitId"
                type="select"
                :label="$t('general.sub_unit')"
                :options="subunits"
                :disabled="!hasUpdatePermissions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="job.locationId"
                type="select"
                :label="$t('general.location')"
                :options="locations"
                :disabled="!hasUpdatePermissions"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="job.empStatusId"
                type="select"
                :label="$t('general.employment_status')"
                :options="employmentStatuses"
                :disabled="!hasUpdatePermissions"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />

        <oxd-form-row class="user-form-header">
          <oxd-text class="user-form-header-text" tag="p">
            {{ $t('pim.include_employment_contract_details') }}
          </oxd-text>
          <oxd-switch-input v-model="showContractDetails" />
        </oxd-form-row>

        <template v-if="showContractDetails">
          <oxd-form-row>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <date-input
                  v-model="contract.startDate"
                  :label="$t('pim.contract_start_date')"
                  :rules="rules.startDate"
                  :disabled="!hasUpdatePermissions"
                />
              </oxd-grid-item>

              <oxd-grid-item>
                <date-input
                  v-model="contract.endDate"
                  :label="$t('pim.contract_end_date')"
                  :rules="rules.endDate"
                  :disabled="!hasUpdatePermissions"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-form-row>
            <oxd-grid :cols="2" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <file-upload-input
                  v-model:newFile="contract.newAttachment"
                  v-model:method="contract.method"
                  :label="$t('pim.contract_details')"
                  :button-label="$t('general.browse')"
                  :file="contract.oldAttachment"
                  :rules="rules.contractAttachment"
                  :url="`pim/viewAttachment/empNumber/${empNumber}/attachId`"
                  :hint="
                    $t('general.accepts_up_to_n_mb', {count: formattedFileSize})
                  "
                  :disabled="!hasUpdatePermissions"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </template>-->
        <oxd-form-actions>
          <submit-button />
        </oxd-form-actions>
      </oxd-form>
    </div>

    <!--
    <oxd-divider v-if="hasUpdatePermissions && !isLoading" />

<div
      v-if="hasUpdatePermissions && !isLoading"
      class="orangehrm-horizontal-padding orangehrm-vertical-padding"
    >
      <profile-action-header
        icon-name=""
        :display-type="terminationActionType"
        :label="terminationActionLabel"
        :title="terminationActionLabel"
        class="--termination-button"
        @click="onClickTerminate"
      >
        {{ $t('pim.employee_termination_activation') }}
        <oxd-text
          v-if="termination && termination.id"
          tag="p"
          class="orangehrm-terminate-date"
          @click="openTerminateModal"
        >
          {{ $t('pim.terminated_on') }}: {{ terminationDate }}
        </oxd-text>
      </profile-action-header>
    </div>
    <terminate-modal
      v-if="showTerminateModal"
      :employee-id="empNumber"
      :termination-reasons="terminationReasons"
      :termination-id="termination.id"
      @close="closeTerminateModal"
    ></terminate-modal>
    -->
  </edit-employee-layout>
</template>

<script>
import {APIService} from '@ohrm/core/util/services/api.service';
//import FileUploadInput from '@/core/components/inputs/FileUploadInput';
import EditEmployeeLayout from '@/orangehrmPimPlugin/components/EditEmployeeLayout';
//import JobSpecDownload from '@/orangehrmPimPlugin/components/JobSpecDownload';
// import ProfileActionHeader from '@/orangehrmPimPlugin/components/ProfileActionHeader';
// import TerminateModal from '@/orangehrmPimPlugin/components/TerminateModal';
import {
  required,
  maxFileSize,
  validFileTypes,
  validDateFormat,
  endDateShouldBeAfterStartDate,
} from '@ohrm/core/util/validation/rules';
import useDateFormat from '@/core/util/composable/useDateFormat';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import useLocale from '@/core/util/composable/useLocale';
// import {ref} from 'vue';
//import {OxdSwitchInput} from '@ohrm/oxd';

const jobDetailsModel = {
  joinedDate: '',
  jobTitleId: [],
  empStatusId: [],
  jobCategoryId: [],
  subunitId: [],
  locationId: [],
};

const contractDetailsModel = {
  startDate: '',
  endDate: '',
  oldAttachment: null,
  newAttachment: null,
  method: 'keepCurrent',
};

export default {
  components: {
    'edit-employee-layout': EditEmployeeLayout,
    //'oxd-switch-input': OxdSwitchInput,
    //'job-spec-download': JobSpecDownload,
    //'file-upload-input': FileUploadInput,
    // 'profile-action-header': ProfileActionHeader,
    // 'terminate-modal': TerminateModal,
  },

  props: {
    empNumber: {
      type: String,
      required: true,
    },
    locations: {
      type: Array,
      default: () => [],
    },
    jobTitles: {
      type: Array,
      default: () => [],
    },
    jobCategories: {
      type: Array,
      default: () => [],
    },
    // subunits: {
    //   type: Array,
    //   default: () => [],
    // },
    // employmentStatuses: {
    //   type: Array,
    //   default: () => [],
    // },
    // terminationReasons: {
    //   type: Array,
    //   default: () => [],
    // },
    // allowedFileTypes: {
    //   type: Array,
    //   required: true,
    // },
    // maxFileSize: {
    //   type: Number,
    //   required: true,
    // },
    sectors: {
      type: Array,
      default: () => [],
    },
  },

  setup(props) {
    // const jobsFetched = ref(0);
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/pim/employees/${props.empNumber}/job-details`,
    );
    console.log('empNumber', props.empNumber);
    const {jsDateFormat, userDateFormat} = useDateFormat();
    const {locale} = useLocale();

    return {
      http,
      locale,
      jsDateFormat,
      userDateFormat,
      jobDetailsModel,
    };
  },

  data() {
    return {
      empNumberInternal: this.empNumber,
      errors: {
        tooMuchOptions: false,
      },
      jobsFetched: [],
      checkedJobs: [],
      isLoading: false,
      showContractDetails: false,
      job: {...jobDetailsModel},
      contract: {...contractDetailsModel},
      termination: null,
      showTerminateModal: false,
      rules: {
        joinedDate: [validDateFormat(this.userDateFormat)],
        startDate: [validDateFormat(this.userDateFormat)],
        endDate: [
          validDateFormat(this.userDateFormat),
          endDateShouldBeAfterStartDate(() => this.contract.startDate),
        ],
        contractAttachment: [
          (v) => {
            if (this.contract.method == 'replaceCurrent') {
              return required(v);
            } else {
              return true;
            }
          },
          validFileTypes(this.allowedFileTypes),
          maxFileSize(this.maxFileSize),
        ],
      },
    };
  },
  computed: {
    selectedJobTitleId() {
      const jobTitleId = this.job.jobTitleId?.id;
      return jobTitleId || 0;
    },
    terminationActionLabel() {
      return this.termination?.id
        ? this.$t('pim.activate_employment')
        : this.$t('pim.terminate_employment');
    },
    terminationActionType() {
      return this.termination?.id ? 'ghost-success' : 'label-danger';
    },
    hasUpdatePermissions() {
      return this.$can.update(`job_details`);
    },
    normalizedJobTitles() {
      return this.jobTitles.map((jobTitle) => {
        return {
          id: jobTitle.id,
          label: jobTitle?.deleted
            ? jobTitle.label + this.$t('general.deleted')
            : jobTitle.label,
        };
      });
    },
    terminationDate() {
      return this.termination?.date
        ? formatDate(parseDate(this.termination.date), this.jsDateFormat, {
            locale: this.locale,
          })
        : null;
    },
    formattedFileSize() {
      return Math.round((this.maxFileSize / (1024 * 1024)) * 100) / 100;
    },
  },
  // watch: {
  //   checkedJobs(newVal) {
  //     if (newVal.length > 3) {
  //       this.checkedJobs.pop();
  //       this.errors.tooMuchOptions = true;
  //     } else {
  //       this.errorMessage = '';
  //       this.errors.tooMuchOptions = false;
  //     }
  //   },
  // },

  beforeMount() {
    this.isLoading = true;
    this.http
      .getAll()
      .then((response) => {
        this.updateJobModel(response);
      })
      .then(() => {
        return this.http.request({
          method: 'GET',
          url: `/api/v2/pim/employees/${this.empNumber}/employment-contract`,
        });
      })
      .then((response) => {
        this.updateContractModel(response);
      })
      .finally(() => {
        this.isLoading = false;
      });
  },

  methods: {
    toggleCheckedJob(job) {
      const index = this.checkedJobs.indexOf(job);

      if (index >= 0) {
        this.checkedJobs.splice(index, 1);
        this.errors.tooMuchOptions = false;
      } else {
        if (this.checkedJobs.length < 3) {
          this.checkedJobs.push(job);
          this.errors.tooMuchOptions = false;
        } else {
          this.errors.tooMuchOptions = true;
        }
      }
      console.log('checkedJobs', this.checkedJobs);
    },
    onSave() {
      this.isLoading = true;
      // const encodedJobs = this.checkedJobs.map((job) =>
      //   encodeURIComponent(job),
      // );

      // const newData = JSON.parse(JSON.stringify(this.checkedJobs));
      // const newData = {
      // ...this.job,
      // jobs: this.checkedJobs,
      // jobTitleId: this.job.jobTitleId?.id ?? null,
      // jobCategoryId: this.job.jobCategoryId?.id ?? null,
      // subunitId: this.job.subunitId?.id ?? null,
      // empStatusId: this.job.empStatusId?.id ?? null,
      // locationId: this.job.locationId?.id ?? null,
      // };

      // jobTitleId: this.job.jobTitleId?.id ?? null,
      // jobCategoryId: this.job.jobCategoryId?.id ?? null,
      // subunitId: this.job.subunitId?.id ?? null,
      // empStatusId: this.job.empStatusId?.id ?? null,
      // locationId: this.job.locationId?.id ?? null,
      console.log('"JOBS HERE : "', this.checkedJobs);
      console.log('data HERE : ', this.checkedJobs);
      console.log('empNumber HERE:', this.empNumberInternal);
      console.log(
        "Données envoyées à l'API:",
        JSON.parse(JSON.stringify(this.checkedJobs)),
      );
      this.http
        // .request({
        //   method: 'PUT',
        //   data: newData,
        // })
        .updateJobs(this.empNumberInternal, {
          jobs: this.checkedJobs,
        })
        .then((response) => {
          console.log('RESPONSE HERE ', response);
          // this.updateJobModel(response);
          /*
        
          return this.http.request({
            method: 'PUT',
            url: `/api/v2/pim/employees/${this.empNumber}/employment-contract`,
            data: {
              startDate: this.contract.startDate,
              endDate: this.contract.endDate,
              currentContractAttachment: this.contract.oldAttachment
                ? this.contract.method
                : undefined,
              contractAttachment: this.contract.newAttachment
                ? this.contract.newAttachment
                : undefined,
            },
          });*/
        })
        .catch((error) => {
          if (error.response) {
            console.error('Erreur API :', error.response);
          } else {
            console.error('Erreur réseau ou autre:', error);
          }
        })
        // .then((response) => {
        //   if (response) {
        //     this.updateContractModel(response);
        //   }
        //   return this.$toast.updateSuccess();
        // })
        .then(() => {
          this.isLoading = false;
        });
    },

    onClickTerminate() {
      if (this.termination?.id) {
        this.isLoading = true;
        this.http
          .request({
            method: 'DELETE',
            url: `/api/v2/pim/employees/${this.empNumber}/terminations`,
          })
          .then(() => {
            return this.$toast.updateSuccess();
          })
          .then(() => {
            location.reload();
          });
      } else {
        this.openTerminateModal();
      }
    },

    openTerminateModal() {
      this.showTerminateModal = true;
    },

    closeTerminateModal(reload) {
      this.showTerminateModal = false;
      if (reload) {
        location.reload();
      }
    },

    updateContractModel(response) {
      const {data} = response.data;
      this.contract.startDate = data.startDate;
      this.contract.endDate = data.endDate;
      this.contract.oldAttachment = data.contractAttachment?.id
        ? data.contractAttachment
        : null;
      this.contract.newAttachment = null;
      this.contract.method = 'keepCurrent';
      if (data.startDate || data.endDate || data.contractAttachment?.id) {
        this.showContractDetails = true;
      } else {
        this.showContractDetails = false;
      }
    },
    updateJobModel(response) {
      const {data} = response.data;
      let tempJobsFetched = [];
      try {
        tempJobsFetched = JSON.parse(data.jobs);
      } catch (error) {
        console.error('Erreur lors du parsing de jobs:', error);
      }
      this.jobsFetched = tempJobsFetched || [];
      let tempCheckedJobs = this.jobsFetched.flatMap(
        (sector) => sector.jobs || [],
      );
      this.checkedJobs.splice(0, this.checkedJobs.length, ...tempCheckedJobs);
      console.log('jobsFetched', this.jobsFetched);
      console.log('checkedJobs après splice', this.checkedJobs);
      this.job.joinedDate = data.joinedDate;
      this.job.jobTitleId = this.normalizedJobTitles.find(
        (item) => item.id === data.jobTitle?.id,
      );
      this.job.jobCategoryId = this.jobCategories.find(
        (item) => item.id === data.jobCategory?.id,
      );
      this.job.subunitId = this.subunits.find(
        (item) => item.id === data.subunit?.id,
      );
      this.job.empStatusId = this.employmentStatuses.find(
        (item) => item.id === data.empStatus?.id,
      );
      this.job.locationId = this.locations.find(
        (item) => item.id === data.location?.id,
      );
      this.termination = data.employeeTerminationRecord;
    },
  },
};
</script>

<style src="./employee.scss" lang="scss" scoped>
.orangehrm-form-hint {
  width: 100%;
  font-weight: 600;
  font-size: 0.75rem;
  margin-right: auto;
  @media screen and (min-width: 400px) {
    width: unset;
  }
}
</style>
