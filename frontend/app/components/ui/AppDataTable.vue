<script setup lang="ts">
import debounce from 'lodash/debounce';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { apiHttpClient } from '@/services/api/http-client';
import type { ApiResponse } from '@/types/api/http';
import type {
  AppDataTableFilterSlotProps,
  AppDataTableProps,
  TableColumn,
  TableDateRangeValue,
  TableFilterInputValue,
  TableFilterState,
  TableFilterStateValue,
  TableFilterValue,
  TablePagination,
  TableRow,
  TableSortOrder,
} from '@/types/ui/table';
import { capitalizeFirstLetter } from '@/utils/text-format';

type AppDataTableListResponse = {
  items: TableRow[]|null;
  data: TableRow[]|null;
  current_page: number|null;
  last_page: number|null;
  per_page: number|null;
  total: number|null;
  from: number|null;
  to: number|null;
  prev_page_url: string|null;
  next_page_url: string|null;
};

type AppDataTableStorage = {
  expires_at: number;
  filters: TableFilterState;
  limit: number;
};

const DATA_TABLE_STORAGE_TTL = 60 * 60 * 1000;
const DATA_TABLE_FETCH_DEBOUNCE_DELAY = 400;

const props = withDefaults(
  defineProps<AppDataTableProps>(),
  {
    title: 'Registros',
    entityLabel: 'registro',
    hasShow: true,
    hasEdit: true,
    hasDelete: true,
    hasRestore: false,
    hasCreate: true,
    hasSearch: true,
    hasFilters: true,
    searchPlaceholder: 'Pesquisar...',
    defaultFilters: () => ({}),
    defaultSortBy: 'id',
    defaultOrder: 'DESC',
    hasLimitSelector: true,
    defaultLimit: 20,
    limitOptions: () => [10, 20, 50, 100],
    withDetails: true,
  },
);

const slots = defineSlots<{
  filters(props: AppDataTableFilterSlotProps): unknown;
}>();

const router = useRouter();
const toast = useAppToast();

const localSearch = ref<string>('');
const filtersOpen = ref<boolean>(false);
const limitSelectorOpen = ref<boolean>(false);
const selectedRow = ref<TableRow|null>(null);
const rowToDelete = ref<TableRow|null>(null);
const isLoading = ref<boolean>(false);
const isDeleting = ref<boolean>(false);
const page = ref<number>(1);
const limit = ref<number>(props.defaultLimit ?? 20);
const sortBy = ref<string>(props.defaultSortBy ?? 'id');
const order = ref<TableSortOrder>(props.defaultOrder ?? 'DESC');
const filterValues = reactive<TableFilterState>({});
const appliedFilterValues = reactive<TableFilterState>({});
const pagination = ref<TablePagination>(createEmptyPagination(limit.value));

const hasActions = computed<boolean>(() => {
  return Boolean(props.hasShow || props.hasEdit || props.hasDelete || props.hasRestore);
});

const canShowFilters = computed<boolean>(() => {
  return Boolean(props.hasFilters) && Boolean(slots.filters);
});

const availableLimitOptions = computed<number[]>(() => {
  return props.limitOptions ?? [20];
});

const activeEntityLabel = computed<string>(() => {
  return props.entityLabel ?? 'registro';
});

const activeEntityPluralLabel = computed<string>(() => {
  return props.entityPluralLabel ?? `${activeEntityLabel.value}s`;
});

const tableStorageKey = computed<string>(() => {
  return props.storageKey ?? `app-data-table:${props.baseApiUrl}`;
});

const activeAppliedFiltersCount = computed<number>(() => {
  const groups = props.filterCountGroups ?? Object.keys(appliedFilterValues).map((key: string): string[] => [key]);

  return groups.reduce((count: number, group: string[]): number => {
    const hasValue = group.some((name: string): boolean => {
      const value = appliedFilterValues[name];

      if (Array.isArray(value)) {
        return hasFilterValue(value[0]) || hasFilterValue(value[1]);
      }

      return hasFilterValue(value);
    });

    return hasValue ? count + 1 : count;
  }, 0);
});

