<template>
  <div class="formBlockLayout" @submit.prevent="onSubmit">
    <h3 class="formTitle">Avez-vous des certificats ou habilitations ?</h3>
    <p class="formSubTitle">Plusieurs choix possibles.</p>
    <form class="formBlock5">
      <p class="CVText">Habilitation électrique</p>
      <div class="checkbox-group">
        <div class="checkbox-item">
          <input
            id="B0"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="B0 : Personnel non électricien"
          />
          <label for="B0">B0 : Personnel non électricien</label>
        </div>
        <div class="checkbox-item">
          <input
            id="B1"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="B1 : Électricien chargé de travaux simples"
          />
          <label for="B1">B1 : Électricien chargé de travaux simples</label>
        </div>
        <div class="checkbox-item">
          <input
            id="B2"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="B2 : Électricien chargé de travaux complexes"
          />
          <label for="B2">B2 : Électricien chargé de travaux complexes</label>
        </div>
        <div class="checkbox-item">
          <input
            id="BR"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="BR : Électricien habilité interventions et consignations"
          />
          <label for="BR"
            >BR : Électricien habilité interventions et consignations
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="H0"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="H0 : Personnel non électricien habilité à travailler hors tension
"
          />
          <label for="H0"
            >H0 : Personnel non électricien habilité à travailler hors tension
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="H1"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Électricien chargé de consignations en haute tension"
          />
          <label for="H1"
            >HC : Électricien chargé de consignations en haute tension
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="H2"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="H2 : Électricien habilité pour travaux haute tension
"
          />
          <label for="H2"
            >H2 : Électricien habilité pour travaux haute tension
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="HC"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="HC : Électricien habilité pour interventions et consignations en haute tension"
          />
          <label for="HC"
            >HC : Électricien habilité pour interventions et consignations en
            haute tension
          </label>
        </div>
      </div>
      <p class="CVText">CACES</p>
      <div class="checkbox-group">
        <div class="checkbox-item">
          <input
            id="CACES1"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Catégorie 1 : Engins de chantier"
          />
          <label for="CACES1">Catégorie 1 : Engins de chantier</label>
        </div>
        <div class="checkbox-item">
          <input
            id="CACES2"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Catégorie 2 : Engins de levage"
          />
          <label for="CACES2">Catégorie 2 : Engins de levage</label>
        </div>
        <div class="checkbox-item">
          <input
            id="CACES3"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Catégorie 3 : Chariots élévateurs"
          />
          <label for="CACES3">Catégorie 3 : Chariots élévateurs</label>
        </div>
        <div class="checkbox-item">
          <input
            id="CACES4"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Catégorie 4 : Grues mobiles"
          />
          <label for="CACES4">Catégorie 4 : Grues mobiles</label>
        </div>
        <div class="checkbox-item">
          <input
            id="CACES5"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Catégorie 5 : Plateformes élévatrices mobiles de personnes (PEMP)"
          />
          <label for="CACES5"
            >Catégorie 5 : Plateformes élévatrices mobiles de personnes (PEMP)
          </label>
        </div>
      </div>
      <p class="CVText">Autres</p>
      <div class="checkbox-group">
        <div class="checkbox-item">
          <input
            id="other_availabilities1"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Certificat de Sauveteur Secouriste du Travail (SST)"
          />
          <label for="other_availabilities1"
            >Certificat de Sauveteur Secouriste du Travail (SST)
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="other_availabilities2"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Habilitation à la manipulation des fluides frigorigènes (Catégories 1 à 4)"
          />
          <label for="other_availabilities2"
            >Habilitation à la manipulation des fluides frigorigènes (Catégories
            1 à 4)
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="other_availabilities3"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Habilitation à l’emploi des produits chimiques dangereux
"
          />
          <label for="other_availabilities3"
            >Habilitation à l’emploi des produits chimiques dangereux
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="other_availabilities4"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Certificat en prévention des risques liés à l’amiante"
          />
          <label for="other_availabilities4"
            >Certificat en prévention des risques liés à l’amiante
          </label>
        </div>
        <div class="checkbox-item">
          <input
            id="other_availabilities5"
            v-model="checkedSkills"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Habilitation pour le travail en hauteur"
          />
          <label for="other_availabilities5"
            >Habilitation pour le travail en hauteur
          </label>
        </div>
      </div>
      <h3 class="formTitle" style="margin-top: 2.5rem">Permis et véhicule</h3>
      <p class="formSubTitle">Plusieurs choix possibles.</p>
      <p class="CVText">Permis obtenus</p>
      <div class="checkbox-group">
        <div class="checkbox-item2">
          <input
            id="permitA"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis A"
          />
          <label for="permitA">Permis A</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitB"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis B"
          />
          <label for="permitB">Permis B</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitC"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis C"
          />
          <label for="permitC">Permis C</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitD"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis D"
          />
          <label for="permitD">Permis D</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitL"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis L"
          />
          <label for="permitL">Permis L</label>
        </div>
        <div class="checkbox-item2">
          <input
            id="permitG"
            v-model="checkedPermits"
            class="custom-checkbox custom-input"
            type="checkbox"
            value="Permis G"
          />
          <label for="permitG">Permis G</label>
        </div>
      </div>

      <p class="adjust-margin CVText">Possédez-vous un véhicule personnel ?</p>
      <div class="radio-group">
        <div id="radio-item-left" class="radio-item">
          <input id="vehicleYes" v-model="picked" type="radio" value="Oui" />
          <label for="vehicleYes">Oui</label>
        </div>
        <div class="radio-item">
          <input id="vehicleNo" v-model="picked" type="radio" value="Non" />
          <label for="vehicleNo">Non</label>
        </div>
      </div>
      <p v-if="errorMessage" id="alert-msg04" class="alert-msg">
        {{ errorMessage }}
      </p>
      <SubmitComponent @go-back="goBack" />
    </form>
  </div>
</template>

<script>
import SubmitComponent from './submit';
export default {
  name: 'FormThree',
  components: {
    SubmitComponent,
  },
  emits: ['situation-submitted', 'go-back'],
  data() {
    return {
      checkedSkills: [],
      checkedPermits: [],
      picked: null,
      errorMessage: '',
    };
  },
  methods: {
    onSubmit() {
      this.errorMessage = '';
      if (this.picked === null) {
        this.errorMessage =
          "L'évaluation est incomplète. Veuillez remplir tous les champs.";
        return;
      }
      let situationReview = {
        permits: this.checkedPermits,
        vehicle: this.picked,
        Skills: this.checkedSkills,
      };
      //console.log(this.checkedPermits);
      this.$emit('situation-submitted', situationReview);
      this.checkedPermits = [];
      this.picked = null;
      //console.log('skills', this.checkedSkills);
      this.checkedSkills = [];
    },
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.checkbox-item {
  /*display: flex;
  align-items: center;*/
  margin-bottom: 0.5rem;
}

.checkbox-item input {
  margin-right: 5px;
}

.checkbox-item label {
  font-family: 'Telegraf', sans-serif;
  font-size: 0.75rem;
  display: inline-block;
  vertical-align: middle;
  padding-left: 20px;
  text-indent: -1.3rem;
}
.checkbox-item2 label {
  font-family: 'Telegraf', sans-serif;
}

.checkbox-item label::before {
  content: '';
  display: inline-block;
  width: 20px;
}
</style>
