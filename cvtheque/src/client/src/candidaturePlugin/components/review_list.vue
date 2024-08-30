<template>
  <div class="review-container">
    <h3>Avis:</h3>
    <ul>
      <li v-for="(review, index) in reviews" :key="index">
        {{ review.permits }}
        <br />
        {{ review.name }}
        <br />
        {{ review.surname }}
        <br />
        {{ review.email }}
        <br />
        {{ review.phone }}
        <br />
        "{{ review.motivation }}"
        <br />
        "{{ review.file }}"
        <br />
        "{{ review.type_de_contrat }}"
        <br />
        "{{ review.postal_code }}"
        <br />
        "{{ review.availability }}"
        <br />
        "{{ review.mobility }}"
        <br />
        "{{ review.education }}"
        <br />
        "{{ review }}"
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'ReviewList',
  props: {
    reviews: {
      type: Array,
      required: true,
    },
  },
};
</script>
