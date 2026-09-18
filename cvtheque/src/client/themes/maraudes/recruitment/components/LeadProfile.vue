<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">
          {{
            isCreateMode
              ? $t('Nouveau contact')
              : $t(
                  'Profil complet du contact' +
                    (profile ? ' n°' + profile.id : ''),
                )
          }}
        </oxd-text>
        <oxd-switch-input
          v-if="!isLoading && !isCreateMode"
          v-model="editable"
          :option-label="$t('general.edit')"
          label-position="left"
        />
      </div>

      <oxd-divider v-show="!isLoading" />

      <oxd-form
        :loading="isLoading"
        @submit-valid="isCreateMode ? createLead() : updateLead()"
      >
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('general.candidate_info') }}
          </oxd-text>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <full-name-input
                v-model:first-name="profile.firstName"
                v-model:last-name="profile.lastName"
                :rules="rules"
                :required="true"
                :disabled="!isCreateMode"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item
              style="display: flex; align-items: center; gap: 1rem"
            >
              <oxd-input-field
                v-model="profile.email"
                :label="$t('general.email')"
                :rules="isCreateMode ? rules.email : []"
                :required="isCreateMode"
                :disabled="!isCreateMode"
              />
              <oxd-icon-button
                v-if="!isCreateMode && profile.email"
                style="height: 1px"
                display-type="success"
                name="envelope-fill"
                @click.stop="openClientEmail"
              ></oxd-icon-button>
            </oxd-grid-item>
            <oxd-grid-item
              style="display: flex; align-items: center; gap: 1rem"
            >
              <oxd-input-field
                v-model="profile.phoneNumber"
                :label="$t('recruitment.contact_number')"
                :rules="isCreateMode ? rules.phoneNumber : []"
                :disabled="!isCreateMode"
                :required="isCreateMode"
              />
              <oxd-icon-button
                v-if="!isCreateMode && profile.phoneNumber"
                style="height: 1px"
                display-type="success"
                name="telephone-fill"
                @click.stop="openClientTelephone"
              ></oxd-icon-button>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid
            v-if="
              defaultColumns.gender ||
              defaultColumns.birthDate ||
              (defaultColumns.age && !isCreateMode)
            "
            :cols="3"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item>
              <oxd-input-field
                v-if="defaultColumns.gender"
                v-model="profile.civility"
                :label="$t('Civilité')"
                type="select"
                :options="civilityOptions"
                :disabled="!isSwitchEditable"
              />
            </oxd-grid-item>
            <oxd-grid-item
              v-if="
                defaultColumns.birthDate && (isCreateMode || profile.birthDate)
              "
            >
              <date-input
                v-model="profile.birthDate"
                :label="$t('pim.date_of_birth')"
                :disabled="!isCreateMode"
              />
            </oxd-grid-item>
            <oxd-grid-item
              v-if="defaultColumns.age && !isCreateMode && profile.age"
            >
              <oxd-input-field
                v-model="profile.age"
                :label="$t('Âge')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row v-if="!isCreateMode">
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.date"
                :label="$t('Date de réception')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <div
          v-if="defaultColumns.job || (defaultColumns.sector && !isCreateMode)"
        >
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <div v-if="defaultColumns.job">
              <oxd-text
                v-if="!isCreateMode && profile.jobs.length > 1"
                class="orangehrm-sub-title"
                tag="h6"
              >
                {{ $t('pim.job_details') }}
              </oxd-text>
              <oxd-text v-else class="orangehrm-sub-title" tag="h6">
                {{ $t('general.job_title') }}
              </oxd-text>
            </div>
            <oxd-grid
              v-if="defaultColumns.sector && !isCreateMode"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.sector"
                  :label="$t('Secteur')"
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid
              v-if="defaultColumns.job && isCreateMode"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item>
                <div v-if="isJobSelected" class="orangehrm-selected-job">
                  <oxd-input-field
                    :model-value="selectedJobLabel"
                    :label="$t('Métier')"
                    :disabled="true"
                  />
                  <oxd-button
                    display-type="secondary"
                    :label="$t('general.edit')"
                    @click="replaceSelectedJob"
                  />
                </div>
                <oxd-input-field
                  v-else
                  v-model="selectedJob"
                  type="autocomplete"
                  :label="$t('Métier')"
                  :clear="true"
                  :create-options="loadJobs"
                  :placeholder="$t('Rechercher un métier')"
                  @update:model-value="onJobSelection"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid
              v-else-if="defaultColumns.job"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item
                v-for="(job, jobIndex) in profile.jobs"
                :key="jobIndex"
              >
                <oxd-input-field
                  v-model="profile.jobs[jobIndex]"
                  :disabled="true"
                  :label="$t('Métier n°' + (jobIndex + 1))"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div v-if="defaultColumns.course">
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Formation') }}
            </oxd-text>
            <oxd-grid
              v-if="isCreateMode"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.of"
                  type="select"
                  :label="$t('OF')"
                  :options="ofOptions"
                  @update:model-value="onCreateOfChange"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <div v-if="selectedCourseId" class="orangehrm-selected-course">
                  <oxd-input-field
                    :model-value="selectedCourseLabel"
                    :label="$t('Formation')"
                    :disabled="true"
                  />
                  <oxd-button
                    display-type="secondary"
                    :label="$t('general.edit')"
                    @click="replaceSelectedCourse"
                  />
                </div>
                <oxd-input-field
                  v-else
                  v-model="profile.course"
                  type="autocomplete"
                  :label="$t('Formation')"
                  :clear="true"
                  :disabled="!selectedOfId"
                  :create-options="loadCourses"
                  :placeholder="$t('Rechercher une formation')"
                  @update:model-value="onCourseSelection"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid v-else :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.course"
                  :label="$t('Formation')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.of"
                  :label="$t('OF')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div
          v-if="
            defaultColumns.address ||
            defaultColumns.city ||
            defaultColumns.postalCode ||
            defaultColumns.country ||
            defaultColumns.mobility
          "
        >
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('pim.contact_details') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="defaultColumns.address">
                <oxd-input-field
                  v-model="profile.address"
                  :label="$t('pim.street1')"
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.city">
                <oxd-input-field
                  v-model="profile.city"
                  :label="$t('general.city')"
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.postalCode">
                <oxd-input-field
                  v-model="profile.postalCode"
                  :label="$t('general.zip_postal_code')"
                  :disabled="!isCreateMode"
                  :rules="rules.postalCode"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="defaultColumns.country">
                <oxd-input-field
                  v-model="profile.country"
                  :label="$t('Pays')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('countries') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.mobility">
                <oxd-input-field
                  v-model="profile.mobility"
                  :label="$t('general.mobility')"
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div
          v-if="
            defaultColumns.need ||
            defaultColumns.status ||
            defaultColumns.studyLevel ||
            defaultColumns.courseStart ||
            defaultColumns.trainingMethod ||
            defaultColumns.handicap ||
            defaultColumns.funding ||
            defaultColumns.timeSlot ||
            defaultColumns.professionalExperience
          "
        >
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('general.candidat_details') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="defaultColumns.need">
                <oxd-input-field
                  v-model="profile.need"
                  :label="$t('Besoin')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('needs') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.status">
                <oxd-input-field
                  v-model="profile.currentSituation"
                  :label="$t('Situation actuelle')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('status') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.studyLevel">
                <oxd-input-field
                  v-model="profile.studyLevel"
                  :label="$t('general.study_level')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode
                      ? sortedSelectOptions('studyLevels')
                      : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.courseStart">
                <oxd-input-field
                  v-model="profile.courseStart"
                  :label="$t('Début de formation')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode
                      ? sortedSelectOptions('courseStarts')
                      : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.trainingMethod">
                <oxd-input-field
                  v-model="profile.trainingMethod"
                  :label="$t('Modalité de formation')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode
                      ? sortedSelectOptions('trainingMethods')
                      : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.handicap">
                <oxd-input-field
                  v-model="profile.handicap"
                  :label="$t('Handicap')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('handicaps') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.funding">
                <oxd-input-field
                  v-model="profile.funding"
                  :label="$t('Financement')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('fundings') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.timeSlot">
                <oxd-input-field
                  v-model="profile.timeSlot"
                  :label="$t('Disponibilité')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('timeSlots') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="defaultColumns.professionalExperience">
                <oxd-input-field
                  v-model="profile.professionalExperience"
                  :label="$t('Expérience professionnelle')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode
                      ? sortedSelectOptions('professionalExperiences')
                      : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div v-if="defaultColumns.source && !isCreateMode">
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Source') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="defaultColumns.source">
                <oxd-input-field
                  v-model="profile.source"
                  :label="$t('Source')"
                  :type="isCreateMode ? 'select' : undefined"
                  :options="
                    isCreateMode ? sortedSelectOptions('sources') : undefined
                  "
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <oxd-form-row
          v-if="
            !isCreateMode &&
            (defaultColumns.callBackDate || defaultColumns.contactLogs)
          "
        >
          <oxd-divider></oxd-divider>
          <div class="orangehrm-telephone-contacts-header">
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Prises de contact') }}
            </oxd-text>
            <oxd-button
              v-if="defaultColumns.contactLogs"
              icon-name="plus"
              display-type="secondary"
              :label="$t('general.add')"
              @click="onClickAddTelephoneContact"
            />
          </div>
          <div v-if="defaultColumns.callBackDate">
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <date-input
                  v-model="profile.callBackDate"
                  :label="$t('Relancer à partir de')"
                  :disabled="!isSwitchEditable"
                />
              </oxd-grid-item>
            </oxd-grid>
          </div>
          <div v-if="defaultColumns.contactLogs">
            <div
              v-if="
                formattedTelephoneContacts &&
                formattedTelephoneContacts.length > 0
              "
              class="orangehrm-container"
            >
              <oxd-card-table
                :headers="telephoneContactHeaders"
                :items="formattedTelephoneContacts"
                row-decorator="oxd-table-decorator-card"
              />
            </div>
            <div
              v-else
              class="orangehrm-corporate-directory-nocontent"
              style="
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 1rem 0;
              "
            >
              <img
                :src="noContentPic"
                alt="No Content"
                style="max-width: 60px; margin: 0 0 0.85rem 0"
              />
              <oxd-text tag="p">
                Aucune prise de contact n'a encore été renseignée.
              </oxd-text>
            </div>
          </div>
        </oxd-form-row>

        <oxd-form-row v-if="scopeOptions.length > 0">
          <oxd-divider></oxd-divider>
          <div class="orangehrm-telephone-contacts-header">
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Périmètres') }}
            </oxd-text>
            <oxd-button
              v-if="canAddScope"
              icon-name="plus"
              display-type="secondary"
              :label="$t('general.add')"
              @click="onClickAddScope"
            />
          </div>
          <div v-if="isAddingScope" class="orangehrm-lead-scope-add">
            <oxd-input-field
              v-model="scopeToAdd"
              type="select"
              :label="$t('Périmètre')"
              :options="availableScopeOptions"
            />
            <div class="orangehrm-lead-scope-add-actions">
              <oxd-button
                :label="$t('general.cancel')"
                display-type="ghost"
                @click="onCancelAddScope"
              />
              <oxd-button
                :label="$t('general.add')"
                display-type="secondary"
                :disabled="!scopeToAdd"
                :loading="isSavingScope"
                @click="onConfirmAddScope"
              />
            </div>
          </div>
          <oxd-grid
            v-if="leadScopes.length > 0"
            :cols="3"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item
              v-for="scope in leadScopes"
              :key="scope.id"
              class="orangehrm-lead-scope-item"
            >
              <oxd-input-field
                :model-value="scope.title"
                :label="''"
                :disabled="true"
              />
              <oxd-icon-button
                v-if="isCreateMode || scope.onlyScope"
                style="height: 1px"
                name="trash"
                :title="$t('general.delete')"
                @click="onClickRemoveScope(scope)"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <div v-if="defaultColumns.complement || defaultColumns.comment">
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Informations additionnelles') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="defaultColumns.complement">
                <oxd-input-field
                  v-model="profile.complement"
                  :label="$t('Complément')"
                  :disabled="!isCreateMode"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid
              v-if="defaultColumns.comment"
              :cols="1"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.comment"
                  :label="$t('Commentaire')"
                  type="textarea"
                  :disabled="!isSwitchEditable"
                  :rules="rules.comment"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div v-if="customColumns && customColumns.length > 0">
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Colonnes personnalisées') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <custom-column-input
                v-for="customColumn in customColumns"
                :key="customColumn.id"
                :column-id="customColumn.id"
                :title="customColumn.title"
                :type="customColumn.type"
                :options="customColumn.options"
                :value="getCustomColumnValue(customColumn.id)"
                :editable="isSwitchEditable"
                @update:value="
                  (value) => updateCustomColumnValue(customColumn.id, value)
                "
              />
            </oxd-grid>
          </oxd-form-row>
        </div>

        <oxd-divider></oxd-divider>
        <oxd-form-actions>
          <required-text />
          <oxd-button
            v-if="isCreateMode"
            class="orangehrm-left-space"
            display-type="secondary"
            :label="$t('Créer')"
            type="submit"
          />
          <submit-button v-else-if="isSwitchEditable" />
        </oxd-form-actions>
      </oxd-form>
    </div>

    <confirmation-dialog
      ref="confirmDialog"
      :title="$t('Confirmation de transmission')"
      :subtitle="
        $t('Souhaitez-vous bien transmettre ce lead au partenaire associé ?')
      "
      :cancel-label="$t('general.no_cancel')"
      :confirm-label="$t('Oui, Confirmer')"
      confirm-button-type="secondary"
    ></confirmation-dialog>

    <delete-confirmation
      ref="deleteTelephoneContactDialog"
      :title="$t('general.delete')"
      :subtitle="
        $t('Êtes-vous sûr de vouloir supprimer cette prise de contact ?')
      "
    ></delete-confirmation>

    <delete-confirmation
      ref="deleteScopeDialog"
      :title="$t('general.delete')"
      :subtitle="$t('Êtes-vous sûr de vouloir retirer ce périmètre ?')"
    ></delete-confirmation>

    <contact-log-dialog
      v-model="showTelephoneContactModal"
      :initial-form="contactLogInitialForm"
      :contact-log-types="contactLogTypes"
      :default-phone-number="profile.phoneNumber"
      :default-email="profile.email"
      :is-editing="isEditingTelephoneContact"
      :user-date-format="userDateFormat"
      :loading="isSavingTelephoneContact"
      :can-edit-comment="canEditComment"
      @save="onSaveContactLog"
      @update:model-value="onCancelTelephoneContact"
    />
  </div>
