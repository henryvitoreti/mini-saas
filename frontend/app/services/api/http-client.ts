import type { FetchOptions } from 'ofetch';
import { handleDomainPermissionDenied } from '@/services/api/domain-permission-denied-handler';
import { handleForceNotFound } from '@/services/api/force-not-found-handler';
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

function getConfigValue(value: unknown): string|null {
  if (typeof value !== 'string' || value === '') {
    return null;
  }

  return value;
}

function createUrl(value: string|null, fallbackOrigin: string): URL|null {
  if (value === null) {
    return null;
  }

  try {
    return new URL(value, fallbackOrigin);
  } catch {
    return null;
  }
}

function getFrontendBaseHostname(frontendBaseUrl: string|null, apiBaseUrl: URL): string|null {
  const fallbackOrigin = `${apiBaseUrl.protocol}//${apiBaseUrl.host}`;
  const parsedFrontendBaseUrl = createUrl(frontendBaseUrl, fallbackOrigin);

  if (parsedFrontendBaseUrl !== null) {
    return parsedFrontendBaseUrl.hostname;
  }

  if (apiBaseUrl.hostname.startsWith('api.')) {
    return apiBaseUrl.hostname.slice(4);
  }

  return null;
}

function getApiBaseUrl(apiBaseUrlValue: string, frontendBaseUrlValue: string|null): string {
  if (!import.meta.client) {
    return apiBaseUrlValue;
  }

  const apiBaseUrl = createUrl(apiBaseUrlValue, window.location.origin);

  if (apiBaseUrl === null) {
    return apiBaseUrlValue;
  }

  const frontendBaseHostname = getFrontendBaseHostname(frontendBaseUrlValue, apiBaseUrl);

  if (frontendBaseHostname === null) {
    return apiBaseUrl.toString();
  }

  const currentHostname = window.location.hostname;

  if (currentHostname === frontendBaseHostname) {
    return apiBaseUrl.toString();
  }

  const frontendBaseHostSuffix = `.${frontendBaseHostname}`;

  if (!currentHostname.endsWith(frontendBaseHostSuffix)) {
    return apiBaseUrl.toString();
  }

  const tenantSubdomain = currentHostname.slice(0, -frontendBaseHostSuffix.length);

  if (tenantSubdomain === '' || tenantSubdomain.includes('.')) {
    return apiBaseUrl.toString();
  }

  apiBaseUrl.hostname = `${tenantSubdomain}.${apiBaseUrl.hostname}`;

  return apiBaseUrl.toString();
}

function mapValidationErrors(errors: Record<string, string[]>|undefined): ValidationErrors {
  if (!errors) {
    return {};
  }

  return Object.fromEntries(
    Object.entries(errors).map(([field, messages]) => [field, messages[0] ?? 'Campo inválido.']),
  );
}

function getApiErrorResponse(error: unknown): ApiErrorResponse|null {
  if (typeof error !== 'object' || error === null || !('data' in error)) {
    return null;
  }

  const response = (error as { data?: unknown }).data;

  return typeof response === 'object' && response !== null
    ? response as ApiErrorResponse
    : null;
}

function isHttpError(error: unknown, statusCode: number): boolean {
  return typeof error === 'object'
    && error !== null
    && 'statusCode' in error
    && (error as { statusCode?: number }).statusCode === statusCode;
}

async function handleRequestError(error: unknown): Promise<never> {
  const response = getApiErrorResponse(error);

  if (isHttpError(error, 404)) {
    handleForceNotFound(response);
  } else if (isHttpError(error, 403)) {
    await handleDomainPermissionDenied(response);
  } else if (isHttpError(error, 429)) {
    const message = response?.message ?? 'Múltiplas requisições detectadas. Aguarde alguns instantes.';

    if (import.meta.client) {
      useAppToast().error(message);
    }
  } else if (isHttpError(error, 422)) {
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
  const configuredApiBaseUrl = getConfigValue(config.public.apiBaseUrl) ?? '/api';
  const frontendBaseUrl = getConfigValue(config.public.baseUrl);
  const apiBaseUrl = getApiBaseUrl(configuredApiBaseUrl, frontendBaseUrl);
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
      baseURL: apiBaseUrl,
      method,
      body,
      headers,
    });
  } catch (error) {
    await handleRequestError(error);
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
