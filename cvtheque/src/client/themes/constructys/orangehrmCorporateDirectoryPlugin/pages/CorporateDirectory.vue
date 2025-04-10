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
    <table-filter-title
      v-if="isLoading || hasNoCompany"
      :title="$t('general.directory_matching_title')"
    >
    </table-filter-title>
    <table-filter v-else :filter-title="$t('general.directory_matching_title')">
      <oxd-form @submit-valid="onSearch" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="1">
            <!--<oxd-grid-item>
              <employee-autocomplete
                v-model="filters.employeeNumber"
                :rules="rules.employee"
                api-path="/api/v2/directory/companies"
              />
            </oxd-grid-item>-->
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.jobTitle"
                type="select"
                :label="$t('general.job')"
                :options="jobTitles"
              />
            </oxd-grid-item>
            <!--<oxd-grid-item>
              <oxd-input-field
                v-model="filters.locationId"
                type="select"
                :label="$t('general.location')"
                :options="locations"
              />
            </oxd-grid-item>-->
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            :label="$t('general.reset')"
            display-type="ghost"
            type="reset"
          />
          <submit-button :label="$t('general.search')" />
        </oxd-form-actions>
      </oxd-form>
    </table-filter>

    <br />

    <div class="orangehrm-corporate-directory">
      <div class="orangehrm-paper-container">
        <div v-if="isEmpty" class="orangehrm-corporate-directory-nocontent">
          <img :src="noContentPic" alt="No Content" />
          <oxd-text tag="p">
            {{ $t('general.directory_matching_no_record') }}
          </oxd-text>
        </div>
        <div v-else>
          <table-header
            :selected="0"
            :total="total"
            :loading="false"
            :show-divider="false"
          ></table-header>
          <div ref="scrollerRef" class="orangehrm-container">
            <oxd-grid :cols="colSize">
              <oxd-grid-item
                v-for="(company, index) in companies"
                :key="company"
              >
                <summary-card
                  v-if="isMobile && currentIndex === index"
                  :company-id="company.companyId"
                  :company-siret="company.companySiret"
                  :company-name="company.companyName"
                  :company-location="company.companyLocation"
                  :company-matching-job-title="company.companyMatchingJobTitle"
                  :candidature-status="company.candidatureStatus"
                  @click="showCompanyDetails(index)"
                >
                  <company-details
                    :company-id="company.companyId"
                    :company-name="company.companyName"
                    :company-phone-number-contact="
                      company.companyPhoneNumberContact
                    "
                    :company-email-contact="company.companyEmailContact"
                    :candidature-status="company.candidatureStatus"
                    :is-mobile="isMobile"
                    @see-details="seeCompanyDetails()"
                  >
                  </company-details>
                </summary-card>
                <summary-card
                  v-else
                  :company-id="company.companyId"
                  :company-siret="company.companySiret"
                  :company-name="company.companyName"
                  :company-location="company.companyLocation"
                  :company-matching-job-title="company.companyMatchingJobTitle"
                  :candidature-status="company.candidatureStatus"
                  @click="showCompanyDetails(index)"
                >
                </summary-card>
              </oxd-grid-item>
            </oxd-grid>
            <oxd-loading-spinner
              v-if="isLoading"
              class="orangehrm-container-loader"
            />
          </div>
          <div class="orangehrm-bottom-container"></div>
        </div>
      </div>
      <div
        v-if="isCompanySelected && isMobile === false"
        class="orangehrm-corporate-directory-sidebar"
      >
        <oxd-grid-item>
          <summary-card-details
            :company-id="companies[currentIndex].companyId"
            :company-siret="companies[currentIndex].companySiret"
            :company-location="companies[currentIndex].companyLocation"
            :company-name="companies[currentIndex].companyName"
            :company-matching-job-title="
              companies[currentIndex].companyMatchingJobTitle
            "
            :company-phone-number-contact="
              companies[currentIndex].companyPhoneNumberContact
            "
            :company-email-contact="companies[currentIndex].companyEmailContact"
            :candidature-status="companies[currentIndex].candidatureStatus"
            @hide-details="hideCompanyDetails()"
            @see-details="seeCompanyDetails()"
          ></summary-card-details>
        </oxd-grid-item>
      </div>
    </div>
  </div>
</template>

<script>
import {reactive, toRefs} from 'vue';
import {navigate} from '@/core/util/helper/navigation';
import usei18n from '@/core/util/composable/usei18n';
import useToast from '@/core/util/composable/useToast';
import {APIService} from '@/core/util/services/api.service';
import {
  shouldNotExceedCharLength,
  validSelection,
} from '@/core/util/validation/rules';
import useInfiniteScroll from '@/core/util/composable/useInfiniteScroll';
//import EmployeeAutocomplete from '@/core/components/inputs/EmployeeAutocomplete';
import SummaryCard from '../components/SummaryCard';
import CompanyDetails from '../components/CompanyDetails';
import SummaryCardDetails from '../components/SummaryCardDetails';
import TableFilterTitle from '@/core/components/labels/TableFilterTitle';
import TableFilter from '@/core/components/dropdown/TableFilter.vue';
import {OxdSpinner, useResponsive} from '@ohrm/oxd';

