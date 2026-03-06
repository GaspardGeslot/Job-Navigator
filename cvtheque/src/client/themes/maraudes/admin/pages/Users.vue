<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-paper-container">
      <div class="orangehrm-left-header-container">
        <oxd-button
          :label="$t('general.add')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <div class="orangehrm-container">
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
    <delete-confirmation ref="deleteDialog"></delete-confirmation>

    <!-- Modal pour ajouter/modifier un utilisateur -->
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
                <oxd-grid-item>
                  <div class="orangehrm-switch-wrapper">
                    <oxd-text class="orangehrm-text">
                      {{ $t('Est un administrateur ?') }}
                    </oxd-text>
                    <oxd-switch-input v-model="isAdmin" />
                  </div>
                </oxd-grid-item>
              </oxd-grid>
              <oxd-grid
                v-if="!isAdmin && matchings && matchings.length"
                :cols="2"
              >
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="matchingSelected"
                    type="select"
                    :label="$t('recruitment.need_title')"
                    :options="[
                      {id: null, label: 'Sans sélection'},
                      ...matchings,
                    ]"
                  />
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
  </div>
</template>

<script>
import {reactive, toRefs, watch} from 'vue';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteConfirmationDialog';
import {APIService} from '@/core/util/services/api.service';
import useSort from '@/core/util/composable/useSort';
import useToast from '@/core/util/composable/useToast';
import {
  required,
  shouldNotExceedCharLength,
  validEmailFormat,
} from '@/core/util/validation/rules';
import {OxdSwitchInput} from '@ohrm/oxd';

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
  },

  setup(props) {
    const {noRecordsFound, error, success} = useToast();
    const {sortDefinition, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });
    const http = new APIService(window.appGlobal.baseUrl, '/api/v2/admin/user');
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
      editingItem: null,
      matchingSelected: null,
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
              matchingId: rawMatchingId,
              matchingLabel: matching ? matching.label : '',
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
          })
        : http.create({
            email: state.email,
            role: state.isAdmin ? 'ACTOR' : 'AGENT',
            password: state.password,
            matchingId: selectedMatchingId,
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

    return {
      http,
      onClickValidate,
      fetchUserData,
      resetForm,
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
    headers() {
      const baseHeaders = [
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
        {
          name: 'matchingLabel',
          title: this.$t('Matching'),
          sortField: 'matchingLabel',
          style: {flex: 0.75},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.cellRenderer,
        },
      ];

      return baseHeaders;
    },
  },
  beforeMount() {
    this.fetchUserData();
  },
  methods: {
    cellRenderer(...[, , , row]) {
      const cellConfig = {};

      // Ne pas afficher les actions si c'est l'utilisateur actuel
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
    onClickCancel() {
      this.resetForm();
    },
    onClickAdd() {
      this.isModalOpen = true;
      this.isEditing = false;
      this.editingItem = null;
      this.matchingSelected = null;
    },
    onClickEdit(item) {
      this.isModalOpen = true;
      this.isEditing = true;
      this.editingItem = item;
      this.email = item.email;
      this.isAdmin = item.isAdmin;
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
