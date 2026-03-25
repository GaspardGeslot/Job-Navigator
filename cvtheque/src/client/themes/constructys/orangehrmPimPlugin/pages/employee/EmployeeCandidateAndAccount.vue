<template>
  <edit-employee-layout :employee-id="empNumber" screen="candidate-and-account">
    <div class="orangehrm-horizontal-padding orangehrm-vertical-padding">
      <oxd-text tag="h6" class="orangehrm-main-title">{{
        $t('Candidature et compte')
      }}</oxd-text>
      <oxd-divider />
    </div>
  </edit-employee-layout>
</template>

<script>
import EditEmployeeLayout from '../../components/EditEmployeeLayout';

export default {
  components: {
    'edit-employee-layout': EditEmployeeLayout,
  },

  props: {
    empNumber: {
      type: String,
      required: true,
    },
  },

  setup() {
    return {};
  },
};
</script>

<style src="./employee.scss" lang="scss" scoped></style>
