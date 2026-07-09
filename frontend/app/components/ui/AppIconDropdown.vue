<script setup lang="ts">
import type {
  AppIconDropdownOption,
  AppIconDropdownOptionValue,
  AppIconDropdownProps,
} from '@/types/ui/components';

const props = withDefaults(
  defineProps<AppIconDropdownProps>(),
  {
    selectedValue: null,
    disabled: false,
  },
);

const emit = defineEmits<{
  toggle: [];
  select: [value: AppIconDropdownOptionValue];
}>();

function handleToggle(): void {
  if (props.disabled) {
    return;
  }

  emit('toggle');
}

function handleSelect(option: AppIconDropdownOption): void {
  emit('select', option.value);
}
</script>

<template>
  <div class="app-icon-dropdown">
    <button
        class="btn app-data-table-icon-button"
        type="button"
        :title="title"
        :aria-label="title"
        :aria-expanded="isOpen"
        :disabled="disabled ?? false"
        @click="handleToggle"
    >
      <i :class="icon"></i>

      <span v-if="selectedValue !== null" class="app-icon-dropdown-badge">
        {{ selectedValue }}
      </span>
    </button>

    <div v-if="isOpen" class="app-icon-dropdown-menu">
      <button
          v-for="option in options"
          :key="String(option.value)"
          class="app-icon-dropdown-option"
          :class="{ 'is-selected': option.value === selectedValue }"
          type="button"
          @click="handleSelect(option)"
      >
        <span>{{ option.title }}</span>

        <i v-if="option.value === selectedValue" class="fa-solid fa-check app-icon-dropdown-option-trailing"></i>
      </button>
    </div>
  </div>
</template>
