import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Role, RoleListParams } from '@/types/entities/role';
import type { FormPayload } from '@/types/forms/form';
import type { TablePagination } from '@/types/ui/table';

type RoleListResponse = {
  items: Role[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number|null;
  to: number|null;
  prev_page_url: string|null;
  next_page_url: string|null;
};

function normalizeParams(params: Partial<RoleListParams>): Record<string, string|number|boolean> {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      return value !== null && value !== undefined && value !== '';
    }),
  ) as Record<string, string|number|boolean>;
}

export const RoleService = {
  async index(params: Partial<RoleListParams>): Promise<TablePagination> {
    const response = await apiHttpClient.get<ApiResponse<RoleListResponse>>('/roles', {
      query: normalizeParams(params),
      showGlobalLoading: false,
    });

    return {
      data: response.data.items,
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
      prev_page_url: response.data.prev_page_url,
      next_page_url: response.data.next_page_url,
    };
  },

  async show(id: number): Promise<Role> {
    const response = await apiHttpClient.get<ApiResponse<Role>>(`/roles/${id}`);
    return response.data;
  },

  async create(payload: FormPayload): Promise<Role> {
    const response = await apiHttpClient.post<ApiResponse<Role>>('/roles', payload);
    return response.data;
  },

  async update(id: number, payload: FormPayload): Promise<Role> {
    const response = await apiHttpClient.put<ApiResponse<Role>>(`/roles/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await apiHttpClient.delete<ApiResponse<[]>>(`/roles/${id}`);
  },
};
