<script setup lang="ts">
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";

const props = defineProps<{
  value?: string | number | null;
  modelValue?: string | number | null;
  name: string;
  label?: string;
  placeholder?: string;
  required?: boolean;
  tip?: string;
  rows?: number;
  errorMessage?: string | null;
}>();

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-textarea-input-${props.name}`);

const inputValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

function emitValue(value: string): void {
  emit('update', value);
  emit('update:value', value);
  emit('update:modelValue', value);
}

function handleInput(event: Event): void {
  const target = event.target as HTMLTextAreaElement;
  emitValue(target.value);
}
</script>

<template>
  <div class="app-textarea-input">
    <div v-if="label" class="app-form-label-row">
      <label class="app-form-label" :for="inputId">
        {{ label }}
        <span v-if="required" class="app-form-required">*</span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" />
    </div>

    <textarea
        :id="inputId"
        class="app-form-textarea"
        :name="name"
        :value="inputValue"
        :placeholder="placeholder"
        :required="required"
        :rows="rows ?? 4"
        @input="handleInput"
    ></textarea>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
