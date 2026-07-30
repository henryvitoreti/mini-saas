import {
  checkAuthToken,
  getAuthPermissions,
  getAuthSession,
  getAuthToken,
  login as loginRequest,
  logout as logoutRequest,
  removeAuthToken,
  refreshAuthPermissions,
} from '@/services/api/auth-service';
import type {
  AuthenticatedCompany,
  AuthenticatedPermissions,
  AuthenticatedSession,
  AuthenticatedUser,
} from '@/types/auth';

export function useAuth() {
  const initialToken = getAuthToken();
  const token = useState<string|null>('auth.token', () => initialToken);
  const session = useState<AuthenticatedSession|null>('auth.session', () => initialToken ? getAuthSession() : null);
  const user = useState<AuthenticatedUser|null>('auth.user', () => session.value?.user ?? null);
  const company = useState<AuthenticatedCompany|null>('auth.company', () => session.value?.company ?? null);
  const permissions = useState<AuthenticatedPermissions>(
    'auth.permissions',
    () => initialToken ? getAuthPermissions() : [],
  );

  const isAuthenticated = computed<boolean>(() => Boolean(token.value));

  function setSession(nextSession: AuthenticatedSession|null): void {
    session.value = nextSession;
    user.value = nextSession?.user ?? null;
    company.value = nextSession?.company ?? null;
  }

  function setPermissions(nextPermissions: AuthenticatedPermissions): void {
    permissions.value = nextPermissions;
  }

  function clearAuthentication(): void {
    removeAuthToken();
    token.value = null;
    setSession(null);
    setPermissions([]);
  }

  async function refreshPermissions(): Promise<void> {
    setPermissions(await refreshAuthPermissions());
  }

  async function login(email: string, password: string, rememberLogin = false): Promise<void> {
    const response = await loginRequest(email, password, rememberLogin);

    token.value = response.access_token;
    setSession({
      user: response.user,
      company: response.company,
    });
    setPermissions(response.permissions);

    await navigateTo('/');
  }

  async function logout(): Promise<void> {
    await logoutRequest();
    token.value = null;
    setSession(null);
    setPermissions([]);

    await navigateTo('/login');
  }

  async function validateToken(): Promise<boolean> {
    const storedToken = getAuthToken();

    if (!storedToken) {
      clearAuthentication();
      return false;
    }

    try {
      await checkAuthToken();
      return true;
    } catch {
      clearAuthentication();
      return false;
    }
  }

  return {
    session,
    user,
    company,
    permissions,
    token,
    isAuthenticated,
    login,
    logout,
    refreshPermissions,
    validateToken,
  };
}