const visiblePages = computed<number[]>(() => {
  const currentPage = pagination.value.current_page;
  const lastPage = pagination.value.last_page;
  const startPage = Math.max(currentPage - 2, 1);
  const endPage = Math.min(startPage + 4, lastPage);
  const adjustedStartPage = Math.max(endPage - 4, 1);

  return Array.from({ length: endPage - adjustedStartPage + 1 }, (_, index: number): number => {
    return adjustedStartPage + index;
  });
});

const deleteModalColumns = computed<TableColumn[]>(() => {
  const columns = props.columns.filter((column: TableColumn): boolean => {
    return column.showInDeleteModal !== false;
  });

  return columns.length > 0 ? columns : props.columns;
});

const debouncedFetchRows = debounce((): void => {
  void fetchRows();
}, DATA_TABLE_FETCH_DEBOUNCE_DELAY);

watch(
  () => props.defaultFilters,
  (): void => {
    ensureFilterState();
  },
  { immediate: true },
);

watch(localSearch, (): void => {
  page.value = 1;
  clearSelectedRow();
  debouncedFetchRows();
});

function createEmptyPagination(perPage: number): TablePagination {
  return {
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: perPage,
    total: 0,
    from: null,
    to: null,
    prev_page_url: null,
    next_page_url: null,
  };
}

function hasFilterValue(value: unknown): boolean {
  return value !== null && value !== undefined && value !== '';
}

function ensureFilterState(): void {
  Object.entries(props.defaultFilters ?? {}).forEach(([name, value]): void => {
    if (!(name in filterValues)) {
      filterValues[name] = value;
    }

    if (!(name in appliedFilterValues)) {
      appliedFilterValues[name] = value;
    }
  });
}

function copyFilterValues(
  target: TableFilterState,
  source: TableFilterState,
): void {
  Object.keys(props.defaultFilters ?? {}).forEach((name: string): void => {
    const value = source[name] ?? props.defaultFilters?.[name] ?? null;
    target[name] = Array.isArray(value) ? [...value] as TableDateRangeValue : value;
  });
}

function getStoredSettings(): AppDataTableStorage|null {
  if (!import.meta.client) {
    return null;
  }

  const storedSettings = localStorage.getItem(tableStorageKey.value);

  if (!storedSettings) {
    return null;
  }

  try {
    const parsedSettings = JSON.parse(storedSettings) as AppDataTableStorage;

    if (parsedSettings.expires_at <= Date.now()) {
      localStorage.removeItem(tableStorageKey.value);
      return null;
    }

    return parsedSettings;
  } catch {
    localStorage.removeItem(tableStorageKey.value);
    return null;
  }
}

function persistSettings(): void {
  if (!import.meta.client) {
    return;
  }

  const settings: AppDataTableStorage = {
    expires_at: Date.now() + DATA_TABLE_STORAGE_TTL,
    filters: {...appliedFilterValues},
    limit: limit.value,
  };

  localStorage.setItem(tableStorageKey.value, JSON.stringify(settings));
}

function restoreSettings(): void {
  const settings = getStoredSettings();

  if (settings === null) {
    return;
  }

  Object.keys(props.defaultFilters ?? {}).forEach((name: string): void => {
    if (name in settings.filters) {
      const value = settings.filters[name];
      filterValues[name] = Array.isArray(value) ? [...value] as TableDateRangeValue : value;
      appliedFilterValues[name] = Array.isArray(value) ? [...value] as TableDateRangeValue : value;
    }
  });

  limit.value = availableLimitOptions.value.includes(settings.limit) ? settings.limit : (props.defaultLimit ?? 20);
  pagination.value = createEmptyPagination(limit.value);
}

function normalizeParams(params: Record<string, TableFilterValue>): Record<string, string|number|boolean> {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]): boolean => {
      return value !== null && value !== '';
    }),
  ) as Record<string, string|number|boolean>;
}

