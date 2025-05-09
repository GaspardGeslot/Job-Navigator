<template>
  <oxd-input-field
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    type="autocomplete"
    :label="$t('Métier')"
    :clear="true"
    :create-options="loadJobs"
    :placeholder="$t('Rechercher un métier')"
  />
</template>

<script>
import {APIService} from '@/core/util/services/api.service';

export default {
  name: 'JobAutocomplete',

  props: {
    modelValue: {
      type: [Object, null],
      default: null,
    },
    apiPath: {
      type: String,
      default: '/api/v2/admin/job/search',
    },
  },

  emits: ['update:modelValue'],

  setup(props) {
    const httpJob = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}${props.apiPath}`,
    );
    return {
      httpJob,
    };
  },
  methods: {
    loadJobs(searchParam) {
      return new Promise((resolve) => {
        if (searchParam.trim() && searchParam.length < 100) {
          this.httpJob
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
    },
  },
};
</script>
