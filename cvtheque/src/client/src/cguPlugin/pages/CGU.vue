<!--eslint-disable-->
<template>
  <div id="accueil">
    <div class="accueilGauche">
      <div class="accueilLogo">
        <div class="jobNavigatorLogo">
          <img
            id="logoJobNavigatorHomepage"
            src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/job_navigator_banner_white.png"
            alt="logoJobNavigator"
            @click="navigateToHome"
            style="cursor: pointer"
          />
        </div>
        <img
          id="logoConstructys"
          src="https://jobnavigator-cdn.fra1.cdn.digitaloceanspaces.com/prod/logo/Constructys_Logo.jpg"
          alt="Logo Constructys"
        />
      </div>
    </div>
  </div>
  <div id="content">
    <div class="commentGauche">
      <h1>Mentions légales & politique de confidentialité des données</h1>
    </div>
    <div class="subtitle">
      <h5>Date de mise à jour : 28/01/2025</h5>
    </div>
    <div class="orangehrm-card-container" style="margin: 1rem">
      <oxd-text tag="h2" class="orangehrm-main-title">
        Le site
        <a @click="navigateToHome" style="color: blue; cursor: pointer">www.jobnavigator.fr</a>
        (appelé ci-après Job Navigator) est édité et vous est proposé par
        Olecio, une société par actions simplifiée (SAS), inscrite au registre
        du commerce et des sociétés Paris B 895 367 258, au capital social de 2
        000 €, dont le siège social est situé au 22 rue de la Clef, 75005 PARIS.
        Job Navigator est exploité par Olecio pour le compte de Constructys.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        En accédant au site, vous acceptez expressément et sans réserve les
        conditions définies ci-après : toute visite du site doit se faire dans
        le respect des présentes conditions.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Activités principales de Job Navigator :
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-sub-title"> Pour les candidats : </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Postuler à des offres d'emploi dans le secteur du
        BTP.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Gérer leurs informations personnelles.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Recevoir des notifications concernant les offres correspondant à leurs critères.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-sub-title"> Pour les entreprises : </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Déposer leurs besoins en recrutement.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Gérer leurs informations personnelles.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Accéder aux profils des candidats correspondant à leurs besoins.
      </oxd-text>

      <br />
      <oxd-text tag="h2" class="cgu-main-title">
        Directeur de publication :
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Sébastien LEVY-PRUDENT – Président et co-fondateur d’Olecio Adresse du siège social : 22bis, rue de la Clef – 75005 Paris
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Contact : <a href="mailto:contact@olecio.fr" style="color: blue">contact@olecio.fr</a> ; +33 7 49 51 58 32
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Hébergement :
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Digital Ocean
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Adresse : 01 6th Avenue, New York, NY 10013, États-Unis
      </oxd-text>
      <br />
      
      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-header">
        Politique de protection des données personnelles et cookies
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="orangehrm-main-title">
        La présente Politique de protection des données à caractère personnel définit les principes et lignes directrices mis en œuvre par Olecio pour Job Navigator en matière de traitement des données personnelles des utilisateurs.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        En utilisant le site, vous acceptez et reconnaissez avoir lu cette politique, nous permettant ainsi de collecter et de traiter vos données conformément aux lois applicables (Loi n°78-17 du 6 janvier 1978, RGPD du 25 mai 2018).
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Olecio s’engage à respecter les principes suivants concernant vos données :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Traitement licite, loyal et transparent.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Collecte pour des finalités déterminées, explicites et légitimes.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Conservation limitée aux finalités nécessaires.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Sécurité, confidentialité et intégrité des données.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Délégué à la Protection des Données (DPO) :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Pour toute question ou demande relative à vos données, contactez notre DPO à
        <a href="mailto:dpo@olecio.fr" style="color: blue">dpo@olecio.fr</a>.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Catégories de données collectées :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Données d’identification (nom, prénom, âge, statut d’emploi, etc.).
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Données de contact (email, lieu d’habitation).
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Données de candidature (CV, compétences).
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Données de connexion (adresse IP, pages consultées, etc.).
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Finalités du traitement des données :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Les données collectées sont utilisées pour :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Répondre à vos demandes d’information.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Personnaliser votre navigation.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Transmettre des communications et gérer vos demandes.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Collecter des données statistiques pour améliorer le service.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Transmettre aux entreprises publiant une annonce dans votre domaine de recherche les élements pour prendre connaissance de votre candidature et vous contacter, à savoir :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Type de contrat recherché
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Code postal
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Disponibilité
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Niveau d'études
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Métiers qui vous intéresse
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Possession ou non dun véhicule personnel
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Nom
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Prénom
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Mail
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Téléphone
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Mobilité géographique
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Expérience professionnelle
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Expérience dans le BTP
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Permis obtenus
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Motivation
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;CV
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Conservation des données :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Les données personnelles sont conservées pour la durée strictement nécessaire aux finalités définies, conformément à la législation en vigueur.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Droits des utilisateurs :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation, d’opposition et de portabilité de vos données. Ces droits peuvent être exercés en contactant le DPO par email ou par courrier :
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        <a href="mailto:dpo@olecio.fr" style="color: blue">dpo@olecio.fr</a>
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Adresse : 22bis, rue de la Clef – 75005 Paris
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Vous pouvez également introduire une réclamation auprès de la CNIL (<a href="mailto:www.cnil.fr" style="color: blue">www.cnil.fr</a>).
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Cookies :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Job Navigator utilise des cookies pour améliorer votre expérience utilisateur. Deux types de cookies sont utilisés :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Cookies fonctionnels : nécessaires pour le fonctionnement du site, notamment pour mémoriser vos identifiants (durée de 13 mois).
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Cookies d’analyse : utilisés pour collecter des statistiques sur la navigation et améliorer les services.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Vous pouvez gérer vos préférences de cookies à tout moment via le module de gestion dédié ou les paramètres de votre navigateur.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Modification ou suppression des données
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        A tout moment, candidats et entreprises peuvent modifier les données les concernant ou supprimer leurs comptes (et leurs informations personnelles) depuis leur espace personnel. Une demande de suppression peut également être adressée au DPO.
      </oxd-text>
      <br />

      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-header">
        Conditions Générales D'utilisation
      </oxd-text>
      <br />
      
      <oxd-text tag="h2" class="orangehrm-main-title">
        Job Navigator est exploité par Olecio pour le compte de Constructys.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        1. Objet du Site
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Les présentes Conditions Générales d’Utilisation (ci-après « CGU ») régissent les rapports entre Olecio SAS (ci-après dénommé « Olecio »), société de droit français, située au 22bis rue de la Clef, 75005 Paris, éditeur du site Job Navigator, d’une part, et vous, ci-après dénommé les Utilisateurs, d’autre part.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Le site <a @click="navigateToHome" style="color: blue; cursor: pointer">www.jobnavigator.fr</a> (ci-après le « Site ») a été élaboré pour :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Les candidats : rechercher des offres d’emploi dans le secteur du BTP, postuler à ces offres, gérer leurs informations personnelles et recevoir des alertes pour des offres correspondant à leurs demandes.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Les entreprises : déposer leurs besoins en recrutement, gérer leurs informations personnelles et consulter les profils des candidats correspondant à leurs critères.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Avant toute utilisation du Site, les Visiteurs et Utilisateurs sont invités à lire attentivement les présentes CGU ainsi que la Politique de Protection des Données.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Olecio se réserve le droit de modifier ou mettre à jour ces CGU sans préavis.
      </oxd-text>
      <br />

      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-title">
        2. Création d’un compte utilisateur
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Pour accéder à l’ensemble des fonctionnalités proposées par le Site, l’Utilisateur doit créer un compte personnel.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="cgu-sub-title">
        2.1 Création du compte
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Pour créer un compte, l’Utilisateur doit :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Remplir le formulaire d’inscription en ligne comprenant : 
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Champs obligatoires :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Type de contrat recherché
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Code postal
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Disponibilité
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Niveau d'études
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Choisir de 1 à 3 métiers
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Possède un véhicule personnel
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Nom
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Prénom
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Mail
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Téléphone
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp; &#8226;&emsp;Champs facultatifs :  
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Mobilité géographique
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Expérience professionnelle
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Expérience dans le BTP
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Permis obtenus
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Motivation
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;CV
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp;&emsp;&emsp;&emsp; &#8226;&emsp;Mot de passe pour créer un compte
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Accepter les présentes CGU en cochant la case prévue à cet effet.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Indiquer s’il souhaite recevoir des communications de la part de Job Navigator et/ou de ses partenaires.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Valider son inscription en cliquant sur « Valider ».
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Une fois l’inscription terminée, un email de confirmation sera envoyé à l’Utilisateur.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Engagement de l’Utilisateur :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        L’Utilisateur garantit l’exactitude des informations fournies lors de l’inscription et s’engage à les tenir à jour.      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-sub-title">
        2.2 Gestion du mot de passe
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        L’Utilisateur est responsable de la confidentialité de son mot de passe. En cas de perte, un lien de réinitialisation est disponible via l’option « J’ai oublié mon mot de passe ».
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Responsabilité :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Olecio décline toute responsabilité en cas d’accès frauduleux au compte résultant d’une négligence de l’Utilisateur (partage du mot de passe, etc.).
      </oxd-text>
      <br />

      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-title">
        3. Propriété intellectuelle
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Le Site et son contenu (textes, images, vidéos, logos, etc.) sont protégés par les lois sur la propriété intellectuelle. Toute utilisation, reproduction ou distribution sans autorisation écrite d’Olecio est strictement interdite.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Interdictions :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Copier, reproduire, distribuer ou modifier tout ou partie du Site sans autorisation.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Utiliser le Site ou son contenu à des fins commerciales ou frauduleuses.
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        L’utilisation du Site est strictement personnelle et ne confère aucun droit de propriété intellectuelle sur le contenu.
      </oxd-text>
      <br />

      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-title">
        4. Protection des données personnelles
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Olecio est le responsable du traitement des données personnelles collectées via le Site. Ces données sont traitées conformément au RGPD (Règlement Général pour la Protection des Données) et à la loi française « Informatique et Libertés ».
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Pour en savoir plus, veuillez consulter notre Politique de Protection des Données.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        Pour toute demande relative à vos données personnelles, contactez notre Délégué à la Protection des Données (DPO) :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Email : dpo@olecio.fr
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Adresse : 22bis rue de la Clef, 75005 Paris
      </oxd-text>
      <br />

      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-title">
        5. Responsabilité
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Olecio s’efforce de fournir des informations fiables sur le Site, mais ne garantit pas leur exactitude, leur exhaustivité ou leur pertinence.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Limitation de responsabilité :
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Olecio ne peut être tenu responsable de tout préjudice direct ou indirect lié à l’utilisation du Site (altération de données, virus, panne technique, etc.).
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        &emsp; &#8226;&emsp;Olecio décline toute responsabilité pour les contenus accessibles via des liens hypertextes renvoyant à des sites tiers.
      </oxd-text>
      <br />

      <oxd-text tag="h2" class="cgu-main-title">
        Utilisation du Site :
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        L’Utilisateur est responsable de l’utilisation qu’il fait du Site et des précautions nécessaires pour protéger ses données et son matériel contre d’éventuels logiciels malveillants.
      </oxd-text>
      <br />

      <oxd-divider />
      <oxd-text tag="h2" class="cgu-main-title">
        6. Droit applicable et litiges
      </oxd-text>
      <oxd-text tag="h2" class="orangehrm-main-title">
        Les présentes CGU sont régies par le droit français.
      </oxd-text>
      <br />
      <oxd-text tag="h2" class="orangehrm-main-title">
        En cas de litige, les parties s’efforceront de trouver une solution amiable. À défaut, le litige sera soumis à la compétence exclusive des tribunaux français compétents.
      </oxd-text>
      <br />
    </div>
  </div>
