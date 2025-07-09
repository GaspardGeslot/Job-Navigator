<template>
  <div id="accueil">
    <div v-if="formVisible" class="formContainer">
      <formComponent
        :options="options"
        :utm-source="utmSrc"
        :utm-medium="utmMed"
        :utm-campaign="utmCamp"
        class="formComponent"
        @close-form="hideForm"
        @login="navigateToLogin"
      />
    </div>

    <!-- Navbar fixe transparente -->
    <nav class="navbar">
      <div class="navbar-container">
        <div class="navbar-logo">
          <img
            id="logoJobNavigatorHomepage"
            src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/job_navigator_banner_white.png"
            alt="logoJobNavigator"
          />
          <a href="https://www.vigiebtp-idf.fr/" style="cursor: pointer">
            <img
              id="logoConstructys"
              src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/Logo_VigieBtp.svg"
              alt="Logo Constructys"
            />
          </a>
        </div>
        <div class="navbar-nav desktop-nav">
          <a href="#comment">Comment ça marche ?</a>
          <a href="#aPropos">A propos</a>
          <a @click="navigateToCGU" style="cursor: pointer">Mentions légales</a>
        </div>
      </div>
    </nav>

    <!-- Contenu principal -->
    <div class="hero-section">
      <div class="hero-content">
        <div class="hero-left">
          <div class="hero-text">
            <h1>Simplifions le recrutement</h1>
            <div class="hero-buttons">
              <button @click="navigateToLogin">Espace candidat</button>
              <button @click="navigateToLoginCompany">Espace entreprise</button>
            </div>
            <button class="cta-button" @click="showForm">Je postule</button>
          </div>
        </div>
        <div class="hero-right">
          <div class="hero-video">
            <video
              controls
              src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/home/constructys_video.mp4"
            ></video>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="comment">
    <div class="commentGauche">
      <h1>Découvrez Job Navigator : votre passerelle vers l'emploi</h1>
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
        Afin de soutenir l'emploi régional et de dynamiser la filière BTP en
        Île-de-France, l'État, les Fédérations et Confédérations
        professionnelles du Bâtiment et des Travaux Publics, ainsi que
        l'Opérateur de Compétences de la Construction, Constructys, ont
        collaboré avec le prestataire Olécio pour développer une CVthèque
        opérationnelle.
      </p>
      <p>
        Cette initiative répond à un enjeu majeur : accompagner les entreprises
        du BTP dans leur recrutement et faciliter la mobilité professionnelle
        dans un secteur marqué par des tensions en matière de main-d'œuvre.
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
        <b>
          Ensemble, contribuons à renforcer l'emploi dans le secteur du BTP en
          Île-de-France grâce à cette solution numérique dédiée à la mise en
          relation des candidats et des employeurs.
        </b>
      </p>
      <div class="aProposBouton">
        <button @click="navigateToLoginCompany">Espace Entreprises</button>
        <button @click="navigateToLogin">Espace Candidats</button>
      </div>
      <p style="font-size: 1rem">
        <i>
          *Critères pris en compte : type de contrat recherché, disponibilité,
          niveau d'études, expérience professionnelle, métiers visés et permis.
        </i>
      </p>
    </div>
  </div>
  <div id="footer">
    <div class="mobile-nav">
      <a href="#comment">Comment ça marche ?</a>
      <a href="#aPropos">A propos</a>
      <a @click="navigateToCGU" style="cursor: pointer">Mentions légales</a>
    </div>
  </div>
</template>

