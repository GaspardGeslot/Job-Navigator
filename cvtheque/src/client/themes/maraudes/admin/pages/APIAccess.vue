<template>
  <div class="orangehrm-background-container">
    <div class="orangehrm-paper-container">
      <div class="orangehrm-header-container">
        <oxd-text tag="h6" class="orangehrm-main-title">{{
          $t('Accès API')
        }}</oxd-text>
        <div class="api-access-header-actions">
          <oxd-button
            v-if="showCreateButton"
            :label="$t('Générer une nouvelle clé api')"
            display-type="secondary"
            @click="onClickAddAccess"
          />
          <oxd-button
            v-if="apiAccesses.length >= 2"
            :label="$t('Réinitialiser le tri')"
            display-type="ghost"
            @click="resetSort"
          />
        </div>
      </div>
      <div v-if="apiAccessLimit" class="api-access-guide">
        <oxd-text tag="p" class="api-access-guide-description">
          Cet espace vous permet de générer des clés API pour que vos
          prestataires puissent envoyer automatiquement des contacts dans Job
          Navigator. Chaque clé est un identifiant unique à transmettre avec la
          documentation API pour connecter leur service.
        </oxd-text>
        <ol class="api-access-guide-steps">
          <li class="api-access-guide-step">
            <span class="api-access-step-index">1</span>
            <div>
              <b>Générez une clé API</b> en renseignant un titre unique et, si
              nécessaire, une source (optionnelle). Cette source préremplit la
              colonne <b>source</b> des contacts reçus.
            </div>
          </li>
          <li class="api-access-guide-step">
            <span class="api-access-step-index">2</span>
            <div>
              <b>Copiez <u>immédiatement</u> la clé générée</b> : elle ne sera
              plus affichée ensuite. Conservez-la dans un emplacement sécurisé.
            </div>
          </li>
          <li class="api-access-guide-step">
            <span class="api-access-step-index">3</span>
            <div>
              <b>Transmettez la clé et la documentation API</b> au prestataire
              pour lui permettre d'envoyer automatiquement des contacts.
            </div>
          </li>
          <li class="api-access-guide-step">
            <span class="api-access-step-index">4</span>
            <div>
              <b>Optionnel :</b> désactivez une clé pour la conserver dans votre
              historique tout en bloquant l'envoi de nouveaux contacts.
            </div>
          </li>
        </ol>
        <div class="api-access-doc-card">
          <div class="api-access-doc-card-text">
            <oxd-text tag="p" class="api-access-doc-card-title">
              Documentation API
            </oxd-text>
            <oxd-text tag="p" class="api-access-doc-card-subtitle">
              Ouvrez le guide PDF pour la partager rapidement à votre
              prestataire.
            </oxd-text>
          </div>
          <oxd-button
            :label="$t('Ouvrir la documentation')"
            display-type="secondary"
            icon-name="download"
            type="button"
            @click="openApiDocumentation"
          />
        </div>
      </div>
      <div
        v-if="!apiAccessLimit"
        class="orangehrm-corporate-directory-nocontent"
        style="
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 1rem 0;
        "
      >
        <i class="oxd-icon bi-lock-fill no-access-lock-icon"></i>
        <oxd-text tag="p">
          Votre contrat actuel ne rend pas disponible les accès API externes.
          Veuillez contacter Olecio pour plus de détails
        </oxd-text>
      </div>
      <div v-else class="orangehrm-container">
        <div
          v-if="!isLoading && items.length === 0"
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
            Il n'y a actuellement aucune clé API créée. Vous pouvez en générer
            une nouvelle.
          </oxd-text>
        </div>
        <oxd-card-table
          v-else
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
    </div>

    <div v-if="isCreateModalOpen" class="modal-overlay" @click="closeModals">
      <div class="modal-container" @click.stop>
        <oxd-form :loading="isCreating" @submit-valid="onConfirmCreate">
          <div class="modal-header">
            <h3>
              {{
                createdToken ? 'Votre clé API' : 'Générer une nouvelle clé API'
              }}
            </h3>
          </div>
          <div v-if="!createdToken" class="modal-body">
            <oxd-form-row>
              <oxd-grid :cols="1">
                <oxd-grid-item>
                  <oxd-input-field
                    v-model="newApiTitle"
                    :label="$t('Nom de la clé API')"
                    required
                    :rules="rules.title"
                  />
                </oxd-grid-item>
                <oxd-grid-item>
                  <div class="source-label-row">
                    <oxd-text class="oxd-label">
                      {{ $t('Source (optionnel)') }}
                    </oxd-text>
                    <div class="source-help">
                      <button type="button" class="source-help-icon">?</button>
                      <span class="source-help-tooltip">
                        Champ facultatif : cette valeur permettra de préremplir
                        le champs <b>source</b> des leads.
                      </span>
                    </div>
                  </div>
                  <oxd-input-field v-model="newApiSource" :label="''" />
                </oxd-grid-item>
              </oxd-grid>
            </oxd-form-row>
          </div>
          <div v-else class="modal-body">
            <oxd-text class="orangehrm-text">
              La clé API a été générée avec succès. Pour des raisons de
              sécurité, elle ne sera plus jamais affichée. Copiez la clé et
              conservez-la dans un endroit sécurisé.
            </oxd-text>
            <div class="token-row">
              <oxd-input-field :model-value="createdToken" :disabled="true" />
              <oxd-button
                :label="$t('Copier')"
                icon-name="copy"
                display-type="secondary"
                class="token-copy-button"
                @click="copyCreatedToken"
              />
            </div>
          </div>
          <div class="modal-footer">
            <oxd-button
              v-if="!createdToken"
              :label="$t('Annuler')"
              display-type="ghost"
              type="button"
              @click="closeModals"
            />
            <oxd-button
              :label="createdToken ? $t('Fermer') : $t('Enregistrer')"
              display-type="secondary"
              :loading="isCreating"
              :type="createdToken ? 'button' : 'submit'"
              @click="createdToken ? closeModals(true) : null"
            />
          </div>
        </oxd-form>
      </div>
    </div>

    <div
      v-if="isEditModalOpen && selectedAccess"
      class="modal-overlay"
      @click="closeModals"
    >
      <div class="modal-container" @click.stop>
        <oxd-form @submit-valid="onConfirmEdit">
          <div class="modal-header">
            <h3>Modifier l'état de cette clé</h3>
          </div>
          <div class="modal-body">
            <div class="orangehrm-switch-wrapper">
              <oxd-text class="oxd-label">Actif</oxd-text>
              <OxdSwitchInput v-model="editedIsActive" />
            </div>
          </div>
          <div class="modal-footer">
            <oxd-button
              :label="$t('Annuler')"
              display-type="ghost"
              type="button"
              @click="closeModals"
            />
            <oxd-button
              :label="$t('Enregistrer')"
              display-type="secondary"
              :loading="isSavingEdit"
              type="submit"
            />
          </div>
        </oxd-form>
      </div>
    </div>

    <div
      v-if="isDeleteModalOpen && selectedAccess"
      class="modal-overlay"
      @click="closeModals"
    >
      <div class="modal-container" @click.stop>
        <oxd-form @submit-valid="onConfirmDelete">
          <div class="modal-header">
            <h3>Êtes-vous sûr ?</h3>
          </div>
          <div class="modal-body">
            <oxd-text>
              La clé sélectionnée sera définitivement supprimée. Êtes-vous sûr
              de vouloir continuer ?
            </oxd-text>
          </div>
          <div class="modal-footer">
            <oxd-button
              :label="$t('Annuler')"
              display-type="ghost"
              type="button"
              @click="closeModals"
            />
            <oxd-button
              :label="$t('Supprimer')"
              display-type="secondary"
              :loading="isDeleting"
              type="submit"
            />
          </div>
        </oxd-form>
      </div>
    </div>
  </div>
