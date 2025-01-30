<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">
          {{ $t('recruitment.company_profile') }}
        </oxd-text>
      </div>

      <oxd-divider v-show="!isLoading" />

      <oxd-form :loading="isLoading">
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('general.candidate_info') }}
          </oxd-text>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.companyName"
                :label="$t('company.name')"
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.companyEmailContact"
                :label="$t('general.email')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.companyPhoneNumberContact"
                :label="$t('recruitment.contact_number')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.companyWebsite"
                :label="$t('pim.web_site')"
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item
              v-if="profile.attachment && profile.attachment !== -1"
            >
              <file-upload-input
                v-model:newFile="attachment.newAttachment"
                v-model:method="attachment.method"
                :label="$t('company.company_presentation_file')"
                :file="attachment.oldAttachment"
                :disabled="!editable"
                :url="getAttachmentUrl"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item
              class="orangehrm-save-candidate-page --span-column-2"
            >
              <oxd-input-field
                v-model="profile.companyPresentation"
                :label="$t('pim.presentation')"
                type="textarea"
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text
            v-if="profile.jobs.length > 1"
            class="orangehrm-sub-title"
            tag="h6"
          >
            {{ $t('pim.job_details') }}
          </oxd-text>
          <oxd-text v-else class="orangehrm-sub-title" tag="h6">
            {{ $t('general.job_title') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item
              v-for="(job, jobIndex) in profile.jobs"
              :key="jobIndex"
            >
              <oxd-input-field :value="job" :disabled="!editable" />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('pim.contact_details') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.street1"
                :label="$t('pim.street1')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.city"
                :label="$t('general.city')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.zipCode"
                :label="$t('general.zip_postal_code')"
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('general.candidat_details') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.companyWorkforce"
                :label="$t('company.workforce')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.companyNafCode"
                :label="$t('company.naf_code')"
                :disabled="!editable"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
      </oxd-form>
    </div>
  </div>
</template>

<script>
import {urlFor} from '@/core/util/helper/url';
import {APIService} from '@/core/util/services/api.service';
import FileUploadInput from '@/core/components/inputs/FileUploadInput';
import useDateFormat from '@/core/util/composable/useDateFormat';

const CompanyProfileModel = {
  province: '',
  companyNafCode: '',
  companyWorkforce: '',
  companyWebsite: '',
  companyName: '',
  companySiret: '',
  companyEmailContact: '',
  companyPhoneNumberContact: '',
  jobs: [],
  street1: '',
  city: '',
  zipCode: '',
  companyPresentation: '',
  attachment: null,
};

const CompanyAttachmentModel = {
  id: null,
  oldAttachment: {},
  newAttachment: null,
  method: 'replaceCurrent',
};

export default {
  name: 'CompanyProfile',
  components: {
    'file-upload-input': FileUploadInput,
  },
  props: {
    company: {
      type: Object,
      required: true,
    },
    allowedFileTypes: {
      type: Array,
      required: true,
    },
    maxFileSize: {
      type: Number,
      required: true,
    },
    updatable: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  emits: ['update'],
  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '/');
    const {userDateFormat} = useDateFormat();

    return {
      http,
      userDateFormat,
    };
  },
  data() {
    return {
      editable: false,
      isLoading: false,
      profile: {...CompanyProfileModel},
      attachment: {...CompanyAttachmentModel},
    };
  },
  computed: {
    formattedFileSize() {
      return Math.round((this.maxFileSize / (1024 * 1024)) * 100) / 100;
    },
  },
  watch: {
    company() {
      this.fetchCompany();
    },
  },
  beforeMount() {
    this.fetchCompany();
  },
  methods: {
    getAttachmentUrl() {
      return urlFor(
        `/${window.appGlobal.theme}/recruitment/viewCandidateAttachment/candidateId/{attachmentId}`,
        {attachmentId: this.company.attachment},
      );
    },
    fetchCompany() {
      this.isLoading = true;
      this.profile.companyName = this.company.companyName;
      this.profile.companyEmailContact = this.company.companyEmailContact;
      this.profile.companyPhoneNumberContact =
        this.company.companyPhoneNumberContact;
      this.profile.companyPresentation = this.company.companyPresentation;
      this.profile.companySiret = this.company.companySiret;
      this.profile.companyWebsite = this.company.companyWebsite;
      this.profile.companyWorkforce = this.company.companyWorkforce;
      this.profile.companyNafCode = this.company.companyNafCode;
      this.profile.jobs =
        this.company.jobs && JSON.parse(this.company.jobs).length > 0
          ? JSON.parse(this.company.jobs)
          : [];
      this.profile.street1 = this.company.street1;
      this.profile.city = this.company.city;
      this.profile.zipCode = this.company.zipcode;
      this.profile.province = this.company.province;
      this.profile.attachment = this.company.attachment;
      if (this.company.attachment && this.company.attachment !== -1) {
        this.http
          .request({
            method: 'GET',
            url: `/api/v2/recruitment/candidate/${this.company.attachment}/attachment`,
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
        this.attachment = {...CompanyAttachmentModel};
      }
      this.isLoading = false;
    },
  },
};
</script>

<style scoped lang="scss">
.orangehrm-header-container {
  padding: 0;
}
.orangehrm-candidate-grid-checkbox {
  .oxd-input-group {
    flex-direction: row-reverse;
    justify-content: flex-end;
  }
}
</style>
