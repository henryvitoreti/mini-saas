export type Tenant = {
  [key: string]: unknown;
  id: string;
  name: string;
  domain: string|null;
  active: boolean;
  role_id: number|null;
  document: string|null;
  email: string|null;
  phone: string|null;
  secondary_phone: string|null;
  zip_code: string|null;
  street: string|null;
  number: string|null;
  complement: string|null;
  district: string|null;
  city: string|null;
  state: string|null;
  logo_path: string|null;
  notes: string|null;
  created_at: string|null;
  updated_at: string|null;
};

export type TenantListResponse = {
  items: Tenant[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number|null;
  to: number|null;
  prev_page_url: string|null;
  next_page_url: string|null;
};

export type TenantListParams = {
  search: string|null;
  page: number;
  limit: number;
  sort_by: string;
  order: 'ASC'|'DESC';
};
