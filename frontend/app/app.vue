<script setup lang="ts">
import AppGlobalRequestLoading from '@/components/ui/AppGlobalRequestLoading.vue';
import AppToastProvider from '@/components/ui/AppToastProvider.vue';
import { getFullLogo } from '@/config/appLogos';
import { onMounted, ref } from 'vue';

const appReady = ref<boolean>(false);
const isDarkTheme = useState<boolean>('theme-is-dark', () => false);
const sidebarCollapsed = useState<boolean>('sidebar-collapsed', () => false);

function applyTheme(): void {
  document.documentElement.setAttribute('data-theme', isDarkTheme.value ? 'dark' : 'light');
}

onMounted((): void => {
  const storedTheme = localStorage.getItem('theme');
  const storedSidebarCollapsed = localStorage.getItem('sidebar-collapsed');

  isDarkTheme.value = storedTheme === 'dark';
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
        :src="getFullLogo()"
        alt="Minski"
    >
  </div>

  <NuxtLayout v-else>
    <NuxtPage />
  </NuxtLayout>

  <AppGlobalRequestLoading />
  <AppToastProvider />
</template>
