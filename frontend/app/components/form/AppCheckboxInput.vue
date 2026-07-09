<script setup lang="ts">
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { AppCheckboxInputProps } from '@/types/ui/form';

const props = withDefaults(
  defineProps<AppCheckboxInputProps>(),
  {
    required: false,
    disabled: false
  },
);

const emit = defineEmits<{
  update: [value: boolean];
  'update:value': [value: boolean];
  'update:modelValue': [value: boolean];
}>();

const inputId = computed<string>(() => `app-checkbox-input-${props.name}`);

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
  <div class="app-checkbox-input">
    <div class="app-checkbox-input-body">
      <label class="app-checkbox-control" :for="inputId">
        <input
            :id="inputId"
            class="app-checkbox-native"
            type="checkbox"
            :name="name"
            :checked="inputValue"
            :disabled="disabled"
            :aria-required="Boolean(required)"
            @change="handleChange"
        >

        <span class="app-checkbox-box">
          <i class="fa-solid fa-check app-checkbox-icon"></i>
        </span>

        <span v-if="label" class="app-checkbox-label">
          {{ label }}
          <span v-if="required" class="app-form-required">*</span>
        </span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" :is-input-label="true" />
    </div>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
