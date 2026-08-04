import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Permission, PermissionSearchParams } from '@/types/entities/role';
import { normalizeQueryParams } from '@/utils/normalizer';

export const PermissionService = {
  async search(params: Partial<PermissionSearchParams> = {}): Promise<Permission[]> {
    const response = await apiHttpClient.post<ApiResponse<Permission[]>>(
      '/permissions/search',
      normalizeQueryParams(params),
      { showGlobalLoading: false },
    );

    return response.data;
  },
};
