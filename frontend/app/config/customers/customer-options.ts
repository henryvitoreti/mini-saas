import type { SelectOption } from '@/types/common/select';
import {
  CUSTOMER_TYPE_COMPANY,
  CUSTOMER_TYPE_INDIVIDUAL,
} from '@/types/entities/customer';

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

function getOptionLabel(options: SelectOption[], value: unknown): string {
  const normalizedValue = String(value ?? '');
  const option = options.find((item: SelectOption): boolean => {
    return String(item.value) === normalizedValue;
  });

  return option?.label ?? '-';
}

export function getCustomerTypeLabel(value: unknown): string {
  return getOptionLabel(customerTypeOptions, value);
}

export function getCustomerTypeBadgeClass(value: unknown): string {
  return value === CUSTOMER_TYPE_COMPANY ? 'is-info' : 'is-primary';
}

function normalizeCustomerStatus(value: unknown): boolean|null {
  if (value === true || value === 'true' || value === 1 || value === '1') {
    return true;
  }

  if (value === false || value === 'false' || value === 0 || value === '0') {
    return false;
  }

  return null;
}

export function getCustomerStatusLabel(value: unknown): string {
  const status = normalizeCustomerStatus(value);

  return status === null ? '-' : getOptionLabel(customerStatusOptions, String(status));
}

export function getCustomerStatusBadgeClass(value: unknown): string {
  return normalizeCustomerStatus(value) ? 'is-success' : 'is-muted';
}
