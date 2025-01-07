<template>
  <div class="formBlock row" style="height: auto">
    <div v-if="isLoading" class="loading-circle-container">
      <div class="loading-circle"></div>
    </div>
    <div
      ref="scrollContainer"
      class="scroll-container"
      :class="{'scroll-active': needsScroll}"
    >
      <!--
      <button
        class="exit-button"
        style="
          position: absolute;
          top: 10px;
          right: 10px;
          background-color: transparent;
          border: none;
        "
        @click="closeForm"
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
            fill="black"
          />
        </svg>
      </button>
      -->
      <div ref="scrollContent" class="scroll-content">
        <FormOne
          v-show="currentStep === 1"
          ref="formOne"
          :course-starts="options.courseStarts"
          :needs="options.needs"
          :study-levels="options.studyLevels"
          :professional-xp="options.professionalExperiences"
          :mobilities="options.mobilities"
          @situation-submitted="addReview"
        />
        <FormTwo
          v-show="currentStep === 2"
          ref="formTwo"
          :sectors="options.sectors"
          @go-back="previousStep"
          @situation-submitted="addReview"
          @skip-form-three="addReviewSkip"
        />
        <FormThree
          v-show="currentStep === 3"
          ref="formThree"
          :driving-licenses="options.drivingLicenses"
          @go-back="previousStep"
          @situation-submitted="addReview"
        />
        <FormFour
          v-show="currentStep === 9"
          ref="formFour"
          @situation-submitted="addReview"
        />
        <FormFive
          v-show="currentStep === 4"
          ref="formFive"
          @go-back="previousStep"
          @situation-submitted="addReview"
        />
        <FormSix
          v-show="currentStep === 5"
          ref="formSix"
          :cannot-create-account="cannotCreateAccount"
          @send-lead="nextStep"
          @go-back="previousStep"
          @situation-submitted="addReview"
        />
        <FormSeven
          v-show="currentStep === 6"
          ref="formSix"
          :match-response="matchResponse"
          @login="$emit('login')"
        />
        <!--
          <FormEight
          v-show="currentStep === 9"
          ref="formEight"
          @go-back="previousStep"
          @situation-submitted="addReview"
        />
        <FormNine
          v-show="currentStep === 8"
          ref="formNine"
          @situation-submitted="addReview"
        />
        <p v-if="cannotCreateAccount" class="alert-msg">
          La création de compte a échoué. Veuillez réessayer.
        </p>
        <h1>{{ cannotCreateAccount }}</h1>
        -->
      </div>
    </div>
    <img
      ref="formImg"
      class="formImg"
      src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/formImg.png"
    />
    <button class="closeButton" @click="$emit('close-form')">
      <svg
        class="closeIcon"
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
  <!--<ReviewList :reviews="reviews" />-->
</template>

<script>
import {ref, onMounted, onBeforeUnmount, nextTick} from 'vue';
//import axios from 'axios';
import FormOne from './formBlock1';
import FormTwo from './formBlock2';
import FormThree from './formBlock3';
import FormFour from './formBlock4';
import FormFive from './formBlock5';
import FormSix from './formBlock6';
import FormSeven from './formBlock7';
// import FormEight from './formBlock8';
// import FormNine from './formBlock9';
import {v4 as uuidv4} from 'uuid';
//import axios from 'axios';
import {APIService} from '@/core/util/services/api.service';
//import ReviewList from './review_list';