function getFilterValue(name: string): TableFilterStateValue {
  return filterValues[name] ?? props.defaultFilters?.[name] ?? null;
}

function getSelectFilterValue(name: string): TableFilterInputValue {
  const value = getFilterValue(name);

  if (Array.isArray(value) || typeof value === 'boolean') {
    return value === true ? 'true' : value === false ? 'false' : null;
  }

  return value;
}

function getDateRangeFilterValue(startName: string, endName: string): TableDateRangeValue {
  const startValue = getFilterValue(startName);
  const endValue = getFilterValue(endName);

  return [
    typeof startValue === 'string' ? startValue : null,
    typeof endValue === 'string' ? endValue : null,
  ];
}

function updateFilterValue(name: string, value: TableFilterStateValue): void {
  if (Array.isArray(value)) {
    filterValues[name] = [...value] as TableDateRangeValue;
    return;
  }

  filterValues[name] = value === '' ? null : value;
}

function updateDateRangeFilterValue(startName: string, endName: string, value: string|TableDateRangeValue): void {
  if (!Array.isArray(value)) {
    filterValues[startName] = null;
    filterValues[endName] = null;
    return;
  }

  filterValues[startName] = value[0] === '' ? null : value[0];
  filterValues[endName] = value[1] === '' ? null : value[1];
}

function buildParams(): Record<string, string|number|boolean> {
  const params: Record<string, TableFilterValue> = {
    search: localSearch.value || null,
    page: page.value,
    limit: limit.value,
    sort_by: sortBy.value,
    order: order.value,
  };

  if (props.withDetails !== false) {
    params.with_details = true;
  }

  Object.entries(appliedFilterValues).forEach(([name, value]): void => {
    if (!Array.isArray(value)) {
      params[name] = value;
    }
  });

  return normalizeParams(params);
}

function normalizePagination(response: AppDataTableListResponse): TablePagination {
  return {
    data: response.items ?? response.data ?? [],
    current_page: response.current_page ?? 1,
    last_page: response.last_page ?? 1,
    per_page: response.per_page ?? limit.value,
    total: response.total ?? 0,
    from: response.from,
    to: response.to,
    prev_page_url: response.prev_page_url,
    next_page_url: response.next_page_url,
  };
}

