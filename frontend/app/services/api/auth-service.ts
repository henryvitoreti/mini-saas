import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { AuthenticatedSession, LoginPayload, LoginResponse } from '@/types/auth';

const AUTH_SESSION_STORAGE_KEY = 'auth.session';
const LEGACY_AUTH_USER_STORAGE_KEY = 'auth.user';

function getStoredAuthSession(): AuthenticatedSession|null {
  if (!import.meta.client) {
    return null;
  }

  const storedSession = localStorage.getItem(AUTH_SESSION_STORAGE_KEY);

  if (!storedSession) {
    return null;
  }

  try {
    return JSON.parse(storedSession) as AuthenticatedSession;
  } catch {
    localStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
    return null;
  }
}

function setStoredAuthSession(session: AuthenticatedSession): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.setItem(AUTH_SESSION_STORAGE_KEY, JSON.stringify(session));
  localStorage.removeItem(LEGACY_AUTH_USER_STORAGE_KEY);
}

function removeStoredAuthSession(): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
  localStorage.removeItem(LEGACY_AUTH_USER_STORAGE_KEY);
}

function createAuthSession(session: AuthenticatedSession): AuthenticatedSession {
  const permissions = session.permissions ?? [];
  const company = session.company ?? null;

  return {
    user: session.user,
    company,
    permissions,
  };
}

export async function login(email: string, password: string, rememberLogin = false): Promise<LoginResponse> {
  const response = await apiHttpClient.post<ApiResponse<LoginResponse>>('/login', {
    email,
    password,
    remember_login: rememberLogin,
  } satisfies LoginPayload);

  apiHttpClient.setToken(response.data.access_token);
  const session = createAuthSession(response.data);
  setStoredAuthSession(session);

  return {
    ...response.data,
    ...session,
  };
}

export async function logout(): Promise<void> {
  try {
    await apiHttpClient.post<ApiResponse<[]>>('/logout');
  } catch (error) {
    void error;
  } finally {
    apiHttpClient.removeToken();
    removeStoredAuthSession();
  }
}

export async function checkAuthToken(): Promise<AuthenticatedSession> {
  const response = await apiHttpClient.get<ApiResponse<AuthenticatedSession>>('/auth/check');
  const session = createAuthSession(response.data);

  setStoredAuthSession(session);

  return session;
}

export function getAuthToken(): string|null {
  return apiHttpClient.getToken();
}

export function removeAuthToken(): void {
  apiHttpClient.removeToken();
  removeStoredAuthSession();
}

export function getAuthSession(): AuthenticatedSession|null {
  return getStoredAuthSession();
}
