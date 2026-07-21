import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Tenant, TenantListParams, TenantListResponse } from '@/types/entities/tenant';
import type { FormPayload } from '@/types/forms/form';

function normalizeParams(params: Partial<TenantListParams>): Record<string, string|number|boolean> {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      return value !== null && value !== undefined && value !== '';
    }),
  ) as Record<string, string|number|boolean>;
}

export const TenantService = {
  async index(params: Partial<TenantListParams>): Promise<TenantListResponse> {
    const response = await apiHttpClient.get<ApiResponse<TenantListResponse>>('/tenants', {
      query: normalizeParams(params),
      showGlobalLoading: false,
    });

    return response.data;
  },

  async create(payload: FormPayload): Promise<Tenant> {
    const response = await apiHttpClient.post<ApiResponse<Tenant>>('/tenants', payload);
    return response.data;
  },

  async show(id: string): Promise<Tenant> {
    const response = await apiHttpClient.get<ApiResponse<Tenant>>(`/tenants/${id}`);
    return response.data;
  },

  async update(id: string, payload: FormPayload): Promise<Tenant> {
    const response = await apiHttpClient.put<ApiResponse<Tenant>>(`/tenants/${id}`, payload);
    return response.data;
  },
};
