import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { AuthenticatedUser, LoginPayload, LoginResponse } from '@/types/auth';

const AUTH_USER_STORAGE_KEY = 'auth.user';

function getStoredAuthUser(): AuthenticatedUser|null {
  if (!import.meta.client) {
    return null;
  }

  const storedUser = localStorage.getItem(AUTH_USER_STORAGE_KEY);

  if (!storedUser) {
    return null;
  }

  try {
    return JSON.parse(storedUser) as AuthenticatedUser;
  } catch {
    localStorage.removeItem(AUTH_USER_STORAGE_KEY);
    return null;
  }
}

function setStoredAuthUser(user: AuthenticatedUser): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(user));
}

function removeStoredAuthUser(): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.removeItem(AUTH_USER_STORAGE_KEY);
}

export async function login(email: string, password: string, rememberLogin = false): Promise<LoginResponse> {
  const response = await apiHttpClient.post<ApiResponse<LoginResponse>>('/login', {
    email,
    password,
    remember_login: rememberLogin,
  } satisfies LoginPayload);

  apiHttpClient.setToken(response.data.access_token);
  setStoredAuthUser(response.data.user);

  return response.data;
}

export async function logout(): Promise<void> {
  try {
    await apiHttpClient.post<ApiResponse<[]>>('/logout');
  } catch (error) {
    void error;
  } finally {
    apiHttpClient.removeToken();
    removeStoredAuthUser();
  }
}

export async function checkAuthToken(): Promise<void> {
  await apiHttpClient.get<void>('/auth/check');
}

export function getAuthToken(): string|null {
  return apiHttpClient.getToken();
}

export function removeAuthToken(): void {
  apiHttpClient.removeToken();
  removeStoredAuthUser();
}

export function getAuthUser(): AuthenticatedUser|null {
  return getStoredAuthUser();
}
