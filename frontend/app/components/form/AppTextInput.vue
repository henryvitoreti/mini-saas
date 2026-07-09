<script setup lang="ts">
import IMask from 'imask';
import type { InputMask } from 'imask';
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { FormInputMask } from '@/types/forms/form';
import type { AppTextInputProps } from '@/types/ui/form';

type InputMaskOptions = {
  mask: string|Array<{ mask: string }>;
};

const props = withDefaults(
  defineProps<AppTextInputProps>(),
  {
    type: 'text',
    required: false,
    disabled: false,
  },
);

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-text-input-${props.name}`);
const inputRef = ref<HTMLInputElement|null>(null);
const inputValue = ref<string>('');
const maskInstance = shallowRef<InputMask|null>(null);

const rawValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

const normalizedMask = computed<FormInputMask|undefined>(() => {
  if (!props.mask) {
    return undefined;
  }

  if (Array.isArray(props.mask)) {
    return props.mask.map((mask) => normalizeMask(mask));
  }

  return normalizeMask(props.mask);
});

const maskOptions = computed<InputMaskOptions|undefined>(() => {
  if (!normalizedMask.value) {
    return undefined;
  }

  if (Array.isArray(normalizedMask.value)) {
    return {
      mask: normalizedMask.value.map((mask) => ({ mask })),
    };
  }

  return { mask: normalizedMask.value };
});

function normalizeMask(mask: string): string {
  const unwrappedMask = mask.startsWith('[') && mask.endsWith(']')
      ? mask.slice(1, -1)
      : mask;

  return unwrappedMask.replaceAll('#', '0');
}

function emitValue(value: string): void {
  emit('update', value);
  emit('update:value', value);
  emit('update:modelValue', value);
}

function destroyMask(): void {
  maskInstance.value?.destroy();
  maskInstance.value = null;
}

function updateMaskValue(): void {
  if (!maskInstance.value || !inputRef.value) {
    return;
  }

  if (inputRef.value.value !== maskInstance.value.value) {
    maskInstance.value.updateValue();
  }
}

function syncValue(): void {
  if (maskInstance.value) {
    updateMaskValue();

    if (maskInstance.value.unmaskedValue !== rawValue.value) {
      maskInstance.value.unmaskedValue = rawValue.value;
    }

    return;
  }

  inputValue.value = rawValue.value;
}

function setupMask(): void {
  destroyMask();

  if (!inputRef.value || !maskOptions.value) {
    syncValue();
    return;
  }

  maskInstance.value = IMask(inputRef.value, maskOptions.value);

  maskInstance.value.on('accept', () => {
    emitValue(maskInstance.value?.unmaskedValue ?? '');
  });

  syncValue();
}

function handleMaskedInput(): void {
  updateMaskValue();
  emitValue(maskInstance.value?.unmaskedValue ?? '');
}

function handleInput(event: Event): void {
  const target = event.target as HTMLInputElement;
  const value = target.value;

  inputValue.value = value;
  emitValue(value);
}

watch(
  [rawValue, normalizedMask],
  async ([, currentMask], [, previousMask]): Promise<void> => {
    await nextTick();

    if (currentMask !== previousMask) {
      setupMask();
      return;
    }

    syncValue();
  },
  { flush: 'post' },
);

onMounted(() => {
  setupMask();
});

onBeforeUnmount(() => {
  destroyMask();
});
</script>

<template>
  <div class="app-text-input">
    <div v-if="label" class="app-form-label-row">
      <label class="app-form-label" :for="inputId">
        {{ label }}
        <span v-if="required" class="app-form-required">*</span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" :is-input-label="true" />
    </div>

    <div
        class="app-text-input-control"
        :class="{
          'has-start-icon': startIcon,
          'has-end-icon': endIcon,
        }"
    >
      <i v-if="startIcon" class="app-text-input-icon is-start" :class="startIcon"></i>

      <input
          v-if="normalizedMask"
          :id="inputId"
          ref="inputRef"
          class="app-form-input app-text-input-field"
          type="text"
          :name="name"
          :placeholder="placeholder"
          :disabled="disabled"
          :aria-required="Boolean(required)"
          @focus="updateMaskValue"
          @keydown.capture="updateMaskValue"
          @input="handleMaskedInput"
          @change="handleMaskedInput"
      >

      <input
          v-else
          :id="inputId"
          ref="inputRef"
          class="app-form-input app-text-input-field"
          :type="type ?? 'text'"
          :name="name"
          :value="inputValue"
          :placeholder="placeholder"
          :disabled="disabled"
          :aria-required="Boolean(required)"
          @input="handleInput"
      >

      <i v-if="endIcon" class="app-text-input-icon is-end" :class="endIcon"></i>
    </div>

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
