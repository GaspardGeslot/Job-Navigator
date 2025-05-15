<template>
  <oxd-grid :cols="2" class="orangehrm-full-width-grid">
    <oxd-grid-item class="orangehrm-job-selection-criteria --span-column-2">
      <oxd-input-field
        v-model="selectedCourses"
        type="autocomplete"
        :label="$t('Formation')"
        :disabled="disabled"
        :clear="true"
        :create-options="loadCourses"
        :placeholder="$t('Rechercher une formation')"
        :multiple="true"
      />
      <oxd-input-group>
        <oxd-icon-button
          style="margin-left: 1rem; margin-bottom: 1rem"
          name="plus"
          @click="addCourse"
        />
      </oxd-input-group>
    </oxd-grid-item>
  </oxd-grid>
  <oxd-grid :cols="3" class="orangehrm-full-width-grid">
    <oxd-grid-item
      v-for="(course, index) in courses"
      :key="index"
      class="orangehrm-job-selection-criteria-selected"
    >
      <oxd-icon-button name="trash-fill" @click="onClickDeleteCourse(course)" />
      <oxd-text class="orangehrm-job-selection-criteria-name">
        {{ course.label }}
      </oxd-text>
    </oxd-grid-item>
  </oxd-grid>
</template>

<script>
import {APIService} from '@/core/util/services/api.service';

export default {
  name: 'CoursesAutocomplete',

  props: {
    courses: {
      type: Array,
      required: true,
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

  emits: ['delete-course', 'add-courses'],

  setup(props) {
    const httpCourse = new APIService(
      window.appGlobal.baseUrl,
      `${window.appGlobal.theme}${props.apiPath}`,
    );
    return {
      httpCourse,
    };
  },

  data() {
    return {
      selectedCourses: [],
    };
  },

  methods: {
    onClickDeleteCourse(course) {
      this.$emit('delete-course', course);
    },

    addCourse() {
      const newCourses = [];
      for (const course of this.selectedCourses) {
        if (
          !this.courses.some(
            (existingCourse) => existingCourse.id === course.id,
          )
        ) {
          newCourses.push(course);
        }
      }
      if (newCourses.length > 0) {
        this.$emit('add-courses', newCourses);
      }
      this.selectedCourses = [];
    },
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

<style scoped lang="scss">
.orangehrm-job-selection {
  &-criteria {
    display: flex;
    align-items: center;
  }
  &-criteria-selected {
    display: flex;
    align-items: baseline;
  }
  &-criteria-name {
    margin-left: 1rem;
    font-weight: 700;
    font-size: $oxd-input-control-font-size;
    padding: $oxd-input-control-vertical-padding 0rem;
  }
  &-icon {
    margin-left: 1rem;
  }
}
</style>
