<template>
  <back-button v-if="isAdding"></back-button>
  <div
    class="orangehrm-card-container"
    :style="{marginTop: isAdding ? '1rem' : '0'}"
  >
    <oxd-text v-if="isAdding" tag="h6" class="orangehrm-main-title">
      {{ $t('Ajouter un acteur') }}
    </oxd-text>
    <oxd-divider v-if="isAdding" />

    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Informations') }}
      </oxd-text>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.name"
            :label="$t('Nom')"
            :disabled="!editable"
            :rules="rules.title"
            required
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.documentation"
            :label="$t('Documentation')"
            :disabled="!editable"
            :rules="rules.documentation"
          />
        </oxd-grid-item>
      </oxd-grid>
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('Est un OF ?') }}
          </oxd-text>
          <oxd-switch-input v-model="actor.isOf" :disabled="true" />
        </oxd-grid-item>
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('Est prioritaire ?') }}
          </oxd-text>
          <oxd-switch-input v-model="actor.isPriority" :disabled="!editable" />
        </oxd-grid-item>
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('Autorise les comptes spécifiques aux matchings ?') }}
          </oxd-text>
          <oxd-switch-input
            v-model="actor.allowMatchingSpecific"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item class="orangerhrm-switch-wrapper">
          <oxd-text class="orangehrm-text" tag="p">
            {{ $t('Nécessite une vérification manuelle avant envoi ?') }}
          </oxd-text>
          <oxd-switch-input
            v-model="actor.manualDelivery"
            :disabled="!editable"
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-grid :cols="3" class="orangehrm-full-width-grid">
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.maxAmountPerDay"
            :label="$t('Quantité maximale par jour')"
            :disabled="!editable"
            :rules="rules.maxAmountPerDay"
          />
        </oxd-grid-item>
        <oxd-grid-item>
          <oxd-input-field
            v-model="actor.maxAmountPerMonth"
            :label="$t('Quantité maximale par mois')"
            :disabled="!editable"
            :rules="rules.maxAmountPerMonth"
          />
        </oxd-grid-item>
      </oxd-grid>
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Métiers spécifiques') }}
      </oxd-text>
      <jobs-autocomplete
        :jobs="actor.jobs"
        :is-actor-specific="true"
        @delete-job="onClickDeleteJob"
        @add-jobs="addJobs"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Ages') }}
      </oxd-text>
      <age-autocomplete
        :ages="actor.ages"
        :disabled="!editable"
        :is-actor-specific="true"
        @delete-age="onClickDeleteAge"
        @add-age="addAgeRange"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Situations actuelles spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Situation actuelle')"
        :custom-fields="actor.status"
        :custom-field-options="status"
        @delete-custom-field="onClickDeleteStatus"
        @add-custom-field="addStatus"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t("Niveaux d'étude spécifiques") }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Niveau d\'étude')"
        :custom-fields="actor.studyLevels"
        :custom-field-options="studyLevels"
        @delete-custom-field="onClickDeleteStudyLevel"
        @add-custom-field="addStudyLevel"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Pays spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Pays')"
        :custom-fields="actor.countries"
        :custom-field-options="countries"
        @delete-custom-field="onClickDeleteCountry"
        @add-custom-field="addCountry"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Méthodes de financement spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Méthode de financement')"
        :custom-fields="actor.fundings"
        :custom-field-options="fundings"
        @delete-custom-field="onClickDeleteFunding"
        @add-custom-field="addFunding"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Besoins spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Besoins')"
        :custom-fields="actor.needs"
        :custom-field-options="needs"
        @delete-custom-field="onClickDeleteNeed"
        @add-custom-field="addNeed"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Modalités de formation spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Modalité de formation')"
        :custom-fields="actor.trainingMethods"
        :custom-field-options="trainingMethods"
        @delete-custom-field="onClickDeleteTrainingMethod"
        @add-custom-field="addTrainingMethod"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Sources spécifiques') }}
      </oxd-text>
      <custom-field-autocomplete
        :label="$t('Source')"
        :custom-fields="actor.sources"
        :custom-field-options="sources"
        :custom-field-label="$t('Campagne UTM')"
        @delete-custom-field="onClickDeleteSource"
        @add-custom-field="addSource"
      />
      <br />
      <oxd-divider />
      <oxd-text class="orangehrm-sub-title" tag="h6">
        {{ $t('Disponibilités spécifiques') }}
      </oxd-text>
      <time-slot-autocomplete
        :time-slots="actor.timeSlots"
        :time-slot-options="timeSlots"
        :disabled="!editable"
        @delete-time-slot="onClickDeleteTimeSlot"
        @add-time-slot="addTimeSlot"
      />
      <br />
      <oxd-divider />
      <oxd-form-row v-if="!isAdding">
        <div class="orangehrm-administrators-header">
          <oxd-text class="orangehrm-sub-title" tag="h6">
            {{ $t('Administrateurs') }}
          </oxd-text>
          <oxd-button
            icon-name="plus"
            display-type="secondary"
            :label="$t('general.add')"
            @click="onClickAddAdministrator"
          />
        </div>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              :model-value="actor.environment"
              :label="$t('Environnement')"
              :disabled="true"
            />
          </oxd-grid-item>
        </oxd-grid>
        <div
          v-if="formattedAdministrators && formattedAdministrators.length > 0"
          class="orangehrm-container"
        >
          <oxd-card-table
            :headers="administratorHeaders"
            :items="formattedAdministrators"
            row-decorator="oxd-table-decorator-card"
          />
        </div>
      </oxd-form-row>
      <br />
      <oxd-divider />
      <oxd-form-actions>
        <required-text />
        <oxd-button
          v-if="isAdding"
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <oxd-button
          v-else
          :label="$t('performance.delete')"
          display-type="danger"
          @click="onClickDelete"
        />
        <submit-button />
      </oxd-form-actions>
    </oxd-form>
  </div>

  <!-- Modal pour choisir l'environnement -->
  <oxd-dialog
    v-if="showEnvironmentModal"
    v-model:show="showEnvironmentModal"
    :style="{width: '90%', maxWidth: '600px'}"
    @update:show="onCancelEnvironment"
  >
    <div class="orangehrm-modal-header">
      <oxd-text type="card-title">
        {{ $t('Choisir un environnement') }}
      </oxd-text>
    </div>
    <oxd-divider />
    <oxd-text tag="p" style="margin-bottom: 1rem">
      {{
        $t(
          '⚠️ Il est nécessaire de choisir un environnement avant de créer des comptes administrateurs.',
        )
      }}
    </oxd-text>
    <oxd-form :loading="isSavingEnvironment" @submit-valid="onSaveEnvironment">
      <oxd-form-row>
        <oxd-grid :cols="1" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="environmentForm.theme"
              type="select"
              :label="$t('Environnement')"
              :options="themes"
              required
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
          @click="onCancelEnvironment"
        />
        <oxd-button
          display-type="secondary"
          :label="$t('general.save')"
          type="submit"
        />
      </oxd-form-actions>
    </oxd-form>
  </oxd-dialog>

  <!-- Modal pour ajouter un administrateur -->
  <oxd-dialog
    v-if="showAdministratorModal"
    v-model:show="showAdministratorModal"
    :style="{width: '90%', maxWidth: '600px'}"
    @update:show="onCancelAdministrator"
  >
    <div class="orangehrm-modal-header">
      <oxd-text type="card-title">
        {{ $t('Ajouter un administrateur') }}
      </oxd-text>
    </div>
    <oxd-divider />
    <oxd-form
      :loading="isSavingAdministrator"
      @submit-valid="onSaveAdministrator"
    >
      <oxd-form-row>
        <oxd-grid :cols="1" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="administratorForm.email"
              :label="$t('general.email')"
              :rules="rules.administratorEmail"
              required
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>
      <oxd-form-row>
        <oxd-grid :cols="2" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="administratorForm.password"
              type="password"
              :label="$t('general.password')"
              :rules="rules.administratorPassword"
              required
            />
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-field
              v-model="administratorForm.confirmPassword"
              type="password"
              :label="$t('Confirmer le mot de passe')"
              :rules="confirmPasswordRules"
              required
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
          @click="onCancelAdministrator"
        />
        <oxd-button
          display-type="secondary"
          :label="$t('general.save')"
          type="submit"
        />
      </oxd-form-actions>
    </oxd-form>
  </oxd-dialog>

  <delete-confirmation
    ref="deleteAdministratorDialog"
    :title="$t('general.delete')"
    :subtitle="$t('Êtes-vous sûr de vouloir supprimer cet administrateur ?')"
  ></delete-confirmation>
