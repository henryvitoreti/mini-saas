import type { Breadcrumb } from '@/types/common/navigation';
import type { SelectOption } from '@/types/common/select';
import type { FormInputMask } from '@/types/forms/form';

export type DateRangeInputValue = [string|null, string|null];

export type DatePickerValue = string|null|DateRangeInputValue;

export type AppTextareaInputProps = {
  value: string|number|null;
  modelValue: string|number|null;
  name: string;
  label: string|null;
  placeholder: string|null;
  required: boolean|null;
  tip: string|null;
  rows: number|null;
  errorMessage: string|null;
};

export type AppSelectInputProps = {
  value: string|number|null;
  modelValue: string|number|null;
  name: string;
  label: string|null;
  placeholder: string|null;
  required: boolean|null;
  disabled: boolean|null;
  clearable: boolean|null;
  tip: string|null;
  errorMessage: string|null;
  options: SelectOption[];
};

export type AppSelect2InputProps = {
  value: string|number|null;
  modelValue: string|number|null;
  name: string;
  label: string|null;
  placeholder: string|null;
  required: boolean|null;
  disabled: boolean|null;
  tip: string|null;
  options: SelectOption[]|null;
  apiUrl: string|null;
  searchParam: string|null;
  pageParam: string|null;
  perPageParam: string|null;
  perPage: number|null;
  errorMessage: string|null;
};

export type AppTextInputProps = {
  value: string|number|null;
  modelValue: string|number|null;
  name: string;
  type: string|null;
  label: string|null;
  mask: FormInputMask|null;
  placeholder: string|null;
  required: boolean|null;
  disabled: boolean|null;
  tip: string|null;
  startIcon: string|null;
  endIcon: string|null;
  errorMessage: string|null;
};

export type AppSwitchInputProps = {
  value: boolean|null;
  modelValue: boolean|null;
  name: string;
  label: string|null;
  required: boolean|null;
  disabled: boolean|null;
  tip: string|null;
  isDefaultLayout: boolean|null;
  errorMessage: string|null;
};

export type AppCheckboxInputProps = {
  value: boolean|null;
  modelValue: boolean|null;
  name: string;
  label: string|null;
  required: boolean|null;
  disabled: boolean|null;
  tip: string|null;
  errorMessage: string|null;
};

export type AppDateInputProps = {
  value: DatePickerValue;
  modelValue: DatePickerValue;
  name: string;
  label: string|null;
  placeholder: string|null;
  required: boolean|null;
  disabled: boolean|null;
  range: boolean|null;
  tip: string|null;
  errorMessage: string|null;
};

export type AppFormContainerProps = {
  title: string;
  subtitle: string|null;
  breadcrumbs: Breadcrumb[]|null;
};