<script>
import formComponent from '../components/formComponent.vue';
//import {urlFor} from '@ohrm/core/util/helper/url';
import {navigate} from '@/core/util/helper/navigation';

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
      utmSrc: '',
      utmMed: '',
      utmCamp: '',
      formVisible: false,
    };
  },
  mounted() {
    this.getUTMParameters();
    if (window.location.href.includes('#apply')) {
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
      navigate(`/${window.appGlobal.theme}/auth/login`);
    },
    navigateToLoginCompany() {
      navigate(`/${window.appGlobal.theme}/auth/company/login`);
    },
    buildUrlWithUtm(baseUrl) {
      const params = new URLSearchParams();
      if (this.utmSrc) params.append('utm_source', this.utmSrc);
      if (this.utmMed) params.append('utm_medium', this.utmMed);
      if (this.utmCamp) params.append('utm_campaign', this.utmCamp);
      const queryString = params.toString();
      return queryString ? `${baseUrl}?${queryString}` : baseUrl;
    },
    navigateToCGU() {
      const baseUrl = `/${window.appGlobal.theme}/cgu/index`;
      navigate(this.buildUrlWithUtm(baseUrl));
    },
    getUTMParameters() {
      // Récupère la partie après le hash (#)
      const hashPart = window.location.hash;
      let utmSource = null;
      let utmMedium = null;
      let utmCampaign = null;

      // Vérifie d'abord les paramètres dans l'URL principale
      const urlParams = new URLSearchParams(window.location.search);
      utmSource = urlParams.get('utm_source');
      utmMedium = urlParams.get('utm_medium');
      utmCampaign = urlParams.get('utm_campaign');

      // Si pas de paramètres dans l'URL principale, cherche dans la partie hash
      if (hashPart.includes('?')) {
        // Extrait les paramètres après le hash (#apply?utm_source=...)
        const hashParams = new URLSearchParams(hashPart.split('?')[1]);
        // Récupère les paramètres s'ils n'ont pas été trouvés dans l'URL principale
        if (!utmSource) {
          utmSource = hashParams.get('utm_source');
        }
        if (!utmMedium) {
          utmMedium = hashParams.get('utm_medium');
        }
        if (!utmCampaign) {
          utmCampaign = hashParams.get('utm_campaign');
        }
      }

      // Stocke les valeurs si elles existent
      if (utmSource) {
        this.utmSrc = utmSource;
      }
      if (utmMedium) {
        this.utmMed = utmMedium;
      }
      if (utmCampaign) {
        this.utmCamp = utmCampaign;
      }
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
#footer a {
  color: black;
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
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 7rem;
  overflow-y: auto;
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
  height: 100vh;
  background-image: url('https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/vigiebtp_home_background.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
}

#accueil::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(43, 47, 99, 0.88);
  z-index: 1;
}

/* Navbar styles */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1001;
  background-color: rgba(43, 47, 99, 1);
  padding: 1rem 0;
}

.navbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.navbar-logo {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.navbar-logo img {
  height: 3rem;
  width: auto;
  object-fit: contain;
}

.navbar-nav {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.navbar-nav a {
  color: white;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.navbar-nav a:hover {
  color: #f18700;
}

/* Hero section styles */
.hero-section {
  height: 100vh;
  display: flex;
  align-items: center;
  position: relative;
  z-index: 2;
}

.hero-content {
  display: flex;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 5rem 2rem;
  gap: 4rem;
}

.hero-left {
  flex: 1;
  display: flex;
  align-items: center;
}

.hero-text {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  align-items: flex-start;
}

.hero-text h1 {
  color: white;
  font-size: 4rem;
  margin: 0;
}

.hero-buttons {
  display: flex;
  gap: 2rem;
  width: 100%;
}

.hero-buttons button {
  height: 3rem;
  flex: 1;
  font-size: large;
  font-weight: 600;
  cursor: pointer;
  border: none;
  border-radius: 1rem;
  background-color: white;
  color: rgba(43, 47, 99, 1);
  transition: all 0.3s ease;
}

.hero-buttons button:hover {
  background-color: transparent;
  color: white;
  border: 2px solid white;
}

.cta-button {
  height: 3rem;
  width: 100%;
  font-size: large;
  font-weight: 600;
  cursor: pointer;
  border: 0px solid transparent;
  border-radius: 1rem;
  background-image: linear-gradient(270deg, #f18700, #d0491a);
  color: white;
  transition: all 0.3s ease;
}

.cta-button:hover {
  background-image: none;
  background-color: transparent;
  position: relative;
  border: none;
}

.cta-button:hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 1rem;
  border: 2px solid transparent;
  background: linear-gradient(270deg, #f18700, #d0491a) border-box;
  -webkit-mask: linear-gradient(#3e298a 0 0) padding-box,
    linear-gradient(#3e298a 0 0);
  -webkit-mask-composite: destination-out;
  mask-composite: exclude;
}

.hero-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero-video {
  width: 100%;
  max-width: 500px;
}

.hero-video video {
  width: 100%;
  height: auto;
  border-radius: 2rem;
  background-color: white;
  padding: 1rem;
}

#comment {
  height: 100vh;
  background-color: white;
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
  border: 0px solid transparent;
  border-radius: 1rem;
  background-image: linear-gradient(270deg, #f18700, #d0491a);
  color: white;
  transition: all 0.3s ease;
}

.commentBouton button:hover {
  color: black;
}
.commentBouton button:hover,
.aProposBouton button:hover {
  background-image: none;
  background-color: transparent;
  position: relative;
  border: none;
}

.commentBouton button:hover::before,
.aProposBouton button:hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 1rem;
  border: 2px solid transparent;
  background: linear-gradient(270deg, #f18700, #d0491a) border-box;
  -webkit-mask: linear-gradient(#3e298a 0 0) padding-box,
    linear-gradient(#3e298a 0 0);
  -webkit-mask-composite: destination-out;
  mask-composite: exclude;
}

#comment img,
#aPropos img {
  margin: auto;
  width: auto;
  height: 80%;
  border-radius: 2rem;
  background-color: white;
  padding: 1rem;
}

#aPropos {
  background-color: rgba(43, 47, 99, 0.88);
  color: white;
  display: flex;
  flex-direction: row;
}
#footer {
  background-color: #1b1f23;
  visibility: collapse;
  padding: 0rem;
}

.mobile-nav {
  display: none;
}

.navbar-logo img {
  margin-top: 0.5rem;
  height: auto;
  width: 10rem;
}

@media (min-width: 901px) {
  #footer a {
    font-size: 0px;
  }
}
/* Responsivité pour les écrans de taille moyenne (tablettes) */
@media (max-width: 900px) {
  #accueil {
    height: auto;
    min-height: 100vh;
  }

  .navbar-container {
    padding: 0 1rem;
  }

  .navbar-logo {
    gap: 1rem;
  }

  .navbar-logo img {
    height: 2.5rem;
  }

  .desktop-nav {
    display: none;
  }

  .hero-section {
    height: auto;
    min-height: calc(100vh - 5rem);
  }

  .hero-content {
    flex-direction: column;
    gap: 2rem;
    padding: 5rem 1rem;
  }

  .hero-text {
    align-items: center;
    text-align: center;
  }

  .hero-text h1 {
    font-size: 2.5rem;
  }

  .hero-buttons {
    flex-direction: column;
    width: 100%;
    max-width: 300px;
  }

  .hero-buttons button {
    width: 100%;
    flex: none;
  }

  .cta-button {
    width: 100%;
    max-width: 300px;
  }

  .hero-video {
    max-width: 100%;
  }

  .hero-video video {
    border-radius: 1.5rem;
    padding: 0.5rem;
  }

  #comment,
  #aPropos {
    height: auto;
    flex-direction: column;
  }

  .commentGauche,
  .aProposDroite {
    box-sizing: border-box;
    width: 100%;
    gap: 2rem;
    padding: 2rem;
  }

  .commentBouton,
  .aProposBouton {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    max-width: 300px;
    margin: 0 auto;
  }

  .commentBouton button,
  .aProposBouton button {
    width: 100%;
  }

  #comment img,
  #aPropos img {
    margin: 1rem;
    width: 40%;
    align-self: center;
    visibility: collapse;
  }

  #footer {
    visibility: visible;
    background-color: white;
    color: black;
    display: flex;
    box-sizing: border-box;
    flex-direction: column;
    padding: 2rem;
    justify-content: center;
    text-align: center;
  }

  /* Ajouter les liens de navigation dans le footer pour mobile */
  #footer::before {
    content: '';
    display: block;
    margin-bottom: 1rem;
  }

  #footer .mobile-nav {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  #footer .mobile-nav a {
    color: black;
    text-decoration: none;
    font-weight: 600;
  }
}

@media screen and (max-width: 461px) {
  .exit-button {
    display: none;
  }

  .navbar-logo img {
    height: 2rem;
  }

  .hero-text h1 {
    font-size: 2rem;
  }

  .hero-buttons {
    max-width: 250px;
  }

  .cta-button {
    max-width: 250px;
  }

  .hero-video video {
    border-radius: 1rem;
    padding: 0.25rem;
  }
}
</style>
