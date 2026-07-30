import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Permission, PermissionSearchParams } from '@/types/entities/role';

function normalizeParams(params: Partial<PermissionSearchParams>): Record<string, string> {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      return value !== null && value !== undefined && value !== '';
    }),
  ) as Record<string, string>;
}

export const PermissionService = {
  async search(params: Partial<PermissionSearchParams> = {}): Promise<Permission[]> {
    const response = await apiHttpClient.post<ApiResponse<Permission[]>>(
      '/permissions/search',
      normalizeParams(params),
      { showGlobalLoading: false },
    );

    return response.data;
  },
};
