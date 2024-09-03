<template>
  <div class="formBlock row" style="max-width: 900px">
    <div
      ref="scrollContainer"
      class="scroll-container"
      :class="{'scroll-active': needsScroll}"
    >
      <div ref="scrollContent" class="scroll-content">
        <FormOne
          v-if="currentStep === 8"
          ref="formOne"
          @situation-submitted="addReview"
        />
        <FormTwo
          v-if="currentStep === 2"
          ref="formTwo"
          @situation-submitted="addReview"
        />
        <FormThree
          v-if="currentStep === 3"
          ref="formThree"
          @situation-submitted="addReview"
        />
        <FormFour
          v-if="currentStep === 9"
          ref="formFour"
          @situation-submitted="addReview"
        />
        <FormFive
          v-if="currentStep === 4"
          ref="formFive"
          @situation-submitted="addReview"
        />
        <FormSix
          v-if="currentStep === 5"
          ref="formSix"
          @situation-submitted="addReview"
        />
        <FormSeven v-if="currentStep === 6" ref="formSix" />
        <FormEight
          v-if="currentStep === 7"
          ref="formEight"
          @situation-submitted="addReview"
        />
        <FormNine
          v-if="currentStep === 1"
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
import FormOne from './formBlock1';
import FormTwo from './formBlock2';
import FormThree from './formBlock3';
import FormFour from './formBlock4';
import FormFive from './formBlock5';
import FormSix from './formBlock6';
import FormSeven from './formBlock7';
import FormEight from './formBlock8';
import FormNine from './formBlock9';
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
  setup() {
    const currentStep = ref(1);
    const reviews = ref([]);
    const needsScroll = ref(false);

    const scrollContainer = ref(null);
    const scrollContent = ref(null);
    const formImg = ref(null);

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

    const addReview = (review) => {
      reviews.value.push(review);
      nextStep();
    };

    const nextStep = () => {
      currentStep.value++;
      nextTick(() => {
        setTimeout(() => {
          checkScroll();
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
      nextStep,
    };
  },
};
</script>

<style src="./form-component.scss" lang="scss"></style>
<style scoped>
@media screen and (max-width: 450px) {
  .formBlock {
    max-width: 410px;
  }
  .scroll-container {
    width: 100%;
  }
  .formImg {
    background-image: none;
  }
}
@media screen and (max-width: 550px) {
  .formImg {
    display: none;
  }
}
</style>
