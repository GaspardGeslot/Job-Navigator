<template>
  <div class="orangehrm-background-container">
    <!-- Section Utilisateurs -->
    <div class="orangehrm-paper-container">
      <div class="orangehrm-left-header-container">
        <oxd-text v-if="specificMatching" tag="h6" class="orangehrm-main-title">
          Utilisateurs
        </oxd-text>
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <div
        class="orangehrm-container users-table-container"
        :class="{'--cell-editing': editingMatchingCell}"
      >
        <oxd-card-table
          v-model:selected="checkedItems"
          v-model:order="sortDefinition"
          :headers="headers"
          :items="items"
          :selectable="false"
          :clickable="false"
          :loading="isLoading"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
      <div class="orangehrm-bottom-container">
        <oxd-pagination
          v-if="showPaginator"
          v-model:current="currentPage"
          :length="pages"
        />
      </div>
    </div>

    <!-- Section Périmètres -->
    <div
      v-if="specificMatching"
      class="orangehrm-paper-container perimeter-section"
    >
      <div class="orangehrm-left-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">Périmètres</oxd-text>
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAddMatching"
        />
      </div>
      <oxd-text tag="p" class="perimeter-description">
        Un périmètre permet de définir un sous-ensemble d'utilisateurs qui ne
        voient que les contacts du périmètre qui leur est associé.
      </oxd-text>
      <div class="orangehrm-container">
        <oxd-card-table
          :headers="matchingHeaders"
          :items="matchingItems"
          :selectable="false"
          :clickable="false"
          :loading="isMatchingLoading"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
    </div>

    <delete-confirmation ref="deleteDialog"></delete-confirmation>

    <!-- Modal utilisateur -->
    <div v-if="isModalOpen" class="modal-overlay" @click="onClickCancel">
      <div class="modal-container" @click.stop>
        <oxd-form :loading="isSaving" @submit-valid="onClickValidate">
          <div class="modal-header">
            <h3>
              {{
                isEditing
                  ? $t("Modifier l'utilisateur")
                  : $t('Ajouter un utilisateur')
              }}
            </h3>
            <span class="close-icon" @click="onClickCancel">&times;</span>
          </div>
          <div class="modal-body">
            <oxd-form-row>
              <oxd-grid :cols="2">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="email"
                    :label="$t('Email')"
                    required
                    :rules="rules.email"
                    :disabled="isEditing"
                  />
                </oxd-grid-item>
              </oxd-grid>
              <oxd-grid :cols="2">
                <oxd-grid-item
                  v-if="
                    !isAdmin &&
                    matchings &&
                    matchings.length &&
                    specificMatching
                  "
                >
                  <oxd-input-field
                    v-model="matchingSelected"
                    type="select"
                    :label="$t('Périmètre')"
                    :options="[{id: null, label: 'Tout'}, ...matchings]"
                  />
                </oxd-grid-item>
              </oxd-grid>
              <oxd-grid :cols="2">
                <oxd-grid-item>
                  <div class="orangehrm-switch-wrapper">
                    <oxd-text class="orangehrm-text">
                      {{ $t('Est un administrateur ?') }}
                    </oxd-text>
                    <oxd-switch-input
                      v-model="isAdmin"
                      :disabled="isEditing && isCurrentUser"
                    />
                  </div>
                </oxd-grid-item>
                <oxd-grid-item>
                  <div class="orangehrm-switch-wrapper">
                    <oxd-text class="orangehrm-text">
                      {{ $t('Notification de nouveau contact par mail ?') }}
                    </oxd-text>
                    <oxd-switch-input v-model="notify" />
                  </div>
                </oxd-grid-item>
              </oxd-grid>
              <oxd-grid v-if="!isEditing" :cols="2">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="password"
                    type="password"
                    :label="$t('Mot de passe')"
                    :rules="rules.password"
                    required
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="passwordConfirm"
                    type="password"
                    :label="$t('Confirmer le mot de passe')"
                    :rules="confirmPasswordRules"
                    required
                  />
                </oxd-grid-item>
              </oxd-grid>
            </oxd-form-row>
          </div>
          <div class="modal-footer">
            <oxd-button
              :label="$t('Annuler')"
              display-type="ghost"
              @click="onClickCancel"
            />
            <oxd-button
              :label="$t(isEditing ? 'Modifier' : 'Enregistrer')"
              display-type="secondary"
              :loading="isSaving"
              type="submit"
            />
          </div>
        </oxd-form>
      </div>
    </div>

    <!-- Modal périmètre -->
    <div
      v-if="isMatchingModalOpen"
      class="modal-overlay"
      @click="onClickCancelMatching"
    >
      <div class="modal-container" @click.stop>
        <oxd-form
          :loading="isMatchingSaving"
          @submit-valid="onClickValidateMatching"
        >
          <div class="modal-header">
            <h3>
              {{
                isMatchingEditing
                  ? $t('Modifier le périmètre')
                  : $t('Ajouter un périmètre')
              }}
            </h3>
            <span class="close-icon" @click="onClickCancelMatching"
              >&times;</span
            >
          </div>
          <div class="modal-body">
            <oxd-form-row>
              <oxd-grid :cols="1">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="matchingTitle"
                    :label="$t('Titre')"
                    required
                    :rules="rules.matchingTitle"
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="matchingDescription"
                    :label="$t('Description')"
                    :rules="rules.matchingDescription"
                  />
                </oxd-grid-item>
              </oxd-grid>
            </oxd-form-row>
          </div>
          <div class="modal-footer">
            <oxd-button
              :label="$t('Annuler')"
              display-type="ghost"
              @click="onClickCancelMatching"
            />
            <oxd-button
              :label="$t(isMatchingEditing ? 'Modifier' : 'Enregistrer')"
              display-type="secondary"
              :loading="isMatchingSaving"
              type="submit"
            />
          </div>
        </oxd-form>
      </div>
    </div>
  </div>
