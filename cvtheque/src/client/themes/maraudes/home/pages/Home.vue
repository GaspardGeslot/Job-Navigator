<template>
  <div class="page">
    <!-- Navbar fixe -->
    <nav class="navbar">
      <div class="navbar-container">
        <div class="navbar-logo">
          <img
            id="logoJobNavigatorHomepage"
            src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/job_navigator_banner_white.png"
            alt="logoJobNavigator"
          />
        </div>
        <div class="navbar-nav desktop-nav">
          <a href="#steps">Comment ça marche ?</a>
          <a href="#banner">À propos</a>
          <a style="cursor: pointer" @click="navigateToCGU">Mentions légales</a>
        </div>
      </div>
    </nav>

    <section id="hero" class="hero">
      <div class="badge">Maraudes numériques • Olecio</div>
      <div class="hero__content">
        <div class="hero__text">
          <h1>
            Facilitons la (ré)insertion des personnes éloignées de l'emploi avec
            les maraudes numériques
          </h1>
          <p>
            Olecio met en place des maraudes numériques pour repérer, engager et
            orienter les jeunes et les adultes éloignés de l'emploi vers les
            partenaires qui pourront le mieux répondre à leur besoin
            d'accompagnement.
          </p>
          <div class="actions">
            <button class="btn-primary" @click="navigateToLogin">
              Mon espace
            </button>
          </div>
          <div class="highlights">
            <div class="pill">Repérage terrain & digital</div>
            <div class="pill">Orientation vers les partenaires locaux</div>
            <div class="pill">Suivi des parcours en continu</div>
          </div>
        </div>
        <div class="hero__visual">
          <div class="card">
            <h3>Votre accompagnement, simplifié</h3>
            <ul class="card__list">
              <li>Centralisez les situations repérées en maraude</li>
              <li>Partagez en sécurité avec vos partenaires</li>
              <li>Suivez les mises en relation et l’avancement</li>
            </ul>
            <button class="btn-secondary" @click="navigateToLogin">
              Accéder à mon espace
            </button>
          </div>
        </div>
      </div>
    </section>

    <section id="steps" class="section steps">
      <div class="section__header">
        <h2>Une démarche en 3 étapes</h2>
        <p>Pour repérer, orienter et accompagner durablement les publics.</p>
      </div>
      <div class="grid">
        <div class="card step">
          <div class="step__icon">1</div>
          <h3>Repérage</h3>
          <p>
            Collecte des situations lors des maraudes physiques et digitales.
          </p>
        </div>
        <div class="card step">
          <div class="step__icon">2</div>
          <h3>Orientation</h3>
          <p>Transmission rapide aux partenaires régionaux les plus adaptés.</p>
        </div>
        <div class="card step">
          <div class="step__icon">3</div>
          <h3>Suivi</h3>
          <p>
            Suivi collaboratif des parcours pour sécuriser la (ré)insertion.
          </p>
        </div>
      </div>
    </section>

    <section id="banner" class="section banner">
      <div class="banner__text">
        <h2>Une plateforme pensée pour vos équipes de terrain</h2>
        <p>
          Accédez à un espace unique pour partager vos remontées, coordonner les
          mises en relation et suivre l'impact de vos maraudes numériques.
        </p>
      </div>
      <button class="btn-primary" @click="navigateToLogin">Mon espace</button>
    </section>

    <!-- Footer -->
    <footer id="footer" class="footer">
      <div class="mobile-nav">
        <a href="#steps">Comment ça marche ?</a>
        <a href="#banner">À propos</a>
        <a style="cursor: pointer" @click="navigateToCGU">Mentions légales</a>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import {buildThemeUrl} from '@/core/util/helper/url';

const navigateToLogin = () => {
  window.location.href = buildThemeUrl('/auth/login');
};

const navigateToCGU = () => {
  window.location.href = buildThemeUrl('/cgu/index');
};
</script>

<style scoped lang="scss">
:global(body) {
  margin: 0;
  font-family: 'DM Sans', 'Inter', system-ui, -apple-system, sans-serif;
}

.page {
  display: flex;
  flex-direction: column;
  gap: 3rem;
  background: linear-gradient(180deg, #f9f7ff 0%, #f4f8ff 100%);
  color: #111;
}

/* Navbar styles */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1001;
  background: linear-gradient(135deg, #312e81 0%, #4f46e5 100%);
  padding: 1rem 0;
  box-shadow: 0 4px 20px rgba(49, 46, 129, 0.2);
}

.navbar-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 clamp(1.25rem, 3vw, 3rem);
}

.navbar-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.navbar-logo img {
  height: 2.5rem;
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
  font-size: 0.95rem;
  transition: color 0.3s ease;
}

.navbar-nav a:hover {
  color: rgba(255, 255, 255, 0.8);
}

.mobile-nav {
  display: none;
}

.hero {
  padding: 6rem clamp(1.25rem, 3vw, 3rem) 1rem;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: rgba(106, 90, 205, 0.12);
  color: #4b3fb3;
  padding: 0.5rem 0.9rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.01em;
  text-transform: uppercase;
}

