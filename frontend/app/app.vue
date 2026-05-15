<script setup lang="ts">
import {onMounted, ref} from "vue";

const appReady = ref<boolean>(false);
const themeIsDark = useState<boolean>('theme-is-dark', () => false);
const sidebarCollapsed = useState<boolean>('sidebar-collapsed', () => false);

const applyTheme = (): void => {
  document.documentElement.setAttribute('data-theme', themeIsDark.value ? 'dark' : 'light');
};

onMounted((): void => {
  const storedTheme = localStorage.getItem('theme');
  const storedSidebarCollapsed = localStorage.getItem('sidebar-collapsed');

  themeIsDark.value = storedTheme === 'dark';
  sidebarCollapsed.value = storedSidebarCollapsed === 'true';

  applyTheme();

  setTimeout((): void => {
    appReady.value = true;
  }, 500);
});
</script>

<template>
  <div v-if="!appReady" class="app-loading-screen">
    <img
        class="app-loading-logo"
        :src="themeIsDark ? '/images/full-logo-dark.svg' : '/images/full-logo-light.svg'"
        alt="Minski"
    >
  </div>

  <NuxtLayout v-else>
    <NuxtPage />
  </NuxtLayout>
</template>