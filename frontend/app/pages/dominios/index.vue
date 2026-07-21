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
import {getTenantStatusBadgeClass, getTenantStatusLabel} from "~/utils/entities/tenant";


active: true
created_at: "08/06/2026 15:35"
domain: "api.app.127.0.0.1.sslip.io"
id: "base"
name: "base"
const columns: TableColumn[] = [
  {
    label: 'Nome',
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
    label: 'Domínio',
    name: 'domain',
    field: 'domain',
    priority: 0,
    sortable: null,
    wrap: true,
    render: null,
    valueFormatter: null,
    badgeClassResolver: null,
    showInDeleteModal: true,
  },
  {
    label: 'Status',
    name: 'active',
    field: 'active',
    priority: 0,
    sortable: false,
    wrap: null,
    render: null,
    valueFormatter: getTenantStatusLabel,
    badgeClassResolver: getTenantStatusBadgeClass,
    showInDeleteModal: false,
  },
  {
    label: 'Criação',
    name: 'created_at',
    field: 'created_at',
    priority: 1,
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

</script>

<template>
  <AppDataTable
      title="Domínios"
      subtitle="Gerencie a base de domínios."
      :columns="columns"
      :has-show="false"
      :has-edit="true"
      :has-delete="false"
      :has-export="false"
      base-url="/dominios"
      base-api-url="/tenants"
      entity-label="dominio"
      entity-plural-label="dominios"
      storage-key="domains.list.settings"
      search-placeholder="Buscar por domínio"
      :default-filters="filters"
      :filter-count-groups="filterCountGroups"
  >
  </AppDataTable>
</template>