.hero__content {
  margin-top: 1.5rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: clamp(1.5rem, 3vw, 3rem);
  align-items: stretch;
}

.hero__text h1 {
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.2;
  margin: 0 0 1rem 0;
  color: #0f172a;
}

.hero__text p {
  margin: 0 0 1.25rem 0;
  color: #334155;
  font-size: 1.05rem;
  line-height: 1.6;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.btn-primary,
.btn-secondary {
  border: none;
  cursor: pointer;
  font-weight: 700;
  border-radius: 12px;
  padding: 0.85rem 1.35rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #7c3aed, #4f46e5);
  color: #fff;
  box-shadow: 0 10px 30px rgba(79, 70, 229, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 14px 34px rgba(79, 70, 229, 0.3);
}

.btn-secondary {
  background: #fff;
  color: #4b3fb3;
  border: 1px solid rgba(75, 63, 179, 0.15);
}

.btn-secondary:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
}

.highlights {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.pill {
  padding: 0.55rem 0.9rem;
  border-radius: 999px;
  background: rgba(79, 70, 229, 0.08);
  color: #312e81;
  font-weight: 600;
  font-size: 0.95rem;
}

.hero__visual {
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
}

@media (min-width: 1024px) {
  .hero {
    padding-left: clamp(1.25rem, 3vw, 3rem);
    padding-right: clamp(1.25rem, 3vw, 3rem);
  }

  .hero__content {
    grid-template-columns: 1.15fr 0.85fr;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
  }

  .hero__visual {
    position: sticky;
    top: 110px;
    max-width: 420px;
    margin-left: auto;
    align-self: start;
  }

  .hero__visual .card {
    width: 100%;
  }
}

.card {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
  border: 1px solid rgba(15, 23, 42, 0.06);
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  min-height: 100%;
}

.card h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #0f172a;
}

.card__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  flex: 1;
}

.card li {
  position: relative;
  padding-left: 1.6rem;
  color: #334155;
  line-height: 1.5;
}

.card li::before {
  content: '•';
  position: absolute;
  left: 0.35rem;
  color: #7c3aed;
  font-weight: 900;
}

.card .btn-secondary {
  margin-top: auto;
}

.section {
  padding: 0 clamp(1.25rem, 3vw, 3rem);
}

.steps {
  padding-bottom: 2.5rem;
}

.section__header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.section__header h2 {
  margin: 0;
  font-size: clamp(1.8rem, 3vw, 2.3rem);
  color: #0f172a;
}

.section__header p {
  margin: 0.6rem 0 0 0;
  color: #475569;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
}

.step {
  text-align: left;
}

.step__icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #7c3aed, #4f46e5);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  margin-bottom: 0.75rem;
}

.step h3 {
  margin: 0 0 0.4rem 0;
  color: #0f172a;
}

.step p {
  margin: 0;
  color: #475569;
  line-height: 1.5;
}

.banner {
  margin: 0 clamp(1.25rem, 3vw, 3rem) 3rem;
  background: linear-gradient(120deg, #312e81 0%, #4f46e5 60%, #7c3aed 100%);
  color: #fff;
  border-radius: 18px;
  padding: clamp(1.5rem, 4vw, 2.5rem);
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 18px 40px rgba(49, 46, 129, 0.35);
}

.banner__text {
  max-width: 700px;
}

.banner h2 {
  margin: 0 0 0.5rem 0;
  font-size: clamp(1.6rem, 3vw, 2rem);
}

.banner p {
  margin: 0;
  color: rgba(255, 255, 255, 0.88);
  line-height: 1.6;
}

@media (max-width: 768px) {
  .hero {
    padding-top: 3rem;
  }

  .hero__content {
    grid-template-columns: 1fr;
  }

  .card {
    padding: 1.25rem;
  }

  .banner {
    margin: 0 1rem 2.5rem;
  }

  .grid {
    gap: 3rem;
  }
}

/* Footer styles */
.footer {
  display: none;
}

@media (max-width: 768px) {
  .navbar-container {
    padding: 0 1rem;
  }

  .navbar-logo img {
    height: 2rem;
  }

  .desktop-nav {
    display: none;
  }

  .hero {
    padding-top: 5rem;
    margin-top: 3.5rem;
  }

  .hero__visual {
    position: static;
    justify-content: flex-start;
  }

  .footer {
    display: block;
    background-color: #1b1f23;
    padding: 2rem 1rem;
    margin-top: 3rem;
  }

  .footer .mobile-nav {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .footer .mobile-nav a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
  }
}

@media (max-width: 540px) {
  .actions {
    width: 100%;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
    text-align: center;
  }

  .highlights {
    flex-direction: column;
    align-items: flex-start;
  }

  .banner {
    flex-direction: column;
    align-items: flex-start;
  }

  .navbar-logo img {
    height: 1.75rem;
  }
}
</style>
