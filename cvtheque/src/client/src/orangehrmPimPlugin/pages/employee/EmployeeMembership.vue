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
  <edit-employee-layout :employee-id="empNumber" screen="membership">
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <oxd-text tag="h6" class="orangehrm-main-title">{{
        $t('Expérience professionnelle')
      }}</oxd-text>
      <oxd-divider />
      <oxd-form :loading="isLoading" @submit-valid="onSave">
        <oxd-form-row>
          <div class="orangehrm-vertical-padding">
            <oxd-grid :cols="2" class="orangehrm-full-width-grid">
              <oxd-grid-item>
                <oxd-input-field
                  v-model="defaultSelectedExperience"
                  type="select"
                  :options="formattedOptions"
                  :label="$t('pim.work_experience_global')"
                  required
                />
              </oxd-grid-item>
              <oxd-grid-item>
                <oxd-input-field
                  v-model="BTPSelectedExperience"
                  type="select"
                  :options="formattedOptions"
                  :label="$t('pim.work_experience_btp')"
                  required
                />
              </oxd-grid-item>
            </oxd-grid>
          </div>
        </oxd-form-row>
        <oxd-form-actions>
          <submit-button />
        </oxd-form-actions>
      </oxd-form>
    </div>

    <oxd-divider />
    <save-membership
      v-if="showSaveModal"
      :http="http"
      :currencies="currencies"
      :paid-by="paidBy"
      :memberships="memberships"
      :skills="items.skills"
      @close="onSaveModalClose"
    ></save-membership>
    <!--
    <edit-membership
      v-if="showEditModal"
      :http="http"
      :currencies="currencies"
      :paid-by="paidBy"
      :memberships="memberships"
      :data="editModalState"
      @close="onEditModalClose"
    ></edit-membership>
    -->
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <profile-action-header
        :add-label="$t('general.add_experience')"
        @click="onClickAdd"
      >
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
        :headers="headers"
        :items="items?.skills"
        :selectable="false"
        :disabled="isDisabled"
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
    <delete-confirmation ref="deleteDialog"></delete-confirmation>
  </edit-employee-layout>
</template>

<script>
import {ref, computed, watch} from 'vue';
import usePaginate from '@ohrm/core/util/composable/usePaginate';
import {APIService} from '@ohrm/core/util/services/api.service';
import ProfileActionHeader from '@/orangehrmPimPlugin/components/ProfileActionHeader';
import EditEmployeeLayout from '@/orangehrmPimPlugin/components/EditEmployeeLayout';
import SaveMembership from '@/orangehrmPimPlugin/components/SaveMembership';
// import EditMembership from '@/orangehrmPimPlugin/components/EditMembership';
import DeleteConfirmationDialog from '@ohrm/components/dialogs/DeleteConfirmationDialog';
// import QualificationDropdown from '@/orangehrmPimPlugin/components/QualificationDropdown';
// import useDateFormat from '@/core/util/composable/useDateFormat';
// import {formatDate, parseDate} from '@/core/util/helper/datefns';
// import useLocale from '@/core/util/composable/useLocale';

export default {
  components: {
    'profile-action-header': ProfileActionHeader,
    'edit-employee-layout': EditEmployeeLayout,
    'save-membership': SaveMembership,
    // 'edit-membership': EditMembership,
    'delete-confirmation': DeleteConfirmationDialog,
    // 'qualification-dropdown': QualificationDropdown,
  },

  props: {
    empNumber: {
      type: String,
      required: true,
    },
    currencies: {
      type: Array,
      default: () => [],
    },
    paidBy: {
      type: Array,
      default: () => [],
    },
    memberships: {
      type: Array,
      default: () => [],
    },
    infoOptions: {
      type: Array,
      required: true,
    },
  },

  setup(props) {
    const formattedOptions = computed(() =>
      props.infoOptions.professionalExperiences.map((option, index) => ({
        id: index, // Identifiant unique pour chaque option
        label: option, // Label affiché dans la liste déroulante
      })),
    );

    const defaultSelectedExperience = ref(null);
    const BTPSelectedExperience = ref(null);
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/pim/employees/${props.empNumber}/memberships`,
    );
    // const {jsDateFormat} = useDateFormat();
    // const {locale} = useLocale();

    const membershipNormalizer = (data) => {
      return {
        professionalExperience: data[0]?.professionalExperience ?? null,
        specificProfessionalExperience:
          data[0]?.specificProfessionalExperience ?? null,
        skills: data.map((item) => ({
          title: item.title,
          year: item.year,
          description: item.description,
          employer: item.employer,
        })),
      };
    };

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
      normalizer: membershipNormalizer,
      toastNoRecords: false,
    });
    const items = ref({
      professionalExperience: null,
      specificProfessionalExperience: null,
      skills: [],
    });

    watch(response, (newResponse) => {
      if (newResponse?.data) {
        items.value = newResponse.data;
        const findOption = (value) => {
          const foundOption =
            formattedOptions.value.find((option) => option.label === value) ||
            null;
          return foundOption || null;
        };

        defaultSelectedExperience.value = findOption(
          items.value.professionalExperience,
        );
        BTPSelectedExperience.value = findOption(
          items.value.specificProfessionalExperience,
        );
      }
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
      items,
      formattedOptions,
      defaultSelectedExperience,
      BTPSelectedExperience,
      //submit,
    };
  },

  data() {
    return {
      headers: [
        {
          name: 'title',
          title: this.$t('Poste occupé'),
          style: {flex: 1},
        },
        {
          name: 'year',
          title: this.$t('Période'),
          style: {flex: 1},
        },
        {
          name: 'employer',
          title: this.$t('Employeur'),
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
    onSave() {
      this.isLoading = true;
      this.http
        .update('', {
          professionalExperience: this.defaultSelectedExperience?.label,
          specificProfessionalExperience: this.BTPSelectedExperience?.label,
        })
        .then(() => {
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.isLoading = false;
        });
    },
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
          this.deleteItems([item]);
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

<style src="./employee.scss" lang="scss" scoped></style>