function getErrorMessage(error: unknown, fallback: string): string {
  if (
    typeof error === 'object'
    && error !== null
    && 'data' in error
    && typeof (error as { data: { message: unknown|null }|null }).data?.message === 'string'
  ) {
    return (error as { data: { message: string } }).data.message;
  }

  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

async function fetchRows(): Promise<void> {
  isLoading.value = true;

  try {
    const response = await apiHttpClient.get<ApiResponse<AppDataTableListResponse>>(props.baseApiUrl, {
      query: buildParams(),
      showGlobalLoading: false,
    });

    pagination.value = normalizePagination(response.data);
  } catch (error) {
    toast.error(getErrorMessage(error, `Não foi possível carregar os ${activeEntityPluralLabel.value}.`));
  } finally {
    isLoading.value = false;
  }
}

function cancelDebouncedFetchRows(): void {
  debouncedFetchRows.cancel();
}

function getColumnDisplayValue(row: TableRow, column: TableColumn): string {
  const value = row[column.field];

  if (column.valueFormatter !== null) {
    return column.valueFormatter(value, row);
  }

  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return String(value);
}

function getColumnBadgeClass(row: TableRow, column: TableColumn): string|null {
  const value = row[column.field];

  if (column.badgeClassResolver === null) {
    return null;
  }

  return column.badgeClassResolver(value, row);
}

function getCellClass(column: TableColumn): string[] {
  return [
    column.wrap ? 'app-table-cell-wrap' : 'app-table-cell-nowrap',
    `app-table-priority-${column.priority ?? 0}`,
  ];
}

function getFilterCountLabel(count: number): string {
  if (count > 9) {
    return '9+';
  }

  return String(count);
}

function isSelectedRow(row: TableRow): boolean {
  return selectedRow.value?.id === row.id;
}

function selectRow(row: TableRow): void {
  if (isSelectedRow(row)) {
    selectedRow.value = null;

    return;
  }

  selectedRow.value = row;
}

function clearSelectedRow(): void {
  selectedRow.value = null;
}

function openFilters(): void {
  filtersOpen.value = true;
}

function closeFilters(shouldRestoreFilters = false): void {
  filtersOpen.value = false;

  if (shouldRestoreFilters) {
    copyFilterValues(filterValues, appliedFilterValues);
  }
}

function applyFilters(): void {
  cancelDebouncedFetchRows();
  closeFilters();
  copyFilterValues(appliedFilterValues, filterValues);
  page.value = 1;
  persistSettings();
  clearSelectedRow();
  void fetchRows();
}

function handleCreate(): void {
  router.push(`${props.baseUrl}/criar`);
}

function handleShow(row: TableRow): void {
  router.push(`${props.baseUrl}/${row.id}`);
}

function handleEdit(row: TableRow): void {
  router.push(`${props.baseUrl}/${row.id}/editar`);
}

function handleDelete(row: TableRow): void {
  rowToDelete.value = row;
}

function closeDeleteModal(): void {
  if (isDeleting.value) {
    return;
  }

  rowToDelete.value = null;
}

function getRowEndpoint(row: TableRow): string {
  return `${props.baseApiUrl.replace(/\/$/, '')}/${row.id}`;
}

async function confirmDelete(): Promise<void> {
  if (rowToDelete.value === null) {
    return;
  }

  cancelDebouncedFetchRows();
  isDeleting.value = true;

  try {
    await apiHttpClient.delete<ApiResponse<[]>>(getRowEndpoint(rowToDelete.value));
    toast.success(`${capitalizeFirstLetter(activeEntityLabel.value)} excluído com sucesso.`);
    rowToDelete.value = null;
    clearSelectedRow();
    await fetchRows();
  } catch (error) {
    toast.error(getErrorMessage(error, `Não foi possível excluir o ${activeEntityLabel.value}.`));
  } finally {
    isDeleting.value = false;
  }
}

function changePage(nextPage: number): void {
  if (nextPage < 1 || nextPage > pagination.value.last_page || nextPage === pagination.value.current_page) {
    return;
  }

  cancelDebouncedFetchRows();
  page.value = nextPage;
  clearSelectedRow();
  void fetchRows();
}

function changeSort(column: TableColumn): void {
  if (column.sortable === false) {
    return;
  }

  order.value = sortBy.value === column.field && order.value === 'DESC' ? 'ASC' : 'DESC';
  sortBy.value = column.field;
  page.value = 1;
  clearSelectedRow();
  debouncedFetchRows();
}

function changeLimit(nextLimit: number): void {
  cancelDebouncedFetchRows();
  limitSelectorOpen.value = false;
  limit.value = nextLimit;
  page.value = 1;
  persistSettings();
  clearSelectedRow();
  void fetchRows();
}

function toggleLimitSelector(): void {
  limitSelectorOpen.value = !limitSelectorOpen.value;
}

function refreshRows(): void {
  cancelDebouncedFetchRows();
  page.value = 1;
  clearSelectedRow();
  void fetchRows();
}

onMounted((): void => {
  ensureFilterState();
  restoreSettings();
  void fetchRows();
});

onBeforeUnmount((): void => {
  cancelDebouncedFetchRows();
});
</script>

<template>
  <section class="app-data-table">
    <div class="app-data-table-header">
      <div>
        <h1 class="app-data-table-title">
          {{ title }}
        </h1>

        <span v-show="subtitle" class="app-data-table-subtitle">
          {{ subtitle }}
        </span>
      </div>

      <button v-if="hasCreate" class="btn btn-custom-primary" type="button" @click="handleCreate">
        <i class="fa-solid fa-plus me-2"></i>
        Novo
      </button>
    </div>

    <div class="app-data-table-toolbar">
      <div v-if="hasSearch" class="app-data-table-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input v-model="localSearch" type="text" :placeholder="searchPlaceholder">
      </div>

      <button
          v-if="hasSearch"
          class="btn app-data-table-icon-button"
          type="button"
          title="Atualizar"
          aria-label="Atualizar listagem"
          :disabled="isLoading"
          @click="refreshRows"
      >
        <i class="fa-solid fa-arrows-rotate"></i>
      </button>

      <div v-if="hasLimitSelector" class="app-data-table-limit">
        <button
            class="btn app-data-table-icon-button"
            type="button"
            title="Registros por página"
            :aria-expanded="limitSelectorOpen"
            @click="toggleLimitSelector"
        >
          <i class="fa-solid fa-list-ol"></i>
          <span class="app-data-table-limit-current">{{ limit }}</span>
        </button>

        <div v-if="limitSelectorOpen" class="app-data-table-limit-menu">
          <button
              v-for="option in availableLimitOptions"
              :key="option"
              class="app-data-table-limit-option"
              :class="{ 'is-selected': option === limit }"
              type="button"
              @click="changeLimit(option)"
          >
            <span>{{ option }}</span>
            <i v-if="option === limit" class="fa-solid fa-check"></i>
          </button>
        </div>
      </div>

      <button
          v-if="canShowFilters"
          class="btn app-data-table-icon-button app-data-table-filter-button"
          type="button"
          title="Filtros"
          @click="openFilters"
      >
        <i class="fa-solid fa-filter"></i>
        <span v-if="activeAppliedFiltersCount > 0" class="app-data-table-filter-count">
          {{ getFilterCountLabel(activeAppliedFiltersCount) }}
        </span>
      </button>
    </div>

    <div class="app-data-table-card">
      <div class="app-data-table-card-header">
        <span>
          Exibindo {{ pagination.from ?? 0 }} até {{ pagination.to ?? 0 }} de {{ pagination.total }} registros
        </span>
      </div>

      <div class="table-responsive app-table-responsive">
        <table class="table app-table mb-0">
          <thead>
          <tr>
            <th
                v-for="column in columns"
                :key="column.name"
                :class="getCellClass(column)"
            >
              <button
                  class="app-table-sort-button"
                  type="button"
                  :disabled="column.sortable === false"
                  @click="changeSort(column)"
              >
                <span>{{ column.label }}</span>
                <i
                    v-if="sortBy === column.field"
                    class="fa-solid"
                    :class="order === 'ASC' ? 'fa-arrow-up-short-wide' : 'fa-arrow-down-wide-short'"
                ></i>
              </button>
            </th>
          </tr>
          </thead>

          <tbody>
          <tr v-if="isLoading">
            <td :colspan="columns.length">
              <div class="app-table-empty">
                Carregando registros...
              </div>
            </td>
          </tr>

          <tr v-else-if="pagination.data.length === 0">
            <td :colspan="columns.length">
              <div class="app-table-empty">
                Nenhum registro encontrado.
              </div>
            </td>
          </tr>

          <template v-else>
            <tr
                v-for="row in pagination.data"
                :key="String(row.id)"
                class="app-table-row"
                :class="{ 'is-selected': isSelectedRow(row) }"
                @click="selectRow(row)"
            >
              <td v-for="column in columns" :key="column.name" :class="getCellClass(column)">
                <slot
                    :name="`cell-${column.name}`"
                    :row="row"
                    :value="row[column.field]"
                >
                  <span
                      v-if="getColumnBadgeClass(row, column)"
                      class="app-badge"
                      :class="getColumnBadgeClass(row, column)"
                  >
                    {{ getColumnDisplayValue(row, column) }}
                  </span>

                  <span v-else>
                    {{ getColumnDisplayValue(row, column) }}
                  </span>
                </slot>
              </td>
            </tr>
          </template>
          </tbody>
        </table>
      </div>

      <footer class="app-data-table-footer">
        <nav class="app-pagination">
          <button
              class="btn btn-sm btn-outline-custom-primary px-0 px-sm-4"
              type="button"
              :disabled="pagination.current_page === 1 || isLoading"
              @click="changePage(pagination.current_page - 1)"
          >
            <i class="fa-solid fa-angle-left"></i>
          </button>

          <button
              v-for="visiblePage in visiblePages"
              :key="visiblePage"
              class="btn btn-sm"
              :class="visiblePage === pagination.current_page ? 'btn-custom-primary' : 'btn-outline-custom-primary'"
              type="button"
              :disabled="isLoading"
              @click="changePage(visiblePage)"
          >
            {{ visiblePage }}
          </button>

          <button
              class="btn btn-sm btn-outline-custom-primary px-0 px-sm-4"
              type="button"
              :disabled="pagination.current_page === pagination.last_page || isLoading"
              @click="changePage(pagination.current_page + 1)"
          >
            <i class="fa-solid fa-angle-right"></i>
          </button>
        </nav>
      </footer>
    </div>

    <div v-if="hasActions && selectedRow" class="app-table-floating-actions">
      <button
          class="app-table-floating-actions-close"
          type="button"
          title="Limpar seleção"
          @click="clearSelectedRow"
      >
        <i class="fa-solid fa-minus"></i>
      </button>

      <button v-if="hasShow" class="app-table-floating-action" type="button" title="Visualizar" @click="handleShow(selectedRow)">
        <i class="fa-solid fa-eye"></i>
      </button>

      <button v-if="hasEdit" class="app-table-floating-action" type="button" title="Editar" @click="handleEdit(selectedRow)">
        <i class="fa-solid fa-pen"></i>
      </button>

      <button v-if="hasDelete" class="app-table-floating-action is-danger" type="button" title="Excluir" @click="handleDelete(selectedRow)">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>

    <div v-if="filtersOpen" class="app-offcanvas-backdrop" @click="closeFilters(true)"></div>

    <aside v-if="canShowFilters" class="app-offcanvas" :class="{ 'is-open': filtersOpen }">
      <header class="app-offcanvas-header">
        <strong>Filtros</strong>

        <button type="button" class="btn btn-sm" @click="closeFilters(true)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </header>

      <div class="app-offcanvas-body">
        <slot
            name="filters"
            :filter-values="filterValues"
            :get-filter-value="getFilterValue"
            :get-select-filter-value="getSelectFilterValue"
            :get-date-range-filter-value="getDateRangeFilterValue"
            :update-filter-value="updateFilterValue"
            :update-date-range-filter-value="updateDateRangeFilterValue"
        ></slot>
      </div>

      <footer class="app-offcanvas-footer">
        <button class="btn btn-outline-secondary" type="button" @click="closeFilters(true)">
          Cancelar
        </button>

        <button class="btn btn-primary" type="button" @click="applyFilters">
          Aplicar filtros
        </button>
      </footer>
    </aside>

    <AppDialog
        v-if="rowToDelete"
        :title="'Excluir ' + activeEntityLabel"
        :is-cancel-disabled="isDeleting"
        @close="closeDeleteModal"
    >
      <p>
        Tem certeza que deseja excluir este {{ activeEntityLabel }}?
      </p>

      <dl class="app-dialog-details">
        <div v-for="column in deleteModalColumns" :key="column.name">
          <dt>{{ column.label }}</dt>
          <dd>
            <span
                v-if="getColumnBadgeClass(rowToDelete, column)"
                class="app-badge"
                :class="getColumnBadgeClass(rowToDelete, column)"
            >
              {{ getColumnDisplayValue(rowToDelete, column) }}
            </span>

            <span v-else>
              {{ getColumnDisplayValue(rowToDelete, column) }}
            </span>
          </dd>
        </div>
      </dl>

      <template #footer-actions>
        <button class="btn btn-danger" type="button" :disabled="isDeleting" @click="confirmDelete">
          <i class="fa-solid fa-trash me-2"></i>
          Excluir
        </button>
      </template>
    </AppDialog>
  </section>
</template>
