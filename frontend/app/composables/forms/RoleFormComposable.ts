import { computed, reactive, ref } from 'vue';
import { useBaseForm } from '@/composables/forms/BaseFormComposable';
import { PermissionService } from '@/services/permissions/PermissionService';
import { RoleService } from '@/services/roles/RoleService';
import { ApiValidationError } from '@/types/api/http';
import type { Permission, Role, RolePermission } from '@/types/entities/role';
import type { FormAttribute, FormPayload } from '@/types/forms/form';

type RoleFormTextAttribute = FormAttribute<string|null>;
type RoleFormBooleanAttribute = FormAttribute<boolean|null>;
type RoleFormPermissionsAttribute = FormAttribute<RolePermission[]>;

type RolePermissionGroup = {
  name: string;
  permissions: RolePermission[];
  isChecked: boolean;
  isIndeterminate: boolean;
};

type RoleFormAttributes = {
  name: RoleFormTextAttribute;
  description: RoleFormTextAttribute;
  isActive: RoleFormBooleanAttribute;
  permissions: RoleFormPermissionsAttribute;
} & Record<string, FormAttribute>;

export function useRoleForm() {
  const attributes = reactive<RoleFormAttributes>({
    name: {
      responseKey: 'name',
      payloadKey: 'name',
      value: null,
      label: 'Nome',
      required: true,
      errorMessage: null,
    },
    description: {
      responseKey: 'description',
      payloadKey: 'description',
      value: null,
      label: 'Descrição',
      placeholder: 'Descreva o objetivo deste perfil de acesso',
      required: false,
      errorMessage: null,
    },
    isActive: {
      responseKey: 'is_active',
      payloadKey: 'is_active',
      value: true,
      label: 'Ativo',
      required: true,
      errorMessage: null,
    },
    permissions: {
      responseKey: 'permissions',
      payloadKey: 'permissions',
      value: [],
      label: 'Permissões',
      required: true,
      errorMessage: null,
    },
  });
  const baseForm = useBaseForm(attributes);
  const permissionOptions = ref<Permission[]>([]);
  const isLoadingPermissions = ref<boolean>(false);
  const hasLoadedPermissionOptions = ref<boolean>(false);

  const permissionGroups = computed<RolePermissionGroup[]>(() => {
    const groupedPermissions = new Map<string, RolePermission[]>();
    const sortedPermissions = [...attributes.permissions.value].sort(
      (firstPermission: RolePermission, secondPermission: RolePermission): number => {
        const firstGroup = normalizePermissionGroup(firstPermission.group);
        const secondGroup = normalizePermissionGroup(secondPermission.group);
        const groupComparison = firstGroup.localeCompare(secondGroup, 'pt-BR');

        if (groupComparison !== 0) {
          return groupComparison;
        }

        return firstPermission.name.localeCompare(secondPermission.name, 'pt-BR');
      },
    );

    sortedPermissions.forEach((permission: RolePermission): void => {
      const groupName = normalizePermissionGroup(permission.group);
      const groupPermissions = groupedPermissions.get(groupName) ?? [];

      groupPermissions.push(permission);
      groupedPermissions.set(groupName, groupPermissions);
    });

    return Array.from(groupedPermissions, ([name, permissions]): RolePermissionGroup => {
      const activePermissionCount = permissions.filter(({ is_active }) => is_active).length;

      return {
        name,
        permissions,
        isChecked: permissions.length > 0 && activePermissionCount === permissions.length,
        isIndeterminate: activePermissionCount > 0 && activePermissionCount < permissions.length,
      };
    });
  });

  function normalizePermissionGroup(group: string|null): string {
    const normalizedGroup = group?.trim();

    return normalizedGroup ? normalizedGroup : 'Outras permissões';
  }

  function normalizePermissions(
    options: Permission[],
    selectedPermissions: RolePermission[],
  ): RolePermission[] {
    const selectedById = new Map(
      selectedPermissions.map((permission: RolePermission) => [permission.id, permission]),
    );
    const normalizedOptions = options.map((permission: Permission): RolePermission => {
      const selectedPermission = selectedById.get(permission.id);
      const isBase = Boolean(permission.is_base);
      const isActive = isBase || Boolean(selectedPermission?.is_active);

      return {
        ...permission,
        is_active: isActive,
        is_base: isBase,
        show_locked_routes: isBase || isActive
          ? false
          : Boolean(selectedPermission?.show_locked_routes),
      };
    });
    const optionIds = new Set(options.map(({ id }) => id));
    const unavailableSelectedPermissions = selectedPermissions
      .filter(({ id }) => !optionIds.has(id))
      .map((permission: RolePermission): RolePermission => ({
        ...permission,
        is_active: permission.is_base ? true : permission.is_active,
        show_locked_routes: permission.is_base || permission.is_active
          ? false
          : permission.show_locked_routes,
      }));

    return [...normalizedOptions, ...unavailableSelectedPermissions];
  }

  function syncPermissionOptions(selectedPermissions: RolePermission[] = attributes.permissions.value): void {
    attributes.permissions.value = normalizePermissions(permissionOptions.value, selectedPermissions);
  }

  function handleRoleError(error: unknown, defaultMessage: string): void {
    baseForm.handleError(error, defaultMessage);

    if (!(error instanceof ApiValidationError)) {
      return;
    }

    if (error.errors.slug) {
      attributes.name.errorMessage = error.errors.slug;
    }

    const permissionError = Object.entries(error.errors).find(([key]) => key.startsWith('permissions'));

    if (permissionError) {
      attributes.permissions.errorMessage = permissionError[1];
    }
  }

  async function loadPermissionOptions(): Promise<boolean> {
    isLoadingPermissions.value = true;

    try {
      permissionOptions.value = await PermissionService.search({
        sort_by: 'name',
        order: 'ASC',
      });
      hasLoadedPermissionOptions.value = true;
      syncPermissionOptions();

      return true;
    } catch (error) {
      hasLoadedPermissionOptions.value = false;
      handleRoleError(error, 'Ocorreu um erro inesperado ao buscar as permissões.');

      return false;
    } finally {
      isLoadingPermissions.value = false;
    }
  }

  async function load(id: number): Promise<Role|null> {
    let response: Role|null = null;

    try {
      response = await RoleService.show(id);
      const selectedPermissions = response.permissions ?? [];

      baseForm.fillAttributes({
        ...response,
        permissions: selectedPermissions,
      });
      syncPermissionOptions(selectedPermissions);
    } catch (error) {
      handleRoleError(error, 'Ocorreu um erro inesperado ao buscar o perfil.');
    }

    return response;
  }

  function formatPayload(payload: FormPayload): FormPayload {
    payload.permissions = attributes.permissions.value.map((permission: RolePermission) => ({
      id: permission.id,
      is_active: permission.is_base ? true : permission.is_active,
      show_locked_routes: permission.is_base || permission.is_active
        ? false
        : permission.show_locked_routes,
    }));

    return payload;
  }

  async function create(): Promise<Role|null> {
    let response: Role|null = null;

    try {
      const payload = baseForm.handleDoSendAttributes(formatPayload);

      response = await RoleService.create(payload);
    } catch (error) {
      handleRoleError(error, 'Ocorreu um erro inesperado ao criar o perfil.');
    }

    return response;
  }

  async function update(id: number): Promise<Role|null> {
    let response: Role|null = null;

    try {
      const payload = baseForm.handleDoSendAttributes(formatPayload);

      response = await RoleService.update(id, payload);
    } catch (error) {
      handleRoleError(error, 'Ocorreu um erro inesperado ao atualizar o perfil.');
    }

    return response;
  }

  function updatePermissionIsActive(permissionId: number, value: boolean): void {
    const permission = attributes.permissions.value.find(({ id }) => id === permissionId);

    if (!permission || permission.is_base) {
      return;
    }

    permission.is_active = value;

    if (value) {
      permission.show_locked_routes = false;
    }
  }

  function updatePermissionShowLockedRoutes(permissionId: number, value: boolean): void {
    const permission = attributes.permissions.value.find(({ id }) => id === permissionId);

    if (!permission || permission.is_base || permission.is_active) {
      return;
    }

    permission.show_locked_routes = value;
  }

  function togglePermissionGroup(groupName: string, shouldActivate?: boolean): void {
    const group = permissionGroups.value.find(({ name }) => name === groupName);

    if (!group) {
      return;
    }

    const nextIsActive = shouldActivate ?? !group.isChecked;

    group.permissions.forEach((permission: RolePermission): void => {
      if (permission.is_base) {
        permission.is_active = true;
        permission.show_locked_routes = false;
        return;
      }

      updatePermissionIsActive(permission.id, nextIsActive);
    });
  }

  return {
    ...baseForm,
    attributes,
    isLoadingPermissions,
    hasLoadedPermissionOptions,
    permissionGroups,
    loadPermissionOptions,
    togglePermissionGroup,
    updatePermissionIsActive,
    updatePermissionShowLockedRoutes,
    load,
    create,
    update,
  };
}
