import type { SelectOption } from '@/types/common/select';
import { CUSTOMER_TYPE_COMPANY, CUSTOMER_TYPE_INDIVIDUAL } from '@/types/entities/customer';
import { getOptionLabel } from '@/utils/label-handler';
import { normalizeBooleanValue } from '@/utils/normalizer';

export const customerTypeOptions: SelectOption[] = [
  {
    label: 'Pessoa Física',
    value: CUSTOMER_TYPE_INDIVIDUAL,
    disabled: null,
  },
  {
    label: 'Pessoa Jurídica',
    value: CUSTOMER_TYPE_COMPANY,
    disabled: null,
  },
];

export const customerStatusOptions: SelectOption[] = [
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

export function getCustomerTypeLabel(value: unknown): string {
  return getOptionLabel(customerTypeOptions, value);
}

export function getCustomerTypeBadgeClass(value: unknown): string {
  return value === CUSTOMER_TYPE_COMPANY ? 'is-info' : 'is-primary';
}

export function getCustomerStatusLabel(value: unknown): string {
  const status = normalizeBooleanValue(value);

  return status === null ? '-' : getOptionLabel(customerStatusOptions, String(status));
}

export function getCustomerStatusBadgeClass(value: unknown): string {
  return normalizeBooleanValue(value) ? 'is-success' : 'is-muted';
}
