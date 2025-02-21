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
    <save-skill
      v-if="showSaveModal"
      :http="http"
      :api="skillsEndpoint"
      :certificate-options="certificateOptions"
      @close="onSaveModalClose"
    ></save-skill>
    <!--
    <edit-skill
      v-if="showEditModal"
      :http="http"
      :data="editModalState"
      @close="onEditModalClose"
    ></edit-skill>
    -->
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <profile-action-header
        :add-label="$t('general.add')"
        @click="onClickAdd"
      ></profile-action-header>
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
        :headers="headers"
        :items="items?.data"
        :clickable="false"
        :loading="isLoading"
        :disabled="isDisabled"
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
import usePaginate from '@/core/util/composable/usePaginate';
import {APIService} from '@/core/util/services/api.service';
import ProfileActionHeader from './ProfileActionHeader';
import SaveSkill from './SaveSkill';
// import EditSkill from './EditSkill';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';

const skillNormalizer = (data) => {
  return data.map((item) => {
    return {
      type: item.type,
      title: item.title,
      description: item.description,
    };
  });
};

export default {
  name: 'EmployeeSkills',

  components: {
    'profile-action-header': ProfileActionHeader,
    'save-skill': SaveSkill,
    // 'edit-skill': EditSkill,
    'delete-confirmation': DeleteConfirmationDialog,
  },

  props: {
    employeeId: {
      type: String,
      required: true,
    },
    certificateOptions: {
      type: Array,
      required: true,
    },
  },

  setup(props) {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/${window.appGlobal.theme}/api/v2/pim/employees/${props.employeeId}/skills`,
    );

    const skillsEndpoint = `/${window.appGlobal.theme}/api/v2/pim/employees/${props.employeeId}/skills/allowed`;

    const {
      showPaginator,
      currentPage,
      total,
      pages,
      pageSize,
      response,
      isLoading,
      execQuery,
    } = usePaginate(http, {normalizer: skillNormalizer, toastNoRecords: false});
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
      skillsEndpoint,
    };
  },

  data() {
    return {
      headers: [
        {
          name: 'type',
          title: this.$t('general.type'),
          style: {flex: 1},
        },
        {
          name: 'title',
          title: this.$t('pim.certificate_title'),
          style: {flex: 1},
        },
        {
          name: 'description',
          title: this.$t('general.description'),
          style: {flex: 2},
        },
        {
          name: 'actions',
          slot: 'action',
          title: this.$t('general.actions'),
          style: {flex: 0.5},
          cellType: 'oxd-table-cell-actions',
          cellConfig: {
            delete: {
              onClick: this.onClickDelete,
              component: 'oxd-icon-button',
              props: {
                name: 'trash',
              },
            },
            // edit: {
            //   onClick: this.onClickEdit,
            //   props: {
            //     name: 'pencil-fill',
            //   },
            // },
          },
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
  },

  methods: {
    onClickDeleteSelected() {
      const ids = this.checkedItems.map((index) => {
        return this.items?.data[index].id;
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
          this.deleteItems([{type: item.type, title: item.title}]);
        }
      });
    },
    deleteItems(items) {
      if (items instanceof Array) {
        this.isLoading = true;
        this.http
          .deleteAll(items[0])
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
      this.editModalState = {
        type: item.type,
        title: item.title,
        description: item.description,
      };
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
