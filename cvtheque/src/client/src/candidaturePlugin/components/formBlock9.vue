<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle">Quel métier vous intéresse ?</h3>
    <p class="formSubTitle">3 choix possibles.</p>
    <form class="formBlock5">
      <p class="CVText">Métiers de la production</p>
      <div class="checkbox-group two-columns">
        <div class="checkbox-item">
          <input
            id="job1-1"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Carreleur"
          />
          <label for="job1-1"> Carreleur</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-2"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Charpentier"
          />
          <label for="job1-2"> Charpentier</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-3"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Chauffagiste"
          />
          <label for="job1-3"> Chauffagiste</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-4"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Couvreur"
          />
          <label for="job1-4">Couvreur</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-5"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Électricien"
          />
          <label for="job1-5">Électricien</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-6"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Maçon"
          />
          <label for="job1-6">Maçon</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-7"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Menuisier"
          />
          <label for="job1-7">Menuisier</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-8"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Métallier"
          />
          <label for="job1-8">Métallier</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-9"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Peintre"
          />
          <label for="job1-9">Peintre</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-10"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Plâtrier-Plaquiste"
          />
          <label for="job1-10">Plâtrier-Plaquiste</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job1-11"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Plombier"
          />
          <label for="job1-11">Plombier</label>
        </div>
      </div>
      <p class="CVText">Métiers liés à l'encadrement et à la conception</p>
      <div class="checkbox-group two-columns">
        <div class="checkbox-item">
          <input
            id="job2-1"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Chef de chantier"
          />
          <label for="job2-1"> Chef de chantier</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job2-2"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Conducteur de travaux"
          />
          <label for="job2-2"> Conducteur de travaux</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job2-3"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Métreur"
          />
          <label for="job2-3"> Métreur</label>
        </div>
      </div>
      <p class="CVText">Métiers commerciaux et administratifs</p>
      <div class="checkbox-group two-columns">
        <div class="checkbox-item">
          <input
            id="job3-1"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Assistant de gestion du Bâtiment"
          />
          <label for="job3-1">Assistant de gestion du Bâtiment</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job3-2"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Chargé d'affaires"
          />
          <label for="job3-2">Chargé d'affaires</label>
        </div>
      </div>
      <p class="CVText">Techniques et études</p>
      <div class="checkbox-group two-columns">
        <div class="checkbox-item">
          <input
            id="job5-1"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Architecte"
          />
          <label for="job5-1">Architecte</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job5-2"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Géomètre"
          />
          <label for="job5-2">Géomètre</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job5-3"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Techniques et études : Autre"
          />
          <label for="job5-3">Autre</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job5-4"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Topographe"
          />
          <label for="job5-4">Topographe</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job5-5"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Dessinateur-projeteur"
          />
          <label for="job5-5">Dessinateur-projeteur</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job5-6"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Ingénieur BTP"
          />
          <label for="job5-6">Ingénieur BTP</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job5-7"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Technicien d'études"
          />
          <label for="job5-7">Technicien d'études</label>
        </div>
      </div>
      <p class="CVText">Maintenance et finition</p>
      <div class="checkbox-group two-columns">
        <div class="checkbox-item">
          <input
            id="job6-1"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Manœuvre"
          />
          <label for="job6-1">Manœuvre</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job6-2"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Technicien maintenance"
          />
          <label for="job6-2">Technicien maintenance</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job6-3"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Soudeur"
          />
          <label for="job6-3">Soudeur</label>
        </div>
        <div class="checkbox-item">
          <input
            id="job6-4"
            v-model="checkedJobs"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Maintenance et finition : Autre"
          />
          <label for="job6-4">Autre</label>
        </div>
      </div>
      <input class="submitButton" type="submit" value="SUIVANT" />
    </form>
  </div>
</template>

<script>
export default {
  name: 'FormNine',
  emits: ['situation-submitted'],
  data() {
    return {
      checkedJobs: [],
      errorMessage: '',
    };
  },
  watch: {
    checkedJobs(newVal) {
      if (newVal.length > 3) {
        this.checkedJobs.pop();
        this.errorMessage =
          'Vous ne pouvez sélectionner que 3 options au total.';
        alert(this.errorMessage);
        //console.log(this.errorMessage);
      } else {
        this.errorMessage = '';
      }
    },
  },
  methods: {
    onSubmit() {
      if (this.checkedJobs.length > 3) {
        this.errorMessage =
          'Vous ne pouvez sélectionner que 3 options au total.';
        return;
      }
      let situationReview = {
        Jobs: this.checkedJobs,
      };
      console.log('Jobs', this.checkedJobs);
      this.$emit('situation-submitted', situationReview);
      this.checkedJobs = [];
      this.errorMessage = '';
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss" scoped></style>

<style scoped>
.checkbox-group.two-columns {
  display: flex;
  flex-wrap: wrap;
}

.checkbox-item {
  flex: 1 1 50%;
  box-sizing: border-box;
  padding: 2px;
}

.checkbox-item input {
  margin-right: 5px;
}
.checkbox-item label {
  font-family: 'Telegraf', sans-serif;
}

.checkbox-item label {
  font-size: 0.75rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
