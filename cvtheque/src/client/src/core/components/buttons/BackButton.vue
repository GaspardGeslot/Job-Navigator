<template>
  <oxd-icon-button
    :name="'chevron-left'"
    class="oxd-main-menu-button"
    role="none"
    @click="onClick"
  />
</template>

<script>
export default {
  name: 'BackButton',

  methods: {
    onClick(event) {
      event.preventDefault();
      window.history.back();
    },
  },
};
</script>
