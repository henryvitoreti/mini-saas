import type { TableFilters } from '@/types/ui/table';

export type Permission = {
  [key: string]: unknown;
  id: number;
  name: string;
  slug: string;
  base_front_url: string;
  base_api_url: string;
  group: string|null;
  is_base: boolean;
  is_active: boolean|null;
  show_locked_routes: boolean|null;
};

export type RolePermission = {
  [key: string]: unknown;
  id: number;
  name: string;
  slug: string;
  base_front_url: string;
  base_api_url: string;
  group: string|null;
  is_base: boolean;
  is_active: boolean;
  show_locked_routes: boolean;
};

export type Role = {
  [key: string]: unknown;
  id: number;
  name: string;
  slug: string;
  description: string;
  is_active: boolean;
  can_modify: boolean;
  permissions?: RolePermission[];
  created_at: string|null;
  updated_at: string|null;
};

export type RoleListFilters = TableFilters & {
  is_active: boolean|null;
  can_modify: boolean|null;
};

export type RoleListParams = RoleListFilters & {
  search: string|null;
  page: number;
  limit: number;
  sort_by: string;
  order: 'ASC'|'DESC';
  with_details: boolean;
};

export type PermissionSearchParams = {
  search: string|null;
  sort_by: string;
  order: 'ASC'|'DESC';
};
