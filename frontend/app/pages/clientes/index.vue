<script type="ts" setup>

import AppDataTable from "@/components/ui/AppDataTable.vue";

const columns = [
  {
    label: 'Nome',
    name: 'name',
    field: 'name',
  },
  {
    label: 'Telefone',
    name: 'phone',
    field: 'phone',
  },
  {
    label: 'Status',
    name: 'is_active',
    field: 'is_active',
    render: (row, value) => {
      return value ? 'Ativo' : 'Inativo';
    },
  },
];
</script>

<template>
  <AppDataTable
      title="Clientes"
      :columns="columns"
      base-url="/clientes"
      base-api-url="/clientes"
      :has-show="true"
      :has-edit="true"
      :has-delete="true"
      :has-restore="true"
      :has-create="true"
      :has-search="true"
      :has-filters="true"
  >
    <template #filters>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select">
          <option>Todos</option>
          <option>Ativos</option>
          <option>Inativos</option>
        </select>
      </div>
    </template>
  </AppDataTable>
</template>