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
    <oxd-table-filter :filter-title="$t('general.vacancies')">
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

            <!--<oxd-grid-item>
              <vacancy-dropdown
                v-model="filters.vacancyId"
                :label="$t('recruitment.vacancy')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <hiring-manager-dropdown v-model="filters.hiringManagerId" />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.status"
                type="select"
                :label="$t('general.status')"
                :clear="false"
                :options="statusOptions"
              />
            </oxd-grid-item>-->
          </oxd-grid>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.statusJobSelected"
                type="select"
                :label="$t('general.status')"
                :options="candidatureStatuses"
              />
            </oxd-grid-item>

            <!--<oxd-grid-item>
              <vacancy-dropdown
                v-model="filters.vacancyId"
                :label="$t('recruitment.vacancy')"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <hiring-manager-dropdown v-model="filters.hiringManagerId" />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.status"
                type="select"
                :label="$t('general.status')"
                :clear="false"
                :options="statusOptions"
              />
            </oxd-grid-item>-->
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
    </oxd-table-filter>
    <br />
    <div class="orangehrm-paper-container">
      <div class="orangehrm-header-container">
        <oxd-button
          :label="$t('recruitment.add_matching')"
          icon-name="plus"
          display-type="secondary"
          @click="onClickAdd"
        />
        <div class="boutonTriBloc">
          <button class="boutonTri" @click="sortByName">
            Trier par nom métier ⇅
          </button>
          <button class="boutonTri" @click="sortByDate">
            Trier par date ⇅
          </button>
        </div>
      </div>
      <!--<table-header
        :selected="checkedItems.length"
        :loading="isLoading"
        :total="total"
        @delete="onClickDeleteSelected"
      ></table-header>-->
      <div class="orangehrm-container" v-if="isSearching">
        <oxd-card-table
          v-model:selected="checkedItems"
          :headers="headers"
          :items="items?.data"
          :selectable="false"
          :clickable="false"
          :loading="isLoading"
          row-decorator="oxd-table-decorator-card"
        />

        <!--class="orangehrm-vacancy-list"-->
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
  </div>
</template>

<script>
import {computed, onMounted, ref} from 'vue';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import {navigate} from '@ohrm/core/util/helper/navigation';
import {APIService} from '@/core/util/services/api.service';
import useDateFormat from '@/core/util/composable/useDateFormat';
import useSort from '@ohrm/core/util/composable/useSort';
import usei18n from '@/core/util/composable/usei18n';
import useLocale from '@/core/util/composable/useLocale';
import {formatDate, parseDate} from '@ohrm/core/util/helper/datefns';
import useEmployeeNameTranslate from '@/core/util/composable/useEmployeeNameTranslate';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';
/*import JobtitleDropdown from '@/orangehrmPimPlugin/components/JobtitleDropdown';
import VacancyDropdown from '@/orangehrmRecruitmentPlugin/components/VacancyDropdown.vue';
import HiringManagerDropdown from '@/orangehrmRecruitmentPlugin/components/HiringManagerDropdown';*/

