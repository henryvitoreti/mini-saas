<script setup lang="ts">
import AppFormContainer from '@/components/form/AppFormContainer.vue';
import RoleFields from '@/components/roles/fields.vue';
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
  { label: 'Perfis de acesso', to: '/perfis' },
  { label: 'Editar perfil', to: null },
];
</script>

<template>
  <AppFormContainer
      title="Editar perfil"
      :subtitle="null"
      :breadcrumbs="breadcrumbs"
  >
    <RoleFields :id="id" />

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
