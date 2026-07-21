import type { SelectOption } from '@/types/common/select';

export type FormInputMask = string|string[];

export type FormAttribute<TValue = unknown> = {
  responseKey: string;
  payloadKey: string;
  value: TValue;
  label: string;
  placeholder?: string|null;
  required?: boolean|null;
  errorMessage: string|null;
  mask?: FormInputMask|null;
  startIcon?: string|null;
  tip?: string|null;
  clearable?: boolean|null;
  options?: SelectOption[]|null;
};

export type FormPayload = Record<string, unknown>;

export type FormPayloadFormatter = (payload: FormPayload) => FormPayload;

export type FieldsProps = {
  id?: number|null;
  isModal?: boolean;
};

export type TenantFormProps = {
  id?: string|null;
  isModal?: boolean;
};