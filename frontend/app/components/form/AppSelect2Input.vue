<script setup lang="ts">
import debounce from 'lodash/debounce';
import VSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import AppInfoTooltip from '@/components/ui/AppInfoTooltip.vue';
import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { PaginatedResponse } from '@/types/common/pagination';
import type { SelectOption } from '@/types/common/select';
import type { AppSelect2InputProps } from '@/types/ui/form';
import AppSelectDeselectIcon from "~/components/form/AppSelectDeselectIcon.vue";

type SelectOptionsPayload = PaginatedResponse<SelectOption>|{
  options: SelectOption[]|null;
};

type SelectOptionsResponse = SelectOptionsPayload|ApiResponse<SelectOptionsPayload>;

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
    apiUrl: null,
    minSearchLength: 3,
    searchDebounceMs: 500,
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
const selectedOptionCache = ref<SelectOption|null>(null);
const searchTerm = ref('');
const normalizedSearchTerm = computed<string>(() => searchTerm.value.trim());
const minSearchLength = computed<number>(() => Math.max(0, props.minSearchLength ?? 3));
const isWaitingForSearchTerm = computed<boolean>(() => {
  return usesApi.value
      && normalizedSearchTerm.value !== ''
      && normalizedSearchTerm.value.length < minSearchLength.value;
});
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const requestIndex = ref(0);
const abortController = shallowRef<AbortController|null>(null);
const intersectionObserver = shallowRef<IntersectionObserver|null>(null);
const selectComponents = {
  Deselect: AppSelectDeselectIcon,
};

const inputValue = computed<string>(() => {
  return String(props.value ?? props.modelValue ?? '');
});

const selectedOption = computed<SelectOption|null>(() => {
  if (inputValue.value === '') {
    return null;
  }

  const matchingOption = selectOptions.value.find((option: SelectOption): boolean => {
    return String(option.value) === inputValue.value;
  });

  if (matchingOption !== undefined) {
    return matchingOption;
  }

  if (
    selectedOptionCache.value !== null
      && String(selectedOptionCache.value.value) === inputValue.value
  ) {
    return selectedOptionCache.value;
  }

  return {
    label: inputValue.value,
    value: inputValue.value,
    disabled: null,
  };
});

function isSelectOption(value: unknown): value is SelectOption {
  return typeof value === 'object' && value !== null && 'value' in value;
}

function emitValue(value: string|number|null): void {
  const normalizedValue = value === null ? '' : String(value);

  emit('update', normalizedValue);
  emit('update:value', normalizedValue);
  emit('update:modelValue', normalizedValue);
}

function emitSelectedOption(value: SelectOption|string|number|null): void {
  if (isSelectOption(value)) {
    selectedOptionCache.value = value;
    emitValue(value.value);
    return;
  }

  if (value === null) {
    selectedOptionCache.value = null;
  }

  emitValue(value);
}

