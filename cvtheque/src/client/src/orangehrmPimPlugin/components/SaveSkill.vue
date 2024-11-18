<!--
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
 -->

<template>
  <div class="orangehrm-horizontal-padding orangehrm-top-padding">
    <oxd-text tag="h6" class="orangehrm-main-title">{{
      $t('general.add_skill')
    }}</oxd-text>
    <oxd-divider />
    <oxd-form :loading="isLoading" @submit-valid="onSave">
      <oxd-form-row>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <qualification-dropdown
              v-model="skill.type"
              :label="$t(`Compétence`)"
              :rules="rules.type"
              :api="api"
              :certificate-options="certificateOptions"
              required
            ></qualification-dropdown>
          </oxd-grid-item>
          <oxd-grid-item>
            <oxd-input-field
              v-model="skill.title"
              :label="$t(`Années d'expérience`)"
              :rules="rules.title"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>

      <oxd-form-row>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item class="--span-column-2">
            <oxd-input-field
              v-model="skill.description"
              type="textarea"
              :label="$t(`Commentaires`)"
              :rules="rules.description"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>

      <oxd-form-actions>
        <required-text />
        <oxd-button
          type="button"
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onCancel"
        />
        <submit-button />
      </oxd-form-actions>
    </oxd-form>
    <oxd-divider />
  </div>
</template>

<script>
import QualificationDropdown from '@/orangehrmPimPlugin/components/QualificationDropdown';
import {
  required,
  shouldNotExceedCharLength,
} from '@ohrm/core/util/validation/rules';

const skillModel = {
  title: '',
  description: '',
  type: '',
};

export default {
  name: 'SaveSkill',

  components: {
    'qualification-dropdown': QualificationDropdown,
  },

  props: {
    http: {
      type: Object,
      required: true,
    },
    api: {
      type: String,
      required: true,
    },
    certificateOptions: {
      type: Array,
      required: true,
    },
  },

  emits: ['close'],

  data() {
    return {
      isLoading: false,
      skill: {...skillModel},
      rules: {
        type: [required],
        title: [shouldNotExceedCharLength(100)],
        description: [shouldNotExceedCharLength(300)],
      },
    };
  },

  methods: {
    onSave() {
      this.isLoading = true;
      this.http
        .create({
          type: this.skill.type?.label,
          title: this.skill.title ? String(this.skill.title) : '',
          description:
            this.skill.description !== '' ? this.skill.description : '',
        })
        .then((response) => {
          console.log('Response du serveur : ', response);
          return this.$toast.saveSuccess();
        })
        .then(() => {
          this.onCancel();
        })
        .catch((error) => {
          if (error.response) {
            console.error('Erreur lors de la requête:', error.response.data);
          } else if (error.request) {
            console.error('Aucune réponse du serveur:', error.request);
          } else {
            console.error(
              'Erreur lors de la configuration de la requête:',
              error.message || 'Erreur inconnue',
            );
          }
        });
    },
    onCancel() {
      this.$emit('close', true);
    },
  },
};
</script>
