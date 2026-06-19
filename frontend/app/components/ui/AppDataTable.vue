<script setup lang="ts">
import {computed, ref} from "vue";
import {useRouter} from "vue-router";
import type { TableColumn, TablePagination, TableRow } from '@/types/ui/table';

const props = withDefaults(defineProps<{
  title?: string;
  subtitle?: string;
  columns: TableColumn[];
  baseUrl: string;
  baseApiUrl: string;
  hasShow?: boolean;
  hasEdit?: boolean;
  hasDelete?: boolean;
  hasRestore?: boolean;
  hasCreate?: boolean;
  hasSearch?: boolean;
  hasFilters?: boolean;
  searchPlaceholder?: string;
}>(), {
  title: 'Registros',
  hasShow: false,
  hasEdit: false,
  hasDelete: false,
  hasRestore: false,
  hasCreate: false,
  hasSearch: true,
  hasFilters: false,
  searchPlaceholder: 'Pesquisar...',
});

const router = useRouter();

const search = ref<string>('');
const filtersOpen = ref<boolean>(false);
const selectedRow = ref<TableRow|null>(null);

const paginate = ref<TablePagination>({
  data: [
    { id: 1, name: 'João Pereira', phone: '(48) 99999-1111', is_active: true },
    { id: 2, name: 'Mariana Souza', phone: '(48) 98888-2222', is_active: true },
    { id: 3, name: 'Carlos Lima', phone: '(48) 97777-3333', is_active: false },
    { id: 4, name: 'Ana Martins', phone: '(48) 96666-4444', is_active: true },
  ],
  current_page: 1,
  last_page: 4,
  per_page: 10,
  total: 24,
  from: 1,
  to: 10,
  prev_page_url: null,
  next_page_url: '/fake?page=2',
});

const hasActions = computed<boolean>(() => {
  return props.hasShow || props.hasEdit || props.hasDelete || props.hasRestore;
});

const visiblePages = computed<number[]>(() => {
  return Array.from({ length: paginate.value.last_page }, (_, index: number): number => {
    return index + 1;
  });
});

function getColumnValue(row: TableRow, column: TableColumn): unknown {
  const value = row[column.field];

  if (column.render) {
    return column.render(row, value);
  }

  return value ?? '-';
}

function getCellClass(column: TableColumn): string {
  return column.wrap ? 'app-table-cell-wrap' : 'app-table-cell-nowrap';
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

function closeFilters(): void {
  filtersOpen.value = false;
}

function applyFilters(): void {
  closeFilters();
}

function handleCreate(): void {
  router.push(`${props.baseUrl}/criar`);
}

function handleShow(row: TableRow): void {
  router.push(`${props.baseUrl}/${row.id}`);
}

function handleEdit(row: TableRow): void {
  // router.push(`${props.baseUrl}/editar`);
  router.push(`${props.baseUrl}/${row.id}/editar`);
}

function handleDelete(row: TableRow): void {
  console.log('delete futuro:', `${props.baseApiUrl}/${row.id}`);
}

function handleRestore(row: TableRow): void {
  console.log('restore futuro:', `${props.baseApiUrl}/${row.id}/restore`);
}

function changePage(page: number): void {
  if (page < 1 || page > paginate.value.last_page) {
    return;
  }

  paginate.value.current_page = page;
  selectedRow.value = null;
}
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

      <button v-if="hasCreate" class="btn btn-custom-primary " type="button" @click="handleCreate">
        <i class="fa-solid fa-plus me-2"></i>
        Novo
      </button>
    </div>

    <div class="app-data-table-toolbar">
      <div v-if="hasSearch" class="app-data-table-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input v-model="search" type="text" :placeholder="searchPlaceholder">
      </div>

      <button v-if="hasFilters" class="btn app-data-table-filter-button" type="button" title="Filtros" @click="openFilters">
        <i class="fa-solid fa-filter"></i>
      </button>
    </div>

    <div class="app-data-table-card">
      <div class="app-data-table-card-header">
        <span>
          Exibindo {{ paginate.from ?? 0 }} até {{ paginate.to ?? 0 }} de {{ paginate.total }} registros
        </span>
      </div>

      <div class="table-responsive app-table-responsive">
        <table class="table app-table mb-0">
          <thead>
          <tr>
            <th v-for="column in columns" :key="column.name" :class="getCellClass(column)">
              {{ column.label }}
            </th>
          </tr>
          </thead>

          <tbody>
          <tr v-if="paginate.data.length === 0">
            <td :colspan="columns.length">
              <div class="app-table-empty">
                Nenhum registro encontrado.
              </div>
            </td>
          </tr>

          <tr
              v-for="row in paginate.data"
              :key="String(row.id)"
              class="app-table-row"
              :class="{ 'is-selected': isSelectedRow(row) }"
              @click="selectRow(row)"
          >
            <td v-for="column in columns" :key="column.name" :class="getCellClass(column)">
              {{ getColumnValue(row, column) }}
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <footer class="app-data-table-footer">
        <nav class="app-pagination">
          <button
              class="btn btn-sm btn-outline-custom-primary px-0 px-sm-4"
              type="button"
              :disabled="paginate.current_page === 1"
              @click="changePage(paginate.current_page - 1)"
          >
            <i class="fa-solid fa-angle-left"></i>
          </button>

          <button
              v-for="page in visiblePages"
              :key="page"
              class="btn btn-sm"
              :class="page === paginate.current_page ? 'btn-custom-primary' : 'btn-outline-custom-primary'"
              type="button"
              @click="changePage(page)"
          >
            {{ page }}
          </button>

          <button
              class="btn btn-sm btn-outline-custom-primary px-0 px-sm-4"
              type="button"
              :disabled="paginate.current_page === paginate.last_page"
              @click="changePage(paginate.current_page + 1)"
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

    <div v-if="filtersOpen" class="app-offcanvas-backdrop" @click="closeFilters"></div>

    <aside class="app-offcanvas" :class="{ 'is-open': filtersOpen }">
      <header class="app-offcanvas-header">
        <strong>Filtros</strong>

        <button type="button" class="btn btn-sm" @click="closeFilters">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </header>

      <div class="app-offcanvas-body">
        <slot name="filters"></slot>
      </div>

      <footer class="app-offcanvas-footer">
        <button class="btn btn-outline-secondary" type="button" @click="closeFilters">
          Cancelar
        </button>

        <button class="btn btn-primary" type="button" @click="applyFilters">
          Aplicar filtros
        </button>
      </footer>
    </aside>
  </section>
</template>
