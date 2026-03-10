<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">
          {{
            $t('Profil complet du lead' + (profile ? ' n°' + profile.id : ''))
          }}
        </oxd-text>
        <div style="display: flex; flex-direction: row; gap: 1rem">
          <oxd-switch-input
            v-if="!isLoading && profile.actor"
            v-model="simplifiedVersion"
            :option-label="$t('Version complète/simplifiée')"
            label-position="left"
          />
          <oxd-switch-input
            v-if="!isLoading"
            v-model="editable"
            :option-label="$t('general.edit')"
            label-position="left"
          />
        </div>
      </div>

      <oxd-divider v-show="!isLoading" />

      <oxd-form :loading="isLoading" @submit-valid="updateLead">
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
                :label="$t('general.full_name')"
                :disabled="!editable"
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
                :rules="rules.email"
                :disabled="true"
              />
              <oxd-icon-button
                v-if="profile.email"
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
                :rules="rules.phoneNumber"
                :disabled="true"
              />
              <oxd-icon-button
                v-if="profile.phoneNumber"
                style="height: 1px"
                display-type="success"
                name="telephone-fill"
                @click.stop="openClientTelephone"
              ></oxd-icon-button>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row v-if="hasAnyColumns('gender', 'birthDate', 'age')">
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item v-if="isColumnVisible('gender')">
              <oxd-input-field
                v-model="profile.civility"
                :label="$t('Civilité')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item v-if="isColumnVisible('birthDate')">
              <date-input
                v-model="profile.birthDate"
                :label="$t('pim.date_of_birth')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item v-if="isColumnVisible('age') && profile.age">
              <oxd-input-field
                v-model="profile.age"
                :label="$t('Âge')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
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

        <div v-if="hasAnyColumns('job', 'sector')">
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
            <oxd-grid
              v-if="isColumnVisible('sector')"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.sector"
                  :label="$t('Secteur')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid
              v-if="isColumnVisible('job')"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item
                v-for="(job, jobIndex) in profile.jobs"
                :key="jobIndex"
              >
                <oxd-input-field
                  :value="job"
                  :disabled="true"
                  :label="$t('Métier n°' + (jobIndex + 1))"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div v-if="isColumnVisible('course')">
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Formation') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
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
            hasAnyColumns(
              'address',
              'city',
              'postalCode',
              'country',
              'mobility',
            )
          "
        >
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('pim.contact_details') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('address')">
                <oxd-input-field
                  v-model="profile.address"
                  :label="$t('pim.street1')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('city')">
                <oxd-input-field
                  v-model="profile.city"
                  :label="$t('general.city')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('postalCode')">
                <oxd-input-field
                  v-model="profile.postalCode"
                  :label="$t('general.zip_postal_code')"
                  :disabled="!editable"
                  :rules="rules.postalCode"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('country')">
                <oxd-input-field
                  v-model="profile.country"
                  :label="$t('Pays')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('mobility')">
                <oxd-input-field
                  v-model="profile.mobility"
                  :label="$t('general.mobility')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div
          v-if="
            hasAnyColumns(
              'need',
              'status',
              'studyLevel',
              'courseStart',
              'trainingMethod',
              'handicap',
              'funding',
              'timeSlot',
              'professionalExperience',
            )
          "
        >
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('general.candidat_details') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('need')">
                <oxd-input-field
                  v-model="profile.need"
                  :label="$t('Besoin')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('status')">
                <oxd-input-field
                  v-model="profile.currentSituation"
                  type="select"
                  :label="$t('Situation actuelle')"
                  :disabled="!editable"
                  :options="sortedStatuses"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('studyLevel')">
                <oxd-input-field
                  v-model="profile.studyLevel"
                  :label="$t('general.study_level')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('courseStart')">
                <oxd-input-field
                  v-model="profile.courseStart"
                  :label="$t('Début de formation')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('trainingMethod')">
                <oxd-input-field
                  v-model="profile.trainingMethod"
                  :label="$t('Modalité de formation')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('handicap')">
                <oxd-input-field
                  v-model="profile.handicap"
                  :label="$t('Handicap')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('funding')">
                <oxd-input-field
                  v-model="profile.funding"
                  :label="$t('Financement')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('timeSlot')">
                <oxd-input-field
                  v-model="profile.timeSlot"
                  :label="$t('Disponibilité')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('professionalExperience')">
                <oxd-input-field
                  v-model="profile.professionalExperience"
                  :label="$t('Expérience professionnelle')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div v-if="hasAnyColumns('source', 'utms')">
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Source et marketing') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('source')">
                <oxd-input-field
                  v-model="profile.source"
                  :label="$t('Source')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid
              v-if="isColumnVisible('utms')"
              :cols="3"
              class="orangehrm-full-width-grid"
            >
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.utmCampaign"
                  :label="$t('Campagne UTM')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.utmGroup"
                  :label="$t('Groupe UTM')"
                  :disabled="true"
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="profile.utmSource"
                  :label="$t('Source UTM')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Partenaire et envoi') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.actor"
                :label="$t('Partenaire')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.matchingState"
                :label="$t('Etat d\'envoi')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item v-if="profile.ko !== null">
              <oxd-text class="orangehrm-input-title" tag="h6">
                {{ $t('KO') }}
              </oxd-text>
              <oxd-switch-input v-model="profile.ko" :disabled="!editable" />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.sentDate"
                :label="$t('Date d\'envoi')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.apiMessage"
                :label="$t('Message d\'erreur / API')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <div class="orangehrm-telephone-contacts-header">
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Prises de contact') }}
            </oxd-text>
            <oxd-button
              icon-name="plus"
              display-type="secondary"
              :label="$t('general.add')"
              @click="onClickAddTelephoneContact"
            />
          </div>
          <div>
            <oxd-grid-item v-if="isColumnVisible('callBackDate')">
              <date-input
                v-model="profile.callBackDate"
                :label="$t('Relancer à partir de')"
                :disabled="!editable"
              />
            </oxd-grid-item>
          </div>
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
        </oxd-form-row>

        <div
          v-if="hasAnyColumns('franceTravailRecordDate', 'franceTravailAgency')"
        >
          <oxd-divider></oxd-divider>
          <oxd-form-row>
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('France Travail') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('franceTravailRecordDate')">
                <date-input
                  v-model="profile.franceTravailRecordDate"
                  :label="$t('Date d\'inscription')"
                  :disabled="!editable"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('franceTravailAgency')">
                <oxd-input-field
                  v-model="profile.franceTravailAgency"
                  :label="$t('Agence')"
                  type="select"
                  :options="franceTravailAgencyOptions"
                  :disabled="!editable"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <div v-if="hasAnyColumns('rqth', 'complement', 'comment')">
          <oxd-divider></oxd-divider>
          <oxd-form-row v-if="hasAnyColumns('rqth', 'complement', 'comment')">
            <oxd-text class="orangehrm-sub-title" tag="h6">
              {{ $t('Informations additionnelles') }}
            </oxd-text>
            <oxd-grid :cols="3" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('rqth')">
                <oxd-input-field
                  v-model="profile.rqth"
                  :label="$t('RQTH')"
                  type="select"
                  :options="rqthOptions"
                  :disabled="!editable"
                />
              </oxd-grid-item>
              <oxd-grid-item v-if="isColumnVisible('complement')">
                <oxd-input-field
                  v-model="profile.complement"
                  :label="$t('Complément')"
                  :disabled="true"
                />
              </oxd-grid-item>
            </oxd-grid>
            <oxd-grid :cols="1" class="orangehrm-full-width-grid">
              <oxd-grid-item v-if="isColumnVisible('comment')">
                <oxd-input-field
                  v-model="profile.comment"
                  :label="$t('Commentaire')"
                  type="textarea"
                  :disabled="!editable"
                  :rules="rules.comment"
                />
              </oxd-grid-item>
            </oxd-grid>
          </oxd-form-row>
        </div>

        <oxd-divider></oxd-divider>
        <oxd-form-actions>
          <required-text />
          <oxd-button
            v-if="
              profile.manualDelivery &&
              !editable &&
              (profile.ko == null || profile.ko === false)
            "
            display-type="ghost"
            :label="$t('Transmettre au partenaire')"
            @click="onClickDeliver"
          />
          <submit-button v-if="editable" />
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
} from '@/core/util/validation/rules';
import DateInput from '@/core/components/inputs/DateInput';
import {APIService} from '@/core/util/services/api.service';
import FullNameInput from '../../orangehrmPimPlugin/components/FullNameInput';
import useDateFormat from '@/core/util/composable/useDateFormat';
import ConfirmationDialog from '@/core/components/dialogs/ConfirmationDialog';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import ContactLogDialog from '@/core/components/dialogs/ContactLogDialog';
import {OxdSwitchInput} from '@ohrm/oxd';
import {formatDate, parseDate} from '@/core/util/helper/datefns';

