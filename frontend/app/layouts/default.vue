<script setup lang="ts">
import AppHeader from '@/components/layouts/AppHeader.vue';
import AppSidebar from '@/components/layouts/AppSidebar.vue';

const sidebarOpen = ref<boolean>(false);
const sidebarCollapsed = useState<boolean>('sidebar-collapsed', () => false);

const toggleSidebar = (): void => {
  sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = (): void => {
  sidebarOpen.value = false;
};
</script>

<template>
  <div class="app-shell" :data-sidebar-collapsed="sidebarCollapsed">
    <AppSidebar
        :sidebar-open="sidebarOpen"
        @close-sidebar="closeSidebar"
    />

    <div
        v-if="sidebarOpen"
        class="app-overlay"
        @click="closeSidebar"
    ></div>

    <div class="app-main">
      <AppHeader @toggle-sidebar="toggleSidebar" />

      <main class="app-content">
        <slot />
      </main>
    </div>
  </div>
</template>