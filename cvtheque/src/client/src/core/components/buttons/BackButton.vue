<template>
  <oxd-icon-button
    :name="'chevron-left'"
    class="oxd-main-menu-button"
    role="none"
    @click="onClick"
  />
</template>

<script>
import {navigate} from '@ohrm/core/util/helper/navigation';

export default {
  name: 'BackButton',
  props: {
    to: {
      type: String,
      default: null,
    },
  },
  methods: {
    onClick(event) {
      if (this.to !== null) navigate(this.to);
      else {
        event.preventDefault();
        window.history.back();
      }
    },
  },
};
</script>
