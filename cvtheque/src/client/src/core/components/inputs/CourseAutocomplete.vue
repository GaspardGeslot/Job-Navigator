<template>
  <oxd-input-field
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    type="autocomplete"
    :clear="true"
    :label="$t('Formation')"
    :create-options="loadCourses"
    :placeholder="$t('Rechercher une formation')"
  />
</template>

<script>
import {APIService} from '@/core/util/services/api.service';

export default {
  name: 'CourseAutocomplete',

  props: {
    modelValue: {
      type: [Object, null],
      default: null,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    apiPath: {
      type: String,
      default: '/api/v2/admin/course/search',
    },
  },

  emits: ['update:modelValue'],

  setup(props) {
    const httpCourse = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}${props.apiPath}`,
    );
    return {
      httpCourse,
    };
  },

  methods: {
    loadCourses(searchParam) {
      return new Promise((resolve) => {
        if (searchParam && searchParam.length < 100) {
          this.httpCourse
            .getAll({
              value: searchParam,
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