</template>
<script>
import {OxdSwitchInput, OxdDialog} from '@ohrm/oxd';
import BackButton from '@/core/components/buttons/BackButton';
import JobsAutocomplete from '@/core/components/inputs/JobsAutocomplete';
import AgeAutocomplete from '@/core/components/inputs/AgeAutocomplete';
import CustomFieldAutocomplete from '@/core/components/inputs/CustomFieldAutocomplete';
import TimeSlotAutocomplete from '@/core/components/inputs/TimeSlotAutocomplete';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {APIService} from '@/core/util/services/api.service';
import {
  required,
  numericOnly,
  digitsOnlyWithTwoDecimalPoints,
  shouldNotExceedCharLength,
  validEmailFormat,
} from '@/core/util/validation/rules';

const AdministratorModel = {
  email: '',
  password: '',
  confirmPassword: '',
};

const EnvironmentModel = {
  theme: null,
};

const ActorModel = {
  id: null,
  name: null,
  isOf: true,
  isPriority: false,
  allowMatchingSpecific: false,
  manualDelivery: false,
  documentation: null,
  maxAmountPerDay: 0,
  maxAmountPerMonth: 0,
  ages: [],
  countries: [],
  needs: [],
  fundings: [],
  jobs: [],
  studyLevels: [],
  status: [],
  trainingMethods: [],
  sources: [],
  administrators: [],
  environment: null,
};