const defaultFilters = {
  companyNumber: null,
  jobTitle: null,
  locationId: null,
};

export default {
  name: 'CorporateDirectory',

  components: {
    'summary-card': SummaryCard,
    'oxd-loading-spinner': OxdSpinner,
    'company-details': CompanyDetails,
    'summary-card-details': SummaryCardDetails,
    'table-filter-title': TableFilterTitle,
    'table-filter': TableFilter,
    //'employee-autocomplete': EmployeeAutocomplete,
  },

  props: {
    jobTitles: {
      type: Array,
      default: () => [],
    },
    locations: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const {$t} = usei18n();
    const {noRecordsFound} = useToast();
    const responsiveState = useResponsive();
    const noContentPic = `${window.appGlobal.publicPath}/images/empty-box.png`;

    const rules = {
      employee: [shouldNotExceedCharLength(100), validSelection],
    };

    const companyDataNormalizer = (data) => {
      return data.map((item) => {
        return {
          companyId: item.companyId,
          companyName: item.companyName,
          companyMatchingJobTitle: item.companyMatchingJobTitle,
          companyLocation: item.companyLocation,
          companyPhoneNumberContact: item.companyPhoneNumberContact,
          companyEmailContact: item.companyEmailContact,
          companySiret: item.companySiret,
          candidatureStatus: item.candidatureStatus,
          matchingId: item.matchingId,
        };
      });
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}/api/v2/directory/employees`,
    );

    const limit = 14;

    const state = reactive({
      total: 0,
      offset: 0,
      companies: [],
      allCompanies: [],
      currentIndex: -1,
      isLoading: false,
      filters: {
        ...defaultFilters,
      },
    });

    const fetchData = () => {
      state.isLoading = true;
      http
        .getAll({
          limit: limit,
          offset: state.offset,
          /*locationId: state.filters.locationId?.id,
          companyNumber: state.filters.companyNumber?.id,*/
          jobTitle: state.filters.jobTitle,
          allCompanies: false,
        })
        .then((response) => {
          const {data, meta} = response.data;
          state.total = meta?.total || 0;
          if (Array.isArray(data)) {
            state.companies = [
              ...state.companies,
              ...companyDataNormalizer(data),
            ];
          }
          state.allCompanies = state.companies;
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .finally(() => (state.isLoading = false));
    };

    const {scrollerRef} = useInfiniteScroll(() => {
      if (state.companies.length >= state.total) return;
      state.offset += limit;
      fetchData();
    });

    return {
      noContentPic,
      rules,
      fetchData,
      scrollerRef,
      ...toRefs(state),
      ...toRefs(responsiveState),
    };
  },

  computed: {
    hasNoCompany() {
      return !this.isLoading && this.allCompanies.length === 0;
    },
    isEmpty() {
      return !this.isLoading && this.companies.length === 0;
    },
    isMobile() {
      return this.windowWidth < 800;
    },
    isCompanySelected() {
      return this.currentIndex >= 0;
    },
    oxdGridClasses() {
      return {
        'orangehrm-container': true,
        'orangehrm-container-min-display': this.isCompanySelected,
      };
    },
    colSize() {
      if (this.windowWidth >= 1920) {
        return this.isCompanySelected ? 5 : 7;
      }
      return this.isCompanySelected ? 3 : 4;
    },
  },

  beforeMount() {
    this.fetchData();
  },

  methods: {
    hideCompanyDetails() {
      this.currentIndex = -1;
    },
    seeCompanyDetails() {
      !this.companies[this.currentIndex].matchingId
        ? navigate(`/${window.appGlobal.theme}/recruitment/viewCompany/{id}`, {
            id: this.companies[this.currentIndex].companyId,
          })
        : navigate(
            `/${window.appGlobal.theme}/recruitment/viewCompany/{companyId}/matching/{matchingId}`,
            {
              companyId: this.companies[this.currentIndex].companyId,
              matchingId: this.companies[this.currentIndex].matchingId,
            },
          );
    },
    showCompanyDetails(index) {
      if (this.currentIndex != index) {
        this.currentIndex = index;
      } else {
        this.hideCompanyDetails();
      }
    },
    onSearch() {
      if (
        this.filters.jobTitle === undefined ||
        this.filters.jobTitle?.label === undefined
      )
        this.onReset();
      else {
        this.hideCompanyDetails();
        this.companies = this.allCompanies.filter(
          (company) =>
            company.companyMatchingJobTitle === this.filters.jobTitle?.label,
        );
        //this.companies = [];
        //this.offset = 0;
        //this.fetchData();
      }
    },
    onReset() {
      this.hideCompanyDetails();
      this.companies = this.allCompanies;
      this.offset = 0;
      this.filters = {...defaultFilters};
      //this.fetchData();
    },
  },
};
</script>

<style src="./corporate-directory.scss" lang="scss" scoped></style>
