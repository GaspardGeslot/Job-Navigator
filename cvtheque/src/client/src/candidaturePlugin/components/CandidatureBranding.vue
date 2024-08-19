<template>
  <div class="orangehrm-candidature-branding">
    <img :src="imgSrc" alt="company-branding" />
  </div>
</template>

<script>
export default {
  name: 'CandidatureBranding',
  props: {
    imgSrc: {
      type: String,
      required: true,
    },
  },
};
</script>

<style src="./candidature-layout.scss" lang="scss" scoped></style>
