<template>
  <candidature-layout>
    <oxd-text class="orangehrm-candidature-title" tag="h5">
      {{ $t('candidature.title') }}
    </oxd-text>
    <div class="orangehrm-candidature-form">
      <div class="orangehrm-header-container">
        <oxd-button
          :label="$t('candidature.login')"
          display-type="main"
          @click="navigateToLogin"
        />
      </div>
    </div>
  </candidature-layout>
</template>

<script>
import {urlFor} from '@ohrm/core/util/helper/url';
import {navigate} from '@ohrm/core/util/helper/navigation';
import CandidatureLayout from '@/candidaturePlugin/components/CandidatureLayout.vue';

export default {
  components: {
    'candidature-layout': CandidatureLayout,
  },

  methods: {
    navigateToLogin() {
      navigate('/auth/login');
    },
  },
};
</script>

<style src="./candidature.scss" lang="scss" scoped></style>
