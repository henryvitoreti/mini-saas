import type { ApiErrorResponse } from '@/types/api/http';

const DOMAIN_PERMISSION_DENIED_ERROR_CODE = 'domain_permission_denied';

let isRefreshingPermissions = false;

export async function handleDomainPermissionDenied(response: ApiErrorResponse|null): Promise<void> {
  if (
    !import.meta.client
    || response?.error_code !== DOMAIN_PERMISSION_DENIED_ERROR_CODE
    || isRefreshingPermissions
  ) {
    return;
  }

  isRefreshingPermissions = true;

  try {
    const { useAuth } = await import('@/composables/useAuth');

    await useAuth().refreshPermissions();
  } catch(error) {
    console.error(error);
  } finally {
    isRefreshingPermissions = false;
  }

  if (window.location.pathname !== '/') {
    await navigateTo('/');
  }
}
