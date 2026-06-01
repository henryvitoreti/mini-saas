<script setup lang="ts">
import AppHeader from '@/components/layouts/AppHeader.vue';
import AppSidebar from '@/components/layouts/AppSidebar.vue';

const sidebarOpen = ref<boolean>(false);
const sidebarCollapsed = useState<boolean>('sidebar-collapsed', () => false);

function toggleSidebar(): void {
  sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebar(): void {
  sidebarOpen.value = false;
}
</script>

<template>
  <div
      class="app-shell"
      :data-sidebar-collapsed="sidebarCollapsed"
      :data-sidebar-open="sidebarOpen"
  >
    <div
        v-if="sidebarOpen"
        class="app-overlay"
        @click="closeSidebar"
    ></div>

    <AppSidebar
        :sidebar-open="sidebarOpen"
        @close-sidebar="closeSidebar"
    />

    <div class="app-main">
      <AppHeader @toggle-sidebar="toggleSidebar" />

      <main class="app-content">
        <slot />
      </main>
    </div>
  </div>
</template>
