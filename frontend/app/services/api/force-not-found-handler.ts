import type { ApiErrorResponse } from '@/types/api/http';

const FORCE_NOT_FOUND_ERROR_CODE = 'force_not_found';

export function handleForceNotFound(response: ApiErrorResponse|null): void {
  if (!import.meta.client || response?.error_code !== FORCE_NOT_FOUND_ERROR_CODE) {
    return;
  }

  showError({
    statusCode: 404,
    statusMessage: response.message ?? 'Página não encontrada.',
  });
}
