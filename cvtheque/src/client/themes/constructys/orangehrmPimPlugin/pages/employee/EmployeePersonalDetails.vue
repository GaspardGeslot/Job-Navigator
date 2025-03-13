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
  <edit-employee-layout :employee-id="empNumber" screen="personal">
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-form-row>
          <oxd-text tag="h6" class="orangehrm-main-title">
            {{ $t('general.personal_details') }}
          </oxd-text>
          <oxd-text class="orangehrm-text" tag="p">
            <i>
              {{ $t('general.think_about_saving') }}
            </i>
          </oxd-text>
          <oxd-form-actions class="top-form-actions">
            <oxd-button
              display-type="secondary"
              :label="$t('general.save')"
              type="submit"
            />
          </oxd-form-actions>
          <oxd-divider />
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid
            v-if="isCandidate"
            :cols="1"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item>
              <full-name-input
                v-model:firstName="employee.firstName"
                v-model:lastName="employee.lastName"
                :rules="rules"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid v-else :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.companyName"
                :label="$t('company.name')"
                :rules="rules.companyName"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.companyWebsite"
                :label="$t('pim.web_site')"
                :rule="rules.website"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid
            v-if="!isCandidate"
            :cols="1"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item
              class="orangehrm-save-candidate-page --span-column-2"
            >
              <oxd-input-field
                v-model="employee.companyPresentation"
                :label="$t('pim.presentation')"
                type="textarea"
              />
            </oxd-grid-item>
          </oxd-grid>
          <!--<oxd-grid
            v-if="showDeprecatedFields"
            :cols="3"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.nickname"
                :label="$t('pim.nickname')"
                :rules="rules.nickname"
              />
            </oxd-grid-item>
          </oxd-grid>-->
        </oxd-form-row>

        <oxd-divider />
        <oxd-form-row v-if="isCandidate">
          <!--<oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.profileId"
                :label="$t('general.employee_id')"
                :rules="rules.employeeId"
                :disabled="!$can.update(`personal_sensitive_information`)"
              />
            </oxd-grid-item>
          </oxd-grid>-->
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <file-upload-input
                v-model:newFile="attachment.newAttachment"
                v-model:method="attachment.method"
                :label="$t('recruitment.resume')"
                :button-label="$t('general.browse')"
                :file="attachment.oldAttachment"
                :rules="rules.attachment"
                :hint="
                  $t('general.accept_custom_format_file_up_to_n_mb', {
                    count: formattedFileSize,
                  })
                "
                :url="getAttachmentUrl"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row v-else>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.companySiret"
                :label="$t('company.siret')"
                :rules="rules.employeeId"
                :disabled="!$can.update(`personal_sensitive_information`)"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.companyNafCode"
                :label="$t('company.naf_code')"
                type="select"
                :options="nafCodes"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <file-upload-input
                v-model:newFile="attachment.newAttachment"
                v-model:method="attachment.method"
                :label="$t('company.company_presentation_file')"
                :button-label="$t('general.browse')"
                :file="attachment.oldAttachment"
                :rules="rules.attachment"
                :hint="
                  $t('general.accept_custom_format_file_up_to_n_mb', {
                    count: formattedFileSize,
                  })
                "
                :url="getAttachmentUrl"
              />
            </oxd-grid-item>
          </oxd-grid>
          <!--<oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.drivingLicenseNo"
                :label="$t('pim.driver_license_number')"
                :rules="rules.drivingLicenseNo"
                :disabled="!$can.update(`personal_sensitive_information`)"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <date-input
                v-model="employee.drivingLicenseExpiredDate"
                :rules="rules.drivingLicenseExpiredDate"
                :label="$t('pim.license_expiry_date')"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item v-if="showSsnField">
              <oxd-input-field
                v-model="employee.ssnNumber"
                :label="$t('pim.ssn_number')"
                :rules="rules.ssnNumber"
                :disabled="!$can.update(`personal_sensitive_information`)"
              />
            </oxd-grid-item>
            <oxd-grid-item v-if="showSinField">
              <oxd-input-field
                v-model="employee.sinNumber"
                :label="$t('pim.sin_number')"
                :rules="rules.sinNumber"
                :disabled="!$can.update(`personal_sensitive_information`)"
              />
            </oxd-grid-item>
          </oxd-grid>-->
        </oxd-form-row>

        <oxd-divider />
        <oxd-form-row>
          <!--<oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.nationality"
                type="select"
                :label="$t('general.nationality')"
                :clear="false"
                :options="nationalities"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.maritalStatus"
                type="select"
                :label="$t('pim.marital_status')"
                :clear="false"
                :options="maritalStatuses"
              />
            </oxd-grid-item>
          </oxd-grid>-->
          <oxd-grid
            v-if="isCandidate"
            :cols="3"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.birthday"
                :label="$t('pim.date_of_birth')"
                :rules="rules.birthday"
                placeholder="23-10-2000"
              />
            </oxd-grid-item>
            <!--
            <oxd-grid-item>
              <oxd-input-group
                :label="$t('pim.gender')"
                :classes="{wrapper: '--gender-grouped-field'}"
              >
                <oxd-input-field
                  v-model="employee.gender"
                  type="radio"
                  :option-label="$t('general.male')"
                  value="1"
                />
                <oxd-input-field
                  v-model="employee.gender"
                  type="radio"
                  :option-label="$t('general.female')"
                  value="2"
                />
              </oxd-input-group>
            </oxd-grid-item>
            -->
          </oxd-grid>
          <oxd-grid v-else :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="employee.companyWorkforce"
                type="select"
                :label="$t('company.workforce')"
                :options="workforces"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />
        <div v-if="isCandidate">
          <oxd-form-row>
            <oxd-grid :cols="2" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="employee.studyLevel"
                  type="select"
                  :label="$t('general.study_level')"
                  :options="studyLevels"
                />
                <!--
                <oxd-input-field :label="$t('pim.degree_and_certification')" />
                -->
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <oxd-divider />
          <oxd-form-row>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="employee.need"
                  type="select"
                  :label="$t('general.need')"
                  :options="needs"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="employee.courseStart"
                  type="select"
                  :label="$t('general.course_start')"
                  :options="courseStarts"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="employee.salary"
                  :label="$t('pim.salary_expectation')"
                  :placeholder="$t('pim.salary_expectation_holder')"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <!--
          <oxd-divider />
          <oxd-form-row>
            <oxd-grid-item>
              <oxd-input-field
                :label="$t('pim.professional_experience_description')"
              />
            </oxd-grid-item>
          </oxd-form-row>
          -->
          <oxd-divider />
          <oxd-form-row>
            <oxd-grid-item
              class="orangehrm-save-candidate-page --span-column-2"
            >
              <oxd-input-field
                v-model="employee.motivation"
                :label="$t('Motivation')"
                type="textarea"
              />
            </oxd-grid-item>
          </oxd-form-row>
          <oxd-divider />
          <oxd-form-row>
            <oxd-text>{{ $t('pim.driving_licences') }}</oxd-text>
            <oxd-grid
              :cols="drivingLicenses.length"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item
                v-for="(elem, elemIndex) in drivingLicenses"
                :key="`${elemIndex}-${elem}`"
              >
                <oxd-input-field
                  v-model="employee.checkedPermits"
                  type="checkbox"
                  :label="elem.label"
                  :value="elem.label"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          <!--
          <oxd-divider />
          <oxd-form-row>
            <oxd-text>Permis obtenus</oxd-text>
            <oxd-grid :cols="4" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  type="checkbox"
                  :label="'Permis A'"
                  :value="'Permis A'"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  type="checkbox"
                  :label="'Permis B'"
                  :value="'Permis B'"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  type="checkbox"
                  :label="'Permis BE'"
                  :value="'Permis BE'"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  type="checkbox"
                  :label="'Permis C'"
                  :value="'Permis C'"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
          -->

          <oxd-divider v-if="showDeprecatedFields" />
          <oxd-form-row v-if="showDeprecatedFields">
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="employee.militaryService"
                  :label="$t('pim.military_service')"
                  :rules="rules.militaryService"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="employee.smoker"
                  type="checkbox"
                  :label="$t('pim.smoker')"
                  :option-label="$t('general.yes')"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>

          <oxd-divider />
        </div>
        <oxd-form-actions>
          <required-text />
          <submit-button />
        </oxd-form-actions>
      </oxd-form>
    </div>
  </edit-employee-layout>
