<script setup lang="ts">
import IMask from 'imask';
import type { InputMask } from 'imask';
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";

const props = defineProps<{
  value?: string | number | null;
  modelValue?: string | number | null;
  name: string;
  type?: string;
  label?: string;
  mask?: string;
  placeholder?: string;
  required?: boolean;
  tip?: string;
  startIcon?: string;
  endIcon?: string;
}>();

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-text-input-${props.name}`);
const inputRef = ref<HTMLInputElement | null>(null);
const inputValue = ref<string>('');
const maskInstance = shallowRef<InputMask | null>(null);

const rawValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

const normalizedMask = computed<string|undefined>(() => {
  if (!props.mask) {
    return undefined;
  }

  const mask = props.mask.trim();
  const unwrappedMask = mask.startsWith('[') && mask.endsWith(']')
      ? mask.slice(1, -1)
      : mask;

  return unwrappedMask.replaceAll('#', '0');
});

const emitValue = (value: string): void => {
  emit('update', value);
  emit('update:value', value);
  emit('update:modelValue', value);
};

const destroyMask = (): void => {
  maskInstance.value?.destroy();
  maskInstance.value = null;
};

const updateMaskValue = (): void => {
  if (!maskInstance.value || !inputRef.value) {
    return;
  }

  if (inputRef.value.value !== maskInstance.value.value) {
    maskInstance.value.updateValue();
  }
};

const syncValue = (): void => {
  if (maskInstance.value) {
    updateMaskValue();

    if (maskInstance.value.unmaskedValue !== rawValue.value) {
      maskInstance.value.unmaskedValue = rawValue.value;
    }

    return;
  }

  inputValue.value = rawValue.value;
};

const setupMask = (): void => {
  destroyMask();

  if (!inputRef.value || !normalizedMask.value) {
    syncValue();
    return;
  }

  maskInstance.value = IMask(inputRef.value, {
    mask: normalizedMask.value,
  });

  maskInstance.value.on('accept', () => {
    emitValue(maskInstance.value?.unmaskedValue ?? '');
  });

  syncValue();
};

const handleMaskedInput = (): void => {
  updateMaskValue();
  emitValue(maskInstance.value?.unmaskedValue ?? '');
};

const handleInput = (event: Event): void => {
  const target = event.target as HTMLInputElement;
  const value = target.value;

  inputValue.value = value;
  emitValue(value);
};

watch(
  () => rawValue.value,
  (): void => {
    syncValue();
  },
);

watch(
  () => normalizedMask.value,
  async (): Promise<void> => {
    await nextTick();
    setupMask();
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

      <AppInfoTooltip v-if="tip" :text="tip" />
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
          :required="required"
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
          :required="required"
          @input="handleInput"
      >

      <i v-if="endIcon" class="app-text-input-icon is-end" :class="endIcon"></i>
    </div>
  </div>
</template>
