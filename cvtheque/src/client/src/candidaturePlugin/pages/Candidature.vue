<template>
  <!-- <div class="VA_bg_img blank" :class="{faded: formVisible}">
    <div id="page-content" class="col remove">
      <div class="mt space-b-to-center top-row row">
        <div class="mr space-b row">
          <img
            id="olecio-logo"
            class="mr-2 mt-05 top-row-logo"
            src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/olecio_logo_white.png"
            style="margin-top: 0.82rem"
          />
          <img
            id="constructys-logo"
            class="ml-6 mb-05 top-row-logo"
            src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/constructys_logo2.png"
            style="margin-bottom: 0.82rem"
          />
        </div>
        <div>
          <button class="connexion_button" @click="navigateToLogin">
            CONNEXION
          </button>
        </div>
      </div>
      <div class="row title-container">
        <h1 class="text-white main-title">
          Avec ou sans CV, trouvez un emploi dans le secteur de la construction.
        </h1>
      </div>
      <div class="row">
        <button v-show="!formVisible" class="bot_button" @click="showForm">
          JE POSTULE
        </button>
      </div>
    </div>
    <div v-if="formVisible" class="formContainer">
      <formComponent
        :options="options"
        class="formComponent"
        @close-form="hideForm"
      />
      
      <button
        class="exit-button"
        style="
          position: absolute;
          top: 10px;
          right: 10px;
          background-color: transparent;
          border: none;
        "
        @click="hideForm"
      >
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M4.70711 3.29289C4.31658 2.90237 3.68342 2.90237 3.29289 3.29289C2.90237 3.68342 2.90237 4.31658 3.29289 4.70711L10.5858 12L3.29289 19.2929C2.90237 19.6834 2.90237 20.3166 3.29289 20.7071C3.68342 21.0976 4.31658 21.0976 4.70711 20.7071L12 13.4142L19.2929 20.7071C19.6834 21.0976 20.3166 21.0976 20.7071 20.7071C21.0976 20.3166 21.0976 19.6834 20.7071 19.2929L13.4142 12L20.7071 4.70711C21.0976 4.31658 21.0976 3.68342 20.7071 3.29289C20.3166 2.90237 19.6834 2.90237 19.2929 3.29289L12 10.5858L4.70711 3.29289Z"
            fill="white"
          />
        </svg>
      </button>
      
    </div>
  </div> -->
  <div id="accueil">
    <div v-if="formVisible" class="formContainer">
      <formComponent
        :options="options"
        class="formComponent"
        @close-form="hideForm"
      />
    </div>
    <div class="accueilGauche">
      <div class="accueilLogo">
        <div class="jobNavigatorLogo">
          <img
            src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/JobNavigatorLogo.png"
            alt="logoJobNavigator"
          />
          <p>Job Navigator</p>
        </div>
        <img
          id="logoConstructys"
          src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/Constructys_Logo.jpg"
          alt="Logo Constructys"
        />
      </div>
      <div class="accueilBouton">
        <h1>Simplifions le recrutement !</h1>
        <button @click="navigateToLoginCompany">Espace entreprise</button>
        <button @click="navigateToLogin">Espace candidat</button>
        <button @click="showForm">Candidater</button>
      </div>
    </div>
    <div class="accueilDroite">
      <div class="accueilNav">
        <a href="#comment">Comment ça marche ?</a>
        <a href="#aPropos">A propos</a>
      </div>
      <video controls src=""></video>
    </div>
  </div>

  <div id="comment">
    <div class="commentGauche">
      <h1>Comment ça marche ?</h1>
      <p>
        D’un côté, des centaines d’entreprises partenaires déposent leurs offres
        d’emploi sur Job Navigator.
      </p>

      <p>De l’autre, les internautes postulent en ligne, avec ou sans CV.</p>

      <p>
        Puis, la magie opère en faisant “matcher” candidats & employeurs qui
        n’ont plus qu’à faire connaissance.
      </p>
      <div class="commentBouton">
        <button @click="navigateToLoginCompany">Espace Entreprises</button>
        <button @click="navigateToLogin">Espace Candidats</button>
      </div>
    </div>
    <img
      src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/home/image_puzzle.png"
      alt="image random"
    />
  </div>
  <div id="aPropos">
    <img
      src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/home/image_women.png"
      alt="Image random"
    />
    <div class="aProposDroite">
      <h1>A propos</h1>
      <p>
        Olecio met à disposition des adhérents de Constructys Ile-de-France Job
        Navigator, un outil de gestion et suivi des candidatures pensé pour
        faciliter le recrutement dans les secteurs en tension.
      </p>
      <div class="aProposBouton">
        <button @click="navigateToLoginCompany">Je suis une entreprise</button>
        <button @click="navigateToLogin">Je suis un candidat</button>
      </div>
    </div>
  </div>
</template>

<script>
import formComponent from '../components/formComponent.vue';
//import {urlFor} from '@ohrm/core/util/helper/url';
import {navigate} from '@ohrm/core/util/helper/navigation';

