<script setup lang="ts">
defineProps<{
  text: string;
}>();
</script>

<template>
  <span class="app-form-tip" tabindex="0">
    <i class="fa-solid fa-question"></i>
    <span class="app-form-tip-content">
      {{ text }}
    </span>
  </span>
</template>
