<script setup lang="ts">
const { isLoading } = useAppRequestLoading();

watch(
  isLoading,
  (loading): void => {
    if (!import.meta.client) {
      return;
    }

    document.documentElement.dataset.appRequestLoading = String(loading);
  },
  { immediate: true },
);
</script>

<template>
  <Teleport to="body">
    <Transition name="app-request-loading">
      <div
          v-if="isLoading"
          class="app-request-loading-overlay"
          aria-live="polite"
          aria-busy="true"
      >
        <div class="app-request-loading-spinner" />
      </div>
    </Transition>
  </Teleport>
</template>