</template>

<script>
import {
  validPhoneNumberFormat,
  shouldNotExceedCharLength,
  required,
  validDateFormat,
  validTimeFormat,
  shouldBeCurrentOrPreviousDate,
  numericOnly,
  validEmailFormat,
} from '@/core/util/validation/rules';
import DateInput from '@/core/components/inputs/DateInput';
import {APIService} from '@/core/util/services/api.service';
import FullNameInput from './FullNameInput.vue';
import CustomColumnInput from './CustomColumnInput.vue';
import useDateFormat from '@/core/util/composable/useDateFormat';
import ConfirmationDialog from '@/core/components/dialogs/ConfirmationDialog';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import ContactLogDialog from '@/core/components/dialogs/ContactLogDialog';
import {OxdSwitchInput} from '@ohrm/oxd';
import {formatDate, parseDate} from '@/core/util/helper/datefns';

const CIVILITY_OPTIONS = [
  {id: 'Monsieur', label: 'Monsieur'},
  {id: 'Madame', label: 'Madame'},
];

const LeadProfileModel = {
  id: 0,
  firstName: '',
  lastName: '',
  email: '',
  phoneNumber: '',
  date: '',
  civility: null,
  comment: '',
  jobs: [],
  job: null,
  sector: '',
  course: '',
  of: '',
  currentSituation: '',
  trainingMethod: '',
  handicap: '',
  funding: '',
  address: '',
  city: '',
  country: '',
  postalCode: '',
  need: '',
  studyLevel: '',
  courseStart: '',
  birthDate: null,
  age: '',
  professionalExperience: '',
  mobility: '',
  source: '',
  timeSlot: '',
  complement: '',
  sentDate: '',
  actor: '',
  matchingState: '',
  apiMessage: '',
  manualDelivery: false,
  telephoneContacts: [],
  customColumns: [],
  callBackDate: null,
};

