<template>
  <div class="orangehrm-background-container">
    <table-filter-title
      v-if="isLoading || hasNoCompany"
      :title="$t('general.directory_all_companies_title')"
    >
    </table-filter-title>
    <table-filter
      v-else
      :filter-title="$t('general.directory_all_companies_title')"
    >
      <oxd-form @submit-valid="onSearch" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="2" class="orangehrm-full-width-grid">
            <oxd-grid-item>
              <oxd-input-field
                v-model="jobSector"
                type="select"
                :label="$t('recruitment.job_sector')"
                :options="sectors"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-group :classes="{wrapper: '--status-grouped-field'}">
                <template #label>
                  <div class="label-is-entitlement-situational">
                    <oxd-label :label="$t('general.job_title')" />
                    <oxd-icon-button
                      style="margin-left: 5px; font-size: 12px"
                      name="exclamation-circle"
                      :with-container="false"
                      @click="onModalOpen"
                    />
                  </div>
                </template>
                <oxd-input-field
                  v-model="jobTitleFilter"
                  type="select"
                  :options="jobTitlesPerSector"
                  required
                  :rules="rules.jobTitle"
                />
              </oxd-input-group>
              <oxd-text class="orangehrm-input-hint" tag="p">
                {{
                  $t(
                    'Veuillez sélectionner la famille avant de pouvoir choisir le métier.',
                  )
                }}
              </oxd-text>
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            :label="$t('general.reset')"
            display-type="ghost"
            type="reset"
            :disabled="!canUpdate"
          />
          <submit-button :label="$t('general.search')" :disabled="!canUpdate" />
        </oxd-form-actions>
      </oxd-form>
    </table-filter>

    <br />

    <div class="orangehrm-corporate-directory">
      <div class="orangehrm-paper-container">
        <div v-if="isEmpty" class="orangehrm-corporate-directory-nocontent">
          <img :src="noContentPic" alt="No Content" />
          <oxd-text tag="p">
            {{ $t('general.directory_all_companies_no_record') }}
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
      <job-category-selection-modal
        v-if="showModal"
        @close="onModalClose"
      ></job-category-selection-modal>
    </div>
  </div>
</template>

<script>
import {reactive, toRefs, ref} from 'vue';
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
import {OxdSpinner, useResponsive, OxdLabel} from '@ohrm/oxd';
import JobCategorySelectionModal from '../../orangehrmRecruitmentPlugin/components/JobCategorySelectionModal.vue';

const defaultFilters = {
  companyNumber: null,
  jobTitle: null,
  locationId: null,
};

export default {
  name: 'ViewAllCompanies',

  components: {
    'summary-card': SummaryCard,
    'oxd-loading-spinner': OxdSpinner,
    'company-details': CompanyDetails,
    'summary-card-details': SummaryCardDetails,
    'table-filter-title': TableFilterTitle,
    'table-filter': TableFilter,
    'oxd-label': OxdLabel,
    'job-category-selection-modal': JobCategorySelectionModal,
  },

  props: {
    sectors: {
      type: Array,
      default: () => [],
    },
    locations: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const jobSector = ref('');
    const jobTitleFilter = ref(null);
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
          companySiret: item.companySiret,
          companyName: item.companyName,
          companyMatchingJobTitle: item.companyMatchingJobTitle,
          companyLocation: item.companyLocation,
          companyLogo: item.companyLogo,
          companyPhoneNumberContact: item.companyPhoneNumberContact,
          companyEmailContact: item.companyEmailContact,
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
      state.companies = [];
      http
        .getAll({
          limit: limit,
          offset: state.offset,
          jobTitle:
            jobTitleFilter.value !== null ? jobTitleFilter.value?.label : null,
          allCompanies: true,
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
          if (
            jobTitleFilter.value === null ||
            jobTitleFilter.value.label == null
          )
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
      jobSector,
      jobTitleFilter,
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
  data() {
    return {
      showModal: false,
      jobTitlesPerSector: [],
      canUpdate: false,
    };
  },
  watch: {
    jobSector(newVal) {
      if (newVal) {
        const selectedSector = this.sectors.find(
          (sector) => sector.label === newVal.label,
        );
        this.jobTitlesPerSector = selectedSector
          ? selectedSector.jobs.map((job, index) => {
              return {id: index, label: job};
            })
          : [];
      } else {
        this.jobTitlesPerSector = [];
        this.jobTitleFilter = null;
      }
    },
    jobTitleFilter(newVal) {
      this.canUpdate = newVal !== null;
    },
  },

  beforeMount() {
    this.fetchData();
  },

  methods: {
    onModalOpen() {
      this.showModal = true;
    },
    onModalClose() {
      this.showModal = false;
    },
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
      this.hideCompanyDetails();
      this.fetchData();
    },
    onReset() {
      this.hideCompanyDetails();
      this.jobTitleFilter = null;
      this.companies = this.allCompanies;
      this.offset = 0;
      this.filters = {...defaultFilters};
    },
  },
};
</script>

<style src="./corporate-directory.scss" lang="scss" scoped></style>
