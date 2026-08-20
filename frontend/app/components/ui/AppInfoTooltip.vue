<script setup lang="ts">
import type { AppInfoTooltipProps } from '@/types/ui/components';

withDefaults(defineProps<AppInfoTooltipProps>(), {
  isInputLabel: false,
  position: 'top',
});
</script>

<template>
  <span :class="{
    'app-input-tip': isInputLabel,
    'app-tip': !isInputLabel,
    'is-position-left': position === 'left',
  }" tabindex="0">
    <i class="fa-solid fa-question"></i>
    <span class="app-tip-content">
      {{ text }}
    </span>
  </span>
</template>