</template>

<script>
import {
  reactive,
  toRefs,
  watch,
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
} from 'vue';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {APIService} from '@/core/util/services/api.service';
import useSort from '@/core/util/composable/useSort';
import useToast from '@/core/util/composable/useToast';
import {
  required,
  shouldNotExceedCharLength,
  validEmailFormat,
} from '@/core/util/validation/rules';
import {OxdSwitchInput, useInjectTableProps} from '@ohrm/oxd';

const EMPTY_MATCHING_OPTION = {id: null, label: 'Tout'};

const MatchingLabelCell = {
  name: 'MatchingLabelCell',
  props: {
    header: {
      type: Object,
      required: true,
    },
    item: {
      type: Object,
      required: true,
    },
    displayLabel: {
      type: String,
      default: '',
    },
    isEditing: {
      type: Boolean,
      default: false,
    },
    options: {
      type: Array,
      default: () => [],
    },
    isCurrentOption: {
      type: Function,
      required: true,
    },
    onCellClick: {
      type: Function,
      default: () => undefined,
    },
    onSelectOption: {
      type: Function,
      default: () => undefined,
    },
  },
  setup() {
    const {screenState} = useInjectTableProps();

    return {
      screenState,
    };
  },
  computed: {
    showHeader() {
      return !(
        this.screenState.screenType === 'lg' ||
        this.screenState.screenType === 'xl'
      );
    },
  },
  template: `
    <div
      class="oxd-table-card-cell matching-label-cell"
      :class="{'matching-label-cell--editing': isEditing}"
      @click.stop="onCellClick"
    >
      <div v-show="showHeader" class="header">{{ header.title }}</div>
      <div class="data">
        <div v-if="isEditing" class="inline-cell-editor" @click.stop>
          <ul class="inline-cell-editor__options">
            <li
              v-for="option in options"
              :key="'matching-' + (option.id ?? 'all')"
              :class="{
                'inline-cell-editor__option': true,
                'inline-cell-editor__option--active': isCurrentOption(option),
                'inline-cell-editor__option--empty': option.id === null,
              }"
              @click.stop="onSelectOption(option)"
            >
              {{ option.id === null ? '—' : option.label }}
            </li>
          </ul>
        </div>
        <template v-else>{{ displayLabel }}</template>
      </div>
    </div>
  `,
};

const defaultSortOrder = {
  title: 'ASC',
};

