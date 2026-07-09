<script setup lang="ts">
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { SelectOption } from '@/types/common/select';
import type { AppSelectInputProps } from '@/types/ui/form';

const props = withDefaults(
  defineProps<AppSelectInputProps>(),
  {
    required: false,
    disabled: false,
    clearable: true,
    options: () => [],
  },
);

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-select-input-${props.name}`);

const inputValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

const isOpen = ref<boolean>(false);

const selectedOption = computed<SelectOption|null>(() => {
  return props.options.find((option: SelectOption): boolean => {
    return String(option.value) === inputValue.value;
  }) ?? null;
});

const selectedLabel = computed<string>(() => {
  return selectedOption.value?.label ?? props.placeholder ?? 'Selecione';
});

const canClearSelection = computed<boolean>(() => {
  return props.clearable && !props.disabled && selectedOption.value !== null;
});

function emitValue(value: string): void {
  emit('update', value);
  emit('update:value', value);
  emit('update:modelValue', value);
}

function toggleOptions(): void {
  if (props.disabled) {
    return;
  }

  isOpen.value = !isOpen.value;
}

function closeOptions(event: FocusEvent|null = null): void {
  if (props.disabled || !isOpen.value) {
    return;
  }

  if (event?.currentTarget instanceof HTMLElement && event.relatedTarget instanceof Node) {
    if (event.currentTarget.contains(event.relatedTarget)) {
      return;
    }
  }

  isOpen.value = false;
}

function selectOption(option: SelectOption): void {
  if (option.disabled) {
    return;
  }

  isOpen.value = false;
  emitValue(String(option.value));
}

function clearSelection(): void {
  if (!canClearSelection.value) {
    return;
  }

  isOpen.value = false;
  emitValue('');
}
</script>

<template>
  <div class="app-select-input">
    <div v-if="label" class="app-form-label-row">
      <label class="app-form-label" :for="inputId">
        {{ label }}
        <span v-if="required" class="app-form-required">*</span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" :is-input-label="true" />
    </div>

    <div class="app-select-input-control" @focusout="closeOptions">
      <button
          :id="inputId"
          class="app-form-select"
          :class="{ 'is-open': isOpen, 'has-clear': canClearSelection }"
          type="button"
          :name="name"
          :disabled="disabled"
          :aria-expanded="isOpen"
          :aria-required="Boolean(required)"
          @click="toggleOptions"
          @keydown.esc="closeOptions()"
      >
        <span :class="{ 'is-placeholder': selectedOption === null }">
          {{ selectedLabel }}
        </span>
      </button>

      <button
          v-if="canClearSelection"
          type="button"
          class="app-select-input-clear"
          aria-label="Limpar seleção"
          @click.stop="clearSelection"
      >
        <i class="fa-solid fa-xmark app-select-input-clear-icon" aria-hidden="true"></i>
      </button>

      <input
          :name="name"
          :value="inputValue"
          tabindex="-1"
          aria-hidden="true"
          class="app-select-hidden-input"
      >

      <div v-if="isOpen" class="app-select-input-menu">
        <button
            v-for="option in options"
            :key="option.value"
            class="app-select-input-option"
            :class="{ 'is-selected': String(option.value) === inputValue }"
            type="button"
            :disabled="option.disabled"
            @click="selectOption(option)"
        >
          <span>{{ option.label }}</span>
          <i v-if="String(option.value) === inputValue" class="fa-solid fa-check"></i>
        </button>
      </div>

      <i class="fa-solid fa-chevron-down app-select-input-arrow"></i>
    </div>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
