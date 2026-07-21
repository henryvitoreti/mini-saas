<script setup lang="ts">
import AppDateInput from '@/components/form/AppDateInput.vue';
import AppSelectInput from '@/components/form/AppSelectInput.vue';
import AppDataTable from '@/components/ui/AppDataTable.vue';
import {
  customerStatusOptions,
  customerTypeOptions,
  getCustomerStatusBadgeClass,
  getCustomerStatusLabel,
  getCustomerTypeBadgeClass,
  getCustomerTypeLabel,
} from '@/utils/entities/customer';
import type { TableColumn, TableFilters } from '@/types/ui/table';

const columns: TableColumn[] = [
  {
    label: '#',
    name: 'id',
    field: 'id',
    priority: 0,
    sortable: null,
    wrap: null,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: true,
  },
  {
    label: 'Nome',
    name: 'name',
    field: 'name',
    priority: 0,
    sortable: null,
    wrap: true,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: true,
  },
  {
    label: 'Documento',
    name: 'document',
    field: 'document',
    priority: 0,
    sortable: null,
    wrap: true,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: true,
  },
  {
    label: 'E-mail',
    name: 'email',
    field: 'email',
    priority: 1,
    sortable: null,
    wrap: null,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: true,
  },
  {
    label: 'Telefone',
    name: 'phone',
    field: 'phone',
    priority: 2,
    sortable: null,
    wrap: null,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: true,
  },
  {
    label: 'Status',
    name: 'is_active',
    field: 'is_active',
    priority: 0,
    sortable: false,
    wrap: null,
    render: null,
    valueFormatter: getCustomerStatusLabel,
    badgeClassResolver: getCustomerStatusBadgeClass,
    showInDeleteModal: false,
  },
  {
    label: 'Tipo',
    name: 'type',
    field: 'type',
    priority: 0,
    sortable: false,
    wrap: null,
    render: null,
    valueFormatter: getCustomerTypeLabel,
    badgeClassResolver: getCustomerTypeBadgeClass,
    showInDeleteModal: false,
  },
  {
    label: 'Criação',
    name: 'created_at',
    field: 'created_at',
    priority: 3,
    sortable: null,
    wrap: null,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: false,
  },
];

const filters: TableFilters = {
  start_date: null,
  end_date: null,
  type: null,
  is_active: null,
};

const filterCountGroups = [
  ['start_date', 'end_date'],
  ['type'],
  ['is_active'],
];

function parseCustomerStatusFilter(value: string): boolean|null {
  if (value === '') {
    return null;
  }

  return value === 'true';
}
</script>

<template>
  <AppDataTable
      title="Clientes"
      subtitle="Gerencie a base de clientes da oficina."
      :columns="columns"
      :has-show="false"
      base-url="/clientes"
      base-api-url="/customers"
      entity-label="cliente"
      entity-plural-label="clientes"
      storage-key="customers.list.settings"
      search-placeholder="Buscar por nome, documento, e-mail ou telefone..."
      :default-filters="filters"
      :filter-count-groups="filterCountGroups"
  >
    <template
        #filters="{
          getSelectFilterValue,
          getDateRangeFilterValue,
          updateFilterValue,
          updateDateRangeFilterValue,
        }"
    >
      <div class="app-filter-stack">
        <AppDateInput
            name="customer_date_range_filter"
            label="Período"
            placeholder="Início e fim"
            :range="true"
            :value="getDateRangeFilterValue('start_date', 'end_date')"
            @update:value="(value) => updateDateRangeFilterValue('start_date', 'end_date', value)"
        />

        <AppSelectInput
            name="customer_type_filter"
            label="Tipo"
            placeholder="Todos"
            :value="getSelectFilterValue('type')"
            :options="customerTypeOptions"
            @update:value="(value) => updateFilterValue('type', value)"
        />

        <AppSelectInput
            name="customer_status_filter"
            label="Status"
            placeholder="Todos"
            :value="getSelectFilterValue('is_active')"
            :options="customerStatusOptions"
            @update:value="(value) => updateFilterValue('is_active', parseCustomerStatusFilter(value))"
        />
      </div>
    </template>
  </AppDataTable>
</template>
