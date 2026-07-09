<script setup lang="ts">
import { defineComponent, h, type VNode } from 'vue';
import VSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { PaginatedResponse } from '@/types/common/pagination';
import type { SelectOption } from '@/types/common/select';
import type { AppSelect2InputProps } from '@/types/ui/form';

const props = withDefaults(
  defineProps<AppSelect2InputProps>(),
  {
    required: false,
    disabled: false,
    options: () => [],
    searchParam: 'search',
    pageParam: 'page',
    perPageParam: 'per_page',
    perPage: 10,
  },
);

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-select2-input-${props.name}`);
const usesApi = computed<boolean>(() => Boolean(props.apiUrl));
const selectOptions = ref<SelectOption[]>([...(props.options ?? [])]);
const searchTerm = ref('');
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const requestIndex = ref(0);
const abortController = shallowRef<AbortController|null>(null);
const intersectionObserver = shallowRef<IntersectionObserver|null>(null);
const selectComponents = {
  Deselect: defineComponent({
    name: 'AppSelect2DeselectIcon',
    setup(): () => VNode {
      return (): VNode => h('i', {
        class: 'fa-solid fa-xmark app-select2-clear-icon',
        'aria-hidden': 'true',
      });
    },
  }),
};

const inputValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

function emitValue(value: string|number|null): void {
  const normalizedValue = value === null ? '' : String(value);

  emit('update', normalizedValue);
  emit('update:value', normalizedValue);
  emit('update:modelValue', normalizedValue);
}

function calculateDropdownPosition(
  dropdownList: HTMLUListElement,
  _component: { $refs: { toggle: HTMLElement|null } },
  position: { width: string; top: string; left: string },
): (() => void) {
  const rootStyles = getComputedStyle(document.documentElement);
  const surface = rootStyles.getPropertyValue('--app-surface').trim();
  const border = rootStyles.getPropertyValue('--app-border').trim();
  const text = rootStyles.getPropertyValue('--app-text').trim();
  const shadow = rootStyles.getPropertyValue('--app-shadow').trim();

  dropdownList.classList.add('app-select2-dropdown-menu');
  dropdownList.style.width = position.width;
  dropdownList.style.top = position.top;
  dropdownList.style.left = position.left;
  dropdownList.style.background = surface;
  dropdownList.style.borderColor = border;
  dropdownList.style.color = text;
  dropdownList.style.boxShadow = shadow;
  dropdownList.style.setProperty('--vs-dropdown-bg', surface);
  dropdownList.style.setProperty('--vs-dropdown-color', text);

  return (): void => {
    dropdownList.classList.remove('app-select2-dropdown-menu');
  };
}

function getResponseOptions(response: PaginatedResponse<SelectOption>): SelectOption[] {
  if (Array.isArray(response)) {
    return response;
  }

  return response.data ?? response.items ?? response.results ?? [];
}

function getResponseLastPage(response: PaginatedResponse<SelectOption>): number|undefined {
  if (Array.isArray(response)) {
    return undefined;
  }

  return response.last_page ?? response.meta?.last_page;
}

async function fetchOptions(page: number): Promise<void> {
  if (!props.apiUrl || isLoading.value || (!hasMorePages.value && page > 1)) {
    return;
  }

  isLoading.value = true;
  const activeRequest = ++requestIndex.value;
  const controller = new AbortController();
  const searchParam = props.searchParam ?? 'search';
  const pageParam = props.pageParam ?? 'page';
  const perPageParam = props.perPageParam ?? 'per_page';
  const perPage = props.perPage ?? 10;
  abortController.value = controller;

  try {
    const response = await $fetch<PaginatedResponse<SelectOption>>(props.apiUrl, {
      signal: controller.signal,
      query: {
        [searchParam]: searchTerm.value,
        [pageParam]: page,
        [perPageParam]: perPage,
      },
    });

    if (activeRequest !== requestIndex.value) {
      return;
    }

    const nextOptions = getResponseOptions(response);
    const lastPage = getResponseLastPage(response);

    selectOptions.value = page === 1
        ? nextOptions
        : [...selectOptions.value, ...nextOptions];

    currentPage.value = page;
    hasMorePages.value = lastPage
        ? page < lastPage
        : nextOptions.length >= perPage;
  } catch (error) {
    if (activeRequest === requestIndex.value) {
      throw error;
    }
  } finally {
    if (activeRequest === requestIndex.value) {
      isLoading.value = false;
    }

    if (abortController.value === controller) {
      abortController.value = null;
    }
  }
}

async function resetRemoteOptions(): Promise<void> {
  abortController.value?.abort();
  requestIndex.value++;
  isLoading.value = false;
  selectOptions.value = [];
  currentPage.value = 1;
  hasMorePages.value = true;
  await fetchOptions(1);
}

function loadNextPage(): void {
  if (!usesApi.value || isLoading.value || !hasMorePages.value) {
    return;
  }

  void fetchOptions(currentPage.value + 1);
}

function handleSearch(search: string): void {
  searchTerm.value = search;

  if (usesApi.value) {
    void resetRemoteOptions();
  }
}

function setLoadMoreElement(element: Element|null): void {
  intersectionObserver.value?.disconnect();

  if (!element || typeof IntersectionObserver === 'undefined') {
    return;
  }

  intersectionObserver.value = new IntersectionObserver((entries) => {
    if (entries[0]?.isIntersecting) {
      loadNextPage();
    }
  });

  intersectionObserver.value.observe(element);
}

watch(
  () => props.options,
  (options: SelectOption[]|null): void => {
    if (!usesApi.value) {
      selectOptions.value = [...(options ?? [])];
    }
  },
);

onMounted(() => {
  if (usesApi.value) {
    void fetchOptions(1);
  }
});

onBeforeUnmount(() => {
  abortController.value?.abort();
  intersectionObserver.value?.disconnect();
});
</script>

<template>
  <div class="app-select2-input">
    <div v-if="label" class="app-form-label-row">
      <label class="app-form-label" :for="inputId">
        {{ label }}
        <span v-if="required" class="app-form-required">*</span>
      </label>

      <AppInfoTooltip v-if="tip" :text="tip" :is-input-label="true" />
    </div>

    <VSelect
        :input-id="inputId"
        class="app-form-select2"
        :model-value="inputValue"
        :options="selectOptions"
        :reduce="(option: SelectOption) => option.value"
        label="label"
        :placeholder="placeholder"
        :disabled="disabled"
        :loading="isLoading"
        :clearable="true"
        :aria-required="Boolean(required)"
        :components="selectComponents"
        :filterable="!usesApi"
        :append-to-body="true"
        :calculate-position="calculateDropdownPosition"
        :selectable="(option: SelectOption) => !option.disabled"
        @update:model-value="emitValue"
        @search="handleSearch"
    >
      <template #selected-option="{ label }">
        <span class="app-select2-selected-option">
          {{ label }}
        </span>
      </template>

      <template #option="{ label }">
        <span class="app-select2-option">
          <span>{{ label }}</span>
        </span>
      </template>

      <template #open-indicator>
        <i class="fa-solid fa-chevron-down app-select2-input-arrow"></i>
      </template>

      <template #list-footer>
        <li
            v-if="usesApi && hasMorePages"
            :ref="setLoadMoreElement"
            class="app-select2-load-more"
        >
          {{ isLoading ? 'Carregando...' : '' }}
        </li>
      </template>

      <template #no-options>
        Nenhuma opção encontrada.
      </template>
    </VSelect>

    <input
        class="app-select2-required-input"
        :name="name"
        :value="inputValue"
        tabindex="-1"
        aria-hidden="true"
    >

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
