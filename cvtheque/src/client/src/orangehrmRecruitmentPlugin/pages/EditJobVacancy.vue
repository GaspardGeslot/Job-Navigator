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
  <back-button :to="`/recruitment/viewJobVacancy/${this.matchingId}`">
  </back-button>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <oxd-text tag="h6" class="orangehrm-main-title">
        {{ $t('recruitment.edit_vacancy') }}
      </oxd-text>
      <oxd-divider />
      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="vacancy.name"
              :label="$t('recruitment.vacancy_name')"
              required
            />
          </oxd-grid-item>
        </oxd-grid>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="jobSector"
              type="select"
              :label="$t('recruitment.job_sector')"
              :options="sectors"
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-group :classes="{wrapper: '--status-grouped-field'}">
              <template #label>
                <div class="label-is-entitlement-situational">
                  <oxd-label :label="$t('Métier*')" />
                  <oxd-icon-button
                    style="margin-left: 5px; font-size: 12px"
                    name="exclamation-circle"
                    :with-container="false"
                    @click="onModalOpen"
                  />
                </div>
              </template>
              <oxd-input-field
                v-model="vacancy.jobSelected"
                type="select"
                :options="jobTitlesPerSector"
                required
              />
            </oxd-input-group>
            <oxd-text class="orangehrm-input-hint" tag="p">
              {{
                $t(
                  'Veuillez sélectionner la famille avant de pouvoir choisir le métier.',
                )
              }}
            </oxd-text>
          </oxd-grid-item>
        </oxd-grid>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item class="orangerhrm-switch-wrapper">
            <oxd-text class="orangehrm-text" tag="p">
              {{ $t('general.active') }}
            </oxd-text>
            <oxd-switch-input v-model="vacancy.status" />
          </oxd-grid-item>
        </oxd-grid>
        <br />

        <oxd-divider />
        <br />
        <oxd-form-row>
          <oxd-icon class="warning-icon" name="exclamation-triangle-fill" />
          <oxd-label
            :label="$t('recruitment.select_only_conditions_for_matching')"
          />
          <oxd-text class="orangehrm-input-hint">
            <i>
              {{
                $t(
                  '(Si vous voulez maximiser les opportunités de candidatures, ne cochez aucun critère, la mise en relation se fera uniquement sur le métier recherché)',
                )
              }}
            </i>
          </oxd-text>
        </oxd-form-row>
        <br />
        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('pim.work_experience_global') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in professionalExperiences"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedProfessionalExperiences"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('general.need_search') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in needs"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedNeeds"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('general.course_start_option') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in courseStarts"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedCourseStarts"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('general.study_level') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in studyLevels"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedStudyLevels"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>

        <oxd-divider />
        <oxd-text class="orangehrm-sub-title" tag="h6">
          {{ $t('pim.license') }}
        </oxd-text>
        <oxd-grid :cols="4" class="orangehrm-full-width-grid">
          <oxd-grid-item
            v-for="(elem, elemIndex) in drivingLicenses"
            :key="`${elemIndex}-${elem}`"
          >
            <oxd-input-field
              v-model="vacancy.checkedDrivingLicences"
              type="checkbox"
              :label="elem.label"
              :value="elem.label"
            />
          </oxd-grid-item>
        </oxd-grid>
        <br />
        <br />
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
      <job-category-selection-modal
        v-if="showModal"
        @close="onModalClose"
      ></job-category-selection-modal>
    </div>
  </div>
</template>
<script>
import {APIService} from '@/core/util/services/api.service';
import {navigate} from '@ohrm/core/util/helper/navigation';
import BackButton from '@/core/components/buttons/BackButton';
//import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';
//import FileUploadInput from '@/core/components/inputs/FileUploadInput';
import {
  required,
  numericOnly,
  maxFileSize,
  validSelection,
  validFileTypes,
  shouldNotExceedCharLength,
  numberShouldBeBetweenMinAndMaxValue,
} from '@ohrm/core/util/validation/rules';
/*import EmployeeAutocomplete from '@/core/components/inputs/EmployeeAutocomplete';
import JobtitleDropdown from '@/orangehrmPimPlugin/components/JobtitleDropdown';
import VacancyLinkCard from '../components/VacancyLinkCard.vue';*/
import {OxdSwitchInput} from '@ohrm/oxd';
import {OxdLabel} from '@ohrm/oxd';
import {OxdIcon} from '@ohrm/oxd';
import useServerValidation from '@/core/util/composable/useServerValidation';
import JobCategorySelectionModal from '../components/JobCategorySelectionModal.vue';