const TelephoneContactModel = {
  date: null,
  time: null,
  phoneNumber: '',
  successful: false,
  comment: '',
};

export default {
  name: 'LeadProfile',
  components: {
    DateInput,
    'oxd-switch-input': OxdSwitchInput,
    'full-name-input': FullNameInput,
    'custom-column-input': CustomColumnInput,
    'confirmation-dialog': ConfirmationDialog,
    'delete-confirmation': DeleteConfirmationDialog,
    'contact-log-dialog': ContactLogDialog,
  },
  props: {
    lead: {
      type: Object,
      required: true,
    },
    updatable: {
      type: Boolean,
      required: false,
      default: true,
    },
    defaultColumns: {
      type: Object,
      required: true,
    },
    customColumns: {
      type: Array,
      required: true,
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    scopeOptions: {
      type: Array,
      default: () => [],
    },
    isCreateMode: {
      type: Boolean,
      default: false,
    },
    leadSelectOptions: {
      type: Object,
      default: () => ({
        needs: [],
        courseStarts: [],
        studyLevels: [],
        countries: [],
        fundings: [],
        handicaps: [],
        status: [],
        trainingMethods: [],
        sources: [],
        timeSlots: [],
        professionalExperiences: [],
      }),
    },
    ofOptions: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['update', 'created'],
  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '/');
    // Coupe le toast auto de l'intercepteur sur POST /leads (création).
    http.setIgnorePath('api/v2/admin/leads$');
    const noContentPic = `${window.appGlobal.publicPath}/images/empty-box.png`;
    const {jsDateFormat} = useDateFormat();
    const userDateFormat = 'yyyy-MM-dd';

    return {
      http,
      noContentPic,
      userDateFormat,
      jsDateFormat,
    };
  },
  data() {
    return {
      editable: false,
      civilityOptions: CIVILITY_OPTIONS,
      isLoading: false,
      profile: {...LeadProfileModel},
      showTelephoneContactModal: false,
      isEditingTelephoneContact: false,
      isSavingTelephoneContact: false,
      canEditComment: false,
      editingTelephoneContactDate: null,
      contactLogInitialForm: null,
      telephoneContactForm: {...TelephoneContactModel},
      leadScopes: [],
      isAddingScope: false,
      isSavingScope: false,
      scopeToAdd: null,
      selectedJob: null,
      rules: {
        firstName: [required, shouldNotExceedCharLength(30)],
        lastName: [required, shouldNotExceedCharLength(30)],
        phoneNumber: [required],
        email: [required, validEmailFormat],
        postalCode: [shouldNotExceedCharLength(5), numericOnly],
        comment: [shouldNotExceedCharLength(1000)],
        telephoneContactDate: [
          required,
          validDateFormat(this.userDateFormat),
          shouldBeCurrentOrPreviousDate(),
        ],
        telephoneContactTime: [required, validTimeFormat],
        telephoneContactPhoneNumber: [
          required,
          validPhoneNumberFormat,
          shouldNotExceedCharLength(25),
        ],
        telephoneContactComment: [shouldNotExceedCharLength(1000)],
      },
    };
  },
  computed: {
    isSwitchEditable() {
      return this.isCreateMode || this.editable;
    },
    selectedOfId() {
      const ofValue = this.profile?.of;
      if (!ofValue) return null;
      return typeof ofValue === 'object' ? ofValue.id : ofValue;
    },
    selectedCourseId() {
      const courseValue = this.profile?.course;
      return courseValue && typeof courseValue === 'object'
        ? courseValue.id
        : null;
    },
    selectedCourseLabel() {
      return this.selectedCourseId ? this.profile.course.label : '';
    },
    selectedJobId() {
      const job = this.selectedJob;
      if (
        !job ||
        typeof job !== 'object' ||
        job.id === null ||
        job.id === undefined
      ) {
        return null;
      }
      return job.id;
    },
    selectedJobLabel() {
      return this.isJobSelected ? this.selectedJob.label : '';
    },
    isJobSelected() {
      return this.selectedJobId !== null && !!this.selectedJob?.label;
    },
    telephoneContactHeaders() {
      const base = [
        {name: 'date', title: this.$t('general.date'), style: {flex: 1}},
        {
          name: 'phoneNumber',
          title: this.$t('recruitment.contact_number'),
          style: {flex: 1},
        },
        {
          name: 'successful',
          title: this.$t('Réussi'),
          style: {flex: 1},
        },
        {name: 'comment', title: this.$t('Commentaire'), style: {flex: 2}},
      ];
      if (this.contactLogTypes && this.contactLogTypes.length > 0) {
        base.splice(2, 0, {
          name: 'type',
          title: this.$t('Type'),
          style: {flex: 1},
        });
      }
      base.push({
        name: 'actions',
        slot: 'action',
        title: this.$t('general.actions'),
        style: {flex: 1},
        cellType: 'oxd-table-cell-actions',
        cellConfig: {
          edit: {
            onClick: this.onClickEditTelephoneContact,
            props: {name: 'pencil-fill'},
          },
          delete: {
            onClick: this.onClickDeleteTelephoneContact,
            component: 'oxd-icon-button',
            props: {name: 'trash'},
          },
        },
      });
      return base;
    },
    formattedTelephoneContacts() {
      if (
        !this.profile.telephoneContacts ||
        this.profile.telephoneContacts.length === 0
      ) {
        return [];
      }
      const hasTypes = this.contactLogTypes && this.contactLogTypes.length > 0;
      return this.profile.telephoneContacts.map((contact) => {
        let formattedDate = contact.date || '';
        if (formattedDate && formattedDate.includes('.')) {
          formattedDate = formattedDate.split('.')[0];
        }
        if (formattedDate && formattedDate.length >= 16) {
          formattedDate = formattedDate.substring(0, 16);
        }
        const row = {
          date: formattedDate,
          phoneNumber: contact.phoneNumber || '',
          successful: contact.successful
            ? this.$t('general.yes')
            : this.$t('general.no'),
          comment: contact.comment || '',
          _originalDate: contact.date,
        };
        if (hasTypes && contact.type != null) {
          const typeOpt = this.contactLogTypes.find(
            (t) => t.id === contact.type || t.id === Number(contact.type),
          );
          row.type = typeOpt ? typeOpt.label : contact.type;
        }
        return row;
      });
    },
    availableScopeOptions() {
      const assignedIds = new Set(
        this.leadScopes.map((scope) => String(scope.id)),
      );
      return this.scopeOptions.filter(
        (option) => !assignedIds.has(String(option.id)),
      );
    },
    canAddScope() {
      return !this.isAddingScope && this.availableScopeOptions.length > 0;
    },
  },
  watch: {
    lead() {
      if (this.isCreateMode) {
        return;
      }
      this.fetchLead();
    },
  },
  beforeMount() {
    if (this.isCreateMode) {
      this.editable = true;
      this.initCreateProfile();
      return;
    }
    this.fetchLead();
  },
  methods: {
    updateLead() {
      this.isLoading = true;

      // Préparer les données pour l'API
      const dataToSend = {...this.profile};

      if (dataToSend.callBackDate) {
        const dateObj = parseDate(dataToSend.callBackDate, this.userDateFormat);
        dataToSend.callBackDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else {
        dataToSend.callBackDate = null;
      }

      if (dataToSend.civility) {
        dataToSend.civility =
          typeof dataToSend.civility === 'object'
            ? dataToSend.civility.label
            : dataToSend.civility;
      } else {
        dataToSend.civility = null;
      }

      // Convertir les customColumns pour l'API : garder la structure mais avec les valeurs mises à jour
      if (dataToSend.customColumns && Array.isArray(dataToSend.customColumns)) {
        dataToSend.customColumns = dataToSend.customColumns.map((cc) => ({
          id: cc.id,
          value:
            cc.value !== null && cc.value !== undefined
              ? String(cc.value)
              : null,
        }));
      }

      this.http
        .request({
          method: 'PUT',
          url: `/api/v2/admin/leads/${this.lead.id}/info`,
          data: dataToSend,
        })
        .then(() => {
          this.$emit('update', {
            id: this.lead.id,
            ...dataToSend,
          });
          this.isLoading = false;
          this.editable = false;
          return this.$toast.updateSuccess();
        });
    },
    emptyToNull(value) {
      if (value === null || value === undefined || value === '') {
        return null;
      }
      return value;
    },
    asOptionLabel(value) {
      const normalized = this.emptyToNull(value);
      if (normalized && typeof normalized === 'object') {
        return normalized.label ?? null;
      }
      return normalized;
    },
    asOptionId(value) {
      const normalized = this.emptyToNull(value);
      if (normalized && typeof normalized === 'object') {
        return normalized.id ?? null;
      }
      return normalized;
    },
    buildCreatePayload() {
      return {
        firstName: this.emptyToNull(this.profile.firstName),
        lastName: this.emptyToNull(this.profile.lastName),
        email: this.emptyToNull(this.profile.email),
        phoneNumber: this.emptyToNull(this.profile.phoneNumber),
        civility: this.asOptionLabel(this.profile.civility),
        comment: this.emptyToNull(this.profile.comment),
        job: this.emptyToNull(this.profile.job),
        of: this.asOptionId(this.profile.of),
        courseId: this.asOptionId(this.profile.course),
        currentSituation: this.asOptionLabel(this.profile.currentSituation),
        trainingMethod: this.asOptionLabel(this.profile.trainingMethod),
        handicap: this.asOptionLabel(this.profile.handicap),
        funding: this.asOptionLabel(this.profile.funding),
        address: this.emptyToNull(this.profile.address),
        city: this.emptyToNull(this.profile.city),
        country: this.asOptionLabel(this.profile.country),
        postalCode: this.emptyToNull(this.profile.postalCode),
        need: this.asOptionLabel(this.profile.need),
        studyLevel: this.asOptionLabel(this.profile.studyLevel),
        courseStart: this.asOptionLabel(this.profile.courseStart),
        birthDate: this.emptyToNull(this.profile.birthDate),
        professionalExperience: this.asOptionLabel(
          this.profile.professionalExperience,
        ),
        mobility: this.emptyToNull(this.profile.mobility),
        source: this.asOptionLabel(this.profile.source),
        timeSlot: this.asOptionLabel(this.profile.timeSlot),
        complement: this.emptyToNull(this.profile.complement),
        matchingIds: this.leadScopes.map((scope) => scope.id),
        customColumns: Array.isArray(this.profile.customColumns)
          ? this.profile.customColumns.map((cc) => ({
              id: cc.id,
              value:
                cc.value !== null && cc.value !== undefined
                  ? String(cc.value)
                  : null,
            }))
          : [],
      };
    },
    createLead() {
      this.isLoading = true;
      this.http
        .request({
          method: 'POST',
          url: '/api/v2/admin/leads',
          data: this.buildCreatePayload(),
        })
        .then(() => {
          this.$emit('created');
          return this.$toast.saveSuccess();
        })
        .catch(() => {
          return this.$toast.error({
            title: 'Création impossible',
            message:
              'Une erreur est survenue lors de la création du contact. Vérifiez les informations saisies et réessayez.',
          });
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    onDeliver() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          url: `/api/v2/admin/leads/${this.lead.id}/deliver`,
        })
        .then(() => {
          this.$emit('update');
          return this.$toast.updateSuccess();
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    initCreateProfile() {
      this.profile = {...LeadProfileModel, jobs: []};
      this.selectedJob = null;
      this.leadScopes = [];
      this.isAddingScope = false;
      this.scopeToAdd = null;
      this.isLoading = false;
    },
    sortedSelectOptions(optionsKey) {
      const options = this.leadSelectOptions?.[optionsKey] || [];
      return [...options].sort((a, b) =>
        (a.label || '').localeCompare(b.label || '', 'fr', {
          sensitivity: 'base',
        }),
      );
    },
    onCreateOfChange() {
      this.profile.course = null;
    },
    onJobSelection(job) {
      const option = this.resolveSelectedJob(job);
      if (option) {
        this.selectedJob = option;
        this.profile.job = option.label;
        return;
      }
      this.selectedJob = null;
      this.profile.job = null;
    },
    resolveSelectedJob(job) {
      if (
        job &&
        typeof job === 'object' &&
        job.id !== null &&
        job.id !== undefined &&
        job.label
      ) {
        return {id: job.id, label: job.label};
      }
      return null;
    },
    replaceSelectedJob() {
      this.selectedJob = null;
      this.profile.job = null;
    },
    replaceSelectedCourse() {
      this.profile.course = null;
    },
    onCourseSelection(course) {
      if (
        course !== null &&
        (typeof course !== 'object' ||
          course.id === null ||
          course.id === undefined)
      ) {
        this.profile.course = null;
      }
    },
    loadCourses(searchParam) {
      return new Promise((resolve) => {
        const query = (searchParam || '').trim();
        if (!this.selectedOfId || !query || query.length >= 100) {
          resolve([]);
          return;
        }
        this.http
          .request({
            method: 'GET',
            url: `/api/v2/admin/course/of/${this.selectedOfId}/search`,
            params: {
              value: query,
            },
          })
          .then(({data}) => {
            resolve(Array.isArray(data) ? data : []);
          })
          .catch(() => resolve([]));
      });
    },
    loadJobs(searchParam) {
      return new Promise((resolve) => {
        const query = (searchParam || '').trim();
        if (!query || query.length >= 100) {
          resolve([]);
          return;
        }
        this.http
          .request({
            method: 'GET',
            url: '/api/v2/admin/job/search',
            params: {title: query},
          })
          .then(({data}) => {
            const items = Array.isArray(data) ? data : [];
            resolve(
              items
                .map((item) => {
                  if (!item || typeof item !== 'object') {
                    return null;
                  }
                  const id = item.id;
                  const label = item.label || item.title;
                  if (id === null || id === undefined || !label) {
                    return null;
                  }
                  return {id, label};
                })
                .filter(Boolean),
            );
          })
          .catch(() => resolve([]));
      });
    },
    fetchLead() {
      this.isLoading = true;
      this.profile.id = this.lead.id;
      this.profile.firstName = this.lead.firstName;
      this.profile.lastName = this.lead.lastName;
      this.profile.email = this.lead.email;
      this.profile.phoneNumber = this.lead.phoneNumber;
      this.profile.date = this.lead.date;
      this.profile.civility =
        CIVILITY_OPTIONS.find(
          (option) => option.label === this.lead.civility,
        ) ?? null;
      this.profile.comment = this.lead.comment;
      this.profile.jobs =
        this.lead.jobs && this.lead.jobs.length > 0
          ? this.lead.jobs
          : this.lead.job
          ? [this.lead.job]
          : [];
      this.profile.sector = this.lead.sector;
      this.profile.course = this.lead.course;
      this.profile.of = this.lead.of;
      this.profile.currentSituation = this.lead.currentSituation;
      this.profile.trainingMethod = this.lead.trainingMethod;
      this.profile.handicap = this.lead.handicap;
      this.profile.funding = this.lead.funding;
      this.profile.address = this.lead.address;
      this.profile.city = this.lead.city;
      this.profile.country = this.lead.country;
      this.profile.postalCode = this.lead.postalCode;
      this.profile.need = this.lead.need;
      this.profile.studyLevel = this.lead.studyLevel;
      this.profile.courseStart = this.lead.courseStart;
      this.profile.birthDate = this.lead.birthDate;
      this.profile.age = this.lead.age;
      this.profile.professionalExperience = this.lead.professionalExperience;
      this.profile.mobility = this.lead.mobility;
      this.profile.source = this.lead.source;
      this.profile.timeSlot = this.lead.timeSlot;
      this.profile.complement = this.lead.complement;
      this.profile.sentDate = this.lead.sentDate;
      this.profile.actor = this.lead.actor;
      this.profile.matchingState = this.lead.matchingState;
      this.profile.apiMessage = this.lead.apiMessage;
      this.profile.manualDelivery = this.lead.manualDelivery;
      this.profile.telephoneContacts = this.lead.telephoneContacts
        ? [...this.lead.telephoneContacts].sort((a, b) => {
            // Trier par date (du plus ancien au plus récent)
            const dateA = a.date || '';
            const dateB = b.date || '';
            // Comparaison lexicographique fonctionne avec le format yyyy-MM-dd HH:mm:ss
            return dateA.localeCompare(dateB);
          })
        : [];
      this.profile.customColumns = this.lead.customColumns || [];
      this.profile.callBackDate = this.lead.callBackDate || null;
      this.leadScopes = Array.isArray(this.lead.matchingShorts)
        ? this.lead.matchingShorts.map((matching) => ({
            id: matching.id,
            title: matching.title,
            onlyScope: matching.onlyScope ?? false,
          }))
        : [];
      this.isLoading = false;
    },
    onClickAddScope() {
      this.scopeToAdd = null;
      this.isAddingScope = true;
    },
    onCancelAddScope() {
      this.isAddingScope = false;
      this.scopeToAdd = null;
    },
    onConfirmAddScope() {
      if (!this.scopeToAdd) return;
      const scope = this.scopeToAdd;
      if (this.isCreateMode) {
        this.addScopeLocally(scope);
        return;
      }
      this.isSavingScope = true;
      this.http
        .request({
          method: 'POST',
          url: `/api/v2/admin/leads/${this.lead.id}/matching`,
          params: {matchingId: scope.id},
        })
        .then(() => {
          this.leadScopes.push({
            id: scope.id,
            title: scope.label,
            onlyScope: scope.onlyScope ?? true,
          });
          this.onCancelAddScope();
          this.$emit('update');
          return this.$toast.saveSuccess();
        })
        .finally(() => {
          this.isSavingScope = false;
        });
    },
    addScopeLocally(scope) {
      this.leadScopes.push({
        id: scope.id,
        title: scope.label,
        onlyScope: scope.onlyScope ?? true,
      });
      this.onCancelAddScope();
    },
    onClickRemoveScope(scope) {
      if (this.isCreateMode) {
        this.leadScopes = this.leadScopes.filter(
          (item) => String(item.id) !== String(scope.id),
        );
        return;
      }
      if (!scope.onlyScope) return;
      this.$refs.deleteScopeDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.isLoading = true;
          this.http
            .request({
              method: 'DELETE',
              url: `/api/v2/admin/leads/${this.lead.id}/matching`,
              params: {matchingId: scope.id},
            })
            .then(() => {
              this.leadScopes = this.leadScopes.filter(
                (item) => String(item.id) !== String(scope.id),
              );
              this.$emit('update');
              return this.$toast.deleteSuccess();
            })
            .finally(() => {
              this.isLoading = false;
            });
        }
      });
    },
    onClickAddTelephoneContact() {
      this.isEditingTelephoneContact = false;
      this.canEditComment = false;
      this.contactLogInitialForm = null;
      this.showTelephoneContactModal = true;
    },
    onClickEditTelephoneContact(item) {
      this.isEditingTelephoneContact = true;
      this.canEditComment = true;
      this.editingTelephoneContactDate = item._originalDate;

      let cleanDate = item._originalDate || '';
      if (cleanDate && cleanDate.includes('.')) {
        cleanDate = cleanDate.split('.')[0];
      }
      if (cleanDate && cleanDate.length > 19) {
        cleanDate = cleanDate.substring(0, 19);
      }

      const dateTime = cleanDate
        ? parseDate(cleanDate, 'yyyy-MM-dd HH:mm:ss')
        : null;

      const originalContact = this.profile.telephoneContacts.find(
        (c) => c.date === item._originalDate,
      );

      const resolveTypeForSelect = (typeFromApi) => {
        if (
          typeFromApi == null ||
          typeFromApi === '' ||
          !this.contactLogTypes?.length
        )
          return null;
        const str = String(typeFromApi).trim();
        const option = this.contactLogTypes.find(
          (opt) =>
            opt.label &&
            String(opt.label).trim().toLowerCase() === str.toLowerCase(),
        );
        if (option) return {id: option.id, label: option.label};
        return {id: typeFromApi, label: str || String(typeFromApi)};
      };

      if (originalContact && dateTime) {
        const dateStr = formatDate(dateTime, 'yyyy-MM-dd');
        const timeStr = formatDate(dateTime, 'HH:mm');
        this.contactLogInitialForm = {
          date: dateStr,
          time: timeStr,
          phoneNumber: originalContact.phoneNumber || '',
          successful: originalContact.successful || false,
          comment: originalContact.comment || '',
          type: resolveTypeForSelect(originalContact.type),
        };
      } else if (originalContact) {
        this.contactLogInitialForm = {
          date: cleanDate.substring(0, 10) || '',
          time: cleanDate.substring(11, 16) || '',
          phoneNumber: originalContact.phoneNumber || '',
          successful: originalContact.successful || false,
          comment: originalContact.comment || '',
          type: resolveTypeForSelect(originalContact.type),
        };
      } else {
        this.contactLogInitialForm = null;
      }
      this.showTelephoneContactModal = true;
    },
    onClickDeliver() {
      this.$refs.confirmDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.onDeliver();
        }
      });
    },
    onClickDeleteTelephoneContact(item) {
      this.$refs.deleteTelephoneContactDialog
        .showDialog()
        .then((confirmation) => {
          if (confirmation === 'ok') {
            this.deleteTelephoneContact(item._originalDate);
          }
        });
    },
    onSaveContactLog(form) {
      this.isSavingTelephoneContact = true;
      const dateTime = parseDate(
        `${form.date} ${form.time}`,
        `${this.userDateFormat} HH:mm`,
      );
      const formattedDateTime = formatDate(dateTime, 'yyyy-MM-dd HH:mm:ss');

      let type = null;
      if (form.type != null && this.contactLogTypes?.length > 0) {
        type = typeof form.type === 'object' ? form.type.id : form.type;
      }

      const isEmailType = type === 1;
      const contactValue = isEmailType
        ? this.profile.email || ''
        : form.phoneNumber || '';

      const contactData = {
        date: formattedDateTime,
        phoneNumber: contactValue,
        successful: form.successful === true,
        comment: form.comment || '',
        typeOrdinal: type,
      };

      if (this.isEditingTelephoneContact) {
        this.http
          .request({
            method: 'PUT',
            url: `/api/v2/admin/leads/${this.lead.id}/contact-log`,
            data: contactData,
          })
          .then(() => {
            this.$emit('update');
            this.onCancelTelephoneContact();
            return this.$toast.updateSuccess();
          })
          .catch((error) => {
            return this.$toast.unexpectedError(error?.response?.data?.message);
          })
          .finally(() => {
            this.isSavingTelephoneContact = false;
          });
      } else {
        this.http
          .request({
            method: 'POST',
            url: `/api/v2/admin/leads/${this.lead.id}/contact-log`,
            data: contactData,
          })
          .then(() => {
            this.$emit('update');
            this.onCancelTelephoneContact();
            return this.$toast.saveSuccess();
          })
          .finally(() => {
            this.isSavingTelephoneContact = false;
          });
      }
    },
    deleteTelephoneContact(date) {
      this.isLoading = true;
      this.http
        .request({
          method: 'DELETE',
          url: `/api/v2/admin/leads/${
            this.lead.id
          }/contact-log?date=${encodeURIComponent(date)}`,
        })
        .then(() => {
          this.$emit('update');
          return this.$toast.deleteSuccess();
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error?.response?.data?.message);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    onCancelTelephoneContact() {
      this.showTelephoneContactModal = false;
      this.isEditingTelephoneContact = false;
      this.canEditComment = false;
      this.editingTelephoneContactDate = null;
      this.contactLogInitialForm = null;
      this.telephoneContactForm = {...TelephoneContactModel};
    },
    openClientEmail() {
      window.location.href = 'mailto:' + this.profile.email;
    },
    openClientTelephone() {
      window.location.href = 'tel:' + this.profile.phoneNumber;
    },
    getCustomColumnValue(columnId) {
      const customColumn = this.profile.customColumns.find(
        (cc) => cc.id === columnId,
      );
      return customColumn ? customColumn.value : null;
    },
    updateCustomColumnValue(columnId, value) {
      const customColumn = this.profile.customColumns.find(
        (cc) => cc.id === columnId,
      );
      if (customColumn) {
        customColumn.value = value;
      } else {
        // Si la colonne n'existe pas encore, l'ajouter
        this.profile.customColumns.push({
          id: columnId,
          title: '',
          value: value,
        });
      }
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
.orangehrm-telephone-contacts-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-bottom: 1rem;
}
.orangehrm-telephone-contacts-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background-color: var(--oxd-background-tint-color);
  border-radius: 0.5rem;
  border: 1px dashed var(--oxd-interface-gray-lighten-52-color);
  text-align: center;
}
.orangehrm-lead-scope-add {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  width: 100%;
  margin-bottom: 1rem;

  .oxd-input-group {
    max-width: 24rem;
  }
}
.orangehrm-lead-scope-add-actions {
  display: flex;
  gap: 0.5rem;
}
.orangehrm-lead-scope-item {
  display: flex;
  align-items: center;
  gap: 1rem;

  .oxd-input-group {
    flex: 1;
  }
}
.orangehrm-selected-course,
.orangehrm-selected-job {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;

  :deep(.oxd-input-group) {
    flex: 1;
    margin-bottom: 0;
  }

  :deep(.oxd-input-group__message) {
    display: none;
  }

  :deep(.oxd-button) {
    flex-shrink: 0;
    height: 45px;
  }
}
</style>