</template>

<script>
import {navigate} from '@/core/util/helper/navigation';
import {urlFor} from '@/core/util/helper/url';
import {APIService} from '@/core/util/services/api.service';
import EditEmployeeLayout from '../../components/EditEmployeeLayout';
import FullNameInput from '../../components/FullNameInput';
import {
  required,
  shouldNotExceedCharLength,
  validDateFormat,
  validDateFormatFrench,
  maxFileSize,
  validFileTypes,
  validWebsiteFormat,
  shouldBeCurrentOrPreviousDateFrench,
} from '@/core/util/validation/rules';
import useDateFormat from '@/core/util/composable/useDateFormat';
import FileUploadInput from '@/core/components/inputs/FileUploadInput';
import {formatDate, parseDate} from '@/core/util/helper/datefns';

const employeeModel = {
  firstName: '',
  middleName: '',
  lastName: '',
  employeeId: '',
  otherId: '',
  drivingLicenseNo: '',
  drivingLicenseExpiredDate: '',
  ssnNumber: '',
  sinNumber: '',
  nationality: [],
  maritalStatus: [],
  need: [],
  checkedPermits: [],
  motivation: '',
  salary: '',
  studyLevel: [],
  courseStart: [],
  birthday: '',
  gender: '',
  nickname: '',
  smoker: '',
  militaryService: '',
  companyName: null,
  companySiret: '',
  companyWebsite: '',
  companyPresentation: '',
  companyWorkforce: [],
  companyNafCode: [],
  resume: null,
  attachment: null,
};

