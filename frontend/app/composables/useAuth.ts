import {
  checkAuthToken,
  getAuthSession,
  getAuthToken,
  login as loginRequest,
  logout as logoutRequest,
  removeAuthToken,
} from '@/services/api/auth-service';
import type {
  AuthenticatedCompany,
  AuthenticatedPermission,
  AuthenticatedSession,
  AuthenticatedUser,
} from '@/types/auth';

export function useAuth() {
  const initialToken = getAuthToken();
  const token = useState<string|null>('auth.token', () => initialToken);
  const session = useState<AuthenticatedSession|null>('auth.session', () => initialToken ? getAuthSession() : null);
  const user = useState<AuthenticatedUser|null>('auth.user', () => session.value?.user ?? null);
  const company = useState<AuthenticatedCompany|null>('auth.company', () => session.value?.company ?? null);
  const permissions = useState<AuthenticatedPermission[]>('auth.permissions', () => session.value?.permissions ?? []);

  const isAuthenticated = computed<boolean>(() => Boolean(token.value));

  function setSession(nextSession: AuthenticatedSession|null): void {
    session.value = nextSession;
    user.value = nextSession?.user ?? null;
    company.value = nextSession?.company ?? null;
    permissions.value = nextSession?.permissions ?? [];
  }

  async function login(email: string, password: string, rememberLogin = false): Promise<void> {
    const response = await loginRequest(email, password, rememberLogin);

    token.value = response.access_token;
    setSession({
      user: response.user,
      company: response.company,
      permissions: response.permissions,
    });

    await navigateTo('/');
  }

  async function logout(): Promise<void> {
    await logoutRequest();
    token.value = null;
    setSession(null);

    await navigateTo('/login');
  }

  async function validateToken(): Promise<boolean> {
    token.value = getAuthToken();

    if (!token.value) {
      removeAuthToken();
      setSession(null);
      return false;
    }

    try {
      const nextSession = await checkAuthToken();
      setSession(nextSession);
      return true;
    } catch {
      removeAuthToken();
      token.value = null;
      setSession(null);
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
    validateToken,
  };
}
