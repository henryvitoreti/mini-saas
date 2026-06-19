<script setup lang="ts">
const emit = defineEmits<{
  toggleSidebar: [];
}>();

const isDarkTheme = useState<boolean>('theme-is-dark', () => false);

function applyTheme(): void {
  document.documentElement.setAttribute('data-theme', isDarkTheme.value ? 'dark' : 'light');
}

function toggleTheme(): void {
  isDarkTheme.value = !isDarkTheme.value;

  localStorage.setItem('theme', isDarkTheme.value ? 'dark' : 'light');

  applyTheme();
}
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
        <i :class="isDarkTheme ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
      </button>

      <button class="app-user-button" type="button">
        <i class="fa-solid fa-user-gear"></i>
      </button>
    </div>
  </header>
</template>
