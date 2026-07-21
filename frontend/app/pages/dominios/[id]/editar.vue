<script setup lang="ts">
import AppFormContainer from '@/components/form/AppFormContainer.vue';
import TenantFields from '@/components/tenants/fields.vue';
import type { Breadcrumb } from '@/types/common/navigation';

definePageMeta({
  validate(route): boolean {
    const routeId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id;

    return typeof routeId === 'string' && /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(routeId);
  },
});

const route = useRoute();
const routeId = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id;
const id = String(routeId);

const breadcrumbs: Breadcrumb[] = [
  { label: 'Domínios', to: '/dominios' },
  { label: 'Editar domínio', to: null },
];
</script>

<template>
  <AppFormContainer
      title="Editar domínio"
      :breadcrumbs="breadcrumbs"
  >
    <TenantFields :id="id" />

    <template #footer>
      <NuxtLink to="/dominios" class="btn btn-secondary app-form-icon-button" aria-label="Voltar">
        <i class="fa-solid fa-angle-left" aria-hidden="true" />
      </NuxtLink>

      <button class="btn btn-primary app-form-action-button save-button" type="submit" form="tenant-form">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true" />
        Salvar
      </button>
    </template>
  </AppFormContainer>
</template>
