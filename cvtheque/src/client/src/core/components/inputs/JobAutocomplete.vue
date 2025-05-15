<template>
  <div>
    <oxd-input-field
      v-model="selectedItems"
      :modelValue="modelValue"
      @update:modelValue="handleUpdate"
      type="autocomplete"
      :label="$t('Métier')"
      :clear="true"
      :create-options="loadJobs"
      :placeholder="$t('Rechercher un métier')"
      :multiple="multiple"
    />
  </div>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';
import {ref, watch} from 'vue';

export default {
  name: 'JobAutocomplete',
  props: {
    modelValue: {
      type: [Object, Array, null],
      default: null,
    },
    multiple: {
      type: Boolean,
      default: false,
    },
    apiPath: {
      type: String,
      default: '/api/v2/admin/job/search',
    },
  },

  emits: ['update:modelValue'],

  setup(props, {emit}) {
    const httpJob = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}${props.apiPath}`,
    );

    const selectedItems = ref([]);

    const handleUpdate = (value) => {
      if (props.multiple) {
        if (Array.isArray(value)) {
          // Transform array of Proxies/Objects to array of plain objects
          const transformedValue = value.map((item) => {
            // If item is a Proxy, get its target object, otherwise use item directly
            return item?._isProxy ? item.valueOf() : item;
          });
          selectedItems.value = transformedValue;
          emit('update:modelValue', selectedItems.value);
        } else if (value) {
          // Transform single value if it's a Proxy
          const transformedValue = value._isProxy ? value.valueOf() : value;
          if (
            !selectedItems.value.some((item) => item.id === transformedValue.id)
          ) {
            selectedItems.value = [...selectedItems.value, transformedValue];
            emit('update:modelValue', selectedItems.value);
          }
        } else {
          // When items are cleared
          emit('update:modelValue', value);
        }
        emit('update-jobs', selectedItems.value);
      } else emit('update:modelValue', value);
    };

    const removeItem = (job) => {
      selectedItems.value = selectedItems.value.filter(
        (item) => item.id !== job.id,
      );
      emit('update:modelValue', selectedItems.value);
    };

    const reset = () => {
      selectedItems.value = [];
    };

    const loadJobs = (searchParam) => {
      return new Promise((resolve) => {
        if (searchParam.trim() && searchParam.length < 100) {
          httpJob
            .getAll({
              title: searchParam.trim(),
            })
            .then(({data}) => {
              resolve(data);
            });
        } else {
          resolve([]);
        }
      });
    };

    return {
      httpJob,
      loadJobs,
      selectedItems,
      handleUpdate,
      removeItem,
      reset,
    };
  },
};
</script>
