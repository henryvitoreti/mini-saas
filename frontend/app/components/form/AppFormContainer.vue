<script setup lang="ts">
import type { AppFormContainerProps } from '@/types/ui/form';

defineProps<AppFormContainerProps>();
</script>

<template>
  <section class="app-form-page">
    <div class="app-form-page-header">
      <h1 class="app-form-page-title">
        {{ title }}
      </h1>

      <div v-if="breadcrumbs?.length" class="app-form-breadcrumb">
        <template v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.label">
          <NuxtLink v-if="breadcrumb.to" :to="breadcrumb.to" class="app-form-breadcrumb-link">
            {{ breadcrumb.label }}
          </NuxtLink>

          <span v-else class="app-form-breadcrumb-current">
            {{ breadcrumb.label }}
          </span>

          <span v-if="index < breadcrumbs.length - 1" class="app-form-breadcrumb-separator">
            /
          </span>
        </template>
      </div>

      <span v-if="subtitle" class="app-form-page-subtitle">
        {{ subtitle }}
      </span>
    </div>

    <slot />

    <footer class="app-form-footer">
      <slot name="footer" />
    </footer>
  </section>
</template>
