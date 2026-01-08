<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-card-container">
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">
          {{
            $t('Profil complet du lead' + (profile ? ' n°' + profile.id : ''))
          }}
        </oxd-text>
        <oxd-switch-input
          v-if="!isLoading"
          v-model="editable"
          :option-label="$t('general.edit')"
          label-position="left"
        />
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
        <oxd-form-row>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.civility"
                :label="$t('Civilité')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <date-input
                v-model="profile.birthDate"
                :label="$t('pim.date_of_birth')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item v-if="profile.age">
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
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.sector"
                :label="$t('Secteur')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
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

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('pim.contact_details') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.address"
                :label="$t('pim.street1')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.city"
                :label="$t('general.city')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.postalCode"
                :label="$t('general.zip_postal_code')"
                :disabled="!editable"
                :rules="rules.postalCode"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.country"
                :label="$t('Pays')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.mobility"
                :label="$t('general.mobility')"
                :disabled="true"
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
                v-model="profile.need"
                :label="$t('Besoin')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.currentSituation"
                type="select"
                :label="$t('Situation actuelle')"
                :disabled="!editable"
                :options="statuses"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.studyLevel"
                :label="$t('general.study_level')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.courseStart"
                :label="$t('Début de formation')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.trainingMethod"
                :label="$t('Modalité de formation')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.handicap"
                :label="$t('Handicap')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.funding"
                :label="$t('Financement')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.timeSlot"
                :label="$t('Disponibilité')"
                :disabled="true"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.professionalExperience"
                :label="$t('Expérience professionnelle')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Source et marketing') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.source"
                :label="$t('Source')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
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
                v-model="profile.sentDate"
                :label="$t('Date d\'envoi')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.matchingState"
                :label="$t('Etat d\'envoi')"
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
        </oxd-form-row>

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('France Travail') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <date-input
                v-model="profile.franceTravailRecordDate"
                :label="$t('Date d\'inscription')"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
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

        <oxd-divider></oxd-divider>
        <oxd-form-row>
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Informations additionnelles') }}
          </oxd-text>
          <oxd-grid :cols="3" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.rqth"
                :label="$t('RQTH')"
                type="select"
                :options="rqthOptions"
                :disabled="!editable"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="profile.complement"
                :label="$t('Complément')"
                :disabled="true"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item>
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

        <oxd-divider></oxd-divider>
        <oxd-form-actions>
          <required-text />
          <oxd-button
            v-if="profile.manualDelivery"
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

    <!-- Modal pour ajouter/éditer une prise de contact -->
    <oxd-dialog
      v-if="showTelephoneContactModal"
      v-model:show="showTelephoneContactModal"
      :style="{width: '90%', maxWidth: '600px'}"
      @update:show="onCancelTelephoneContact"
    >
      <div class="orangehrm-modal-header">
        <oxd-text type="card-title">
          {{
            isEditingTelephoneContact
              ? $t('Modifier la prise de contact')
              : $t('Ajouter une prise de contact')
          }}
        </oxd-text>
      </div>
      <oxd-divider />
      <oxd-form
        :loading="isSavingTelephoneContact"
        @submit-valid="onSaveTelephoneContact"
      >
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <date-input
                v-model="telephoneContactForm.date"
                :label="$t('general.date')"
                :rules="rules.telephoneContactDate"
                :disabled="isEditingTelephoneContact"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <time-input
                v-model="telephoneContactForm.time"
                :label="$t('Heure')"
                :rules="rules.telephoneContactTime"
                :disabled="isEditingTelephoneContact"
                :step="1"
                required
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="telephoneContactForm.phoneNumber"
                :label="$t('recruitment.contact_number')"
                :rules="rules.telephoneContactPhoneNumber"
                :disabled="isEditingTelephoneContact"
                required
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-text class="orangehrm-input-title" tag="h6">
                {{ $t('Appel abouti avec succès') }}
              </oxd-text>
              <oxd-switch-input
                v-model="telephoneContactForm.successful"
                :disabled="isEditingTelephoneContact"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-row>
          <oxd-grid :cols="1" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="telephoneContactForm.comment"
                type="textarea"
                :label="$t('Commentaire')"
                :rules="rules.telephoneContactComment"
                :disabled="isEditingTelephoneContact && !canEditComment"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-divider />
        <oxd-form-actions class="orangehrm-form-action">
          <required-text />
          <oxd-button
            display-type="ghost"
            :label="$t('general.cancel')"
            @click="onCancelTelephoneContact"
          />
          <oxd-button
            display-type="secondary"
            :label="$t('general.save')"
            type="submit"
          />
        </oxd-form-actions>
      </oxd-form>
    </oxd-dialog>
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
import TimeInput from '@/core/components/inputs/TimeInput';
import {APIService} from '@/core/util/services/api.service';
import FullNameInput from '../../orangehrmPimPlugin/components/FullNameInput';
import useDateFormat from '@/core/util/composable/useDateFormat';
import ConfirmationDialog from '@/core/components/dialogs/ConfirmationDialog';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {OxdSwitchInput, OxdDialog} from '@ohrm/oxd';
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
  currentSituation: '',
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
  franceTravailRecordDate: null,
  franceTravailAgency: null,
  rqth: null,
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
    TimeInput,
    'oxd-switch-input': OxdSwitchInput,
    'oxd-dialog': OxdDialog,
    'full-name-input': FullNameInput,
    'confirmation-dialog': ConfirmationDialog,
    'delete-confirmation': DeleteConfirmationDialog,
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
    statuses: {
      type: Array,
      required: true,
    },
  },
  emits: ['update'],
  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '/');
    const {jsDateFormat} = useDateFormat();
    const userDateFormat = 'yyyy-MM-dd';

    return {
      http,
      userDateFormat,
      jsDateFormat,
    };
  },
  data() {
    return {
      editable: false,
      isLoading: false,
      profile: {...LeadProfileModel},
      showTelephoneContactModal: false,
      isEditingTelephoneContact: false,
      isSavingTelephoneContact: false,
      canEditComment: false,
      editingTelephoneContactDate: null,
      telephoneContactForm: {...TelephoneContactModel},
      telephoneContactHeaders: [
        {
          name: 'date',
          title: this.$t('general.date'),
          style: {flex: 1},
        },
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
        {id: 0, label: 'PÔLE EMPLOI ANTONY 92160'},
        {id: 1, label: 'PÔLE EMPLOI ASNIÈRES-SUR-SEINE 92600'},
        {id: 2, label: 'PÔLE EMPLOI BAGNEUX 92220'},
        {id: 3, label: 'PÔLE EMPLOI BOIS-COLOMBES 92270'},
        {id: 4, label: 'PÔLE EMPLOI BOULOGNE-BILLANCOURT 92100'},
        {id: 5, label: 'PÔLE EMPLOI CLICHY 92110'},
        {id: 6, label: 'PÔLE EMPLOI COLOMBES 92700'},
        {id: 7, label: 'PÔLE EMPLOI COURBEVOIE 92400'},
        {id: 8, label: 'PÔLE EMPLOI GENNEVILLIERS 92230'},
        {id: 9, label: 'PÔLE EMPLOI ISSY-LES-MOULINEAUX 92130'},
        {id: 10, label: 'PÔLE EMPLOI LEVALLOIS-PERRET 92300'},
        {id: 11, label: 'PÔLE EMPLOI MEUDON 92190'},
        {id: 12, label: 'PÔLE EMPLOI MONTROUGE 92120'},
        {id: 13, label: 'PÔLE EMPLOI NANTERRE 92000'},
        {id: 14, label: 'PÔLE EMPLOI PUTEAUX 92800'},
        {id: 15, label: 'PÔLE EMPLOI RUEIL-MALMAISON 92500'},
        {id: 16, label: '-'},
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

      if (dataToSend.franceTravailAgency)
        dataToSend.franceTravailAgency = dataToSend.franceTravailAgency?.label;

      if (dataToSend.rqth) dataToSend.rqth = dataToSend.rqth?.label;

      if (dataToSend.currentSituation)
        dataToSend.currentSituation = dataToSend.currentSituation?.label;

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

      if (this.lead.birthDate) {
        const dateObj = parseDate(this.lead.birthDate, 'yyyy-MM-dd');
        this.profile.birthDate = dateObj
          ? formatDate(dateObj, 'yyyy-MM-dd')
          : null;
      } else this.profile.birthDate = null;

      if (this.lead.currentSituation)
        this.profile.currentSituation = this.statuses.find(
          (option) => option.label === this.lead.currentSituation,
        );

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
      const now = new Date();
      this.telephoneContactForm = {
        date: formatDate(now, this.userDateFormat),
        time: formatDate(now, 'HH:mm'),
        phoneNumber: this.profile.phoneNumber || '',
        successful: false,
        comment: '',
      };
      this.showTelephoneContactModal = true;
    },
    onClickEditTelephoneContact(item) {
      this.isEditingTelephoneContact = true;
      this.canEditComment = true;
      this.editingTelephoneContactDate = item._originalDate;

      // Nettoyer la date pour retirer les millisecondes si présentes
      let cleanDate = item._originalDate || '';
      if (cleanDate && cleanDate.includes('.')) {
        cleanDate = cleanDate.split('.')[0];
      }
      if (cleanDate && cleanDate.length > 19) {
        cleanDate = cleanDate.substring(0, 19);
      }

      // Parser la date au format yyyy-MM-dd HH:mm:ss
      const dateTime = cleanDate
        ? parseDate(cleanDate, 'yyyy-MM-dd HH:mm:ss')
        : null;

      const originalContact = this.profile.telephoneContacts.find(
        (c) => c.date === item._originalDate,
      );

      if (originalContact && dateTime) {
        // Extraire la date (yyyy-MM-dd) et l'heure (HH:mm) séparément
        const dateStr = formatDate(dateTime, 'yyyy-MM-dd');
        const timeStr = formatDate(dateTime, 'HH:mm');

        this.telephoneContactForm = {
          date: dateStr,
          time: timeStr,
          phoneNumber: originalContact.phoneNumber || '',
          successful: originalContact.successful || false,
          comment: originalContact.comment || '',
        };
      } else if (originalContact) {
        // Fallback si le parsing échoue
        this.telephoneContactForm = {
          date: cleanDate.substring(0, 10) || '',
          time: cleanDate.substring(11, 16) || '',
          phoneNumber: originalContact.phoneNumber || '',
          successful: originalContact.successful || false,
          comment: originalContact.comment || '',
        };
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
    onSaveTelephoneContact() {
      this.isSavingTelephoneContact = true;
      const dateTime = parseDate(
        `${this.telephoneContactForm.date} ${this.telephoneContactForm.time}`,
        `${this.userDateFormat} HH:mm`,
      );
      const formattedDateTime = formatDate(dateTime, 'yyyy-MM-dd HH:mm:ss');

      const contactData = {
        date: formattedDateTime,
        phoneNumber: this.telephoneContactForm.phoneNumber,
        successful: this.telephoneContactForm.successful,
        comment: this.telephoneContactForm.comment,
      };

      if (this.isEditingTelephoneContact) {
        // Mise à jour du commentaire uniquement
        this.http
          .request({
            method: 'PUT',
            url: `${window.appGlobal.theme}/api/v2/admin/leads/${this.lead.id}/telephone-contact`,
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
        // Ajout d'une nouvelle prise de contact
        this.http
          .request({
            method: 'POST',
            url: `${window.appGlobal.theme}/api/v2/admin/leads/${this.lead.id}/telephone-contact`,
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
          }/telephone-contact?date=${encodeURIComponent(date)}`,
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
      this.telephoneContactForm = {...TelephoneContactModel};
    },
    openClientEmail() {
      window.location.href = 'mailto:' + this.profile.email;
    },
    openClientTelephone() {
      window.location.href = 'tel:' + this.profile.phoneNumber;
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
</style>