const defaultFilters = {
  matchingSelected: null,
  jobTitleId: null,
  hiringManagerId: null,
  jobTitle: null,
  status: null,
  statusJobSelected: null,
};
const defaultSortOrder = {
  'candidate.jobTitle': 'DEFAULT',
  'candidate.dateOfApplication': 'DESC',
  'candidateVacancy.status': 'DEFAULT',
};
export default {
  name: 'ViewJobVacancy',
  components: {
    'delete-confirmation': DeleteConfirmationDialog,
    /*'jobtitle-dropdown': JobtitleDropdown,
    'vacancy-dropdown': VacancyDropdown,
    'hiring-manager-dropdown': HiringManagerDropdown,*/
  },

  props: {
    matchings: {
      type: Array,
      default: () => [],
    },
    candidatureStatuses: {
      type: Array,
      default: () => [],
    },
  },
  /*mounted() {
    if (this.filters?.value) {
      this.$watch(
        () => this.filters.value.matchingSelected,
        (newVal) => {
          this.canUpdate = !!newVal;
        },
      );
    }
  },*/
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
    // Surveille items.data pour exécuter le tri une fois les données chargées
    'items.data': function (newData) {
      if (newData && newData.length > 0) {
        // Vérifie que les données sont chargées
        this.sortByDate();
      }
    },
  },

  setup(props) {
    const {$t} = usei18n();
    const {locale} = useLocale();
    const {jsDateFormat, userDateFormat} = useDateFormat();
    const {$tEmpName} = useEmployeeNameTranslate();
    const statuses = [
      {id: 1, label: $t('recruitment.application_initiated')},
      {id: 2, label: $t('recruitment.shortlisted')},
      {id: 3, label: $t('leave.rejected')},
      {id: 4, label: $t('recruitment.interview_scheduled')},
      {id: 5, label: $t('recruitment.interview_passed')},
      {id: 6, label: $t('recruitment.interview_failed')},
      {id: 7, label: $t('recruitment.job_offered')},
      {id: 8, label: $t('recruitment.offer_declined')},
      {id: 9, label: $t('recruitment.hired')},
    ];
    const filters = ref({...defaultFilters});
    const {sortDefinition, sortField, sortOrder, onSort} = useSort({
      sortDefinition: defaultSortOrder,
    });

    const serializedFilters = computed(() => {
      return {
        matchingId: filters.value.matchingSelected?.id,
        vacancyId: filters.value.vacancyId?.id,
        jobTitleId: filters.value.jobTitleId?.id,
        hiringManagerId: filters.value.hiringManagerId?.id,
        status: filters.value.status?.id,
        sortField: sortField.value,
        sortOrder: sortOrder.value,
        model: 'detailed',
        statusJob: filters.value.statusJobSelected?.label,
      };
    });

    const candidateDataNormalizer = (data) => {
      return data.map((item) => {
        return {
          id: item.leadId,
          jobTitle: item.jobTitle,
          candidate: `${item.firstName} ${item.middleName || ''} ${
            item.lastName
          }`,
          dateOfApplication: formatDate(
            parseDate(item.dateOfApplication),
            jsDateFormat,
            {locale},
          ),
          email: item.email,
          /*status:
            statuses.find((status) => status.id === item.status?.id)?.label ||
            '',*/
          status: item.candidatureStatus,
          resume: item.hasAttachment,
          isSelectable: item.deletable,
          matchingId: item.matchingId,
        };
      });
    };
    /*const userdataNormalizer = (data) => {
      return data.map((item) => {
        return {
          id: item.id,
          vacancy: item.name,
          jobTitle: item.jobTitle?.isDeleted
            ? item.jobTitle.title + $t('general.deleted')
            : item.jobTitle?.title,

          hiringManager: item.hiringManager?.id
            ? $tEmpName(item.hiringManager)
            : $t('general.deleted'),
          status: item.status ? $t('general.active') : $t('general.closed'),
        };
      });
    };*/

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/recruitment/candidates',
    );
    const {
      showPaginator,
      currentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
    } = usePaginate(http, {
      query: serializedFilters,
      normalizer: candidateDataNormalizer,
    });
    onSort(execQuery);

    return {
      http,
      showPaginator,
      currentPage,
      isLoading,
      total,
      pages,
      pageSize,
      execQuery,
      items: response,
      filters,
      sortDefinition,
    };
  },

  data() {
    return {
      isSearching: false,
      canUpdate: false,
      matchingSelected: null,
      statusJobSelected: null,
      isDateAscending: true,
      isNomAscending: false,
      /*headers: [
        {
          name: 'vacancy',
          slot: 'title',
          title: this.$t('recruitment.vacancy'),
          sortField: 'vacancy.name',
          style: {flex: 3},
        },
        {
          name: 'jobTitle',
          title: this.$t('general.job_title'),
          sortField: 'jobTitle.jobTitleName',
          style: {flex: 3},
        },
        {
          name: 'hiringManager',
          title: this.$t('recruitment.hiring_manager'),
          sortField: 'hiringManager.lastName',
          style: {flex: 3},
        },
        {
          name: 'status',
          title: this.$t('general.status'),
          sortField: 'vacancy.status',
          style: {flex: 2},
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
            edit: {
              onClick: this.onClickEdit,
              props: {
                name: 'pencil-fill',
              },
            },
          },
        },
      ],*/
      headers: [
        {
          name: 'jobTitle',
          title: this.$t('general.job_title'),
          style: {flex: 1},
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
          style: {flex: 1},
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
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellRenderer: this.cellRenderer,
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

  methods: {
    sortByDate() {
      // Change l'ordre de tri
      this.isDateAscending = !this.isDateAscending;
      // Trie les éléments en fonction de l'ordre défini
      this.items.data.sort((a, b) => {
        const dateA = new Date(a.dateOfApplication);
        const dateB = new Date(b.dateOfApplication);

        return this.isDateAscending ? dateA - dateB : dateB - dateA;
      });
    },
    sortByName() {
      this.isNomAscending = !this.isNomAscending;
      this.items.data.sort((a, b) => {
        return this.isNomAscending
          ? a.jobTitle.localeCompare(b.jobTitle)
          : b.jobTitle.localeCompare(a.jobTitle);
      });
    },
    cellRenderer(...[, , , row]) {
      const cellConfig = {
        view: {
          onClick: this.onClickCandidate,
          props: {
            name: 'eye-fill',
          },
        },
      };
      /*if (row.isSelectable) {
        cellConfig.delete = {
          onClick: this.onClickDelete,
          component: 'oxd-icon-button',
          props: {
            name: 'trash',
          },
        };
      }*/
      if (row.resume) {
        cellConfig.download = {
          onClick: this.onDownload,
          props: {
            name: 'download',
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
    onClickCandidate(item) {
      !item.matchingId
        ? navigate('/recruitment/viewCandidate/{id}', {id: item.id})
        : navigate(
            '/recruitment/viewCandidate/{leadId}/matching/{matchingId}',
            {
              leadId: item.id,
              matchingId: item.matchingId,
            },
          );
    },
    onClickAdd() {
      navigate('/recruitment/addJobVacancy');
    },
    onClickEdit(item) {
      navigate('/recruitment/addJobVacancy/{id}', {
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
          if (confirmation === 'ok') {
            this.deleteData([this.filters.matchingSelected.id]);
          }
        });
      }
    },
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items?.data[index].id;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteData(ids);
        }
      });
    },

    async deleteData(items) {
      if (items instanceof Array) {
        this.isLoading = true;
        new APIService(
          window.appGlobal.baseUrl,
          '/api/v2/recruitment/vacancies',
        )
          .deleteAll({
            ids: items,
          })
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            navigate('/recruitment/viewJobVacancy');
            this.isLoading = false;
          });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    async filterItems() {
      this.isSearching = this.filters.matchingSelected;
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
