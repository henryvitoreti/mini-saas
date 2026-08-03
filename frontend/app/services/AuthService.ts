import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { AuthenticatedPermissions, LoginPayload, LoginResponse } from '@/types/auth';

export const AuthService = {
  async login(payload: LoginPayload): Promise<LoginResponse> {
    const response = await apiHttpClient.post<ApiResponse<LoginResponse>>('/login', payload);
    return response.data;
  },

  async logout(): Promise<void> {
    await apiHttpClient.post<ApiResponse<[]>>('/logout');
  },

  async checkToken(): Promise<void> {
    await apiHttpClient.get<void>('/auth/check', { showGlobalLoading: false });
  },

  async permissions(): Promise<AuthenticatedPermissions> {
    const response = await apiHttpClient.get<ApiResponse<{ permissions: AuthenticatedPermissions }>>(
      '/auth/permissions',
      { showGlobalLoading: false },
    );

    return response.data.permissions;
  },
};
