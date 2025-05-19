<template>
  <div class="orangehrm-action-header">
    <oxd-text tag="h6" class="orangehrm-main-title">
      <slot></slot>
    </oxd-text>
    <oxd-button
      v-if="actionButtonShown"
      :label="computedAddLabel"
      icon-name="plus"
      display-type="text"
      v-bind="$attrs"
    />
  </div>
</template>

<script>
import usei18n from '@/core/util/composable/usei18n';

export default {
  name: 'ProfileActionHeader',
  inheritAttrs: false,
  props: {
    actionButtonShown: {
      type: Boolean,
      required: false,
      default: true,
    },
    addLabel: {
      type: String,
      required: false,
    },
  },
  setup(props) {
    const {$t} = usei18n();
    const computedAddLabel = props.addLabel || $t('general.add');

    return {
      computedAddLabel,
    };
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-action {
  &-header {
    display: flex;
    overflow-wrap: break-word;
    align-items: center;
    button {
      margin-left: 1rem;
      white-space: nowrap;
    }
  }
}
</style>