</template>

<script setup>
import {defineProps, onMounted, ref, computed} from 'vue';
import {APIService} from '@/core/util/services/api.service';
import {OxdSwitchInput} from '@ohrm/oxd';
import useToast from '@/core/util/composable/useToast';
import useSort from '@/core/util/composable/useSort';
import {required} from '@/core/util/validation/rules';

const props = defineProps({
  apiAccessLimit: {
    type: Number,
    default: 0,
  },
});
const {error, success} = useToast();
const defaultSortOrder = {
  title: 'DEFAULT',
  status: 'DEFAULT',
  createdAt: 'DEFAULT',
};
const {sortDefinition, sortField, sortOrder, onSort} = useSort({
  sortDefinition: defaultSortOrder,
});

const http = new APIService(
  window.appGlobal.baseUrl,
  '/api/v2/admin/api-access',
);
const apiAccesses = ref([]);
const isLoading = ref(false);
const checkedItems = ref([]);
const selectedAccess = ref(null);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const editedIsActive = ref(false);
const isSavingEdit = ref(false);
const isDeleting = ref(false);
const isCreating = ref(false);
const newApiTitle = ref('');
const newApiSource = ref('');
const createdToken = ref('');
const noContentPic = `${window.appGlobal.publicPath}/images/empty-box.png`;
// const apiDocumentationUrl = 'https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/assets/Documentation_API_Job_Navigator_Leads.pdf';
const apiDocumentationUrl = '/api/v2/admin/api-access/documentation';

