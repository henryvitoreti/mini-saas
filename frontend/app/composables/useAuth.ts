import {
  checkAuthToken,
  getAuthToken,
  getAuthUser,
  login as loginRequest,
  logout as logoutRequest,
  removeAuthToken,
} from '@/services/api/auth-service';
import type { AuthenticatedPermission, AuthenticatedUser } from '@/types/auth';

export function useAuth() {
  const token = useState<string|null>('auth.token', () => getAuthToken());
  const user = useState<AuthenticatedUser|null>('auth.user', () => getAuthUser());
  const permissions = useState<AuthenticatedPermission[]>('auth.permissions', () => []);

  const isAuthenticated = computed<boolean>(() => Boolean(token.value));

  async function login(email: string, password: string, rememberLogin = false): Promise<void> {
    const response = await loginRequest(email, password, rememberLogin);

    token.value = response.access_token;
    user.value = response.user;
    permissions.value = response.user.permissions ?? [];

    await navigateTo('/');
  }

  async function logout(): Promise<void> {
    await logoutRequest();
    token.value = null;
    user.value = null;
    permissions.value = [];

    await navigateTo('/login');
  }

  async function validateToken(): Promise<boolean> {
    token.value = getAuthToken();

    if (!token.value) {
      return false;
    }

    try {
      await checkAuthToken();
      return true;
    } catch {
      removeAuthToken();
      token.value = null;
      user.value = null;
      permissions.value = [];
      return false;
    }
  }

  return {
    user,
    permissions,
    token,
    isAuthenticated,
    login,
    logout,
    validateToken,
  };
}
