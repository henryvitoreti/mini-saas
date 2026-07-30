<script setup lang="ts">
import AppFormContainer from '@/components/form/AppFormContainer.vue';
import RoleFields from '@/components/roles/fields.vue';
import type { Breadcrumb } from '@/types/common/navigation';

const breadcrumbs: Breadcrumb[] = [
  { label: 'Perfis de acesso', to: '/perfis' },
  { label: 'Criar perfil', to: null },
];
</script>

<template>
  <AppFormContainer
      title="Criar perfil"
      :subtitle="null"
      :breadcrumbs="breadcrumbs"
  >
    <RoleFields />

    <template #footer>
      <NuxtLink to="/perfis" class="btn btn-secondary app-form-icon-button" aria-label="Voltar">
        <i class="fa-solid fa-angle-left" aria-hidden="true" />
      </NuxtLink>

      <button class="btn btn-primary app-form-action-button save-button" type="submit" form="role-form">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true" />
        Salvar
      </button>
    </template>
  </AppFormContainer>
</template>
