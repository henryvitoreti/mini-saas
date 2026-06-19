import type { FetchOptions } from 'ofetch';
import {
  ApiValidationError,
  type ApiErrorResponse,
  type ApiResponse,
  type HttpMethod,
  type ValidationErrors,
} from '@/types/api/http';

const TOKEN_STORAGE_KEY = 'auth.token';

export type AppFetchOptions = FetchOptions & {
  showGlobalLoading?: boolean;
};

function getStoredToken(): string|null {
  if (!import.meta.client) {
    return null;
  }

  return localStorage.getItem(TOKEN_STORAGE_KEY);
}

function setStoredToken(token: string): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.setItem(TOKEN_STORAGE_KEY, token);
}

function removeStoredToken(): void {
  if (!import.meta.client) {
    return;
  }

  localStorage.removeItem(TOKEN_STORAGE_KEY);
}

function mapValidationErrors(errors: Record<string, string[]>|undefined): ValidationErrors {
  if (!errors) {
    return {};
  }

  return Object.fromEntries(
    Object.entries(errors).map(([field, messages]) => [field, messages[0] ?? 'Campo inválido.']),
  );
}

function handleRequestError(error: unknown): never {
  if (
    typeof error === 'object'
    && error !== null
    && 'statusCode' in error
    && (error as { statusCode?: number }).statusCode === 429
  ) {
    const response = (error as { data?: ApiErrorResponse }).data;
    const message = response?.message ?? 'Múltiplas requisições detectadas. Aguarde alguns instantes.';

    if (import.meta.client) {
      useAppToast().error(message);
    }

    throw error;
  }

  if (
    typeof error === 'object'
    && error !== null
    && 'statusCode' in error
    && (error as { statusCode?: number }).statusCode === 422
  ) {
    const response = (error as { data?: ApiErrorResponse }).data;

    throw new ApiValidationError(
      mapValidationErrors(response?.errors),
      response?.message ?? 'Verifique os campos informados.',
    );
  }

  throw error;
}

async function request<T>(
  method: HttpMethod,
  endpoint: string,
  body?: unknown,
  options: AppFetchOptions = {},
): Promise<T> {
  const config = useRuntimeConfig();
  const token = getStoredToken();
  const headers = new Headers(options.headers as HeadersInit|undefined);
  const { showGlobalLoading = true, ...fetchOptions } = options;
  const requestLoading = useAppRequestLoading();
  const usesGlobalLoading = requestLoading.shouldShowLoading(method, showGlobalLoading);

  if (usesGlobalLoading && requestLoading.isLoading.value) {
    if (import.meta.client) {
      useAppToast().warning('Aguarde a requisição atual finalizar.');
    }

    throw new Error('Mutable request already in progress.');
  }

  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }

  if (usesGlobalLoading) {
    requestLoading.startLoading();
  }

  try {
    return await $fetch<T>(endpoint, {
      ...fetchOptions,
      baseURL: config.public.apiBaseUrl,
      method,
      body,
      headers,
    });
  } catch (error) {
    handleRequestError(error);
  } finally {
    if (usesGlobalLoading) {
      requestLoading.stopLoading();
    }
  }
}

export const apiHttpClient = {
  get<T>(endpoint: string, options?: AppFetchOptions): Promise<T> {
    return request<T>('GET', endpoint, undefined, options);
  },

  post<T>(endpoint: string, body?: unknown, options?: AppFetchOptions): Promise<T> {
    return request<T>('POST', endpoint, body, options);
  },

  put<T>(endpoint: string, body?: unknown, options?: AppFetchOptions): Promise<T> {
    return request<T>('PUT', endpoint, body, options);
  },

  patch<T>(endpoint: string, body?: unknown, options?: AppFetchOptions): Promise<T> {
    return request<T>('PATCH', endpoint, body, options);
  },

  delete<T>(endpoint: string, options?: AppFetchOptions): Promise<T> {
    return request<T>('DELETE', endpoint, undefined, options);
  },

  getToken(): string|null {
    return getStoredToken();
  },

  setToken(token: string): void {
    setStoredToken(token);
  },

  removeToken(): void {
    removeStoredToken();
  },
};

export type { ApiResponse };
