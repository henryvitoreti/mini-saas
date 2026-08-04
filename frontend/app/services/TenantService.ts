import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Tenant, TenantListParams, TenantListResponse } from '@/types/entities/tenant';
import type { FormPayload } from '@/types/forms/form';
import { normalizeQueryParams } from '@/utils/normalizer';

export const TenantService = {
  async index(params: Partial<TenantListParams>): Promise<TenantListResponse> {
    const response = await apiHttpClient.get<ApiResponse<TenantListResponse>>('/tenants', {
      query: normalizeQueryParams(params),
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
