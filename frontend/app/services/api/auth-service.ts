import { AuthService } from '@/services/AuthService';
import { apiHttpClient } from '@/services/api/http-client';
import type {
  AuthenticatedPermissions,
  AuthenticatedSession,
  LoginResponse,
} from '@/types/auth';

const AUTH_SESSION_STORAGE_KEY = 'auth.session';
const AUTH_PERMISSIONS_STORAGE_KEY = 'auth.permissions';
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
    localStorage.removeItem(AUTH_PERMISSIONS_STORAGE_KEY);
    localStorage.removeItem(LEGACY_AUTH_USER_STORAGE_KEY);
    return null;
  }
}

function getStoredAuthPermissions(): AuthenticatedPermissions {
  if (!import.meta.client) {
    return [];
  }

  const storedPermissions = localStorage.getItem(AUTH_PERMISSIONS_STORAGE_KEY);

  if (!storedPermissions) {
    return [];
  }

  try {
    const permissions = JSON.parse(storedPermissions);

    return Array.isArray(permissions) ? (permissions as AuthenticatedPermissions) : [];
  } catch {
    localStorage.removeItem(AUTH_PERMISSIONS_STORAGE_KEY);
    return [];
  }
}

function setStoredAuthSessionAndPermissions(
  session: AuthenticatedSession,
  permissions: AuthenticatedPermissions,
): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.setItem(AUTH_SESSION_STORAGE_KEY, JSON.stringify(session));
  setStoredAuthPermissions(permissions);
  localStorage.removeItem(LEGACY_AUTH_USER_STORAGE_KEY);
}

function setStoredAuthPermissions(permissions: AuthenticatedPermissions): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.setItem(AUTH_PERMISSIONS_STORAGE_KEY, JSON.stringify(permissions));
}

function removeStoredAuthSession(): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.removeItem(AUTH_SESSION_STORAGE_KEY);
  localStorage.removeItem(AUTH_PERMISSIONS_STORAGE_KEY);
  localStorage.removeItem(LEGACY_AUTH_USER_STORAGE_KEY);
}

function createAuthSession(session: AuthenticatedSession): AuthenticatedSession {
  const company = session.company ?? null;

  return {
    user: session.user,
    company,
  };
}

function createAuthPermissions(permissions: AuthenticatedPermissions|null|undefined): AuthenticatedPermissions {
  return Array.isArray(permissions) ? permissions : [];
}

export async function login(email: string, password: string, rememberLogin = false): Promise<LoginResponse> {
  const response = await AuthService.login({
    email,
    password,
    remember_login: rememberLogin,
  });

  apiHttpClient.setToken(response.access_token);
  const session = createAuthSession(response);
  const permissions = createAuthPermissions(response.permissions);

  setStoredAuthSessionAndPermissions(session, permissions);

  return {
    ...response,
    ...session,
    permissions,
  };
}

export async function logout(): Promise<void> {
  try {
    await AuthService.logout();
  } catch (error) {
    void error;
  } finally {
    apiHttpClient.removeToken();
    removeStoredAuthSession();
  }
}

export async function checkAuthToken(): Promise<void> {
  await AuthService.checkToken();
}

export async function refreshAuthPermissions(): Promise<AuthenticatedPermissions> {
  const permissions = createAuthPermissions(await AuthService.permissions());
  setStoredAuthPermissions(permissions);

  return permissions;
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

export function getAuthPermissions(): AuthenticatedPermissions {
  return getStoredAuthPermissions();
}