function syncSelectedOptionCache(options: SelectOption[]): void {
  if (inputValue.value === '') {
    selectedOptionCache.value = null;
    return;
  }

  const matchingOption = options.find((option: SelectOption): boolean => {
    return String(option.value) === inputValue.value;
  });

  if (matchingOption !== undefined) {
    selectedOptionCache.value = matchingOption;
  }
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

function unwrapResponsePayload(response: SelectOptionsResponse): SelectOptionsPayload {
  if (!Array.isArray(response) && 'message' in response && 'data' in response) {
    return response.data;
  }

  return response;
}

function getResponseOptions(response: SelectOptionsResponse): SelectOption[] {
  const payload = unwrapResponsePayload(response);

  if (Array.isArray(payload)) {
    return payload;
  }

  if ('options' in payload) {
    return payload.options ?? [];
  }

  return payload.data ?? payload.items ?? payload.results ?? [];
}

function getResponseLastPage(response: SelectOptionsResponse): number|undefined {
  const payload = unwrapResponsePayload(response);

  if (Array.isArray(payload) || 'options' in payload) {
    return undefined;
  }

  return payload.last_page ?? payload.meta?.last_page;
}

async function fetchOptions(page: number): Promise<void> {
  if (!props.apiUrl || isLoading.value || isWaitingForSearchTerm.value || (!hasMorePages.value && page > 1)) {
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
    const response = await apiHttpClient.get<SelectOptionsResponse>(props.apiUrl, {
      signal: controller.signal,
      query: {
        [searchParam]: searchTerm.value,
        [pageParam]: page,
        [perPageParam]: perPage,
      },
      showGlobalLoading: false,
    });

    if (activeRequest !== requestIndex.value) {
      return;
    }

    const nextOptions = getResponseOptions(response);
    const lastPage = getResponseLastPage(response);

    selectOptions.value = page === 1
        ? nextOptions
        : [...selectOptions.value, ...nextOptions];
    syncSelectedOptionCache(selectOptions.value);

    currentPage.value = page;
    hasMorePages.value = lastPage
        ? page < lastPage
        : nextOptions.length >= perPage;
  } catch (error) {
    if (activeRequest === requestIndex.value) {
      console.error(error);
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

const debouncedResetRemoteOptions = debounce((): void => {
  void resetRemoteOptions();
}, Math.max(0, props.searchDebounceMs ?? 500));

function clearRemoteSearchResults(): void {
  debouncedResetRemoteOptions.cancel();
  abortController.value?.abort();
  requestIndex.value++;
  isLoading.value = false;
  selectOptions.value = [];
  currentPage.value = 1;
  hasMorePages.value = false;
}

function loadNextPage(): void {
  if (!usesApi.value || isLoading.value || isWaitingForSearchTerm.value || !hasMorePages.value) {
    return;
  }

  void fetchOptions(currentPage.value + 1);
}

function handleSearch(search: string): void {
  searchTerm.value = search;

  if (!usesApi.value) {
    return;
  }

  if (isWaitingForSearchTerm.value) {
    clearRemoteSearchResults();
    return;
  }

  debouncedResetRemoteOptions();
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
      syncSelectedOptionCache(selectOptions.value);
    }
  },
);

watch(inputValue, (value: string): void => {
  if (value === '') {
    selectedOptionCache.value = null;
    return;
  }

  syncSelectedOptionCache(selectOptions.value);
});

onMounted(() => {
  if (usesApi.value) {
    void fetchOptions(1);
  }
});

onBeforeUnmount(() => {
  debouncedResetRemoteOptions.cancel();
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
        :model-value="selectedOption"
        :options="selectOptions"
        label="label"
        :placeholder="placeholder"
        :disabled="disabled"
        :clearable="true"
        :aria-required="Boolean(required)"
        :components="selectComponents"
        :filterable="!usesApi"
        :append-to-body="true"
        :calculate-position="calculateDropdownPosition"
        :selectable="(option: SelectOption) => !option.disabled"
        @update:model-value="emitSelectedOption"
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
        <li v-if="isLoading && selectOptions.length > 0" class="app-select2-dropdown-state">
          <i class="fa-solid fa-spinner app-select2-loading-icon" aria-hidden="true"></i>
          <span>Carregando...</span>
        </li>

        <li
            v-else-if="usesApi && hasMorePages"
            :ref="setLoadMoreElement"
            class="app-select2-load-more"
        >
        </li>
      </template>

      <template #no-options>
        <div v-if="isLoading" class="app-select2-dropdown-state">
          <i class="fa-solid fa-spinner app-select2-loading-icon" aria-hidden="true"></i>
          <span>Carregando...</span>
        </div>

        <div v-else-if="isWaitingForSearchTerm" class="app-select2-dropdown-state">
          Digite ao menos {{ minSearchLength }} caracteres.
        </div>

        <div v-else class="app-select2-dropdown-state">
          Nenhuma opção encontrada.
        </div>
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
