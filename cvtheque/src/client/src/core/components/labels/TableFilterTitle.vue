<template>
  <div class="oxd-table-filter">
    <div class="oxd-table-filter-header">
      <div class="oxd-table-filter-header-title">
        <oxd-text class="oxd-table-filter-title" tag="h5">
          {{ title }}
        </oxd-text>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TableFilterTitle',
  props: {
    title: {
      type: String,
      required: true,
      default: '',
    },
  },
};
</script>

<style lang="scss" scoped>
.oxd-table-filter {
  background-color: $oxd-white-color;
  border-radius: 1.2rem;
  padding: 25px;
  &-title {
    font-size: 14px;
    font-weight: 800;
  }
  &-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: space-between;
    &-options {
      display: flex;
      & .--toggle,
      .--export {
        display: flex;
      }
    }
  }
}
</style>
