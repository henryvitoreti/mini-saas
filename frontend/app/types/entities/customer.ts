import type { TableFilters } from '@/types/ui/table';

export const CUSTOMER_TYPE_INDIVIDUAL = 'individual';
export const CUSTOMER_TYPE_COMPANY = 'company';

export type CustomerType = typeof CUSTOMER_TYPE_INDIVIDUAL|typeof CUSTOMER_TYPE_COMPANY;

export type Customer = {
  [key: string]: unknown;
  id: number;
  name: string;
  document: string|null;
  document_raw: string|null;
  email: string;
  type: CustomerType|null;
  type_label: string|null;
  birth_date: string|null;
  birth_date_raw: string|null;
  phone: string|null;
  phone_raw: string|null;
  is_active: boolean;
  created_at: string|null;
};

export type CustomerForm = {
  name: string|null;
  document: string|null;
  email: string|null;
  type: CustomerType;
  birth_date: string|null;
  phone: string|null;
  secondary_phone: string|null;
  zip_code: string|null;
  street: string|null;
  number: string|null;
  complement: string|null;
  district: string|null;
  city: string|null;
  state: string|null;
  notes: string|null;
  is_active: boolean|null;
};

export type CustomerListFilters = TableFilters & {
  start_date: string|null;
  end_date: string|null;
  type: CustomerType|null;
  is_active: boolean|null;
};

export type CustomerListParams = CustomerListFilters & {
  search: string|null;
  page: number;
  limit: number;
  sort_by: string;
  order: 'ASC'|'DESC';
  with_details: boolean;
};
