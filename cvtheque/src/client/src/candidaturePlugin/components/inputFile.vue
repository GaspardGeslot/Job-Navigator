<template>
  <label class="file-select">
    <div class="select-button">
      <div>
        <span v-if="value">Selected File: {{ value.name }}</span>
        <span v-else>Télécharger</span>
      </div>
      <div class="download-icon-wrapper">
        <svg
          width="21"
          height="18"
          viewBox="0 0 21 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M1 9V14.3333C1 16.3333 1.54902 17 3.19608 17H17.4706C19.1176 17 19.6667 16.3333 19.6667 14.3333V9"
            stroke="#071E22"
            stroke-width="1.5"
            stroke-linecap="round"
          />
          <path
            d="M10.0664 11.6667V1M10.0664 1L6.06641 5M10.0664 1L14.0664 5"
            stroke="#071E22"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </div>
    </div>
    <input type="file" @change="handleFileChange" />
  </label>
</template>

<script>
export default {
  props: {
    value: File,
  },
  emits: ['emit-input'],
  methods: {
    handleFileChange(e) {
      this.$emit('emit-input', e.target.files[0]);
    },
  },
};
</script>

<style scoped>
.download-icon {
  max-width: 1.15rem;
  height: auto;
  background-color: rgba(255, 255, 255, 1);
}

.select-button {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  color: black;
  text-align: center;
  font-weight: 400;
  background-color: rgba(255, 255, 255, 1);
  transition: background-color 0.3s;
}

.file-select {
  background-color: rgba(255, 255, 255, 1);
  margin-top: 0.25rem;
  border-color: black;
  border-width: 1px;
  border-style: solid;
}

.download-icon-wrapper {
  display: inline-block;
  padding: 0.2rem;
}

.file-select > .select-button:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.file-select input[type='file'] {
  display: none;
}
</style>
