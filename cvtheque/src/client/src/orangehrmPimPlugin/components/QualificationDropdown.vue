<template>
  <oxd-input-field
    v-model="selectedOption"
    type="select"
    :options="formattedOptions"
  />
</template>

<script>
import {ref, computed, watch} from 'vue';

export default {
  name: 'QualificationDropdown',
  props: {
    api: {
      type: String,
      required: true,
    },
    certificateOptions: {
      type: Array,
      required: true,
    },
  },
  emits: ['update:modelValue'],
  setup(props, {emit}) {
    const formattedOptions = computed(() =>
      props.certificateOptions.map((option, index) => ({
        id: index,
        label: option,
      })),
    );

    const selectedOption = ref(null);

    watch(selectedOption, (newValue) => {
      emit('update:modelValue', newValue);
    });

    return {
      formattedOptions,
      selectedOption,
    };
  },
};
</script>