export default {
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'oxd-switch-input': OxdSwitchInput,
  },

  props: {
    matchings: {
      type: Array,
      required: true,
      default: () => [],
    },
    specificMatching: {
      type: Boolean,
      required: true,
      default: false,
    },
  },

  setup(props) {
    const instance = getCurrentInstance();
    const {noRecordsFound, error, success, updateSuccess} = useToast();
    const editingMatchingCell = ref(null);
    const {sortDefinition, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const http = new APIService(window.appGlobal.baseUrl, '/api/v2/admin/user');
    const httpMatchings = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/admin/matching/info',
    );
    const state = reactive({
      items: [],
      total: 0,
      isLoading: false,
      isModalOpen: false,
      isEditing: false,
      isSaving: false,
      email: '',
      password: '',
      passwordConfirm: '',
      isAdmin: false,
      notify: false,
      isCurrentUser: false,
      editingItem: null,
      matchingSelected: null,
      // Périmètres
      matchingItems: [],
      isMatchingLoading: false,
      isMatchingModalOpen: false,
      isMatchingEditing: false,
      isMatchingSaving: false,
      matchingTitle: '',
      matchingDescription: '',
      editingMatchingItem: null,
    });

    const fetchUserData = () => {
      state.isLoading = true;
      http
        .getAll()
        .then((response) => {
          state.items = response.data.map((item) => {
            const rawMatchingId =
              item.matchingId ??
              (item.matching && (item.matching.id ?? item.matching));

            const matching = Array.isArray(props.matchings)
              ? props.matchings.find(
                  (match) => String(match.id) === String(rawMatchingId),
                )
              : null;

            return {
              id: item.id,
              email: item.email,
              role: item.role === 'ACTOR' ? 'Administrateur' : 'Agent',
              isAdmin: item.role === 'ACTOR',
              isCurrentUser: item.isCurrentUser,
              matchingId: rawMatchingId ?? null,
              matchingLabel:
                item.role !== 'ACTOR' &&
                (rawMatchingId === null ||
                  rawMatchingId === undefined ||
                  rawMatchingId === '')
                  ? EMPTY_MATCHING_OPTION.label
                  : matching
                  ? matching.label
                  : '',
              notify: Boolean(item.notify),
              notifyLabel: item.notify ? 'Oui' : 'Non',
            };
          });
          state.total = response.data.length;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: 'Impossible de récupérer les utilisateurs',
          });
        })
        .finally(() => {
          state.isLoading = false;
        });
    };

    const onClickValidate = () => {
      // Vérifier si le nom existe déjà (sauf si on est en mode édition du même item)
      const existingItem = state.items.find(
        (item) => item.email === state.email,
      );
      if (
        existingItem &&
        (!state.isEditing || existingItem.email !== state.editingItem?.email)
      ) {
        return error({
          title: 'Conflit',
          message: 'Cet email existe déjà',
        });
      }

      state.isSaving = true;

      // Si l'utilisateur est administrateur, on ne doit jamais envoyer de matchingId
      const selectedMatchingId = state.isAdmin
        ? null
        : state.matchingSelected && state.matchingSelected.id
        ? state.matchingSelected.id
        : null;

      const requestPromise = state.isEditing
        ? http.update(state.editingItem.id, {
            email: state.email,
            isAdmin: state.isAdmin,
            matchingId: selectedMatchingId,
            notify: state.notify,
          })
        : http.create({
            email: state.email,
            role: state.isAdmin ? 'ACTOR' : 'AGENT',
            password: state.password,
            matchingId: selectedMatchingId,
            notify: state.notify,
          });

      requestPromise
        .then(() => {
          success({
            title: 'Succès',
            message: state.isEditing
              ? 'Utilisateur modifié avec succès'
              : 'Utilisateur ajouté avec succès',
          });
          resetForm();
          fetchUserData();
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: state.isEditing
              ? "Impossible de modifier l'utilisateur"
              : "Impossible d'ajouter l'utilisateur",
          });
        })
        .finally(() => {
          state.isSaving = false;
        });
    };

    const resetForm = () => {
      state.email = '';
      state.password = '';
      state.passwordConfirm = '';
      state.isAdmin = false;
      state.notify = false;
      state.isCurrentUser = false;
      state.matchingSelected = null;
      state.isModalOpen = false;
      state.isEditing = false;
      state.editingItem = null;
    };

    const sort = () => {
      if (sortOrder.value === 'ASC')
        state.items.sort((a, b) => a.email.localeCompare(b.email));
      else state.items.sort((a, b) => b.email.localeCompare(a.email));
    };

    // Si on passe un utilisateur en administrateur, on efface systématiquement le matching sélectionné
    watch(
      () => state.isAdmin,
      (isAdmin) => {
        if (isAdmin) {
          state.matchingSelected = null;
        }
      },
    );

    onSort(sort);

    const matchingSelectOptions = computed(() => [
      EMPTY_MATCHING_OPTION,
      ...(props.matchings || []),
    ]);

    const isAgentUser = (item) => item?.role === 'Agent';

    const getMatchingDisplayLabel = (item) => {
      if (!isAgentUser(item)) {
        return '';
      }
      if (
        item.matchingId === null ||
        item.matchingId === undefined ||
        item.matchingId === ''
      ) {
        return EMPTY_MATCHING_OPTION.label;
      }
      return item.matchingLabel || '';
    };

    const isEditingMatchingCell = (rowIndex) =>
      editingMatchingCell.value?.row === rowIndex;

    const isCurrentMatchingOption = (item, option) => {
      const currentId = item.matchingId ?? null;
      if (option.id === null) {
        return (
          currentId === null || currentId === undefined || currentId === ''
        );
      }
      return String(currentId) === String(option.id);
    };

    const applyMatchingValue = (item, matchingId, matchingLabel) => {
      item.matchingId = matchingId;
      item.matchingLabel = matchingLabel;
    };

    const closeMatchingEditor = () => {
      editingMatchingCell.value = null;
    };

    const persistAgentMatchingEdit = (item, matchingId, matchingLabel) => {
      const previousMatchingId = item.matchingId ?? null;
      const previousMatchingLabel = item.matchingLabel ?? '';
      applyMatchingValue(item, matchingId, matchingLabel);

      return http
        .request({
          method: 'PUT',
          url: `/api/v2/admin/user/${item.id}/matching`,
          data: {matchingId},
        })
        .then(() => {
          updateSuccess();
        })
        .catch((requestError) => {
          applyMatchingValue(item, previousMatchingId, previousMatchingLabel);
          instance?.proxy?.$toast?.unexpectedError(
            requestError?.response?.data?.message,
          );
        });
    };

    const onMatchingCellClick = (rowIndex, item) => {
      if (item.role !== 'Agent') {
        closeMatchingEditor();
        return;
      }
      editingMatchingCell.value = {row: rowIndex, userId: item.id};
    };

    const onSelectMatchingOption = (item, option) => {
      if (item.role !== 'Agent') {
        return;
      }
      if (isCurrentMatchingOption(item, option)) {
        return;
      }
      const matchingId = option.id === null ? null : option.id;
      const matchingLabel =
        option.id === null ? EMPTY_MATCHING_OPTION.label : option.label;
      persistAgentMatchingEdit(item, matchingId, matchingLabel);
      closeMatchingEditor();
    };

    const onDocumentClick = (event) => {
      if (!editingMatchingCell.value) {
        return;
      }
      if (event.target.closest('.inline-cell-editor')) {
        return;
      }
      closeMatchingEditor();
    };

    onMounted(() => {
      document.addEventListener('click', onDocumentClick);
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', onDocumentClick);
    });

    const fetchMatchingData = () => {
      state.isMatchingLoading = true;
      httpMatchings
        .getAll()
        .then((response) => {
          state.matchingItems = (response.data || []).map((item) => ({
            id: item.id,
            title: item.title,
            description: item.description,
            onlyScope: item.onlyScope ?? item.only_scope ?? false,
          }));
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: 'Impossible de récupérer les périmètres',
          });
        })
        .finally(() => {
          state.isMatchingLoading = false;
        });
    };

    const resetMatchingForm = () => {
      state.matchingTitle = '';
      state.matchingDescription = '';
      state.isMatchingModalOpen = false;
      state.isMatchingEditing = false;
      state.isMatchingSaving = false;
      state.editingMatchingItem = null;
    };

    const onClickValidateMatching = () => {
      state.isMatchingSaving = true;
      const requestPromise = state.isMatchingEditing
        ? httpMatchings.update(state.editingMatchingItem.id, {
            title: state.matchingTitle,
            description: state.matchingDescription,
          })
        : httpMatchings.create({
            title: state.matchingTitle,
            description: state.matchingDescription,
          });

      requestPromise
        .then(() => {
          success({
            title: 'Succès',
            message: state.isMatchingEditing
              ? 'Périmètre modifié avec succès'
              : 'Périmètre ajouté avec succès',
          });
          resetMatchingForm();
          fetchMatchingData();
        })
        .catch(() => {
          error({
            title: 'Erreur',
            message: state.isMatchingEditing
              ? 'Impossible de modifier le périmètre'
              : "Impossible d'ajouter le périmètre",
          });
        })
        .finally(() => {
          state.isMatchingSaving = false;
        });
    };

    return {
      http,
      httpMatchings,
      onClickValidate,
      fetchUserData,
      fetchMatchingData,
      resetForm,
      resetMatchingForm,
      onClickValidateMatching,
      editingMatchingCell,
      matchingSelectOptions,
      isAgentUser,
      getMatchingDisplayLabel,
      isEditingMatchingCell,
      isCurrentMatchingOption,
      onMatchingCellClick,
      onSelectMatchingOption,
      ...toRefs(state),
      sortDefinition,
    };
  },

  data() {
    return {
      rules: {
        email: [required, validEmailFormat],
        password: [required, shouldNotExceedCharLength(100)],
        passwordConfirm: [required],
        matchingTitle: [required, shouldNotExceedCharLength(255)],
        matchingDescription: [shouldNotExceedCharLength(500)],
      },
      checkedItems: [],
    };
  },
  computed: {
    confirmPasswordRules() {
      return [
        ...this.rules.passwordConfirm,
        (value) => {
          if (!value) {
            return this.$t('general.required');
          }
          if (value !== this.password) {
            return this.$t('Les mots de passe ne correspondent pas');
          }
          return true;
        },
      ];
    },
    matchingHeaders() {
      return [
        {
          name: 'title',
          title: this.$t('Titre'),
          style: {flex: 1},
        },
        {
          name: 'description',
          title: this.$t('Description'),
          style: {flex: 2},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.matchingCellRenderer,
        },
      ];
    },
    headers() {
      return [
        {
          name: 'email',
          title: this.$t('Email'),
          sortField: 'email',
          style: {flex: 1},
        },
        {
          name: 'role',
          title: this.$t('Rôle'),
          sortField: 'role',
          style: {flex: 0.5},
        },
        ...(this.specificMatching
          ? [
              {
                name: 'matchingLabel',
                title: this.$t('Périmètre'),
                sortField: 'matchingLabel',
                style: {flex: 0.75},
                cellRenderer: this.matchingLabelCellRenderer,
              },
            ]
          : []),
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.cellRenderer,
        },
      ];
    },
  },
  beforeMount() {
    this.fetchUserData();
    if (this.specificMatching) this.fetchMatchingData();
  },
  methods: {
    matchingLabelCellRenderer(...[, , , row]) {
      const item = this.items.find((user) => user.id === row.id);
      if (!item || item.role !== 'Agent') {
        return;
      }

      const rowIndex = this.items.indexOf(item);

      return {
        component: MatchingLabelCell,
        props: {
          item,
          displayLabel: this.getMatchingDisplayLabel(item),
          isEditing: this.isEditingMatchingCell(rowIndex),
          options: this.matchingSelectOptions,
          isCurrentOption: (option) =>
            this.isCurrentMatchingOption(item, option),
          onCellClick: () => this.onMatchingCellClick(rowIndex, item),
          onSelectOption: (option) => this.onSelectMatchingOption(item, option),
        },
      };
    },
    cellRenderer(...[, , , row]) {
      const cellConfig = {};

      if (!row.isCurrentUser) {
        cellConfig.edit = {
          onClick: this.onClickEdit,
          props: {
            name: 'pencil',
          },
        };

        cellConfig.delete = {
          onClick: this.onClickDelete,
          props: {
            name: 'trash',
          },
        };
      }

      return {
        props: {
          header: {
            cellConfig,
          },
        },
      };
    },
    matchingCellRenderer(...[, , , row]) {
      const cellConfig = {};

      if (row.onlyScope) {
        cellConfig.edit = {
          onClick: this.onClickEditMatching,
          props: {name: 'pencil'},
        };
        cellConfig.delete = {
          onClick: this.onClickDeleteMatching,
          props: {name: 'trash'},
        };
      }

      return {props: {header: {cellConfig}}};
    },
    onClickAddMatching() {
      this.isMatchingModalOpen = true;
      this.isMatchingEditing = false;
      this.editingMatchingItem = null;
      this.matchingTitle = '';
      this.matchingDescription = '';
    },
    onClickEditMatching(item) {
      this.isMatchingModalOpen = true;
      this.isMatchingEditing = true;
      this.editingMatchingItem = item;
      this.matchingTitle = item.title;
      this.matchingDescription = item.description || '';
    },
    onClickDeleteMatching(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.isMatchingLoading = true;
          this.httpMatchings
            .delete(item.id)
            .then(() => {
              return this.$toast.deleteSuccess();
            })
            .then(() => {
              this.fetchMatchingData();
            })
            .catch(() => {
              this.isMatchingLoading = false;
            });
        }
      });
    },
    onClickCancelMatching() {
      this.resetMatchingForm();
    },
    onClickCancel() {
      this.resetForm();
    },
    onClickAdd() {
      this.isModalOpen = true;
      this.isEditing = false;
      this.editingItem = null;
      this.matchingSelected = null;
      this.notify = false;
    },
    onClickEdit(item) {
      this.isModalOpen = true;
      this.isEditing = true;
      this.editingItem = item;
      this.email = item.email;
      this.isAdmin = item.isAdmin;
      this.notify = item.notify;
      this.isCurrentUser = item.isCurrentUser;
      this.matchingSelected =
        this.matchings &&
        this.matchings.find(
          (matching) => String(matching.id) === String(item.matchingId),
        );
    },
    onClickDelete(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(item);
        }
      });
    },
    deleteItems(item) {
      this.isLoading = true;
      this.http
        .deleteAll({
          id: item.id,
          email: item.email,
          isAdmin: item.isAdmin,
        })
        .then(() => {
          return this.$toast.deleteSuccess();
        })
        .then(() => {
          this.isLoading = false;
          this.resetDataTable();
        })
        .catch(() => {
          this.isLoading = false;
        });
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.fetchUserData();
    },
  },
};
</script>

