<script setup lang="ts">
const emit = defineEmits<{
  toggleSidebar: [];
}>();

const themeIsDark = useState<boolean>('theme-is-dark', () => false);

const applyTheme = (): void => {
  document.documentElement.setAttribute('data-theme', themeIsDark.value ? 'dark' : 'light');
};

const toggleTheme = (): void => {
  themeIsDark.value = !themeIsDark.value;

  localStorage.setItem('theme', themeIsDark.value ? 'dark' : 'light');

  applyTheme();
};
</script>

<template>
  <header class="app-header">
    <button
        class="app-header-menu btn"
        type="button"
        @click="emit('toggleSidebar')"
    >
      <i class="fa-solid fa-bars"></i>
    </button>

    <div class="app-header-title">
      <span class="app-header-eyebrow">Sistema</span>
      <strong>Gestão da oficina</strong>
    </div>

    <div class="app-header-actions">
      <button
          class="app-theme-button"
          type="button"
          @click="toggleTheme"
      >
        <i :class="themeIsDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
      </button>

      <button class="app-user-button" type="button">
        <i class="fa-solid fa-user-gear"></i>
      </button>
    </div>
  </header>
</template>