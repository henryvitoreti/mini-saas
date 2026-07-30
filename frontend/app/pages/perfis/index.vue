<script setup lang="ts">
import AppSelectInput from '@/components/form/AppSelectInput.vue';
import AppDataTable from '@/components/ui/AppDataTable.vue';
import type { TableBeforeAction, TableColumn, TableFilters, TableRow } from '@/types/ui/table';
import {
  getRoleModificationBadgeClass,
  getRoleModificationLabel,
  getRoleStatusBadgeClass,
  getRoleStatusLabel,
  roleModificationOptions,
  roleStatusOptions,
} from '@/utils/entities/role';

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
    label: 'Descrição',
    name: 'description',
    field: 'description',
    priority: 1,
    sortable: false,
    wrap: true,
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
    valueFormatter: getRoleStatusLabel,
    badgeClassResolver: getRoleStatusBadgeClass,
    showInDeleteModal: false,
  },
  {
    label: 'Modificação',
    name: 'can_modify',
    field: 'can_modify',
    priority: 2,
    sortable: false,
    wrap: null,
    render: null,
    valueFormatter: getRoleModificationLabel,
    badgeClassResolver: getRoleModificationBadgeClass,
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
  is_active: null,
  can_modify: null,
};

const filterCountGroups = [
  ['is_active'],
  ['can_modify'],
];

function parseBooleanFilter(value: string): boolean|null {
  if (value === '') {
    return null;
  }

  return value === 'true';
}

const beforeEdit: TableBeforeAction = (row: TableRow): boolean|string => {
  return row.can_modify === true
    ? true
    : 'Este perfil é protegido e não pode ser editado.';
};

const beforeDelete: TableBeforeAction = (row: TableRow): boolean|string => {
  return row.can_modify === true
    ? true
    : 'Este perfil é protegido e não pode ser excluído.';
};
</script>

<template>
  <AppDataTable
      title="Perfis de acesso"
      subtitle="Gerencie os perfis e as permissões disponíveis para cada empresa."
      :columns="columns"
      :has-show="false"
      :has-edit="true"
      :has-delete="true"
      :has-restore="false"
      :has-create="true"
      :has-search="true"
      :has-export="true"
      :has-filters="true"
      :has-limit-selector="true"
      :default-limit="20"
      :limit-options="[10, 20, 50, 100]"
      default-sort-by="id"
      default-order="DESC"
      :with-details="false"
      :before-edit="beforeEdit"
      :before-delete="beforeDelete"
      base-url="/perfis"
      base-api-url="/roles"
      entity-label="perfil"
      entity-plural-label="perfis"
      storage-key="roles.list.settings"
      search-placeholder="Buscar por nome..."
      :default-filters="filters"
      :filter-count-groups="filterCountGroups"
  >
    <template #filters="{ getSelectFilterValue, updateFilterValue }">
      <div class="app-filter-stack">
        <AppSelectInput
            name="role_status_filter"
            label="Status"
            placeholder="Todos"
            :value="getSelectFilterValue('is_active')"
            :model-value="null"
            :required="false"
            :disabled="false"
            :clearable="true"
            :tip="null"
            :error-message="null"
            :options="roleStatusOptions"
            @update:value="(value) => updateFilterValue('is_active', parseBooleanFilter(String(value ?? '')))"
        />

        <AppSelectInput
            name="role_modification_filter"
            label="Modificação"
            placeholder="Todas"
            :value="getSelectFilterValue('can_modify')"
            :model-value="null"
            :required="false"
            :disabled="false"
            :clearable="true"
            :tip="null"
            :error-message="null"
            :options="roleModificationOptions"
            @update:value="(value) => updateFilterValue('can_modify', parseBooleanFilter(String(value ?? '')))"
        />
      </div>
    </template>
  </AppDataTable>
</template>
