<template>
  <div class="orangehrm-candidature-layout">
    <div class="orangehrm-candidature-layout-blob">
      <div class="orangehrm-candidature-container">
        <div class="orangehrm-candidature-slot-wrapper">
          <!-- prettier-ignore -->
          <candidature-branding :img-src="candidatureBannerSrc"></candidature-branding>
          <div class="orangehrm-candidature-slot">
            <div class="orangehrm-candidature-logo-mobile">
              <img :src="candidatureLogoSrc" alt="orangehrm-logo" />
            </div>
            <slot></slot>
          </div>
        </div>
      </div>
      <div class="orangehrm-candidature-logo">
        <img :src="candidatureLogoSrc" alt="orangehrm-logo" />
      </div>
    </div>
  </div>
</template>

<script>
import CandidatureBranding from './CandidatureBranding';

export default {
  name: 'CandidatureLayout',
  components: {
    'candidature-branding': CandidatureBranding,
  },
  props: {
    candidatureLogoSrc: {
      type: String,
      required: true,
    },
    candidatureBannerSrc: {
      type: String,
      required: true,
    },
  },
};
</script>

<style src="./candidature-layout.scss" lang="scss" scoped></style>