const vacancyModel = {
  jobTitle: null,
  jobSelected: null,
  name: '',
  hiringManager: null,
  checkedProfessionalExperiences: [],
  checkedDrivingLicences: [],
  checkedNeeds: [],
  checkedCourseStarts: [],
  checkedStudyLevels: [],
  numOfPositions: '',
  description: '',
  status: true,
  isPublished: true,
};

const VacancyAttachmentModel = {
  id: null,
  comment: '',
  oldAttachment: {},
  newAttachment: null,
  method: 'keepCurrent',
};

const basePath = `${window.location.protocol}//${window.location.host}${window.appGlobal.baseUrl}`;

const attachmentNormalizer = (data) => {
  return data.map((item) => {
    return {
      id: item.id,
      vacancyId: item.vacancyId,
      fileName: item.attachment.fileName,
      fileSize: +(item.attachment.fileSize / 1024).toFixed(2) + ' kb',
      fileType: item.attachment.fileType,
      comment: item.comment,
      attachmentType: item.attachmentType,
    };
  });
};

export default {
  components: {
    'oxd-switch-input': OxdSwitchInput,
    'back-button': BackButton,
    'oxd-label': OxdLabel,
    'oxd-icon': OxdIcon,
    'job-category-selection-modal': JobCategorySelectionModal,
    /*'employee-autocomplete': EmployeeAutocomplete,
    'jobtitle-dropdown': JobtitleDropdown,
    'vacancy-link-card': VacancyLinkCard,
    'delete-confirmation': DeleteConfirmationDialog,
    'file-upload-input': FileUploadInput,*/
  },

  props: {
    professionalExperiences: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    drivingLicenses: {
      type: Array,
      default: () => [],
    },
    courseStarts: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    sectors: {
      type: Array,
      default: () => [],
    },
    matchingId: {
      type: String,
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
  },
  setup(props) {
    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/vacancies',
    );
    const httpAttachments = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/vacancy/attachments',
    );
    const {createUniqueValidator} = useServerValidation(http);
    const vacancyNameUniqueValidation = createUniqueValidator(
      'Vacancy',
      'name',
      {entityId: props.matchingId},
    );
    return {
      http,
      httpAttachments,
      vacancyNameUniqueValidation,
    };
  },
  data() {
    return {
      showModal: false,
      jobSector: null,
      jobTitlesPerSector: [],
      isLoading: false,
      isLoadingAttachment: false,
      isLoadingTable: false,
      isAddClicked: false,
      isEditClicked: false,
      currentName: '',
      vacancy: {...vacancyModel},
      vacancyAttachment: {...VacancyAttachmentModel},
      rules: {
        jobTitle: [required],
        name: [
          required,
          this.vacancyNameUniqueValidation,
          shouldNotExceedCharLength(50),
        ],
        hiringManager: [
          required,
          validSelection,
          (v) => (v?.isPastEmployee ? this.$t('general.invalid') : true),
        ],
        numOfPositions: [
          (value) => {
            if (value === null || value === '') return true;
            return typeof numericOnly(value) === 'string'
              ? numericOnly(value)
              : numberShouldBeBetweenMinAndMaxValue(1, 99)(value);
          },
        ],
        description: [],
        status: [required],
        isPublished: [required],
        addAttachment: [
          required,
          maxFileSize(this.maxFileSize),
          validFileTypes(this.allowedFileTypes),
        ],
        updateAttachment: [
          (v) => {
            if (this.vacancyAttachment.method == 'replaceCurrent') {
              return required(v);
            } else {
              return true;
            }
          },
          validFileTypes(this.allowedFileTypes),
          maxFileSize(this.maxFileSize),
        ],
        comment: [shouldNotExceedCharLength(200)],
      },
      headers: [
        {
          name: 'fileName',
          slot: 'title',
          title: this.$t('general.file_name'),
          style: {flex: 3},
        },
        {
          name: 'fileSize',
          title: this.$t('general.file_size'),
          style: {flex: 2},
        },
        {
          name: 'fileType',
          title: this.$t('general.file_type'),
          style: {flex: 2},
        },
        {
          name: 'comment',
          title: this.$t('general.comment'),
          style: {flex: 4},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 2},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            delete: {
              onClick: this.onClickDelete,
              component: 'oxd-icon-button',
              props: {
                name: 'trash',
              },
            },
            download: {
              onClick: this.downloadFile,
              props: {
                name: 'download',
              },
            },
            edit: {
              onClick: this.onClickEdit,
              props: {
                name: 'pencil-fill',
              },
            },
          },
        },
      ],
      attachments: [],
      checkedItems: [],
      rssFeedUrl: `${basePath}/recruitmentApply/jobs.rss`,
      webUrl: `${basePath}/recruitmentApply/jobs.html`,
    };
  },
  computed: {
    formattedFileSize() {
      return Math.round((this.maxFileSize / (1024 * 1024)) * 100) / 100;
    },
  },
  created() {
    this.isLoading = true;
    this.isLoadingTable = true;

    this.http
      .get(this.matchingId)
      .then((response) => {
        //console.log('Data : ', response.data);
        const {data} = response.data;
        this.currentName = data.name;
        this.vacancy.name = data.name;
        this.vacancy.description = data.description;
        this.vacancy.numOfPositions = data.numOfPositions || '';
        this.vacancy.status = data.status;
        this.vacancy.checkedProfessionalExperiences = JSON.parse(
          data.professionalExperiences,
        );
        this.vacancy.checkedNeeds = JSON.parse(data.needs);
        this.vacancy.checkedStudyLevels = JSON.parse(data.levels);
        this.vacancy.checkedCourseStarts = JSON.parse(data.courseStarts);
        this.vacancy.checkedDrivingLicences = JSON.parse(data.drivingLicenses);
        const firstJob =
          data.jobs && data.jobs.length > 0 ? JSON.parse(data.jobs)[0] : null;
        if (this.sectors && firstJob) {
          this.sectors.forEach((sector) => {
            const jobIndex = sector.jobs.findIndex((job) => job === firstJob);
            if (jobIndex !== -1) {
              this.vacancy.jobSelected = {id: jobIndex, label: firstJob};
              this.jobSector = sector;
              return;
            }
          });
        }
        /*this.vacancy.isPublished = data.isPublished;
        this.vacancy.hiringManager = data.hiringManager.id
          ? {
              id: data.hiringManager.id,
              label: `${data.hiringManager.firstName} ${data.hiringManager.middleName} ${data.hiringManager.lastName}`,
              isPastEmployee: data.hiringManager.terminationId ? true : false,
            }
          : this.$t('general.deleted');
        this.vacancy.jobTitle = data.jobTitle.isDeleted
          ? null
          : {
              id: data.jobTitle.id,
              label: data.jobTitle.title,
            };*/
      })
      /*.then(() => {
        this.httpAttachments
          .request({
            method: 'GET',
            url: `/api/v2/recruitment/vacancies/${this.matchingId}/attachments`,
          })
          .then((response) => {
            const {data} = response.data;
            this.attachments = attachmentNormalizer(data);
          });
      })*/
      .finally(() => {
        this.isLoadingTable = false;
        this.isLoading = false;
      });
  },
  watch: {
    jobSector(newVal) {
      if (newVal) {
        const selectedSector = this.sectors.find(
          (sector) => sector.label === newVal.label,
        );
        this.jobTitlesPerSector = selectedSector
          ? selectedSector.jobs.map((job, index) => {
              return {id: index, label: job};
            })
          : [];
      } else this.jobTitlesPerSector = [];
    },
  },
  methods: {
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
    onCancel() {
      navigate(`/recruitment/viewJobVacancy/${this.matchingId}`);
    },
    onSave() {
      this.isLoading = true;
      this.vacancy = {
        name: this.vacancy.name,
        //jobTitleId: this.vacancy.jobTitle?.id,
        jobTitle: this.vacancy.jobSelected?.label,
        /*countries: JSON.stringify(
          this.vacancy.countries?.map((country) => {
            return country.label;
          }),
        ),*/
        professionalExperiences: JSON.stringify(
          this.vacancy.checkedProfessionalExperiences,
        ),
        drivingLicenses: JSON.stringify(this.vacancy.checkedDrivingLicences),
        needs: JSON.stringify(this.vacancy.checkedNeeds),
        courseStarts: JSON.stringify(this.vacancy.checkedCourseStarts),
        studyLevels: JSON.stringify(this.vacancy.checkedStudyLevels),
        //employeeId: this.vacancy.hiringManager?.id,
        numOfPositions: this.vacancy.numOfPositions
          ? parseInt(this.vacancy.numOfPositions)
          : null,
        //description: this.vacancy.description,
        status: this.vacancy.status,
        //isPublished: this.vacancy.isPublished,
      };
      this.http
        .update(this.matchingId, {...this.vacancy})
        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          navigate(`/recruitment/viewJobVacancy/${this.matchingId}`);
        });
    },
    /*onSaveAttachment() {
      this.isLoadingAttachment = true;
      this.isLoadingTable = true;
      this.httpAttachments
        .create({
          vacancyId: parseInt(this.vacancyId),
          attachment: this.vacancyAttachment.newAttachment
            ? this.vacancyAttachment.newAttachment
            : undefined,
          comment: this.vacancyAttachment.comment,
          attachmentType: 1,
        })
        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.updateVisibility();
          this.resetDataTable();
          this.isLoadingAttachment = false;
          this.isLoadingTable = false;
        });
    },*/
    onClickDelete(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteData([item.id]);
        }
      });
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.attachments[index]?.id;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteData(ids);
        }
      });
    },

    async deleteData(items) {
      if (items instanceof Array) {
        this.isLoadingTable = true;
        this.httpAttachments
          .deleteAll({
            ids: items,
          })
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.resetDataTable();
            this.isLoadingTable = false;
          });
      }
    },
    /*resetDataTable() {
      this.checkedItems = [];
      this.httpAttachments
        .request({
          method: 'GET',
          url: `/api/v2/recruitment/vacancies/${this.vacancyId}/attachments`,
        })
        .then((response) => {
          const {data} = response.data;
          this.attachments = attachmentNormalizer(data);
        });
    },*/
    onClickAdd() {
      this.isEditClicked = false;
      this.isAddClicked = true;
    },
    onClickEdit(item) {
      this.vacancyAttachment.id = item.id;
      this.vacancyAttachment.comment = item.comment;
      this.vacancyAttachment.oldAttachment = {
        id: item.id,
        filename: item.fileName,
        fileType: item.fileType,
        fileSize: item.filefileSize,
      };
      this.vacancyAttachment.newAttachment = null;
      this.vacancyAttachment.method = 'keepCurrent';
      this.isAddClicked = false;
      this.isEditClicked = true;
    },
    /*onUpdateAttachment() {
      this.isLoadingAttachment = true;
      this.isLoadingTable = true;
      this.httpAttachments
        .request({
          method: 'PUT',
          url: `/api/v2/recruitment/vacancies/${this.vacancyId}/attachments/${this.vacancyAttachment.id}`,
          data: {
            vacancyId: parseInt(this.vacancyId),
            currentAttachment: this.vacancyAttachment.oldAttachment
              ? this.vacancyAttachment.method
              : undefined,
            attachment: this.vacancyAttachment.newAttachment
              ? this.vacancyAttachment.newAttachment
              : undefined,
            comment: this.vacancyAttachment.comment,
            attachmentType: 1,
          },
        })
        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.updateVisibility();
          this.resetDataTable();
          this.isLoadingAttachment = false;
          this.isLoadingTable = false;
        });
    },*/
    updateVisibility() {
      this.isAddClicked = false;
      this.isEditClicked = false;
      this.vacancyAttachment = {...VacancyAttachmentModel};
    },
    downloadFile(item) {
      if (!item?.id) return;
      const fileUrl = 'recruitment/viewVacancyAttachment/attachId';
      const downUrl = `${window.appGlobal.baseUrl}/${fileUrl}/${item.id}`;
      window.open(downUrl, '_blank');
    },
  },
};
</script>

<style src="./vacancy.scss" lang="scss" scoped></style>
