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
        @login="navigateToLogin"
      />
    </div>
    <div class="accueilGauche">
      <div class="accueilLogo">
        <div class="jobNavigatorLogo">
          <img
            id="logoJobNavigatorHomepage"
            src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/job_navigator_banner_white.png"
            alt="logoJobNavigator"
          />
        </div>
        <img
          id="logoConstructys"
          src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/Constructys_Logo.jpg"
          alt="Logo Constructys"
        />
      </div>
      <div class="accueilBouton">
        <h1>Simplifions le recrutement !</h1>
        <div class="accueilBoutonLogins">
          <button @click="navigateToLogin">Espace candidat</button>
          <button @click="navigateToLoginCompany">Espace entreprise</button>
        </div>
        <button @click="showForm">Je postule</button>
      </div>
    </div>
    <div class="accueilDroite">
      <div class="accueilNav">
        <a href="#comment">Comment ça marche ?</a>
        <a href="#aPropos">A propos</a>
        <a @click="navigateToCGU" style="cursor: pointer">Mentions légales</a>
      </div>
      <div class="accueilDroiteVideo">
        <video
          controls
          src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/home/constructys_video.mp4"
        ></video>
      </div>
    </div>
  </div>

  <div id="comment">
    <div class="commentGauche">
      <h1>Découvrez Job Navigator : votre passerelle vers l’emploi !</h1>
      <p>
        <b>Entreprises partenaires :</b> Publiez vos besoins en recrutement et
        attirez les meilleurs profils au sein de votre structure.
      </p>
      <p>
        <b>Candidats :</b> Postulez en ligne, avec ou sans CV, et laissez notre
        plateforme faire le reste.
      </p>
      <p>
        <b>Job Navigator</b> se charge de connecter les candidats et les
        employeurs pour des rencontres professionnelles réussies.
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
      style="width: 20%"
    />
    <div class="aProposDroite">
      <h1>A propos</h1>
      <p>
        Afin de soutenir l’emploi régional et de dynamiser la filière BTP en
        Île-de-France, l’État, les Fédérations et Confédérations
        professionnelles du Bâtiment et des Travaux Publics, ainsi que
        l’Opérateur de Compétences de la Construction, Constructys, ont
        collaboré avec le prestataire Olécio pour développer une CVthèque
        opérationnelle.
      </p>
      <p>
        Cette initiative répond à un enjeu majeur : accompagner les entreprises
        du BTP dans leur recrutement et faciliter la mobilité professionnelle
        dans un secteur marqué par des tensions en matière de main-d’œuvre.
      </p>
      <p>
        <b>Une solution simple, rapide et efficace</b><br /><br />
        La CVthèque offre :<br /><br />
        &emsp; &#8226;&emsp;Une gestion intuitive des besoins en recrutement des
        entreprises <br /><br />
        &emsp; &#8226;&emsp;Un espace dédié au dépôt et à la gestion des
        candidatures
      </p>
      <p><b>Les atouts de la CVthèque</b></p>
      <p>
        &emsp; &#8226;&emsp;<b>Un sourcing garanti :</b> un flux de 1 200
        candidatures qualifiées chaque année, pendant 3 ans <br /><br />
        &emsp; &#8226;&emsp;<b>Un matching intelligent :</b> un algorithme
        performant basé sur 6 critères clés* pour rapprocher efficacement les
        profils des besoins des entreprises<br /><br />
        &emsp; &#8226;&emsp;<b>Un gain de temps :</b> les candidatures
        correspondant aux attentes des entreprises sont envoyées automatiquement
        et immédiatement<br />
      </p>
      <p>
        *Critères pris en compte : type de contrat recherché, disponibilité,
        niveau d’études, expérience professionnelle, métiers visés et permis.
      </p>
      <p>
        Ensemble, contribuons à renforcer l’emploi dans le secteur du BTP en
        Île-de-France grâce à cette solution numérique dédiée à la mise en
        relation des candidats et des employeurs.
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
      //console.log('options :', this.options);
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
    navigateToCGU() {
      navigate('/cgu/index');
    },
  },
};
</script>

<style src="./view-application.scss" lang="scss"></style>
<style scoped>
* {
  font-family: 'DM Sans', sans-serif;
}

h1 {
  font-size: 4rem;
}
#aPropos p {
  font-size: 1.3rem;
  margin-bottom: 1rem;
  line-height: 150%;
}

#comment p {
  font-size: 1.3rem;
  margin-bottom: 0.5rem;
}

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
}

#accueil h1,
#accueil a {
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
  margin: 0rem 4rem;
  gap: 2rem;
  height: 100%;
  align-items: center;
}

.accueilBoutonLogins {
  box-sizing: border-box;
  display: flex;
  flex-direction: row;
  gap: 2rem;
  width: 100%;
}

.accueilBouton button {
  height: 3rem;
  width: 60%;
  font-size: large;
  font-weight: 600;
  cursor: pointer;
  border: none;
  border-radius: 1rem;
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

.accueilDroiteVideo {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  justify-content: center;
}

.accueilNav {
  display: flex;
  flex-direction: row;
  align-self: end;
  padding-right: 5rem;
  height: 4rem;
  align-items: center;
  font-weight: bold;
}

.accueilNav a {
  margin: 1rem;
  color: inherit;
  text-decoration: none;
}

.accueilDroite video {
  padding: 2rem;
  background-color: white;
  border-radius: 3rem;
  margin-right: 4rem;
  height: auto;
}

#comment {
  height: 100vh;
  background-color: #f4f1ed;
  display: flex;
  flex-direction: row;
  width: 100%;
}

.commentGauche {
  box-sizing: border-box;
  width: 60%;
  display: flex;
  flex-direction: column;
  padding: 4rem;
  justify-content: center;
}

.aProposDroite {
  box-sizing: border-box;
  width: 70%;
  display: flex;
  flex-direction: column;
  padding: 4rem;
  justify-content: center;
}

.commentGauche h1,
.aProposDroite h1 {
  font-size: 2.5rem;
}

.commentBouton,
.aProposBouton {
  display: flex;
  flex-direction: row;
  gap: 1rem;
  margin-top: 1rem;
}

.commentBouton button,
.aProposBouton button {
  height: 3rem;
  width: 45%;
  font-size: large;
  font-weight: 600;
  cursor: pointer;
  border: none;
  border-radius: 1rem;
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
  margin: auto;
  width: auto;
  height: 80%;
}

#aPropos {
  background-color: black;
  color: white;
  display: flex;
  flex-direction: row;
}
#logoJobNavigatorHomepage {
  margin-top: 0.5rem;
  height: auto;
  width: 20rem;
}

/* Responsivité pour les écrans de taille moyenne (tablettes) */
@media (max-width: 900px) {
  #accueil,
  #comment {
    flex-direction: column;
    height: auto;
  }

  h1 {
    font-size: 2.5rem;
    text-align: center;
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
  .accueilBoutonLogin {
    width: 100%;
    margin: 0rem;
    padding: 2rem;
    align-items: center;
    text-align: center;
    height: auto;
  }

  .accueilBoutonLogins {
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    align-items: center;
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
    border-radius: 3rem;
    margin: 2rem;
    padding: 1rem;
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
    visibility: collapse;
  }
}

@media screen and (max-width: 461px) {
  .exit-button {
    display: none;
  }
  #logoJobNavigatorHomepage {
    margin-top: 0.75rem;
    height: auto;
    width: 10rem;
  }
}
</style>
