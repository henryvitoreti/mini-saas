<script setup lang="ts">
import VSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import type { PaginatedResponse } from '@/types/common/pagination';
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
  options?: SelectOption[];
  apiUrl?: string;
  searchParam?: string;
  pageParam?: string;
  perPageParam?: string;
  perPage?: number;
  errorMessage?: string | null;
}>(), {
  options: () => [],
  searchParam: 'search',
  pageParam: 'page',
  perPageParam: 'per_page',
  perPage: 10,
});

const emit = defineEmits<{
  update: [value: string];
  'update:value': [value: string];
  'update:modelValue': [value: string];
}>();

const inputId = computed<string>(() => `app-select2-input-${props.name}`);
const usesApi = computed<boolean>(() => Boolean(props.apiUrl));
const selectOptions = ref<SelectOption[]>([...props.options]);
const searchTerm = ref('');
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const requestIndex = ref(0);
const abortController = shallowRef<AbortController | null>(null);
const intersectionObserver = shallowRef<IntersectionObserver | null>(null);

const inputValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

function emitValue(value: string | number | null): void {
  const normalizedValue = value === null ? '' : String(value);

  emit('update', normalizedValue);
  emit('update:value', normalizedValue);
  emit('update:modelValue', normalizedValue);
}

function getResponseOptions(response: PaginatedResponse<SelectOption>): SelectOption[] {
  if (Array.isArray(response)) {
    return response;
  }

  return response.data ?? response.items ?? response.results ?? [];
}

function getResponseLastPage(response: PaginatedResponse<SelectOption>): number | undefined {
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
  abortController.value = controller;

  try {
    const response = await $fetch<PaginatedResponse<SelectOption>>(props.apiUrl, {
      signal: controller.signal,
      query: {
        [props.searchParam]: searchTerm.value,
        [props.pageParam]: page,
        [props.perPageParam]: props.perPage,
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
        : nextOptions.length >= props.perPage;
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

function setLoadMoreElement(element: Element | null): void {
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
  (options): void => {
    if (!usesApi.value) {
      selectOptions.value = [...options];
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

      <AppInfoTooltip v-if="tip" :text="tip" />
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
        :filterable="!usesApi"
        :selectable="(option: SelectOption) => !option.disabled"
        @update:model-value="emitValue"
        @search="handleSearch"
    >
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
        :required="required"
        tabindex="-1"
        aria-hidden="true"
    >

    <p v-if="errorMessage" class="app-form-error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>
