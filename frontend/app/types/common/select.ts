export type SelectOptionValue = string|number;

export type SelectOption = {
  label: string;
  value: SelectOptionValue;
  disabled: boolean|null;
};
