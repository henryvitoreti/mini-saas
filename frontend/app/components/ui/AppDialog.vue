<script setup lang="ts">
import { computed } from 'vue';
import type { AppDialogProps } from '@/types/ui/components';

const props = withDefaults(
  defineProps<AppDialogProps>(),
  {
    showCloseButton: true,
    closeOnBackdrop: true,
    showCancelButton: true,
    cancelLabel: 'Cancelar',
    isCancelDisabled: false,
    closeButtonTitle: 'Fechar',
  },
);

const emit = defineEmits<{
  close: [];
}>();

const slots = defineSlots<{
  default?: () => unknown;
  'footer-actions'?: () => unknown;
}>();

const hasHeader = computed<boolean>(() => {
  return Boolean(props.title || props.showCloseButton);
});

const hasFooter = computed<boolean>(() => {
  return Boolean(props.showCancelButton || slots['footer-actions']);
});

function closeDialog(): void {
  emit('close');
}

function handleBackdropClick(): void {
  if (!props.closeOnBackdrop) {
    return;
  }

  closeDialog();
}
</script>

<template>
  <div class="app-modal-backdrop" @click="handleBackdropClick"></div>

  <section class="app-dialog" role="dialog" aria-modal="true">
    <header v-if="hasHeader" class="app-dialog-header">
      <h2 v-if="title">
        {{ title }}
      </h2>

      <button
          v-if="showCloseButton"
          class="btn btn-sm"
          type="button"
          :title="closeButtonTitle ?? 'Fechar'"
          @click="closeDialog"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>
    </header>

    <div class="app-dialog-body">
      <slot></slot>
    </div>

    <footer v-if="hasFooter" class="app-dialog-footer">
      <button
          v-if="showCancelButton"
          class="btn btn-outline-secondary"
          type="button"
          :disabled="isCancelDisabled ?? false"
          @click="closeDialog"
      >
        {{ cancelLabel ?? 'Cancelar' }}
      </button>

      <slot name="footer-actions"></slot>
    </footer>
  </section>
</template>
