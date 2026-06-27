<script setup lang="ts">
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { AppSwitchInputProps } from '@/types/ui/form';

const props = withDefaults(
  defineProps<AppSwitchInputProps>(),
  {
    required: false,
    disabled: false,
    isDefaultLayout: true,
  },
);

const emit = defineEmits<{
  update: [value: boolean];
  'update:value': [value: boolean];
  'update:modelValue': [value: boolean];
}>();

const inputId = computed<string>(() => `app-switch-input-${props.name}`);

const inputValue = computed<boolean>(() => {
  return Boolean(props.value ?? props.modelValue ?? false);
});

function emitValue(value: boolean): void {
  emit('update', value);
  emit('update:value', value);
  emit('update:modelValue', value);
}

function handleChange(event: Event): void {
  const target = event.target as HTMLInputElement;
  emitValue(target.checked);
}
</script>

<template>
  <div class="app-switch-input">
    <div
        class="app-switch-input-body"
        :class="{
          'is-default-layout': isDefaultLayout,
          'is-inline-layout': !isDefaultLayout,
        }"
    >
      <div v-if="isDefaultLayout && label" class="app-form-label-row">
        <label class="app-form-label" :for="inputId">
          {{ label }}
          <span v-if="required" class="app-form-required">*</span>
        </label>

        <AppInfoTooltip v-if="tip" :text="tip" :is-input-label="true" />
      </div>

      <label class="app-switch-control" :for="inputId">
        <input
            :id="inputId"
            class="app-switch-native"
            type="checkbox"
            :name="name"
            :checked="inputValue"
            :required="required"
            :disabled="disabled"
            @change="handleChange"
        >

        <span class="app-switch-track">
          <span class="app-switch-thumb"></span>
        </span>

        <span v-if="!isDefaultLayout && label" class="app-switch-label">
          {{ label }}
          <span v-if="required" class="app-form-required">*</span>
        </span>
      </label>

      <AppInfoTooltip v-if="!isDefaultLayout && tip" :text="tip" />
    </div>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
