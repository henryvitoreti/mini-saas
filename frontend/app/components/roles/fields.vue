<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppSwitchInput from '@/components/form/AppSwitchInput.vue';
import AppTextInput from '@/components/form/AppTextInput.vue';
import AppTextareaInput from '@/components/form/AppTextareaInput.vue';
import { useRoleForm } from '@/composables/forms/RoleFormComposable';
import type { FieldsProps } from '@/types/forms/form';

const props = withDefaults(
  defineProps<FieldsProps>(),
  {
    id: null,
    isModal: false,
  },
);

const router = useRouter();
const appToast = useAppToast();
const roleForm = useRoleForm();
const { attributes } = roleForm;
const collapsedPermissionGroups = ref<Set<string>>(new Set());

const isEditing = computed<boolean>(() => {
  return props.id !== null;
});

function isPermissionGroupCollapsed(groupName: string): boolean {
  return collapsedPermissionGroups.value.has(groupName);
}

function togglePermissionGroupCollapse(groupName: string): void {
  const nextCollapsedGroups = new Set(collapsedPermissionGroups.value);

  if (nextCollapsedGroups.has(groupName)) {
    nextCollapsedGroups.delete(groupName);
  } else {
    nextCollapsedGroups.add(groupName);
  }

  collapsedPermissionGroups.value = nextCollapsedGroups;
}

async function handlePermissionGroupCheckboxChange(groupName: string, event: Event): Promise<void> {
  const target = event.target as HTMLInputElement;

  roleForm.togglePermissionGroup(groupName, target.checked);
  await nextTick();

  const group = roleForm.permissionGroups.value.find(({ name }) => name === groupName);

  if (!group) {
    return;
  }

  target.checked = group.isChecked;
  target.indeterminate = group.isIndeterminate;
}

async function loadRole(): Promise<void> {
  if (props.id === null) {
    return;
  }

  const role = await roleForm.load(props.id);

  if (role === null) {
    await router.push('/perfis');
    return;
  }

  if (!role.can_modify) {
    appToast.warning('Este perfil é protegido e não pode ser editado.');
    await router.push('/perfis');
  }
}

async function save(): Promise<void> {
  if (!roleForm.hasLoadedPermissionOptions.value) {
    const message = roleForm.isLoadingPermissions.value
      ? 'Aguarde o carregamento das permissões antes de salvar.'
      : 'Não foi possível carregar as permissões. Atualize a página e tente novamente.';

    appToast.warning(message);
    return;
  }

  const role = props.id === null
    ? await roleForm.create()
    : await roleForm.update(props.id);

  if (role === null) {
    return;
  }

  appToast.success(isEditing.value ? 'Perfil atualizado com sucesso.' : 'Perfil cadastrado com sucesso.');
  await router.push('/perfis');
}

onMounted(async (): Promise<void> => {
  await roleForm.loadPermissionOptions();
  await loadRole();
});
</script>

