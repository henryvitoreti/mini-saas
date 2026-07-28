export type HttpMethod = 'GET'|'POST'|'PUT'|'PATCH'|'DELETE';

export type ValidationErrors = Record<string, string>;

export type ApiResponse<T> = {
  message: string;
  data: T;
};

export type ApiErrorResponse = {
  message: string|null;
  errors: Record<string, string[]>|null;
  error_code?: string|null;
};

export class ApiValidationError extends Error {
  public readonly errors: ValidationErrors;

  public constructor(errors: ValidationErrors, message: string = 'Erro de validação.') {
    super(message);
    this.name = 'ApiValidationError';
    this.errors = errors;
  }
}
