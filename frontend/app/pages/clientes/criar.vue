<script setup lang="ts">
import AppFormContainer from '@/components/form/AppFormContainer.vue';
import CustomerFields from '@/components/customers/fields.vue';
import type { Breadcrumb } from '@/types/common/navigation';

const breadcrumbs: Breadcrumb[] = [
  { label: 'Clientes', to: '/clientes' },
  { label: 'Criar cliente', to: null },
];
</script>

<template>
  <AppFormContainer
      title="Criar cliente"
      :breadcrumbs="breadcrumbs"
  >
    <CustomerFields />

    <template #footer>
      <NuxtLink to="/clientes" class="btn btn-secondary app-form-icon-button" aria-label="Voltar">
        <i class="fa-solid fa-angle-left" aria-hidden="true" />
      </NuxtLink>

      <button class="btn btn-primary app-form-action-button save-button" type="submit" form="customer-form">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true" />
        Salvar
      </button>
    </template>
  </AppFormContainer>
</template>
