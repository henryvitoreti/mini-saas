export type AppInfoTooltipProps = {
  text: string;
  isInputLabel: boolean|null;
};

export type AppDialogProps = {
  title: string|null;
  showCloseButton: boolean|null;
  closeOnBackdrop: boolean|null;
  showCancelButton: boolean|null;
  cancelLabel: string|null;
  isCancelDisabled: boolean|null;
  closeButtonTitle: string|null;
};

export type AppIconDropdownOptionValue = string|number;

export type AppIconDropdownOption = {
  title: string;
  value: AppIconDropdownOptionValue;
};

export type AppIconDropdownProps = {
  icon: string;
  title: string;
  isOpen: boolean;
  options: AppIconDropdownOption[];
  selectedValue: AppIconDropdownOptionValue|null;
  disabled: boolean|null;
};
