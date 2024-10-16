<template>
  <div class="formBlock row" style="max-width: 900px">
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
          @go-back="previousStep"
          @situation-submitted="addReview"
        />
        <FormSeven v-show="currentStep === 6" ref="formSix" />
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
      </div>
    </div>
    <img
      ref="formImg"
      class="formImg"
      src="https://oleciocdn.fra1.cdn.digitaloceanspaces.com/prod/cvtheque/formImg.png"
    />
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
import FormEight from './formBlock8';
import FormNine from './formBlock9';
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
    FormEight,
    FormNine,
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
    const currentStep = ref(1);
    const reviews = ref([]);
    const needsScroll = ref(false);

    const scrollContainer = ref(null);
    const scrollContent = ref(null);
    const formImg = ref(null);

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

      console.log('activeForm height:', activeFormHeight);
      console.log('image height:', imageHeight);
      if (scrollContainer.value && formImg.value && imageHeight != 0) {
        scrollContainer.value.style.maxHeight = `${imageHeight}px`;
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
      if (currentStep.value == 5) {
        const dataToProcess = reviews.value.slice(0, 5);
        //console.log('Données extraites :', dataToProcess);
        const combinedData = combineData(dataToProcess);
        //console.log('combinedData.Xp : ', combinedData.Xp);
        //console.log('combinedData.checkedEXP : ', combinedData.checkedEXP);
        delete combinedData.checkedEXP;
        if (combinedData.file == null) {
          delete combinedData.file;
        }
        //delete combinedData.file;
        delete combinedData.fileName;
        //console.log('Données prêtes pour POST :', combinedData);

        const formData = new FormData();

        for (const key in combinedData) {
          if (Object.prototype.hasOwnProperty.call(combinedData, key)) {
            if (Array.isArray(combinedData[key])) {
              // Sérialiser les tableaux en JSON
              formData.append(key, JSON.stringify(combinedData[key]));
              // console.log(
              //   `${key} added to FormData as JSON: ${JSON.stringify(
              //     combinedData[key],
              //   )}`,
              // );
            } else if (key === 'file' && combinedData[key] instanceof File) {
              formData.append(key, combinedData[key]);
              // console.log(`File added to FormData: ${combinedData[key].name}`);
            } else {
              formData.append(key, combinedData[key]);
              // console.log(`${key} added to FormData: ${combinedData[key]}`);
            }
          }
        }

        //console.log('Données formData :', formData);
        // Vérifier le contenu de formData
        // for (let pair of formData.entries()) {
        //   console.log(pair[0] + ', ' + pair[1]);
        // }

        const http = new APIService(
          window.appGlobal.baseUrl,
          '/candidature/lead',
        );
        console.log('FormData content before submission:', [
          ...formData.entries(),
        ]);

        http
          .create(formData)
          .then((response) => {
            console.log('Success 1 :', response.data);
            console.log('Success 2 :', response.data.MatchResponse);
            console.log('Success 3 :', response);
          })
          .catch((error) => {
            console.error(
              'Error:',
              error.response ? error.response.data : error.message,
            );
          });

        // const response = await axios.post('/candidature/lead', combinedData, {
        //   headers: {
        //     'Content-Type': 'application/json',
        //   },
        // });
        // console.log("Réponse de l'API:", response.data);
      }
      currentStep.value++;
      nextTick(() => {
        setTimeout(() => {
          checkScroll();
          scrollContainer.value.scrollTo(0, 0);
        }, 50);
      });
    };
    const previousStep = () => {
      console.log('currentStep before = ', currentStep);
      currentStep.value--;
      console.log('currentStep after = ', currentStep);
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
      currentStep,
      reviews,
      needsScroll,
      scrollContainer,
      scrollContent,
      formImg,
      addReview,
      addReviewSkip,
      skipNextStep,
      nextStep,
      previousStep,
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
.formImg,
.scroll-container {
  border-radius: 1rem;
}
.formBlock {
  margin-top: 13rem;
  margin-bottom: 6rem;
}
/* @media screen and (max-width: 460px) {
  .formBlock {
    margin-top: 13rem;
  }
} */
@media screen and (max-width: 450px) {
  .formBlock {
    max-width: 410px;
    margin-bottom: 0rem;
  }
  .scroll-container {
    width: 100%;
  }
  .formImg {
    background-image: none;
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