<style lang="scss" scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.modal-container {
  background-color: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
  width: 500px;
  max-width: 90%;
  z-index: 1001;
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--oxd-border-light-color);
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 0.75rem 0.75rem 0 0;

  h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
    font-family: Nunito Sans, sans-serif;
  }

  .close-icon {
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    color: #6c757d;
    transition: color 0.2s ease;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;

    &:hover {
      color: #dc3545;
      background-color: rgba(220, 53, 69, 0.1);
    }
  }
}

.modal-body {
  padding: 2rem;
  background-color: #ffffff;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--oxd-border-light-color);
  background-color: #f8f9fa;
  border-radius: 0 0 0.75rem 0.75rem;
}

.perimeter-section {
  margin-top: 1.5rem;
}

.perimeter-description {
  margin: 0 0 1rem 1rem;
  color: #6c757d;
  font-size: 0.9rem;
}

.users-table-container {
  &.--cell-editing :deep(.oxd-table-card) {
    overflow: visible;
  }

  :deep(.matching-label-cell--editing .data) {
    position: relative;
    overflow: visible;
  }

  :deep(.inline-cell-editor) {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 30;
    min-width: 100%;
    max-height: 200px;
    overflow-x: hidden;
    overflow-y: auto;
    background-color: #ffffff;
    border: 1px solid #d8dadf;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  }

  :deep(.inline-cell-editor__options) {
    list-style: none;
    margin: 0;
    padding: 0.25rem 0;
  }

  :deep(.inline-cell-editor__option) {
    padding: 0.5rem 1rem;
    cursor: pointer;
    white-space: nowrap;

    &:hover {
      background-color: #f5f6f7;
    }
  }

  :deep(.inline-cell-editor__option--active) {
    background-color: #eef2ff;
    color: var(--oxd-primary-one-color);
    font-weight: 600;
    cursor: default;

    &:hover {
      background-color: #eef2ff;
    }
  }

  :deep(.inline-cell-editor__option--empty) {
    color: #9aa5b8;
    font-style: italic;
  }
}

// Amélioration des champs de formulaire
:deep(.oxd-input-field) {
  .oxd-input {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.2s ease;

    &:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
  }

  .oxd-label {
    font-weight: 500;
    color: #495057;
  }
}

// Amélioration des boutons
:deep(.oxd-button) {
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;

  &.oxd-button--secondary {
    background: linear-gradient(135deg, #a7df73 0%, #76bc21 100%);
    border: none;

    &:hover {
      transform: translateY(-1px);
      box-shadow: 0 0.5rem 1rem rgba(118, 188, 33, 0.3);
    }
  }

  &.oxd-button--ghost {
    &:hover {
      background-color: rgba(108, 117, 125, 0.1);
    }
  }
}
</style>
