<script setup lang="ts">
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { ptBR } from 'date-fns/locale/pt-BR';
import AppInfoTooltip from '@/components/ui/AppInfoTooltip.vue';
import type {
  AppDateInputProps,
  DatePickerValue,
  DateRangeInputValue,
} from '@/types/ui/form';

type DatePickerModelValue = string|null|string[];
type DatePickerRangeConfig = false|{ partialRange: boolean };
type DatePickerInputAttrs = {
  name: string;
  required: boolean;
  id: string;
  clearable: boolean;
};

const datePickerFormats = { input: 'dd/MM/yyyy' };
const datePickerTextInput = { format: 'dd/MM/yyyy' };
const datePickerTimeConfig = { enableTimePicker: false };

const props = withDefaults(
  defineProps<AppDateInputProps>(),
  {
    required: false,
    disabled: false,
    range: false,
  },
);

const emit = defineEmits<{
  update: [value: string|DateRangeInputValue];
  'update:value': [value: string|DateRangeInputValue];
  'update:modelValue': [value: string|DateRangeInputValue];
}>();

const isDarkTheme = useState<boolean>('theme-is-dark', () => false);
const inputId = computed<string>(() => `app-date-input-${props.name}`);

const datePickerInputAttrs = computed<DatePickerInputAttrs>(() => {
  return {
    name: props.name,
    required: Boolean(props.required),
    id: inputId.value,
    clearable: !props.required,
  };
});

const datePickerRangeConfig = computed<DatePickerRangeConfig>(() => {
  return props.range ? { partialRange: false } : false;
});

const inputValue = computed<DatePickerValue>(() => {
  return props.value ?? props.modelValue ?? (props.range ? [null, null] : null);
});

const datePickerValue = computed<DatePickerModelValue>({
  get: (): DatePickerModelValue => {
    if (!props.range) {
      return typeof inputValue.value === 'string' ? inputValue.value : null;
    }

    if (!Array.isArray(inputValue.value)) {
      return [];
    }

    return inputValue.value.filter((value): value is string => {
      return typeof value === 'string' && value !== '';
    });
  },
  set: (value: DatePickerModelValue): void => {
    emitValue(value);
  },
});

function emitValue(value: DatePickerModelValue): void {
  if (!props.range) {
    const normalizedValue = typeof value === 'string' ? value : '';

    emit('update', normalizedValue);
    emit('update:value', normalizedValue);
    emit('update:modelValue', normalizedValue);
    return;
  }

  const normalizedValue: DateRangeInputValue = [
    Array.isArray(value) && typeof value[0] === 'string' ? value[0] : null,
    Array.isArray(value) && typeof value[1] === 'string' ? value[1] : null,
  ];

  emit('update', normalizedValue);
  emit('update:value', normalizedValue);
  emit('update:modelValue', normalizedValue);
}
</script>

<template>
  <div class="app-date-input">
    <div v-if="label" class="app-form-label-row">
      <label class="app-form-label" :for="inputId">
        {{ label }}
        <span v-if="required" class="app-form-required">*</span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" :is-input-label="true" />
    </div>

    <VueDatePicker
        v-model="datePickerValue"
        class="app-date-picker"
        model-type="yyyy-MM-dd"
        :formats="datePickerFormats"
        :locale="ptBR"
        :dark="isDarkTheme"
        :placeholder="placeholder"
        :disabled="disabled"
        :range="datePickerRangeConfig"
        :time-config="datePickerTimeConfig"
        :auto-apply="true"
        :text-input="datePickerTextInput"
        :input-attrs="datePickerInputAttrs"
    >
      <template #input-icon>
        <i class="fa-solid fa-calendar-day app-date-picker-icon"></i>
      </template>

      <template #clear-icon="{ clear }">
        <button
            type="button"
            class="app-date-picker-clear"
            aria-label="Limpar data"
            @click.stop.prevent="clear"
        >
          <i class="fa-solid fa-xmark app-date-picker-clear-icon" aria-hidden="true"></i>
        </button>
      </template>
    </VueDatePicker>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