const EmployeeAttachmentModel = {
  id: null,
  oldAttachment: {},
  newAttachment: null,
  method: 'replaceCurrent',
};

export default {
  components: {
    'edit-employee-layout': EditEmployeeLayout,
    'full-name-input': FullNameInput,
    'file-upload-input': FileUploadInput,
  },

  props: {
    empNumber: {
      type: String,
      required: true,
    },
    nationalities: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    workforces: {
      type: Array,
      default: () => [],
    },
    nafCodes: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    courseStarts: {
      type: Array,
      default: () => [],
    },
    drivingLicenses: {
      type: Array,
      default: () => [],
    },
    showDeprecatedFields: {
      type: Boolean,
      default: false,
    },
    showSsnField: {
      type: Boolean,
      default: false,
    },
    showSinField: {
      type: Boolean,
      default: false,
    },
    allowedFileTypes: {
      type: Array,
      required: true,
    },
    maxFileSize: {
      type: Number,
      required: true,
    },
  },

  setup(props) {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/pim/employees/${props.empNumber}/personal-details`,
    );
    const {userDateFormat} = useDateFormat();

    return {
      http,
      userDateFormat,
    };
  },

  data() {
    return {
      isLoading: false,
      employee: {...employeeModel},
      attachment: {...EmployeeAttachmentModel},
      rules: {
        firstName: [required, shouldNotExceedCharLength(30)],
        companyName: [required, shouldNotExceedCharLength(50)],
        middleName: [shouldNotExceedCharLength(30)],
        lastName: [required, shouldNotExceedCharLength(30)],
        employeeId: [shouldNotExceedCharLength(10)],
        otherId: [shouldNotExceedCharLength(30)],
        drivingLicenseNo: [shouldNotExceedCharLength(30)],
        ssnNumber: [shouldNotExceedCharLength(30)],
        sinNumber: [shouldNotExceedCharLength(30)],
        nickname: [shouldNotExceedCharLength(30)],
        militaryService: [shouldNotExceedCharLength(30)],
        birthday: [
          validDateFormatFrench('dd-MM-yyyy'),
          shouldBeCurrentOrPreviousDateFrench(),
        ],
        website: [validWebsiteFormat()],
        drivingLicenseExpiredDate: [validDateFormat(this.userDateFormat)],
        attachment: [
          maxFileSize(this.maxFileSize),
          validFileTypes(this.allowedFileTypes),
        ],
      },
      maritalStatuses: [
        {id: 'Single', label: this.$t('pim.single')},
        {id: 'Married', label: this.$t('pim.married')},
        {id: 'Other', label: this.$t('pim.other')},
      ],
      isCandidate: true,
    };
  },
  computed: {
    formattedFileSize() {
      return Math.round((this.maxFileSize / (1024 * 1024)) * 100) / 100;
    },
  },
  beforeMount() {
    this.isLoading = true;
    this.http
      .getAll()
      .then((response) => {
        this.updateModel(response);
        return this.http.request({
          method: 'GET',
          url: '/api/v2/pim/employees',
        });
      })
      /*.then((response) => {
        const {data} = response.data;
        this.rules.employeeId.push((v) => {
          const index = data.findIndex(
            (item) =>
              item.employeeId?.trim() &&
              String(item.employeeId).toLowerCase() == String(v).toLowerCase(),
          );
          if (index > -1) {
            const {empNumber} = data[index];
            return empNumber != this.empNumber
              ? this.$t('pim.employee_id_exists')
              : true;
          } else {
            return true;
          }
        });
      })*/
      .finally(() => {
        this.isLoading = false;
      });
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          data: {
            lastName: this.employee.lastName,
            firstName: this.employee.firstName,
            middleName: this.employee.middleName,
            employeeId: this.employee.employeeId,
            otherId: this.employee.otherId,
            drivingLicenseNo: this.employee.drivingLicenseNo,
            drivingLicenseExpiredDate: this.employee.drivingLicenseExpiredDate,
            gender: this.employee.gender,
            maritalStatus: this.employee.maritalStatus?.id,
            birthday: this.employee.birthday
              ? formatDate(
                  parseDate(this.employee.birthday, 'dd-MM-yyyy'),
                  'yyyy-MM-dd',
                )
              : undefined,
            nationalityId: this.employee.nationality?.id,
            ssnNumber: this.showSsnField ? this.employee.ssnNumber : undefined,
            sinNumber: this.showSinField ? this.employee.sinNumber : undefined,
            need: this.employee.need?.label,
            drivingLicense: this.employee.checkedPermits
              ? JSON.stringify(this.employee.checkedPermits)
              : undefined,
            motivation: this.employee.motivation,
            salary: this.employee.salary,
            studyLevel: this.employee.studyLevel?.label,
            courseStart: this.employee.courseStart?.label,
            attachment: this.attachment.newAttachment,
            attachmentMethod: this.attachment.method,
            attachmentId: this.attachment.id,
            companyName: this.employee.companyName,
            companyNafCode: this.employee.companyNafCode?.label,
            companyWorkforce: this.employee.companyWorkforce?.label,
            companySiret: this.employee.companySiret,
            companyWebsite: this.employee.companyWebsite,
            companyPresentation: this.employee.companyPresentation,
            nickname: this.showDeprecatedFields
              ? this.employee.nickname
              : undefined,
            smoker: this.showDeprecatedFields
              ? this.employee.smoker
              : undefined,
            militaryService: this.showDeprecatedFields
              ? this.employee.militaryService
              : undefined,
          },
        })
        .then((response) => {
          this.updateModel(response);
          return this.$toast.updateSuccess();
        })
        .then(() => {
          this.isLoading = false;
          navigate(
            `/${window.appGlobal.theme}/pim/contactDetails/empNumber/${this.empNumber}`,
          );
        });
    },

    updateModel(response) {
      const {data} = response.data;
      this.employee = {...employeeModel, ...data};
      if (this.employee.birthday) {
        this.employee.birthday = formatDate(
          new Date(this.employee.birthday),
          'dd-MM-yyyy',
        );
      }
      if (data.drivingLicense && data.drivingLicense != '') {
        try {
          this.employee.checkedPermits = JSON.parse(data.drivingLicense);
        } catch (e) {
          this.employee.checkedPermits = [];
        }
      } else {
        this.employee.checkedPermits = [];
      }
      this.isCandidate = this.employee.otherId
        ? JSON.parse(this.employee.otherId).includes(
            process.env.VUE_APP_CANDIDATE_ROLE_NAME,
          ) || JSON.parse(this.employee.otherId).includes('ESS')
        : false;
      this.employee.maritalStatus = this.maritalStatuses.find(
        (item) => item.id === data.maritalStatus,
      );
      this.employee.nationality = this.nationalities.find(
        (item) => item.id === data.nationality?.id,
      );
      if (this.isCandidate) {
        this.employee.need = this.needs.find(
          (item) => item.label === data.need,
        );
        this.employee.studyLevel = this.studyLevels.find(
          (item) => item.label === data.studyLevel,
        );
        this.employee.courseStart = this.courseStarts.find(
          (item) => item.label === data.courseStart,
        );
        if (this.employee.resume && this.employee.resume != -1) {
          this.http
            .request({
              method: 'GET',
              url: `/api/v2/pim/attachments/` + this.employee.resume,
            })
            .then(({data: {data}}) => {
              this.attachment.id = data.id;
              this.attachment.newAttachment = null;
              this.attachment.oldAttachment = {
                id: data.id,
                filename: data.attachment.fileName,
                fileType: data.attachment.fileType,
                fileSize: data.attachment.fileSize,
              };
              this.attachment.method = 'keepCurrent';
            });
        } else {
          this.attachment = {...EmployeeAttachmentModel};
        }
      } else {
        this.employee.companyWorkforce = this.workforces.find(
          (item) => item.label === data.companyWorkforce,
        );
        this.employee.companyNafCode = this.nafCodes.find(
          (item) => item.label === data.companyNafCode,
        );
        if (this.employee.attachment && this.employee.attachment != -1) {
          this.http
            .request({
              method: 'GET',
              url: `/api/v2/pim/attachments/` + this.employee.attachment,
            })
            .then(({data: {data}}) => {
              this.attachment.id = data.id;
              this.attachment.newAttachment = null;
              this.attachment.oldAttachment = {
                id: data.id,
                filename: data.attachment.fileName,
                fileType: data.attachment.fileType,
                fileSize: data.attachment.fileSize,
              };
              this.attachment.method = 'keepCurrent';
            });
        } else {
          this.attachment = {...EmployeeAttachmentModel};
        }
      }
    },
    getAttachmentUrl() {
      return urlFor(
        `/${window.appGlobal.theme}/pim/viewAttachment/attachId/{attachId}`,
        {
          attachId: this.isCandidate
            ? this.employee.resume
            : this.employee.attachment,
        },
      );
    },
  },
};
</script>

<style src="./employee.scss" lang="scss" scoped></style>