export default {
  name: 'Candidature',
  components: {
    formComponent,
  },
  props: {
    options: {
      type: Object,
      default: () => null,
    },
  },
  data() {
    return {
      formVisible: false,
    };
  },
  mounted() {
    if (window.location.hash === '#apply') {
      this.formVisible = true;
    }
  },
  methods: {
    showForm() {
      console.log('options :', this.options);
      this.formVisible = true;
    },
    hideForm() {
      this.formVisible = false;
    },
    navigateToLogin() {
      navigate('/auth/login');
    },
    navigateToLoginCompany() {
      navigate('/auth/company/login');
    },
  },
};
</script>

<style src="./view-application.scss" lang="scss"></style>
<style scoped>
.title-container,
#olecio-logo {
  display: none;
}

.formContainer {
  z-index: 10;
}

.exit-button {
  position: relative;
  top: 10px;
  right: 10px;
  background-color: transparent;
  border: none;
  cursor: pointer;
}

#accueil {
  display: flex;
  flex-direction: row;
  height: 100vh;
  background-color: #1b1f23;
  color: white;
}

.accueilLogo {
  display: flex;
  flex-direction: row;
  height: 20%;
  gap: 4rem;
}

.jobNavigatorLogo {
  display: flex;
  flex-direction: row;
  margin: 2rem;
  gap: 1rem;
  align-items: center;
  color: #f7e28f;
}

#logoConstructys {
  border-bottom-left-radius: 20%;
  border-bottom-right-radius: 20%;
}

.accueilGauche {
  display: flex;
  flex-direction: column;
  width: 50%;
  height: 100%;
}

.accueilBouton {
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  margin: 3rem 4rem;
  gap: 1rem;
  height: 100%;
}

.accueilBouton button {
  height: 3rem;
  width: 60%;
  font-size: large;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.accueilBouton button:hover {
  background-color: #e54021;
  color: white;
}

.accueilDroite {
  display: flex;
  flex-direction: column;
  width: 50%;
}

.accueilNav {
  display: flex;
  flex-direction: row;
  align-self: end;
  padding-right: 4rem;
}

.accueilNav a {
  margin: 1rem;
  color: inherit;
  text-decoration: none;
}

.accueilDroite video {
  padding: 2rem;
  background-color: white;
  border-radius: 10%;
  margin-right: 4rem;
  margin-top: 1rem;
  height: 70%;
}

#comment {
  height: 100vh;
  background-color: #f4f1ed;
  display: flex;
  flex-direction: row;
  width: 100%;
}

.commentGauche,
.aProposDroite {
  width: 50%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  padding: 4rem;
  justify-content: center;
}

.commentBouton,
.aProposBouton {
  display: flex;
  flex-direction: row;
  gap: 1rem;
}

.commentBouton button,
.aProposBouton button {
  height: 3rem;
  width: 45%;
  font-size: large;
  font-weight: 600;
  cursor: pointer;
  border: none;
  background-color: #e54021;
  color: white;
}

.commentBouton button:hover,
.aProposBouton button:hover {
  background-color: white;
  color: black;
}

#comment img,
#aPropos img {
  margin: 8rem;
  width: 30%;
}

#aPropos {
  height: 100vh;
  background-color: black;
  color: white;
  display: flex;
  flex-direction: row;
}

/* Responsivité pour les écrans de taille moyenne (tablettes) */
@media (max-width: 900px) {
  #accueil,
  #comment {
    flex-direction: column;
    height: auto;
  }

  .accueilGauche {
    width: 100%;
  }

  .accueilLogo {
    gap: 0;
    height: 5rem;
    justify-content: space-evenly;
  }

  .accueilBouton {
    width: 100%;
    margin: 0rem;
    padding: 2rem;
    align-items: center;
    text-align: center;
    height: auto;
  }

  .jobNavigatorLogo {
    margin: 2rem 0;
  }

  #logoConstructys {
    width: auto;
    max-width: 100%;
    height: auto;
    max-height: 100%;
    object-fit: contain;
  }

  .accueilNav {
    display: none;
  }

  .accueilDroite {
    width: 100%;
  }

  .accueilDroite video {
    padding: 2rem;
    border-radius: 5rem;
    margin: 2rem;
    height: 70%;
  }

  #aPropos {
    height: auto;
    flex-direction: column-reverse;
  }

  .commentGauche,
  .aProposDroite {
    box-sizing: border-box;
    width: 100%;
    gap: 2rem;
    padding: 2rem;
    text-align: center;
  }

  .commentBouton,
  .aProposBouton {
    display: flex;
    flex-direction: row;
    gap: 1rem;
    width: 100%;
    justify-content: center;
  }

  #comment img,
  #aPropos img {
    margin: 1rem;
    width: 40%;
    align-self: center;
  }
}

@media screen and (max-width: 461px) {
  .exit-button {
    display: none;
  }
}
</style>