const LeadProfileModel = {
  id: 0,
  firstName: '',
  lastName: '',
  email: '',
  phoneNumber: '',
  date: '',
  civility: '',
  comment: '',
  jobs: [],
  sector: '',
  course: '',
  of: '',
  currentSituation: null,
  trainingMethod: '',
  handicap: '',
  funding: '',
  utmCampaign: '',
  utmGroup: '',
  utmSource: '',
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
  callBackDate: null,
  franceTravailRecordDate: null,
  franceTravailAgency: null,
  rqth: null,
  ko: null,
};

const TelephoneContactModel = {
  date: null,
  time: null,
  phoneNumber: '',
  successful: false,
  comment: '',
  type: null,
};

export default {
  name: 'LeadProfile',
  components: {
    DateInput,
    'oxd-switch-input': OxdSwitchInput,
    'full-name-input': FullNameInput,
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
    allStatuses: {
      type: Array,
      required: true,
    },
    actorStatuses: {
      type: Array,
      required: true,
    },
    contactLogTypes: {
      type: Array,
      default: () => [],
    },
    defaultColumns: {
      type: Object,
      required: false,
      default: null,
    },
  },
  emits: ['update'],
  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '/');
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
      simplifiedVersion: true,
      isLoading: false,
      profile: {...LeadProfileModel},
      showTelephoneContactModal: false,
      isEditingTelephoneContact: false,
      isSavingTelephoneContact: false,
      canEditComment: false,
      editingTelephoneContactDate: null,
      contactLogInitialForm: null,
      telephoneContactForm: {...TelephoneContactModel},
      telephoneContactHeaders: [
        {
          name: 'date',
          title: this.$t('general.date'),
          style: {flex: 1},
        },
        {
          name: 'type',
          title: this.$t('Type'),
          style: {flex: 1},
        },
        {
          name: 'phoneNumber',
          title: this.$t('Moyen de contact'),
          style: {flex: 1},
        },
        {
          name: 'successful',
          title: this.$t('Réussi/Répondu'),
          style: {flex: 1},
        },
        {
          name: 'comment',
          title: this.$t('Commentaire'),
          style: {flex: 2},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            edit: {
              onClick: this.onClickEditTelephoneContact,
              props: {
                name: 'pencil-fill',
              },
            },
            delete: {
              onClick: this.onClickDeleteTelephoneContact,
              component: 'oxd-icon-button',
              props: {
                name: 'trash',
              },
            },
          },
        },
      ],
      rules: {
        firstName: [shouldNotExceedCharLength(30)],
        lastName: [shouldNotExceedCharLength(30)],
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
      franceTravailAgencyOptions: [
        {id: 0, label: 'FRANCE TRAVAIL ANTONY 92160'},
        {id: 1, label: 'FRANCE TRAVAIL ASNIÈRES-SUR-SEINE 92600'},
        {id: 2, label: 'FRANCE TRAVAIL BAGNEUX 92220'},
        {id: 3, label: 'FRANCE TRAVAIL BOIS-COLOMBES 92270'},
        {id: 4, label: 'FRANCE TRAVAIL BOULOGNE-BILLANCOURT 92100'},
        {id: 5, label: 'FRANCE TRAVAIL CLICHY 92110'},
        {id: 6, label: 'FRANCE TRAVAIL COLOMBES 92700'},
        {id: 7, label: 'FRANCE TRAVAIL COURBEVOIE 92400'},
        {id: 8, label: 'FRANCE TRAVAIL GENNEVILLIERS 92230'},
        {id: 9, label: 'FRANCE TRAVAIL ISSY-LES-MOULINEAUX 92130'},
        {id: 10, label: 'FRANCE TRAVAIL LEVALLOIS-PERRET 92300'},
        {id: 11, label: 'FRANCE TRAVAIL MEUDON 92190'},
        {id: 12, label: 'FRANCE TRAVAIL MONTROUGE 92120'},
        {id: 13, label: 'FRANCE TRAVAIL NANTERRE 92000'},
        {id: 14, label: 'FRANCE TRAVAIL PUTEAUX 92800'},
        {id: 15, label: 'FRANCE TRAVAIL RUEIL-MALMAISON 92500'},
        {id: 16, label: 'PARIS'},
        {id: 17, label: '-'},
      ],
      rqthOptions: [
        {id: 0, label: 'Non, je ne suis pas concerné·e'},
        {id: 1, label: "Oui, je bénéficie d'une RQTH"},
        {
          id: 2,
          label:
            "J'envisage d'en faire la demande / Ma demande de RQTH est en cours",
        },
      ],
    };
  },
  computed: {
    contactLogTypeOrdinal() {
      const t = this.telephoneContactForm.type;
      if (t == null) return null;
      if (typeof t === 'object') return t.value ?? t.id ?? null;
      const n = Number(t);
      return Number.isInteger(n) ? n : null;
    },
    isContactLogTypePhone() {
      const o = this.contactLogTypeOrdinal;
      return o !== null && o !== 1;
    },
    isContactLogTypeTelephone() {
      return this.contactLogTypeOrdinal !== 1;
    },
    sortedStatuses() {
      if (
        (!this.simplifiedVersion &&
          (!this.allStatuses || !Array.isArray(this.allStatuses))) ||
        (this.simplifiedVersion &&
          (!this.actorStatuses || !Array.isArray(this.actorStatuses)))
      ) {
        return [];
      }
      // Créer une copie du tableau et trier par label (ordre alphabétique)
      return [
        ...(this.simplifiedVersion ? this.actorStatuses : this.allStatuses),
      ].sort((a, b) => {
        const labelA = (a.label || '').toLowerCase();
        const labelB = (b.label || '').toLowerCase();
        return labelA.localeCompare(labelB);
      });
    },
    formattedTelephoneContacts() {
      if (
        !this.profile.telephoneContacts ||
        this.profile.telephoneContacts.length === 0
      ) {
        return [];
      }
      return this.profile.telephoneContacts.map((contact) => {
        // Afficher la date au format yyyy-MM-dd HH:mm (sans secondes)
        // Si la date contient des millisecondes, on les retire
        let formattedDate = contact.date || '';
        if (formattedDate && formattedDate.includes('.')) {
          // Retirer les millisecondes si présentes
          formattedDate = formattedDate.split('.')[0];
        }
        // Retirer les secondes pour afficher yyyy-MM-dd HH:mm
        if (formattedDate && formattedDate.length >= 16) {
          // Tronquer à 16 caractères pour obtenir yyyy-MM-dd HH:mm
          formattedDate = formattedDate.substring(0, 16);
        }
        return {
          date: formattedDate,
          type: contact.type || '',
          phoneNumber: contact.phoneNumber || '',
          successful: contact.successful
            ? this.$t('general.yes')
            : this.$t('general.no'),
          comment: contact.comment || '',
          _originalDate: contact.date,
        };
      });
    },
  },
  watch: {
    lead() {
      this.fetchLead();
    },
  },
  beforeMount() {
    this.fetchLead();
  },
  methods: {
    updateLead() {
      this.isLoading = true;
      const dataToSend = {...this.profile};

      if (
        dataToSend.franceTravailRecordDate &&
        dataToSend.franceTravailRecordDate.trim() !== ''
      ) {
        const dateObj = parseDate(
          dataToSend.franceTravailRecordDate,
          this.userDateFormat,
        );
        dataToSend.franceTravailRecordDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else dataToSend.franceTravailRecordDate = null;

      if (dataToSend.birthDate) {
        const dateObj = parseDate(dataToSend.birthDate, this.userDateFormat);
        dataToSend.birthDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else dataToSend.birthDate = null;

      if (dataToSend.callBackDate) {
        const dateObj = parseDate(dataToSend.callBackDate, this.userDateFormat);
        dataToSend.callBackDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else dataToSend.callBackDate = null;

      if (dataToSend.rqth) dataToSend.rqth = dataToSend.rqth?.label;

      if (dataToSend.currentSituation)
        dataToSend.currentSituation = this.profile.currentSituation?.label;

      if (dataToSend.franceTravailAgency)
        dataToSend.franceTravailAgency = dataToSend.franceTravailAgency?.label;

      this.http
        .request({
          method: 'PUT',
          url: `${window.appGlobal.theme}/api/v2/admin/leads/${this.lead.id}/info`,
          data: dataToSend,
        })
        .then(() => {
          this.$emit('update');
          this.isLoading = false;
          this.editable = false;
          return this.$toast.updateSuccess();
        });
    },
    onDeliver() {
      this.isLoading = true;
      this.http
        .request({
          method: 'PUT',
          url: `${window.appGlobal.theme}/api/v2/admin/leads/${this.lead.id}/deliver`,
        })
        .then(() => {
          this.$emit('update');
          return this.$toast.updateSuccess();
        })
        .finally(() => {
          this.isLoading = false;
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
      this.profile.civility = this.lead.civility;
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
      this.profile.trainingMethod = this.lead.trainingMethod;
      this.profile.handicap = this.lead.handicap;
      this.profile.funding = this.lead.funding;
      this.profile.utmCampaign = this.lead.utmCampaign;
      this.profile.utmGroup = this.lead.utmGroup;
      this.profile.utmSource = this.lead.utmSource;
      this.profile.address = this.lead.address;
      this.profile.city = this.lead.city;
      this.profile.country = this.lead.country;
      this.profile.postalCode = this.lead.postalCode;
      this.profile.need = this.lead.need;
      this.profile.studyLevel = this.lead.studyLevel;
      this.profile.courseStart = this.lead.courseStart;
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
      this.profile.ko = this.lead.ko;
      this.profile.telephoneContacts = this.lead.telephoneContacts
        ? [...this.lead.telephoneContacts].sort((a, b) => {
            // Trier par date (du plus ancien au plus récent)
            const dateA = a.date || '';
            const dateB = b.date || '';
            // Comparaison lexicographique fonctionne avec le format yyyy-MM-dd HH:mm:ss
            return dateA.localeCompare(dateB);
          })
        : [];

      if (this.lead.franceTravailRecordDate) {
        const dateObj = parseDate(
          this.lead.franceTravailRecordDate,
          'yyyy-MM-dd',
        );
        this.profile.franceTravailRecordDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else this.profile.franceTravailRecordDate = null;

      if (this.lead.callBackDate) {
        const dateObj = parseDate(this.lead.callBackDate, 'yyyy-MM-dd');
        this.profile.callBackDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else this.profile.callBackDate = null;

      if (this.lead.birthDate) {
        const dateObj = parseDate(this.lead.birthDate, 'yyyy-MM-dd');
        this.profile.birthDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else this.profile.birthDate = null;

      if (this.lead.currentSituation)
        this.profile.currentSituation = (
          this.simplifiedVersion ? this.actorStatuses : this.allStatuses
        ).find((option) => option.label === this.lead.currentSituation);

      if (this.lead.franceTravailAgency)
        this.profile.franceTravailAgency = this.franceTravailAgencyOptions.find(
          (option) => option.label === this.lead.franceTravailAgency,
        );

      if (this.lead.rqth)
        this.profile.rqth = this.rqthOptions.find(
          (option) => option.label === this.lead.rqth,
        );

      this.isLoading = false;
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

      const typeValue = form.type;
      let typeForPayload = null;
      if (typeValue != null) {
        if (typeof typeValue === 'object') {
          typeForPayload = typeValue.id ?? null;
        } else {
          typeForPayload = typeValue;
        }
      }

      const isEmailType = typeForPayload === 1;
      const contactValue = isEmailType
        ? this.profile.email || ''
        : form.phoneNumber || '';

      const contactData = {
        date: formattedDateTime,
        phoneNumber: contactValue,
        successful: form.successful === true,
        comment: form.comment || '',
        typeOrdinal: typeForPayload,
      };

      if (this.isEditingTelephoneContact) {
        this.http
          .request({
            method: 'PUT',
            url: `${window.appGlobal.theme}/api/v2/admin/leads/${this.lead.id}/contact-log`,
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
            url: `${window.appGlobal.theme}/api/v2/admin/leads/${this.lead.id}/contact-log`,
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
          url: `${window.appGlobal.theme}/api/v2/admin/leads/${
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
    isColumnVisible(column) {
      if (!this.simplifiedVersion) {
        return true;
      }
      if (!this.defaultColumns) {
        return true;
      }
      const value = this.defaultColumns[column];
      if (value === undefined) {
        return true;
      }
      return !!value;
    },
    hasAnyColumns(...columns) {
      if (!this.simplifiedVersion) {
        return true;
      }
      if (!this.defaultColumns) {
        return true;
      }
      return columns.some((col) => {
        const value = this.defaultColumns[col];
        return value === undefined ? true : !!value;
      });
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
.orangehrm-telephone-contacts-empty-icon {
  flex-shrink: 0;
  font-size: 1.5rem;
  color: var(--oxd-interface-gray-darken-1-color);
}
.orangehrm-telephone-contacts-empty-text {
  color: var(--oxd-interface-gray-darken-1-color);
  margin: 0;
}
</style>
