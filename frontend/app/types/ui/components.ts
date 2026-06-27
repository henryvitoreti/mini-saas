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
