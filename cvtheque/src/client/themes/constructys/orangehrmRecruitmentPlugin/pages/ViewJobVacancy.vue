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
  <div class="orangehrm-background-container">
    <table-filter-title v-if="hasNoMatchings" :title="$t('general.vacancies')">
    </table-filter-title>
    <table-filter v-else :filter-title="$t('general.vacancies')">
      <oxd-form @submit-valid="filterItems">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.matchingSelected"
                type="select"
                :label="$t('recruitment.need_title')"
                :options="matchings"
              />
            </oxd-grid-item>
          </oxd-grid>
          <oxd-grid
            v-if="canUpdate"
            :cols="2"
            class="orangehrm-full-width-grid"
          >
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.statusJobSelected"
                type="select"
                :label="$t('general.status')"
                :options="candidatureStatuses"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>
        <oxd-form-actions>
          <oxd-button
            v-if="canUpdate"
            :label="$t('performance.delete')"
            display-type="danger"
            @click="onClickDelete"
          />
          <oxd-button
            v-if="canUpdate"
            class="orangehrm-left-space"
            display-type="ghost"
            :label="$t('general.update')"
            @click="onClickEdit"
          />
        </oxd-form-actions>
        <oxd-divider />
        <oxd-form-actions>
          <oxd-button
            class="orangehrm-left-space"
            display-type="secondary"
            :label="$t('general.search')"
            type="submit"
            :disabled="!canUpdate"
          />
        </oxd-form-actions>
      </oxd-form>
    </table-filter>
    <br />
    <div class="orangehrm-paper-container">
      <div v-if="hasName" class="orangehrm-header-container">
        <oxd-button
          :label="$t('recruitment.add_matching')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
      </div>
      <div v-else class="orangehrm-header-container">
        <oxd-text class="orangehrm-sub-title" style="color: red" tag="h6">
          {{ $t('recruitment.company_has_no_name') }}
        </oxd-text>
      </div>
    </div>
    <br />
    <oxd-table-filter
      v-if="isSearching"
      :filter-title="$t('Découvrez les candidats qui correspondent')"
    >
      <div v-if="items && items.length > 0" class="boutonTriBloc">
        <button class="boutonTri" @click="sortByDate">
          Trier par date de candidature ⇅
        </button>
      </div>
      <div class="orangehrm-container">
        <oxd-card-table
          v-model:selected="checkedItems"
          :headers="headers"
          :items="items"
          :selectable="false"
          :clickable="false"
          :loading="isLoading1"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
      <div
        v-if="!items || items.length === 0"
        class="orangehrm-text-center-align"
      >
        <br />
        <oxd-text type="card-body" style="color: #64728c; font-style: italic">
          Aucun résultat obtenu pour cette recherche
        </oxd-text>
      </div>
      <div v-if="totalPages1 > 1" class="orangehrm-pagination-wrapper">
        <oxd-pagination
          v-model:current="currentPage1"
          :length="totalPages1"
          @update:current="onPageChange1"
        />
      </div>
    </oxd-table-filter>
    <br />
    <oxd-table-filter
      v-if="isSearchingNoStatut"
      :filter-title="$t('Découvrez les autres candidats sur ce métier')"
    >
      <div v-if="otherLeads && otherLeads.length > 0" class="boutonTriBloc">
        <button class="boutonTri" @click="sortByDate2">
          Trier par date de candidature ⇅
        </button>
      </div>
      <div v-if="isSearching" class="orangehrm-container">
        <oxd-card-table
          v-model:selected="checkedItems"
          :headers="headers2"
          :items="otherLeads"
          :selectable="false"
          :clickable="false"
          :loading="isLoading2"
          row-decorator="oxd-table-decorator-card"
        />
      </div>
      <div
        v-if="!otherLeads || otherLeads.length === 0"
        class="orangehrm-text-center-align"
      >
        <br />
        <oxd-text type="card-body" style="color: #64728c; font-style: italic">
          Aucun résultat obtenu pour cette recherche
        </oxd-text>
      </div>
      <div v-if="totalPages2 > 1" class="orangehrm-pagination-wrapper">
        <oxd-pagination
          v-model:current="currentPage2"
          :length="totalPages2"
          @update:current="onPageChange2"
        />
      </div>
    </oxd-table-filter>
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
  </div>
</template>

<script>
import {computed, ref} from 'vue';
import {navigate} from '@/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import useDateFormat from '@/core/util/composable/useDateFormat';
import useLocale from '@/core/util/composable/useLocale';
import {formatDate, parseDate} from '@/core/util/helper/datefns';
import DeleteConfirmationDialog from '@/core/components/dialogs/DeleteJobVacancyConfirmationDialog';
import TableFilterTitle from '@/core/components/labels/TableFilterTitle';
import TableFilter from '@/core/components/dropdown/TableFilter.vue';
import {
  markCandidateAsViewed,
  getCandidateLastViewed,
} from '@/core/util/helper/viewed';

