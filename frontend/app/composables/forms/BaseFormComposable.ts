import { useAppToast } from '@/composables/useAppToast';
import type { FormAttribute, FormPayload, FormPayloadFormatter } from '@/types/forms/form';

export function useBaseForm<TAttributes extends Record<string, FormAttribute>>(
  attributes: TAttributes,
) {
  function normalizePayloadValue(value: unknown): unknown {
    return value === '' ? null : value;
  }

  function clearErrors(): void {
    for (const attributeKey in attributes) {
      attributes[attributeKey].errorMessage = null;
    }
  }

  function fillAttributes(data: Record<string, unknown>): void {
    for (const attributeKey in attributes) {
      const attribute = attributes[attributeKey];

      attribute.value = data[attribute.responseKey] ?? null;
      attribute.errorMessage = null;
    }
  }

  function handleDoSendAttributes(formatter?: FormPayloadFormatter): FormPayload {
    const payload: FormPayload = {};

    for (const attributeKey in attributes) {
      const attribute = attributes[attributeKey];

      payload[attribute.payloadKey] = normalizePayloadValue(attribute.value);
    }

    if (formatter) {
      return formatter(payload);
    }

    return payload;
  }

  function handleError(error: any, defaultMessage: string): void {
    const status = error?.statusCode ?? error?.status ?? error?.response?.status;
    const data = error?.data ?? error?.response?._data ?? error?.response?.data;
    const validationErrors = data?.errors ?? error?.errors;

    if ((status === 422 && data?.errors) || error?.name === 'ApiValidationError') {
      clearErrors();

      for (const attributeKey in attributes) {
        const attribute = attributes[attributeKey];
        const validationMessage = validationErrors?.[attribute.payloadKey] ?? null;
        const errorMessage = Array.isArray(validationMessage)
          ? validationMessage[0] ?? null
          : validationMessage;

        attribute.errorMessage = typeof errorMessage === 'string' ? errorMessage : null;
      }

      return;
    }

    const appToast = useAppToast();

    if (status === 400 && data?.message) {
      appToast.error(data.message);

      return;
    }

    appToast.error(defaultMessage);
  }

  return {
    clearErrors,
    fillAttributes,
    handleDoSendAttributes,
    handleError,
  };
}