const formatDate = (value) => {
  if (!value) return '';
  const date = new Date(Number(value));
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleDateString('fr-FR');
};

const headers = computed(() => [
  {
    name: 'maskedKey',
    title: 'Clé API',
    style: {flex: 1},
  },
  {
    name: 'title',
    title: 'Nom',
    sortField: 'title',
    style: {flex: 1},
  },
  {
    name: 'source',
    title: 'Source',
    style: {flex: 1},
  },
  {
    name: 'status',
    title: 'Statut',
    sortField: 'status',
    style: {flex: 0.8},
  },
  {
    name: 'createdAt',
    title: 'Date de création',
    sortField: 'createdAt',
    style: {flex: 1},
  },
  {
    name: 'actions',
    slot: 'action',
    title: 'Actions',
    style: {flex: 0.5},
    cellType: 'oxd-table-cell-actions',
    cellRenderer: cellRenderer,
  },
]);

const items = computed(() =>
  apiAccesses.value.map((item) => ({
    ...item,
    maskedKey: `********${item.lastCharacters ?? ''}`,
    status: item.isActive === true ? 'Actif' : 'Inactif',
    createdAt: formatDate(item.insertedAt),
  })),
);
const apiAccessLimitValue = computed(() => {
  return props.apiAccessLimit;
});
const showCreateButton = computed(() => Number(apiAccessLimitValue.value) > 0);
const canCreateApiAccess = computed(
  () =>
    Number(apiAccessLimitValue.value) > 0 &&
    apiAccesses.value.length < Number(apiAccessLimitValue.value),
);