export default {
  name: 'FormComponent',
  components: {
    FormOne,
    FormTwo,
    FormThree,
    FormFour,
    FormFive,
    FormSix,
    FormSeven,
    // FormEight,
    // FormNine,
    //ReviewList,
  },
  props: {
    options: {
      type: Object,
      default: () => null,
    },
  },
  emits: ['close-form'],
  setup() {
    const cannotCreateAccount = ref(false);
    const currentStep = ref(1);
    const highestStep = ref(1);
    const sessionId = ref(null);

    const reviews = ref([]);
    const needsScroll = ref(false);
    const matchResponse = ref(0);

    const scrollContainer = ref(null);
    const scrollContent = ref(null);
    const formImg = ref(null);

    const isLoading = ref(false);

    const createSession = async () => {
      sessionId.value = uuidv4();
      const now = new Date();
      now.setHours(now.getHours() + 2);
      const date = now.toISOString();
      const data = {
        step: currentStep.value,
        createdAt: date,
        sessionId: sessionId.value,
      };

      //console.log('data from createSession', data);

      try {
        const http = new APIService(
          window.appGlobal.baseUrl,
          '/events/push/form-session',
        );
        await http.create(data);
        // const response = await http.create(data);
        // console.log('Session created:', sessionId.value);
        // console.log('Response data:', response);
      } catch (error) {
        console.error('Error creating session:', error);
      }
    };

    const updateHighestStep = async () => {
      if (currentStep.value > highestStep.value) {
        highestStep.value = currentStep.value;
        //console.log('highestStep', highestStep.value);

        const data = {
          sessionId: sessionId.value,
          step: highestStep.value,
        };

        try {
          const http = new APIService(
            window.appGlobal.baseUrl,
            '/events/push/form-session',
          );
          // console.log('Données envoyées pour la mise à jour:', {
          //   sessionId: sessionId.value,
          //   step: highestStep.value,
          // });
          await http.update(sessionId.value, data);
          // const response = await http.update(sessionId.value, data);
          // console.log('Session updated:', response);
        } catch (error) {
          console.error('Error updating session:', error);
        }
      }
    };

    const combineData = (dataArray) => {
      return dataArray.reduce((acc, item) => {
        for (const key in item) {
          if (Object.prototype.hasOwnProperty.call(item, key)) {
            if (item[key] instanceof File) {
              acc[key] = item[key];
            } else {
              acc[key] = item[key];
            }
          }
        }
        return acc;
      }, {});
    };
    const checkScroll = () => {
      const activeForm = scrollContent.value.querySelector(
        `[ref="form${currentStep.value}"]`,
      );
      const activeFormHeight = activeForm
        ? activeForm.getBoundingClientRect().height
        : 0;
      const imageHeight = formImg.value
        ? formImg.value.getBoundingClientRect().height
        : 0;

      // console.log('activeForm height:', activeFormHeight);
      // console.log('image height:', imageHeight);
      if (scrollContainer.value && formImg.value && imageHeight != 0) {
        scrollContainer.value.style.maxHeight = `${imageHeight}px`;
        scrollContainer.value.style.height = `${imageHeight}px`;
      } else {
        scrollContainer.value.style.maxHeight = `500px`;
      }
      needsScroll.value = activeFormHeight > imageHeight;
    };

    const onImageLoad = () => {
      checkScroll();
    };

    onMounted(() => {
      const imgElement = formImg.value;
      if (imgElement.complete) {
        checkScroll();
      } else {
        imgElement.addEventListener('load', onImageLoad);
      }
      createSession();
    });

    onBeforeUnmount(() => {
      const imgElement = formImg.value;
      if (imgElement) {
        imgElement.removeEventListener('load', onImageLoad);
      }
    });

    const addReviewSkip = (review) => {
      //reviews.value.push(review);
      reviews.value[currentStep.value - 1] = review;
      skipNextStep();
    };
    const addReview = (review) => {
      //reviews.value.push(review);
      reviews.value[currentStep.value - 1] = review;
      nextStep();
    };

    const skipNextStep = () => {
      currentStep.value += 2;
      nextTick(() => {
        setTimeout(() => {
          checkScroll();
        }, 50);
      });
    };
    const nextStep = async () => {
      cannotCreateAccount.value = false;
      if (currentStep.value == 5) {
        const dataToProcess = reviews.value.slice(0, 5);
        const combinedData = combineData(dataToProcess);
        // console.log(
        //   'Array.isArray(combinedData[key])',
        //   Array.isArray(combinedData['file']),
        // );
        // console.log('combinedData', combinedData);
        // delete combinedData.checkedEXP;
        if (combinedData.file == null) {
          delete combinedData.file;
        }
        delete combinedData.fileName;

        const formData = new FormData();
        if (combinedData.password == '') delete combinedData.password;
        for (const key in combinedData) {
          if (Object.prototype.hasOwnProperty.call(combinedData, key)) {
            if (Array.isArray(combinedData[key])) {
              // Sérialiser les tableaux en JSON
              formData.append(key, JSON.stringify(combinedData[key]));
            } else if (key === 'file' && combinedData[key].base64) {
              formData.append(key, JSON.stringify(combinedData[key]));
            }
            // else if (key === 'file' && combinedData[key] instanceof File) {
            //   console.log('on entre dans la condition de file');
            //   formData.append(key, combinedData[key]);
            // }
            else {
              formData.append(key, combinedData[key]);
            }
          }
        }

        if (formData.has('password')) {
          try {
            //console.log('password here', formData.get('password'));
            const http = new APIService(
              window.appGlobal.baseUrl,
              '/candidature/account',
            );
            // console.log('FormData content before submission:', [
            //   ...formData.entries(),
            // ]);

            await http.create(formData);
            // const response = await http.create(formData);
            // console.log('RESPONSE HERE !!!! ', response);

            // Si la création de compte réussit, passez à la création de lead
            await createLead(formData);
          } catch (error) {
            console.error(
              'Error:',
              error.response ? error.response.data : error.message,
            );
            // Si la création de compte échoue, ne passez pas à la création de lead
            cannotCreateAccount.value = true;
            return;
          }
        } else {
          //console.log('no need to create account');
          // Si aucun mot de passe n'est présent, passez directement à la création de lead
          await createLead(formData);
        }
      }

      currentStep.value++;
      updateHighestStep();
      nextTick(() => {
        setTimeout(() => {
          checkScroll();
          scrollContainer.value.scrollTo(0, 0);
        }, 50);
      });
    };

    async function createLead(formData) {
      const leadHttp = new APIService(
        window.appGlobal.baseUrl,
        '/candidature/lead',
      );
      // console.log('FormData content before submission:', [
      //   ...formData.entries(),
      // ]);

      try {
        isLoading.value = true;
        const leadResponse = await leadHttp.create(formData);
        // console.log('Lead RESPONSE HERE !!!! ', leadResponse);
        matchResponse.value = leadResponse.data.MatchResponse;
        // console.log('matchResponse.value in formComp', matchResponse.value);
        isLoading.value = false;
      } catch (error) {
        console.error(
          'Error:',
          error.response ? error.response.data : error.message,
        );
      }
    }

    const previousStep = () => {
      //console.log('currentStep before = ', currentStep);
      currentStep.value--;
      //console.log('currentStep after = ', currentStep);
      nextTick(() => {
        setTimeout(() => {
          checkScroll();
          scrollContainer.value.scrollTo(0, 0);
        }, 50);
      });
    };

    onMounted(() => {
      checkScroll();
    });

    return {
      cannotCreateAccount,
      currentStep,
      highestStep,
      sessionId,
      reviews,
      needsScroll,
      matchResponse,
      scrollContainer,
      scrollContent,
      formImg,
      addReview,
      addReviewSkip,
      skipNextStep,
      nextStep,
      previousStep,
      isLoading,
    };
  },
  methods: {
    closeForm() {
      this.$emit('close-form');
    },
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
.loading-circle-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.3);
  z-index: 9999;
}
.loading-circle {
  width: 50px;
  height: 50px;
  border: 5px solid rgba(0, 0, 0, 0.2);
  border-top: 5px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.formImg,
.scroll-container {
  border-radius: 1rem;
}
.formBlock {
  display: flex;
  align-items: center;
  position: relative;
  margin: 0;
  padding: 0;
  width: 75%;
}
.closeButton {
  position: absolute;
  right: 0px;
  top: 0px;
  padding: 10px;
  cursor: pointer;
  border: none;
  background-color: transparent;
  font-weight: bold;
  font-size: 1.5rem;
  border-top-right-radius: 1rem;
}

.closeButton:hover {
  background-color: red;
}

/* @media screen and (max-width: 460px) {
  .formBlock {
    margin-top: 13rem;
  }
} */
@media screen and (max-width: 450px) {
  .formBlock {
    width: calc(100% - 2rem);
    margin-left: 1rem;
    margin-right: 1rem;
  }
  .scroll-container {
    width: 100%;
  }
  .formImg {
    background-image: none;
  }
  .closeButton {
    background-color: black;
    border-top-right-radius: 0rem;
    border-bottom-left-radius: 1rem;
    padding: 5px;
  }
}
@media screen and (min-width: 461px) {
  .exit-button {
    display: none;
  }
}
@media screen and (max-width: 550px) {
  .formImg {
    display: none;
  }
}
</style>
