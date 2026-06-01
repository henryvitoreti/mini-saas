<script setup lang="ts">
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { SelectOption } from '@/types/common/select';

const props = withDefaults(defineProps<{
  value?: string | number | null;
  modelValue?: string | number | null;
  name: string;
  label?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  tip?: string;
  errorMessage?: string | null;
  options: SelectOption[];
}>(), {
  options: () => [],
});

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-select-input-${props.name}`);

const inputValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

function emitValue(value: string): void {
  emit('update', value);
  emit('update:value', value);
  emit('update:modelValue', value);
}

function handleChange(event: Event): void {
  const target = event.target as HTMLSelectElement;
  emitValue(target.value);
}
</script>

<template>
  <div class="app-select-input">
    <div v-if="label" class="app-form-label-row">
      <label class="app-form-label" :for="inputId">
        {{ label }}
        <span v-if="required" class="app-form-required">*</span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" />
    </div>

    <div class="app-select-input-control">
      <select
          :id="inputId"
          class="app-form-select"
          :name="name"
          :value="inputValue"
          :required="required"
          :disabled="disabled"
          @change="handleChange"
      >
        <option v-if="placeholder" value="" disabled>
          {{ placeholder }}
        </option>

        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
            :disabled="option.disabled"
        >
          {{ option.label }}
        </option>
      </select>

      <i class="fa-solid fa-chevron-down app-select-input-arrow"></i>
    </div>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