</template>

<script>
//import {urlFor} from '@ohrm/core/util/helper/url';
import {navigate} from '@ohrm/core/util/helper/navigation';

export default {
  name: 'Candidature',
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
    navigateToHome() {
      navigate('/candidature/index');
    },
  },
};
</script>

<style src="./view-application.scss" lang="scss"></style>
<style scoped>
* {
  font-family: 'DM Sans', sans-serif;
}

h1,
h5 {
  text-align: center;
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
  height: 22vh;
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
  height: 500px;
}

.accueilBouton {
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  margin: 0rem 4rem;
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
  padding-right: 5rem;
  height: 10rem;
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

#content {
  padding: 25px 5px;
  /*height: 25vh;
  display: flex;
  flex-direction: column;
  width: 100%;*/
}

.commentGauche,
.subtitle {
  box-sizing: border-box;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

#logoJobNavigatorHomepage {
  margin-top: 0.5rem;
  height: auto;
  width: 20rem;
}

/* Responsivité pour les écrans de taille moyenne (tablettes) */
@media (max-width: 900px) {
  #accueil {
    flex-direction: column;
    height: 15vh;
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
    border-radius: 3rem;
    margin: 2rem;
    padding: 1rem;
  }

  #aPropos {
    height: auto;
    flex-direction: column-reverse;
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

.cgu-main-header {
  font-size: 25px !important;
  font-weight: 1000 !important;
}

.cgu-main-title {
  font-size: 20px !important;
  font-weight: 800 !important;
}

.cgu-sub-title {
  font-size: 18px !important;
  font-weight: 800 !important;
  padding: 0.25rem 0 !important;
}
</style>
