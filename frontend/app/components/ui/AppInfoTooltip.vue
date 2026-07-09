<script setup lang="ts">
import type { AppInfoTooltipProps } from '@/types/ui/components';

withDefaults(defineProps<AppInfoTooltipProps>(), {
  isInputLabel: false,
});
</script>

<template>
  <span :class="{
    'app-input-tip': isInputLabel,
    'app-tip': !isInputLabel
  }" tabindex="0">
    <i class="fa-solid fa-question"></i>
    <span class="app-tip-content">
      {{ text }}
    </span>
  </span>
</template>
