<template>
  <div class="maraudes-container">
    <div class="maraudes-content">
      <h1 class="maraudes-title">Page Maraudes</h1>
      <button class="maraudes-button" @click="navigateToLogin">
        Accéder à l'espace de connexion
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import {buildThemeUrl} from '@/core/util/helper/url';

const navigateToLogin = () => {
  window.location.href = buildThemeUrl('/auth/login');
};
</script>

<style scoped lang="scss">
.maraudes-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.maraudes-content {
  text-align: center;
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 100%;
}

.maraudes-title {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 2rem;
  font-weight: 700;
}

.maraudes-button {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 1rem 2rem;
  font-size: 1.1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
  }

  &:active {
    transform: translateY(0);
  }
}
</style>