const fetchApiAccesses = () => {
  isLoading.value = true;
  http
    .getAll()
    .then(({data}) => {
      apiAccesses.value = Array.isArray(data) ? data : [];
      sort();
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const closeModals = (force = false) => {
  if (!force && (isSavingEdit.value || isDeleting.value)) return;
  isCreateModalOpen.value = false;
  isEditModalOpen.value = false;
  isDeleteModalOpen.value = false;
  selectedAccess.value = null;
  newApiTitle.value = '';
  newApiSource.value = '';
  createdToken.value = '';
};

const onClickAddAccess = () => {
  if (!canCreateApiAccess.value) {
    error({
      title: 'Erreur',
      message: 'La limite du contrat est déjà atteinte.',
    });
    return;
  }
  isCreateModalOpen.value = true;
};

const resetSort = () => {
  sortDefinition.value = {
    title: 'DEFAULT',
    status: 'DEFAULT',
    createdAt: 'DEFAULT',
  };
  fetchApiAccesses();
};

const sort = () => {
  const field = sortField.value;
  const direction = sortOrder.value === 'DESC' ? -1 : 1;

  if (!field || field === 'DEFAULT') return;

  apiAccesses.value.sort((a, b) => {
    if (field === 'createdAt') {
      return (
        (Number(a.insertedAt || 0) - Number(b.insertedAt || 0)) * direction
      );
    }
    if (field === 'status') {
      return (
        ((a.isActive === true ? 1 : 0) - (b.isActive === true ? 1 : 0)) *
        direction
      );
    }
    if (field === 'title') {
      return (
        String(a.title || '').localeCompare(String(b.title || '')) * direction
      );
    }
    return 0;
  });
};

const onConfirmCreate = () => {
  if (!newApiTitle.value || newApiTitle.value.trim() === '') {
    error({
      title: 'Erreur de validation',
      message: 'Le champ du nom est obligatoire.',
    });
    return;
  }

  isCreating.value = true;
  http
    .create({
      title: newApiTitle.value.trim(),
      source: newApiSource.value?.trim() || null,
    })
    .then(({data}) => {
      createdToken.value =
        typeof data === 'string'
          ? data
          : data?.token || data?.apiKey || data?.key || '';

      if (!createdToken.value) {
        error({
          title: 'Erreur',
          message: 'Token non reçu après la création de la clé API.',
        });
        return;
      }

      success({
        title: 'Succès',
        message: 'Clé API générée avec succès.',
      });
      fetchApiAccesses();
    })
    .finally(() => {
      isCreating.value = false;
    });
};

const copyCreatedToken = async () => {
  if (!createdToken.value) return;
  try {
    await navigator.clipboard.writeText(createdToken.value);
    success({
      title: 'Copié',
      message: 'La clé a été copiée dans le presse-papiers.',
    });
  } catch (e) {
    error({
      title: 'Erreur',
      message: 'Impossible de copier le token.',
    });
  }
};

const openApiDocumentation = () => {
  // Ancienne version (lien direct CDN) :
  // window.open(apiDocumentationUrl, '_blank', 'noopener,noreferrer');
  http
    .request({
      method: 'GET',
      url: apiDocumentationUrl,
      responseType: 'blob',
    })
    .then(({data, headers}) => {
      const contentType = headers?.['content-type'] || 'application/pdf';
      const blob = new Blob([data], {type: contentType});
      const objectUrl = URL.createObjectURL(blob);
      window.open(objectUrl, '_blank', 'noopener,noreferrer');
      setTimeout(() => URL.revokeObjectURL(objectUrl), 60000);
    });
};

const onClickEdit = (access) => {
  selectedAccess.value = access;
  editedIsActive.value = access.isActive === true;
  isEditModalOpen.value = true;
};

const onClickDelete = (access) => {
  selectedAccess.value = access;
  isDeleteModalOpen.value = true;
};

const onConfirmEdit = () => {
  if (!selectedAccess.value) return;
  isSavingEdit.value = true;
  const targetId = selectedAccess.value.id;

  http
    .update(targetId, {isActive: editedIsActive.value})
    .then(() => {
      fetchApiAccesses();
      closeModals(true);
    })
    .finally(() => {
      isSavingEdit.value = false;
    });
};

const onConfirmDelete = () => {
  if (!selectedAccess.value) return;
  isDeleting.value = true;
  const targetId = selectedAccess.value.id;

  http
    .delete(targetId)
    .then(() => {
      fetchApiAccesses();
      closeModals(true);
    })
    .finally(() => {
      isDeleting.value = false;
    });
};

const cellRenderer = () => {
  return {
    props: {
      header: {
        cellConfig: {
          delete: {
            onClick: onClickDelete,
            props: {
              name: 'trash',
            },
          },
          edit: {
            onClick: onClickEdit,
            props: {
              name: 'pencil',
            },
          },
        },
      },
    },
  };
};

onMounted(() => {
  fetchApiAccesses();
});

const rules = {
  title: [required],
};

onSort(sort);
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
}

.modal-body {
  padding: 2rem;
  background-color: #ffffff;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--oxd-border-light-color);
  background-color: #f8f9fa;
  border-radius: 0 0 0.75rem 0.75rem;
  flex-shrink: 0;
}

:deep(.oxd-button) {
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;

  &.oxd-button--secondary {
    background: linear-gradient(135deg, #a7df73 0%, #76bc21 100%);
    border: none;
  }
}

.orangehrm-header-container {
  padding-bottom: 0 !important;
}

.orangehrm-switch-wrapper {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
}

:deep(.oxd-switch-input) {
  display: inline-flex;
  visibility: visible;
  opacity: 1;
}

.token-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1rem;
}

.token-copy-button {
  flex-shrink: 0;
}

.api-access-header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.api-access-guide {
  margin: 0.75rem 0 1rem;
  padding: 0.9rem 1rem;
  background: #f4f7ff;
}

.api-access-guide-description {
  margin-bottom: 0.75rem;
  color: #334155;
}

.api-access-doc-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-top: 0.85rem;
  padding: 0.7rem 0.8rem;
  background: #fff;
  border: 1px solid #dce5ff;
  border-radius: 0.5rem;
}

.api-access-doc-card-text {
  min-width: 0;
}

.api-access-doc-card-title {
  margin: 0 0 0.2rem 0;
  font-weight: 700;
  color: #1e293b;
}

.api-access-doc-card-subtitle {
  margin: 0;
  color: #475569;
  font-size: 0.82rem;
}

.api-access-guide-steps {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.6rem;
}

.api-access-guide-step {
  display: flex;
  align-items: flex-start;
  gap: 0.55rem;
  color: #1f2937;
  line-height: 1.35;
}

.api-access-step-index {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.72rem;
  font-weight: 700;
  color: #fff;
  background: #3f63c9;
}

.no-access-lock-icon {
  font-size: 2.25rem;
  color: #64728c;
  margin: 0 0 0.85rem 0;
}

.source-help {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.source-label-row {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.35rem;
}

.source-help-icon {
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  background: #e9ecef;
  color: #495057;
  flex-shrink: 0;
  border: none;
  cursor: pointer;
  padding: 0;
}

.source-help-tooltip {
  position: absolute;
  left: 1.5rem;
  top: -0.25rem;
  min-width: 260px;
  max-width: 320px;
  background: #1f2d3d;
  color: #fff;
  border-radius: 0.35rem;
  padding: 0.45rem 0.6rem;
  font-size: 0.75rem;
  line-height: 1.3;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transition: opacity 0.15s ease;
  z-index: 3;
}

.source-help:hover .source-help-tooltip,
.source-help:focus-within .source-help-tooltip {
  opacity: 1;
  visibility: visible;
}

@media (max-width: 768px) {
  .orangehrm-header-container {
    align-items: flex-start;
    flex-direction: column;
    gap: 0.75rem;
  }

  .api-access-header-actions {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .api-access-guide {
    padding: 0.8rem;
  }

  .api-access-doc-card {
    flex-direction: column;
    align-items: flex-start;
  }

  .source-label-row {
    position: relative;
  }

  .source-help {
    position: static;
  }

  .source-help-tooltip {
    top: auto;
    bottom: calc(100% + 0.35rem);
    left: 50%;
    transform: translateX(-50%);
    min-width: 220px;
    max-width: min(90vw, 320px);
  }
}
</style>
