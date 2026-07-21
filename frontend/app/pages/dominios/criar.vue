<script setup lang="ts">
import AppFormContainer from '@/components/form/AppFormContainer.vue';
import TenantFields from '@/components/tenants/fields.vue';
import type { Breadcrumb } from '@/types/common/navigation';

const breadcrumbs: Breadcrumb[] = [
  { label: 'Domínios', to: '/dominios' },
  { label: 'Criar Domínio', to: null },
];
</script>

<template>
  <AppFormContainer
      title="Criar domínio"
      :breadcrumbs="breadcrumbs"
  >
    <TenantFields />

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
