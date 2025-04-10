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
  <div>
    <save-employee-report-to
      v-if="showSaveModal"
      :http="http"
      :type="'Subordinate'"
      :emp-number="empNumber"
      :reporting-methods="reportingMethods"
      @close="onSaveModalClose"
    ></save-employee-report-to>
    <edit-employee-report-to
      v-if="showEditModal"
      :http="http"
      :emp-number="empNumber"
      :data="editModalState"
      :type="'Subordinate'"
      :api="subordinateEndpoint"
      :reporting-methods="reportingMethods"
      @close="onEditModalClose"
    ></edit-employee-report-to>
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <profile-action-header
        :action-button-shown="$can.create(`subordinates`)"
        @click="onClickAdd"
      >
        {{ $t('pim.assigned_subordinates') }}
      </profile-action-header>
    </div>
    <table-header
      :selected="checkedItems.length"
      :total="total"
      :loading="isLoading"
      @delete="onClickDeleteSelected"
    ></table-header>
    <div class="orangehrm-container">
      <oxd-card-table
        v-model:selected="checkedItems"
        :headers="tableHeaders"
        :items="items?.data"
        :selectable="$can.delete(`subordinates`)"
        :disabled="isDisabled"
        :clickable="false"
        :loading="isLoading"
        row-decorator="oxd-table-decorator-card"
      />
    </div>
    <div v-if="showPaginator" class="orangehrm-bottom-container">
      <oxd-pagination v-model:current="currentPage" :length="pages" />
    </div>
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
  </div>
</template>

<script>
import ProfileActionHeader from './ProfileActionHeader';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';
import {APIService} from '@/core/util/services/api.service';
import usePaginate from '@/core/util/composable/usePaginate';
import SaveEmployeeReportTo from './SaveEmployeeReportTo';
import EditEmployeeReportTo from './EditEmployeeReportTo';
import usei18n from '@/core/util/composable/usei18n';

export default {
  name: 'EmployeeSubordinates',

  components: {
    'edit-employee-report-to': EditEmployeeReportTo,
    'save-employee-report-to': SaveEmployeeReportTo,
    'profile-action-header': ProfileActionHeader,
    'delete-confirmation': DeleteConfirmationDialog,
  },

  props: {
    empNumber: {
      type: String,
      required: true,
    },
    reportingMethods: {
      type: Array,
      required: true,
    },
  },

  setup(props) {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/pim/employees/${props.empNumber}/subordinates`,
    );
    const {$t} = usei18n();

    const defaultReportingMethods = ['Direct', 'Indirect'];

    const subordinateNormalizer = (data) => {
      return data.map((item) => {
        let reportingMethodName = item.reportingMethod.name;
        return {
          name: `${item.subordinate?.firstName} ${item.subordinate?.lastName} ${
            item.subordinate.terminationId ? $t('general.past_employee') : ''
          }`,
          reportingMethod: defaultReportingMethods.includes(reportingMethodName)
            ? $t('pim.' + reportingMethodName.toLowerCase())
            : reportingMethodName,
          subordinateEmpNumber: item.subordinate.empNumber,
        };
      });
    };
    const subordinateEndpoint = `/api/v2/pim/employees/${props.empNumber}/subordinates/`;
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
      normalizer: subordinateNormalizer,
      toastNoRecords: false,
    });
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
      subordinateEndpoint,
    };
  },

  data() {
    return {
      headers: [
        {
          name: 'name',
          slot: 'title',
          title: this.$t('general.name'),
          style: {flex: 1},
        },
        {
          name: 'reportingMethod',
          title: this.$t('pim.reporting_method'),
          style: {flex: 1},
        },
      ],
      checkedItems: [],
      showSaveModal: false,
      showEditModal: false,
      editModalState: null,
    };
  },

  computed: {
    isDisabled() {
      return this.showSaveModal || this.showEditModal;
    },
    tableHeaders() {
      const headerActions = {
        name: 'actions',
        slot: 'action',
        title: this.$t('general.actions'),
        style: {flex: 1},
        cellType: 'oxd-table-cell-actions',
        cellConfig: {},
      };
      if (this.$can.delete(`subordinates`)) {
        headerActions.cellConfig.delete = {
          onClick: this.onClickDelete,
          component: 'oxd-icon-button',
          props: {
            name: 'trash',
          },
        };
      }
      if (this.$can.update(`subordinates`)) {
        headerActions.cellConfig.edit = {
          onClick: this.onClickEdit,
          props: {
            name: 'pencil-fill',
          },
        };
      }
      return Object.keys(headerActions.cellConfig).length > 0
        ? this.headers.concat([headerActions])
        : this.headers;
    },
  },

  methods: {
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items?.data[index].subordinateEmpNumber;
      });
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems(ids);
        }
      });
    },
    onClickDelete(item) {
      this.$refs.deleteDialog.showDialog().then((confirmation) => {
        if (confirmation === 'ok') {
          this.deleteItems([item.subordinateEmpNumber]);
        }
      });
    },
    deleteItems(items) {
      if (items instanceof Array) {
        this.isLoading = true;
        this.http
          .deleteAll({
            ids: items,
          })
          .then(() => {
            return this.$toast.deleteSuccess();
          })
          .then(() => {
            this.isLoading = false;
            this.resetDataTable();
          });
      }
    },
    async resetDataTable() {
      this.checkedItems = [];
      await this.execQuery();
    },
    onClickAdd() {
      this.showEditModal = false;
      this.editModalState = null;
      this.showSaveModal = true;
    },
    onClickEdit(item) {
      this.showSaveModal = false;
      this.editModalState = item;
      this.showEditModal = true;
    },
    onSaveModalClose() {
      this.showSaveModal = false;
      this.resetDataTable();
    },
    onEditModalClose() {
      this.showEditModal = false;
      this.editModalState = null;
      this.resetDataTable();
    },
  },
};
</script>
