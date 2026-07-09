<script setup lang="ts">
import AppFormContainer from '@/components/form/AppFormContainer.vue';
import CustomerFields from '@/components/customers/fields.vue';
import type { Breadcrumb } from '@/types/common/navigation';

definePageMeta({
  validate(route): boolean {
    const routeId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id;
    const id = Number(routeId);

    return Number.isInteger(id) && id > 0;
  },
});

const route = useRoute();
const routeId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id;
const id = Number(routeId);

const breadcrumbs: Breadcrumb[] = [
  { label: 'Clientes', to: '/clientes' },
  { label: 'Editar cliente', to: null },
];
</script>

<template>
  <AppFormContainer
      title="Editar cliente"
      :breadcrumbs="breadcrumbs"
  >
    <CustomerFields :id="id" />

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
