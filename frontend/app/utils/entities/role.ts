import type { SelectOption } from '@/types/common/select';
import { getOptionLabel } from '@/utils/label-handler';
import { normalizeBooleanValue } from '@/utils/normalizer';

export const roleStatusOptions: SelectOption[] = [
  {
    label: 'Ativo',
    value: 'true',
    disabled: null,
  },
  {
    label: 'Desativado',
    value: 'false',
    disabled: null,
  },
];

export const roleModificationOptions: SelectOption[] = [
  {
    label: 'Permitida',
    value: 'true',
    disabled: null,
  },
  {
    label: 'Protegida',
    value: 'false',
    disabled: null,
  },
];

export function getRoleStatusLabel(value: unknown): string {
  const status = normalizeBooleanValue(value);

  return status === null ? '-' : getOptionLabel(roleStatusOptions, String(status));
}

export function getRoleStatusBadgeClass(value: unknown): string {
  return normalizeBooleanValue(value) ? 'is-success' : 'is-muted';
}

export function getRoleModificationLabel(value: unknown): string {
  const canModify = normalizeBooleanValue(value);

  return canModify === null ? '-' : getOptionLabel(roleModificationOptions, String(canModify));
}

export function getRoleModificationBadgeClass(value: unknown): string {
  return normalizeBooleanValue(value) ? 'is-info' : 'is-warning';
}