export default {
  name: 'ActorCard',

  components: {
    'oxd-switch-input': OxdSwitchInput,
    'oxd-dialog': OxdDialog,
    'back-button': BackButton,
    'jobs-autocomplete': JobsAutocomplete,
    'custom-field-autocomplete': CustomFieldAutocomplete,
    'age-autocomplete': AgeAutocomplete,
    'time-slot-autocomplete': TimeSlotAutocomplete,
    'delete-confirmation': DeleteConfirmationDialog,
  },

  props: {
    actorCurrent: {
      type: Object,
      required: true,
    },
    countries: {
      type: Array,
      default: () => [],
    },
    fundings: {
      type: Array,
      default: () => [],
    },
    studyLevels: {
      type: Array,
      default: () => [],
    },
    needs: {
      type: Array,
      default: () => [],
    },
    status: {
      type: Array,
      default: () => [],
    },
    trainingMethods: {
      type: Array,
      default: () => [],
    },
    sources: {
      type: Array,
      default: () => [],
    },
    timeSlots: {
      type: Array,
      default: () => [],
    },
    isAdding: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    themes: {
      type: Array,
      default: () => [],
    },
  },

  emits: ['cancel', 'delete', 'save', 'update'],

  setup() {
    const http = new APIService(window.appGlobal.baseUrl, '/');
    const rules = {
      actor: [required],
      title: [shouldNotExceedCharLength(100), required],
      price: [digitsOnlyWithTwoDecimalPoints],
      maxAmountPerDay: [numericOnly],
      maxAmountPerMonth: [numericOnly],
      postalCode: [numericOnly],
      administratorEmail: [required, validEmailFormat],
      administratorPassword: [required, shouldNotExceedCharLength(255)],
      administratorConfirmPassword: [required],
    };
    return {
      http,
      rules,
    };
  },
  data() {
    return {
      editable: true,
      actor: {...ActorModel},
      showAdministratorModal: false,
      showEnvironmentModal: false,
      isSavingAdministrator: false,
      isSavingEnvironment: false,
      administratorForm: {...AdministratorModel},
      environmentForm: {...EnvironmentModel},
      administratorHeaders: [
        {
          name: 'email',
          title: this.$t('general.email'),
          style: {flex: 1},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 1},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            delete: {
              onClick: this.onClickDeleteAdministrator,
              component: 'oxd-icon-button',
              props: {
                name: 'trash',
              },
            },
          },
        },
      ],
    };
  },
  computed: {
    formattedAdministrators() {
      if (
        !this.actor.administrators ||
        this.actor.administrators.length === 0
      ) {
        return [];
      }
      return this.actor.administrators.map((admin) => ({
        email: admin.email || '',
        _originalId: admin.id,
      }));
    },
    confirmPasswordRules() {
      return [
        ...this.rules.administratorConfirmPassword,
        (value) => {
          if (!value) {
            return this.$t('general.required');
          }
          if (value !== this.administratorForm.password) {
            return this.$t('Les mots de passe ne correspondent pas');
          }
          return true;
        },
      ];
    },
  },
  watch: {
    actorCurrent() {
      this.fetchActor();
    },
  },
  beforeMount() {
    if (!this.isAdding) this.fetchActor();
  },
  methods: {
    onCancel() {
      this.$emit('cancel');
    },
    onSave() {
      this.actor.maxAmountPerDay = parseInt(this.actor.maxAmountPerDay);
      this.actor.maxAmountPerMonth = parseInt(this.actor.maxAmountPerMonth);
      if (this.actor.ages) {
        for (const age of this.actor.ages) {
          age.min = parseInt(age.min);
          age.max = parseInt(age.max);
        }
      }
      if (this.actor.jobs) {
        for (const job of this.actor.jobs) {
          if (job.actorJobId) job.actorJobId = parseInt(job.actorJobId);
          if (job.trainingId) job.trainingId = parseInt(job.trainingId);
        }
      }
      if (this.actor.timeSlots) {
        for (const timeSlot of this.actor.timeSlots) {
          if (timeSlot.min) timeSlot.min = parseInt(timeSlot.min);
          if (timeSlot.max) timeSlot.max = parseInt(timeSlot.max);
        }
      }
      this.$emit('save', this.actor);
    },
    onClickDeleteJob(job) {
      this.actor.jobs = this.actor.jobs.filter((j) => j !== job);
    },
    addJobs(newJobs) {
      for (const job of newJobs)
        this.actor.jobs.push({title: job, actorJobId: '', trainingId: ''});
    },
    onClickDeleteStatus(status) {
      this.actor.status = this.actor.status.filter((s) => s !== status);
    },
    addStatus(newStatus) {
      this.actor.status.push({title: newStatus});
    },
    onClickDeleteStudyLevel(studyLevel) {
      this.actor.studyLevels = this.actor.studyLevels.filter(
        (s) => s !== studyLevel,
      );
    },
    addStudyLevel(newStudyLevel) {
      this.actor.studyLevels.push({title: newStudyLevel});
    },
    onClickDeleteAge(age) {
      this.actor.ages = this.actor.ages.filter((a) => a !== age);
    },
    addAgeRange(age) {
      this.actor.ages.push(age);
    },
    onClickDeleteCountry(country) {
      this.actor.countries = this.actor.countries.filter((c) => c !== country);
    },
    addCountry(newCountry) {
      this.actor.countries.push({title: newCountry});
    },
    onClickDeleteFunding(funding) {
      this.actor.fundings = this.actor.fundings.filter((f) => f !== funding);
    },
    addFunding(newFunding) {
      this.actor.fundings.push({title: newFunding});
    },
    onClickDeleteNeed(need) {
      this.actor.needs = this.actor.needs.filter((n) => n !== need);
    },
    addNeed(newNeed) {
      this.actor.needs.push({title: newNeed});
    },
    onClickDeleteTrainingMethod(trainingMethod) {
      this.actor.trainingMethods = this.actor.trainingMethods.filter(
        (t) => t !== trainingMethod,
      );
    },
    addTrainingMethod(newTrainingMethod) {
      this.actor.trainingMethods.push({title: newTrainingMethod});
    },
    onClickDeleteSource(source) {
      this.actor.sources = this.actor.sources.filter((s) => s !== source);
    },
    addSource(newSource) {
      this.actor.sources.push({title: newSource});
    },
    onClickDelete() {
      this.$emit('delete', this.actor.id);
    },
    onClickDeleteTimeSlot(timeSlot) {
      this.actor.timeSlots = this.actor.timeSlots.filter((t) => t !== timeSlot);
    },
    addTimeSlot(newTimeSlot) {
      this.actor.timeSlots.push(newTimeSlot);
    },
    fetchActor() {
      this.actor.id = this.actorCurrent.id;
      this.actor.name = this.actorCurrent.name;
      this.actor.isOf = this.actorCurrent.isOf;
      this.actor.isPriority = this.actorCurrent.isPriority;
      this.actor.allowMatchingSpecific =
        this.actorCurrent.allowMatchingSpecific;
      this.actor.manualDelivery = this.actorCurrent.manualDelivery;
      this.actor.documentation = this.actorCurrent.documentation;
      this.actor.maxAmountPerDay = this.actorCurrent.maxAmountPerDay;
      this.actor.maxAmountPerMonth = this.actorCurrent.maxAmountPerMonth;
      this.actor.ages = this.actorCurrent.ages;
      if (this.actor.ages) this.actor.ages.sort((a, b) => a.min - b.min);
      this.actor.countries = this.actorCurrent.countries;
      this.actor.needs = this.actorCurrent.needs;
      this.actor.fundings = this.actorCurrent.fundings;
      this.actor.jobs = this.actorCurrent.jobs;
      if (this.actor.jobs) {
        for (const job of this.actor.jobs) {
          job.actorJobId = !job.actorJobId ? '' : job.actorJobId;
          job.trainingId = !job.trainingId ? '' : job.trainingId;
        }
      }
      this.actor.studyLevels = this.actorCurrent.studyLevels;
      this.actor.status = this.actorCurrent.status;
      this.actor.trainingMethods = this.actorCurrent.trainingMethods;
      this.actor.sources = this.actorCurrent.sources;
      this.actor.timeSlots = this.actorCurrent.timeSlots;
      this.actor.environment = this.actorCurrent.environment;
      this.actor.administrators = this.actorCurrent.administrators
        ? [...this.actorCurrent.administrators]
        : [];
    },
    onClickAddAdministrator() {
      // Si l'environnement n'est pas défini, ouvrir la modal de sélection d'environnement
      if (!this.actor.environment) {
        this.environmentForm = {...EnvironmentModel};
        this.showEnvironmentModal = true;
      } else {
        // Sinon, ouvrir directement la modal de création d'administrateur
        this.administratorForm = {...AdministratorModel};
        this.showAdministratorModal = true;
      }
    },
    onCancelAdministrator() {
      this.showAdministratorModal = false;
      this.administratorForm = {...AdministratorModel};
    },
    onCancelEnvironment() {
      this.showEnvironmentModal = false;
      this.environmentForm = {...EnvironmentModel};
    },
    onSaveEnvironment() {
      this.isSavingEnvironment = true;
      const selectedTheme = this.environmentForm.theme;
      const environmentLabel = selectedTheme?.label || null;

      // Mettre à jour l'acteur avec l'environnement choisi
      const updatedActor = {
        ...this.actor,
        environment: environmentLabel,
      };

      this.http
        .request({
          method: 'PUT',
          url: `/api/v2/admin/actor/${this.actor.id}`,
          data: updatedActor,
        })
        .then(() => {
          // Mettre à jour l'environnement localement
          this.actor.environment = environmentLabel;
          this.onCancelEnvironment();
          this.$toast.saveSuccess();
          // Ouvrir la modal de création d'administrateur
          this.administratorForm = {...AdministratorModel};
          this.showAdministratorModal = true;
          this.$emit('update');
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error?.response?.data?.message);
        })
        .finally(() => {
          this.isSavingEnvironment = false;
        });
    },
    onSaveAdministrator() {
      this.isSavingAdministrator = true;
      const administratorData = {
        email: this.administratorForm.email,
        password: this.administratorForm.password,
        theme: this.actor.environment, // Utiliser l'environnement de l'acteur
      };

      this.http
        .request({
          method: 'POST',
          url: `/api/v2/admin/actor/${this.actor.id}/administrator`,
          data: administratorData,
        })
        .then(() => {
          this.onCancelAdministrator();
          return this.$toast.saveSuccess();
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error?.response?.data?.message);
        })
        .finally(() => {
          this.isSavingAdministrator = false;
          this.$emit('update');
        });
    },
    onClickDeleteAdministrator(item) {
      this.$refs.deleteAdministratorDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteAdministrator(item);
        }
      });
    },
    deleteAdministrator(item) {
      const administratorData = {
        email: item.email,
        theme: this.actor.environment,
      };
      this.http
        .request({
          method: 'DELETE',
          url: `/api/v2/admin/actor/administrator/${item._originalId}`,
          data: administratorData,
        })
        .then(() => {
          // Retirer l'administrateur de la liste locale
          this.actor.administrators = this.actor.administrators.filter(
            (admin) => admin.id !== item._originalId,
          );
          return this.$toast.deleteSuccess();
        })
        .catch((error) => {
          return this.$toast.unexpectedError(error?.response?.data?.message);
        });
    },
  },
};
</script>
<style scoped lang="scss">
.orangehrm-job-selection {
  &-criteria {
    display: flex;
    align-items: center;
  }
  &-criteria-selected {
    display: flex;
    align-items: baseline;
  }
  &-criteria-name {
    margin-left: 1rem;
    font-weight: 700;
    font-size: $oxd-input-control-font-size;
    padding: $oxd-input-control-vertical-padding 0rem;
  }
  &-icon {
    margin-left: 1rem;
  }
}

.orangerhrm-switch-wrapper {
  display: flex;
  flex-direction: row;
  justify-content: start;
  gap: 1rem;

  @include oxd-respond-to('sm') {
    max-width: 50%;
  }
  @include oxd-respond-to('md') {
    max-width: 100%;
  }
}
.orangehrm-text {
  font-size: 12px;
  font-weight: 600;
  color: $oxd-interface-gray-darken-1-color;
}

.orangehrm-administrators-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-bottom: 1rem;
}
</style>
