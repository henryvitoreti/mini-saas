<script setup lang="ts">
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';

const isDarkTheme = useState<boolean>('theme-is-dark', () => false);
const toastTheme = computed<'light'|'dark'>(() => isDarkTheme.value ? 'dark' : 'light');
</script>

<template>
  <Toaster
      position="top-center"
      rich-colors
      close-button
      :theme="toastTheme"
      :toast-options="{
        class: 'app-toast',
      }"
  />
</template>