const defaultFilters = {
  matchingSelected: null,
  jobTitleId: null,
  hiringManagerId: null,
  jobTitle: null,
  status: null,
  statusJobSelected: null,
  page: 0,
  size: 20,
};
export default {
  name: 'ViewJobVacancy',
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    'table-filter-title': TableFilterTitle,
    'table-filter': TableFilter,
  },

  props: {
    matchings: {
      type: Array,
      default: () => [],
    },
    matchingSelected: {
      type: Object,
      default: null,
    },
    candidatureStatuses: {
      type: Array,
      default: () => [],
    },
    hasName: {
      type: Boolean,
      default: true,
    },
  },

  setup(props) {
    const {locale} = useLocale();
    const {jsDateFormat, jsDateTimeFormat} = useDateFormat();
    const items = ref([]);
    const otherLeads = ref([]);
    const totalPages1 = ref(0);
    const totalPages2 = ref(0);
    const isLoading1 = ref(false);
    const isLoading2 = ref(false);
    const currentPage1 = ref(1);
    const currentPage2 = ref(1);
    const isSearching = ref(false);
    const isSearchingNoStatut = ref(false);

    // Filtres initiaux pour la comparaison
    const initialFilters = {
      matchingSelected: null,
      jobTitleId: null,
      hiringManagerId: null,
      jobTitle: null,
      status: null,
      statusJobSelected: null,
      page: 0,
      size: 20,
    };

    // Filtres réactifs
    const filters = ref({
      ...initialFilters,
    });

    // Add sort order states for both collections
    const isDateAscending = ref(false); // Default to descending (false)
    const isDateAscending2 = ref(false); // Default to descending (false)

    const serializedFilters = computed(() => {
      return {
        page: currentPage1.value - 1,
        size: filters.value.size,
        matchingId: filters.value.matchingSelected?.id,
        jobTitleId: filters.value.jobTitleId?.id,
        status: filters.value.status?.id,
        model: 'detailed',
        statusJob: filters.value.statusJobSelected?.label,
        sortDirection: isDateAscending.value ? 'ASC' : 'DESC',
      };
    });

    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/recruitment/candidates`,
    );

    const execQuery = async () => {
      isLoading1.value = true;
      http
        .getAll({
          ...serializedFilters.value,
        })
        .then(({data: {data, meta}}) => {
          items.value = data
            .sort((a, b) => {
              const dateA = parseDate(a.date);
              const dateB = parseDate(b.date);
              return isDateAscending.value ? dateA - dateB : dateB - dateA;
            })
            .map((item) => {
              return {
                id: item.id,
                jobTitle: item.job,
                candidate: `${item.firstName} ${item.lastName}`,
                dateOfApplication: formatDate(
                  parseDate(item.date),
                  jsDateFormat,
                  {locale},
                ),
                email: item.email,
                status: item.candidatureStatus,
                matchingId: item.matchingId,
                lastViewed: formatDate(
                  getCandidateLastViewed(item.email),
                  jsDateTimeFormat,
                  {locale},
                ),
              };
            });
          totalPages1.value = calculateTotalPages(meta);
        })
        .catch((error) => {
          console.error('Erreur lors de la récupération des leads :', error);
          items.value = [];
          totalPages1.value = 0;
        })
        .finally(() => {
          isLoading1.value = false;
        });
    };

    const getOtherLeads = async () => {
      isLoading2.value = true;
      new APIService(
        window.appGlobal.baseUrl,
        `${window.appGlobal.theme}/api/v2/recruitment/candidates`,
      )
        .getAll({
          page: currentPage2.value - 1,
          size: filters.value.size,
          matchingId: filters.value.matchingSelected?.id,
          vacancyId: filters.value.vacancyId?.id,
          jobTitleId: filters.value.jobTitleId?.id,
          hiringManagerId: filters.value.hiringManagerId?.id,
          status: filters.value.status?.id,
          model: 'detailed',
          statusJob: filters.value.statusJobSelected?.label,
          otherLeads: 'entreprise',
          sortDirection: isDateAscending2.value ? 'ASC' : 'DESC',
        })
        .then(({data: {data, meta}}) => {
          otherLeads.value = data
            .sort((a, b) => {
              const dateA = parseDate(a.date);
              const dateB = parseDate(b.date);
              return isDateAscending2.value ? dateA - dateB : dateB - dateA;
            })
            .map((item) => {
              return {
                id: item.id,
                jobTitle: item.jobs.join(', '),
                candidate: `${item.firstName} ${item.lastName}`,
                dateOfApplication: formatDate(
                  parseDate(item.date),
                  jsDateFormat,
                  {locale},
                ),
                email: item.email,
                status: item.candidatureStatus,
                matchingId: item.matchingId,
                lastViewed: formatDate(
                  getCandidateLastViewed(item.email),
                  jsDateTimeFormat,
                  {locale},
                ),
              };
            });
          totalPages2.value = calculateTotalPages(meta);
        })
        .catch((error) => {
          console.error('Erreur lors de la récupération des leads :', error);
          otherLeads.value = [];
          totalPages2.value = 0;
        })
        .finally(() => {
          isLoading2.value = false;
        });
    };

    const calculateTotalPages = (meta) => {
      if (typeof meta?.totalPages === 'number') return meta.totalPages;
      if (typeof meta?.total === 'number' && meta?.pageSize > 0)
        return Math.ceil(meta.total / meta.pageSize);
      return 0;
    };

    // Computed pour vérifier si les filtres ont changé
    const canUpdate = computed(() => {
      return (
        filters.value.matchingSelected !== initialFilters.matchingSelected ||
        filters.value.jobTitleId !== initialFilters.jobTitleId ||
        filters.value.hiringManagerId !== initialFilters.hiringManagerId ||
        filters.value.jobTitle !== initialFilters.jobTitle ||
        filters.value.status !== initialFilters.status ||
        filters.value.statusJobSelected !== initialFilters.statusJobSelected
      );
    });

    // Gestionnaire de changement de page
    const onPageChange1 = (page) => {
      // Mise à jour de la page courante
      currentPage1.value = page;
      // Appel direct de l'API avec les nouveaux paramètres
      execQuery();
    };

    // Gestionnaire de changement de page
    const onPageChange2 = (page) => {
      // Mise à jour de la page courante
      currentPage2.value = page;
      // Appel direct de l'API avec les nouveaux paramètres
      getOtherLeads();
    };

    const filterItems = async () => {
      // Réinitialiser la pagination lors de l'application des filtres
      currentPage1.value = 1;
      currentPage2.value = 1;
      isSearching.value = filters.value.matchingSelected;
      isSearchingNoStatut.value = filters.value.matchingSelected;
      if (filters.value.statusJobSelected != null) {
        isSearchingNoStatut.value = false;
      }
      await Promise.all([getOtherLeads(), execQuery()]);
    };

    if (localStorage.getItem('matchingFilters')) {
      const filterData = JSON.parse(localStorage.getItem('matchingFilters'));
      Object.keys(filterData).forEach((filterKey) => {
        const filter = filterData[filterKey];
        switch (filterKey) {
          case 'matchingId':
            filters.value.matchingSelected = props.matchings.find(
              (matching) => matching.id === filter,
            );
            break;
          case 'status':
            filters.value.statusJobSelected = props.candidatureStatuses.find(
              (status) => status.label === filter,
            );
            break;
        }
      });
      filterItems();
      localStorage.removeItem('matchingFilters');
    }

    return {
      http,
      jsDateFormat,
      locale,
      currentPage1,
      currentPage2,
      isLoading1,
      isLoading2,
      totalPages1,
      totalPages2,
      execQuery,
      items,
      otherLeads,
      filters,
      onPageChange1,
      onPageChange2,
      canUpdate,
      isDateAscending,
      isDateAscending2,
      getOtherLeads,
      filterItems,
      isSearching,
      isSearchingNoStatut,
    };
  },
  data() {
    return {
      statusJobSelected: null,
      isNomAscending: false,
      leads: [],
      headers: [
        {
          name: 'jobTitle',
          title: this.$t('general.job_title'),
          style: {flex: 0.5},
        },
        {
          name: 'candidate',
          slot: 'title',
          title: this.$t('recruitment.candidate'),
          style: {flex: 1},
        },
        {
          name: 'dateOfApplication',
          title: this.$t('recruitment.date_of_application'),
          style: {flex: 0.5},
        },
        {
          name: 'email',
          title: this.$t('general.other_email'),
          style: {flex: 1},
        },
        {
          name: 'status',
          title: this.$t('general.status'),
          style: {flex: 1},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.25},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            view: {
              onClick: (item) => this.onClickCandidate(item, true),
              props: {
                name: 'eye-fill',
              },
            },
          },
        },
        {
          name: 'lastViewed',
          title: 'Dernière consultation',
          style: {flex: 0.5},
        },
      ],
      headers2: [
        {
          name: 'jobTitle',
          title: this.$t('general.job_title'),
          style: {flex: 0.5},
        },
        {
          name: 'candidate',
          slot: 'title',
          title: this.$t('recruitment.candidate'),
          style: {flex: 1},
        },
        {
          name: 'dateOfApplication',
          title: this.$t('recruitment.date_of_application'),
          style: {flex: 0.5},
        },
        {
          name: 'email',
          title: this.$t('general.other_email'),
          style: {flex: 1},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.25},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            view: {
              onClick: (item) => this.onClickCandidate(item, false),
              props: {
                name: 'eye-fill',
              },
            },
          },
        },
        {
          name: 'lastViewed',
          title: 'Dernière consultation',
          style: {flex: 0.5},
        },
      ],
      statusOptions: [
        {id: true, param: 'active', label: this.$t('general.active')},
        {id: false, param: 'closed', label: this.$t('general.closed')},
      ],
      vacancies: [],
      checkedItems: [],
    };
  },
  computed: {
    hasNoMatchings() {
      return this.matchings.length === 0;
    },
  },
  watch: {
    'filters.matchingSelected': {
      handler(newVal) {
        this.canUpdate = newVal;
      },
      immediate: true,
      deep: true,
    },
    'filters.statusJobSelected': {
      handler(newVal) {
        this.canUpdate = newVal;
      },
      immediate: true,
      deep: true,
    },
  },
  beforeMount() {
    if (this.matchingSelected) {
      if (this.filters === undefined) this.filters = {...defaultFilters};
      this.filters.matchingSelected = this.matchingSelected;
      this.filterItems();
    }
  },

  methods: {
    sortByDate() {
      // Change l'ordre de tri
      this.isDateAscending = !this.isDateAscending;
      this.execQuery();
    },

    async sortByDate2() {
      this.isDateAscending2 = !this.isDateAscending2;
      await this.getOtherLeads();
    },
    onClickCandidate(item, isMatching) {
      if (item.email) markCandidateAsViewed(item.email);

      if (this.filters.matchingSelected?.id) {
        const filterData = {
          matchingId: this.filters.matchingSelected.id,
          status: this.filters.statusJobSelected?.label,
        };
        localStorage.setItem('matchingFilters', JSON.stringify(filterData));
      } else localStorage.removeItem('matchingFilters');

      !isMatching || !this.filters.matchingSelected?.id
        ? navigate(
            `/${window.appGlobal.theme}/recruitment/viewCandidate/{id}`,
            {id: item.id},
          )
        : navigate(
            `/${window.appGlobal.theme}/recruitment/viewCandidate/{leadId}/matching/{matchingId}`,
            {
              leadId: item.id,
              matchingId: this.filters.matchingSelected.id,
            },
          );
    },
    onClickAdd() {
      navigate(`/${window.appGlobal.theme}/recruitment/addJobVacancy`);
    },
    onClickEdit(item) {
      navigate(`/${window.appGlobal.theme}/recruitment/addJobVacancy/{id}`, {
        id: this.filters.matchingSelected?.id,
      });
    },
    onDownload(item) {
      if (!item?.id) return;
      const fileUrl = 'recruitment/viewCandidateAttachment/candidateId';
      const downUrl = `${window.appGlobal.baseUrl}/${fileUrl}/${item.id}`;
      window.open(downUrl, '_blank');
    },

    onClickDelete() {
      if (this.filters.matchingSelected) {
        this.$refs.deleteDialog.showDialog().then((confirmation) => {
          if (confirmation.action === 'ok') {
            this.deleteData(
              [this.filters.matchingSelected.id],
              confirmation.reason,
            );
          }
        });
      }
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items[index].id;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation.action === 'ok') {
          this.deleteData(ids, confirmation.reason);
        }
      });
    },

    async deleteData(items, reason) {
      if (items instanceof Array) {
        this.isLoading = true;
        const deletePayload = {
          ids: items,
        };

        let url = `${window.appGlobal.theme}/api/v2/recruitment/vacancies`;
        if (reason) {
          url += `?reason=${encodeURIComponent(reason)}`;
        }

        const apiService = new APIService(window.appGlobal.baseUrl, url);

        apiService
          .deleteAll(deletePayload)
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            navigate(`/${window.appGlobal.theme}/recruitment/viewJobVacancy`);
            this.isLoading = false;
          });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    onClickReset() {
      this.isSearching = false;
      this.filters = {...defaultFilters};
      this.filterItems();
    },
  },
};
</script>

<style src="./vacancy.scss" lang="scss" scoped></style>

<style lang="scss" scoped>
.orangehrm-pagination-wrapper {
  margin-top: 1rem;
}
</style>
