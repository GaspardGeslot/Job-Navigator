<template>
  <div class="formBlockLayout">
    <h3 class="formTitle" style="margin-top: 0.5rem; margin-bottom: 0.5rem">
      Candidature<br />envoyée 🚀
    </h3>
    <div v-if="matchResponse && matchResponse >= 1" class="matchResponse-txt">
      Superbe nouvelle ! Nous avons déjà identifié
      <span v-if="matchResponse == 1"
        >{{ matchResponse }} entreprise qui correspond à ton profil et avons
        partagé tes informations avec elle.</span
      ><span v-else
        >{{ matchResponse }} entreprises qui correspondent à ton profil et avons
        partagé tes informations avec elles.</span
      >
    </div>
    <p class="formSubTitle">
      Pensez à valider votre demande en cliquant sur le lien qui a vous a été
      envoyé par mail.
    </p>
    <p class="formSubTitle">
      Et, parce que vous avez certainement des amis ou des proches qui cherchent
      un emploi, n’hésitez pas à leur partager ce bon plan. Ils vous
      remercieront !
    </p>
    <br />
    <div class="loginContainer">
      <p class="formSubTitle">
        Vous souhaitez compléter encore plus votre profil et voir les
        entreprises qui recrutent ?
      </p>
      <button
        class="loginButton"
        type="submit"
        v-on:click="$emit('login')"
        style="margin-top: 1rem"
      >
        Se connecter
      </button>
    </div>
    <div class="col">
      <p id="share-msg">SHARE THIS:</p>
      <div class="row" style="align-items: flex-start">
        <div class="col" style="width: 55%">
          <div class="icon-row">
            <svg
              style="margin-right: 0.25rem"
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M23 6V18C23 19.6523 21.6523 21 20 21H4C2.34772 21 1 19.6523 1 18V6C1 4.34315 2.34315 3 4 3H20C21.6569 3 23 4.34315 23 6ZM3.10658 5.55395C3.27196 5.22692 3.61204 5 4 5H20C20.388 5 20.728 5.22692 20.8934 5.55395L12 11.7793L3.10658 5.55395ZM3 7.92066L10.8531 13.4178C11.5417 13.8999 12.4583 13.8999 13.1469 13.4178L21 7.92066V18C21 18.5477 20.5477 19 20 19H4C3.45228 19 3 18.5477 3 18V7.92066Z"
                fill="black"
              />
            </svg>
            <img
              src="https://img.icons8.com/?size=100&id=uLWV5A9vXIPu&format=png&color=000000"
              style="max-width: 17%; height: auto"
            />
            <img
              src="https://img.icons8.com/?size=100&id=62152&format=png&color=000000"
              style="max-width: 16%; height: auto"
            />
            <img
              src="https://img.icons8.com/?size=100&id=63676&format=png&color=000000"
              style="max-width: 17%; height: auto"
            />
            <img
              src="https://img.icons8.com/?size=100&id=16713&format=png&color=000000"
              style="max-width: 20%; height: auto"
            />
            <svg
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M5 3C5 2.44772 5.44772 2 6 2H15C15.2652 2 15.5196 2.10536 15.7071 2.29289L18.7071 5.29289C18.8946 5.48043 19 5.73478 19 6V8H20C21.6569 8 23 9.34315 23 11V16C23 17.1046 22.1046 18 21 18H19V21C19 21.5523 18.5523 22 18 22H6C5.44772 22 5 21.5523 5 21V18H3C1.89543 18 1 17.1046 1 16V11C1 9.34315 2.34315 8 4 8H5V3ZM4 10C3.44772 10 3 10.4477 3 11V16H5V14C5 13.4477 5.44772 13 6 13H18C18.5523 13 19 13.4477 19 14V16H21V11C21 10.4477 20.5523 10 20 10H4ZM17 8H7V4H14.5858L17 6.41421V8ZM7 15V20H17V15H7Z"
                fill="black"
              />
            </svg>
          </div>
          <button class="buttons svg-button">
            <div class="svg-container">
              <svg
                class="vertical-svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M18.5858 7L16.2929 9.29289C15.9024 9.68342 15.9024 10.3166 16.2929 10.7071C16.6834 11.0976 17.3166 11.0976 17.7071 10.7071L21.7071 6.70711C22.0976 6.31658 22.0976 5.68342 21.7071 5.29289L17.7071 1.29289C17.3166 0.902369 16.6834 0.902369 16.2929 1.29289C15.9024 1.68342 15.9024 2.31658 16.2929 2.70711L18.5858 5H7C5.67392 5 4.40215 5.52678 3.46447 6.46447C2.52678 7.40215 2 8.67392 2 10V11C2 11.5523 2.44772 12 3 12C3.55228 12 4 11.5523 4 11V10C4 9.20435 4.31607 8.44129 4.87868 7.87868C5.44129 7.31607 6.20435 7 7 7H18.5858Z"
                  fill="red"
                />
                <path
                  d="M7.70711 14.7071C8.09763 14.3166 8.09763 13.6834 7.70711 13.2929C7.31658 12.9024 6.68342 12.9024 6.29289 13.2929L2.29289 17.2929C1.90237 17.6834 1.90237 18.3166 2.29289 18.7071L6.29289 22.7071C6.68342 23.0976 7.31658 23.0976 7.70711 22.7071C8.09763 22.3166 8.09763 21.6834 7.70711 21.2929L5.41421 19H17C18.3261 19 19.5979 18.4732 20.5355 17.5355C21.4732 16.5979 22 15.3261 22 14V13C22 12.4477 21.5523 12 21 12C20.4477 12 20 12.4477 20 13V14C20 14.7956 19.6839 15.5587 19.1213 16.1213C18.5587 16.6839 17.7956 17 17 17H5.41421L7.70711 14.7071Z"
                  fill="red"
                />
              </svg>
            </div>
            Reblog
          </button>
        </div>
        <div
          id="more-social-block"
          class="col"
          style="width: 45%; position: relative"
        >
          <button
            class="buttons"
            style="margin-top: 0.25rem; width: 4rem; gap: 4px"
            @click="toggleWindow"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              x="0px"
              y="0px"
              width="16"
              height="16"
              viewBox="0 0 30 30"
            >
              <path
                d="M 23 3 A 4 4 0 0 0 19 7 A 4 4 0 0 0 19.09375 7.8359375 L 10.011719 12.376953 A 4 4 0 0 0 7 11 A 4 4 0 0 0 3 15 A 4 4 0 0 0 7 19 A 4 4 0 0 0 10.013672 17.625 L 19.089844 22.164062 A 4 4 0 0 0 19 23 A 4 4 0 0 0 23 27 A 4 4 0 0 0 27 23 A 4 4 0 0 0 23 19 A 4 4 0 0 0 19.986328 20.375 L 10.910156 15.835938 A 4 4 0 0 0 11 15 A 4 4 0 0 0 10.90625 14.166016 L 19.988281 9.625 A 4 4 0 0 0 23 11 A 4 4 0 0 0 27 7 A 4 4 0 0 0 23 3 z"
              ></path>
            </svg>
            More
          </button>
          <div v-if="showWindow" class="social-media-window row">
            <img
              class="social-media-window-elements"
              src="https://img.icons8.com/?size=100&id=xuvGCOXi8Wyg&format=png&color=000000"
              style="width: 24px; height: 24px"
            />
            <img
              class="social-media-window-elements"
              src="https://img.icons8.com/?size=100&id=8weyx4k1jpJy&format=png&color=000000"
              style="width: 18px; height: 18px"
            />
            <img
              class="social-media-window-elements"
              src="https://img.icons8.com/?size=100&id=phOKFKYpe00C&format=png&color=000000"
              style="width: 21px; height: 21px"
            />
            <img
              class="social-media-window-elements"
              src="https://img.icons8.com/?size=100&id=63204&format=png&color=000000"
              style="width: 22px; height: 22px"
            />
            <img
              id="social-media-last-element"
              class="social-media-window-elements"
              src="https://img.icons8.com/?size=100&id=13963&format=png&color=000000"
              style="width: 24px; height: 24px"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormSeven',
  props: {
    matchResponse: {
      type: Number,
      required: false,
      default: null,
    },
  },
  data() {
    return {
      showWindow: false,
    };
  },
  mounted() {
    console.log('MatchResponse in FormSeven:', this.matchResponse);
  },
  methods: {
    toggleWindow() {
      this.showWindow = !this.showWindow;
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.col {
  display: none;
  /* display: flex;
  flex-direction: column; */
}
.row {
  display: flex;
  flex-direction: row;
}
.formSubTitle,
.matchResponse-txt {
  font-family: 'Telegraf', sans-serif;
  font-size: 0.75rem;
  line-height: 1rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}
#share-msg {
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  font-size: 0.75rem;
  line-height: 1rem;
  color: grey;
  font-family: 'Telegraf', sans-serif;
  font-weight: 500;
}
.icon-row {
  display: flex;
  flex-direction: row;
  align-items: center;
  /*justify-content: center;*/
}
.buttons {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0.5px solid #000;
  border-radius: 0.25rem;
  background-color: white;
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
  gap: 4px;
  font-weight: 500;
}
.svg-button {
  margin-top: 0.25rem;
  width: 4.5rem;
}
.svg-button:hover {
  background-color: rgb(244, 244, 244);
  cursor: pointer;
}
.svg-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.vertical-svg {
  transform: rotate(90deg);
}
.social-media-window {
  align-items: center;
  justify-content: center;
  gap: 4px;
  position: absolute;
  bottom: 0;
  top: 100%;
  left: 48%;
  transform: translateX(-50%);
  background-color: white;
  border: 1px solid grey;
  padding: 1rem;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
  border-radius: 0.25rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  z-index: 1;
  width: 80%;
  margin-top: 0.5rem;
}
.loginButton {
  height: 3rem;
  width: 45%;
  font-size: large;
  font-family: 'Telegraf', sans-serif;
  font-weight: 600;
  cursor: pointer;
  border: none;
  background-color: black;
  color: white;
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  border-radius: 1.5rem;
}
.loginButton:hover {
  background-color: #e54021;
  color: white;
}
.loginContainer {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
}
@media screen and (max-width: 350px) {
  .social-media-window {
    /* padding-left: 0.75rem;
    padding-right: 0.75rem; */
    gap: 0px;
  }
  .social-media-window-elements {
    transform: scale(0.8);
  }
}
@media screen and (max-width: 450px) {
  .social-media-window {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
  #social-media-last-element {
    display: none;
  }
}
@media screen and (max-width: 800px) {
  #more-social-block {
    padding-left: 0.5rem;
  }
}
@media screen and (min-width: 551px) and (max-width: 800px) {
  .social-media-window {
    padding-left: 1.25rem;
    padding-right: 1.25rem;
  }
}
</style>
