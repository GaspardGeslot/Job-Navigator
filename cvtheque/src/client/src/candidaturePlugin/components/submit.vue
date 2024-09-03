<template>
  <div class="submit-block">
    <button class="previousButton" type="button" @click="goBack">
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M15.7071 18.7071C16.0976 18.3166 16.0976 17.6834 15.7071 17.2929L10.4142 12L15.7071 6.70711C16.0976 6.31658 16.0976 5.68342 15.7071 5.29289C15.3166 4.90237 14.6834 4.90237 14.2929 5.29289L8.29289 11.2929C7.90237 11.6834 7.90237 12.3166 8.29289 12.7071L14.2929 18.7071C14.6834 19.0976 15.3166 19.0976 15.7071 18.7071Z"
          fill="white"
        />
      </svg>
    </button>
    <!--
          <input class="previousButton" type="submit" value="RETOUR" />
          <button>
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M9.70711 17.2929C10.0976 17.6834 10.0976 18.3166 9.70711 18.7071C9.31658 19.0976 8.68342 19.0976 8.29289 18.7071L2.29289 12.7071C1.90237 12.3166 1.90237 11.6834 2.29289 11.2929L8.29289 5.29289C8.68342 4.90237 9.31658 4.90237 9.70711 5.29289C10.0976 5.68342 10.0976 6.31658 9.70711 6.70711L5.41421 11L21 11C21.5523 11 22 11.4477 22 12C22 12.5523 21.5523 13 21 13L5.41422 13L9.70711 17.2929Z"
              fill="black"
            />
          </svg>
        </button>
        <input class="submitButton2" type="submit" value="SUIVANT" />
      --><button class="mainSubmitButton" type="submit">
      <div class="mainSubmitButtonTxt">SUIVANT</div>
      <div class="mainSubmitButtonSvg">
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M8.29289 18.7071C7.90237 18.3166 7.90237 17.6834 8.29289 17.2929L13.5858 12L8.29289 6.70711C7.90237 6.31658 7.90237 5.68342 8.29289 5.29289C8.68342 4.90237 9.31658 4.90237 9.70711 5.29289L15.7071 11.2929C16.0976 11.6834 16.0976 12.3166 15.7071 12.7071L9.70711 18.7071C9.31658 19.0976 8.68342 19.0976 8.29289 18.7071Z"
            fill="white"
          />
        </svg>
      </div>
    </button>
  </div>
</template>

<script>
export default {
  name: 'SubmitComponent',
  emits: ['go-back'],
  methods: {
    goBack() {
      this.$emit('go-back');
    },
  },
};
</script>