<template>
  <form id="role-form" class="app-form-stack" @submit.prevent="save">
    <section class="app-form-card">
      <div class="app-form-card-header">
        <div class="app-form-card-title-row">
          <h2 class="app-form-card-title">
            Dados principais
          </h2>
        </div>
      </div>

      <div class="app-form-card-body">
        <div class="row g-3">
          <div class="col-12 col-md-10">
            <AppTextInput
                name="name"
                type="text"
                :label="attributes.name.label"
                :model-value="null"
                :mask="null"
                :placeholder="null"
                :required="Boolean(attributes.name.required)"
                :disabled="false"
                :tip="null"
                :start-icon="null"
                :end-icon="null"
                v-model:value="attributes.name.value"
                :error-message="attributes.name.errorMessage"
            />
          </div>

          <div class="col-12 col-md-2">
            <AppSwitchInput
                name="is_active"
                :label="attributes.isActive.label"
                :model-value="null"
                :required="Boolean(attributes.isActive.required)"
                :disabled="false"
                :tip="null"
                :is-default-layout="true"
                v-model:value="attributes.isActive.value"
                :error-message="attributes.isActive.errorMessage"
            />
          </div>

          <div class="col-12">
            <AppTextareaInput
                name="description"
                :label="attributes.description.label"
                :model-value="null"
                :placeholder="attributes.description.placeholder ?? null"
                :required="Boolean(attributes.description.required)"
                :tip="null"
                :rows="4"
                v-model:value="attributes.description.value"
                :error-message="attributes.description.errorMessage"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="app-form-card">
      <div class="app-form-card-header">
        <h2 class="app-form-card-title">
          Permissões
        </h2>

        <span class="app-form-card-subtitle">
          Defina quais áreas este perfil pode acessar ou apenas visualizar como bloqueadas.
        </span>
      </div>

      <div class="app-form-card-body">
        <div v-if="roleForm.isLoadingPermissions.value" class="app-grouped-list-empty">
          <i class="fa-solid fa-spinner fa-spin" aria-hidden="true" />
          Carregando permissões...
        </div>

        <div v-else-if="attributes.permissions.value.length === 0" class="app-grouped-list-empty">
          Nenhuma permissão disponível.
        </div>

        <div v-else class="app-grouped-list">
          <section
              v-for="(group, groupIndex) in roleForm.permissionGroups.value"
              :key="group.name"
              class="app-grouped-list-section"
              :class="{ 'is-collapsed': isPermissionGroupCollapsed(group.name) }"
          >
            <header class="app-grouped-list-header">
              <div class="app-grouped-list-header-main">
                <label class="app-grouped-list-selector">
                  <input
                      v-indeterminate="group.isIndeterminate"
                      class="app-grouped-list-checkbox"
                      :class="{
                        'is-checked': group.isChecked,
                        'is-indeterminate': group.isIndeterminate,
                      }"
                      type="checkbox"
                      :checked="group.isChecked"
                      :aria-checked="group.isIndeterminate ? 'mixed' : group.isChecked ? 'true' : 'false'"
                      @change="(event) => handlePermissionGroupCheckboxChange(group.name, event)"
                  >

                  <span>{{ group.name }}</span>
                </label>
              </div>

              <div class="app-grouped-list-header-actions">
                <span class="app-grouped-list-count">
                  {{ group.permissions.length }}
                  {{ group.permissions.length === 1 ? 'permissão' : 'permissões' }}
                </span>

                <button
                    class="app-grouped-list-toggle"
                    type="button"
                    :aria-controls="`permission-group-${groupIndex}`"
                    :aria-expanded="!isPermissionGroupCollapsed(group.name)"
                    :title="isPermissionGroupCollapsed(group.name) ? 'Expandir grupo' : 'Comprimir grupo'"
                    @click="togglePermissionGroupCollapse(group.name)"
                >
                  <i
                      class="fa-solid"
                      :class="isPermissionGroupCollapsed(group.name) ? 'fa-chevron-down' : 'fa-chevron-up'"
                      aria-hidden="true"
                  />
                </button>
              </div>
            </header>

            <div
                v-show="!isPermissionGroupCollapsed(group.name)"
                :id="`permission-group-${groupIndex}`"
                class="app-grouped-list-items"
            >
              <div
                  v-for="permission in group.permissions"
                  :key="permission.id"
                  class="app-grouped-list-row"
              >
                <div class="app-grouped-list-item">
                  <div class="app-grouped-list-item-heading">
                    <strong>{{ permission.name }}</strong>
                    <span v-if="permission.is_base" class="app-badge is-info">
                      Obrigatória
                    </span>
                  </div>

                  <span>{{ permission.base_front_url }}</span>
                </div>

                <AppSwitchInput
                    :name="`permission_${permission.id}_is_active`"
                    label="Acesso ativo"
                    :is-default-layout="false"
                    :model-value="null"
                    :required="false"
                    :disabled="permission.is_base"
                    :value="permission.is_active"
                    :error-message="null"
                    @update:value="(value) => roleForm.updatePermissionIsActive(permission.id, value)"
                />

                <AppSwitchInput
                    :name="`permission_${permission.id}_show_locked_routes`"
                    label="Exibir bloqueada"
                    tip="Exibe a rota no menu, mas mantém o acesso bloqueado."
                    tip-position="left"
                    :is-default-layout="false"
                    :model-value="null"
                    :required="false"
                    :disabled="permission.is_base || permission.is_active"
                    :value="permission.show_locked_routes"
                    :error-message="null"
                    @update:value="(value) => roleForm.updatePermissionShowLockedRoutes(permission.id, value)"
                />
              </div>
            </div>
          </section>
        </div>

        <p v-if="attributes.permissions.errorMessage" class="app-form-error-message mt-3">
          {{ attributes.permissions.errorMessage }}
        </p>
      </div>
    </section>
  </form>
</template>
