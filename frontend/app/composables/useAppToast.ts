import { toast, type ExternalToast } from 'vue-sonner';

type AppToastType = 'primary'|'success'|'error'|'warning'|'default';

const DUPLICATE_MESSAGE_SECONDS = 3;

let lastToastMessage: string|null = null;
let lastToastTimeout: ReturnType<typeof setTimeout>|null = null;

export function useAppToast() {
  function shouldRenderToast(message: string): boolean {
    if (lastToastMessage === message) {
      return false;
    }

    lastToastMessage = message;

    if (lastToastTimeout !== null) {
      clearTimeout(lastToastTimeout);
    }

    lastToastTimeout = setTimeout((): void => {
      lastToastMessage = null;
      lastToastTimeout = null;
    }, DUPLICATE_MESSAGE_SECONDS * 1000);

    return true;
  }

  function show(type: AppToastType, message: string, options: ExternalToast = {}): string|number|undefined {
    if (!shouldRenderToast(message)) {
      return undefined;
    }

    if (type === 'primary') {
      return toast.info(message, options);
    }

    if (type === 'success') {
      return toast.success(message, options);
    }

    if (type === 'error') {
      return toast.error(message, options);
    }

    if (type === 'warning') {
      return toast.warning(message, options);
    }

    return toast(message, {
      ...options,
      class: ['app-toast-default', options.class].filter(Boolean).join(' '),
    });
  }

  return {
    primary(message: string, options?: ExternalToast): string|number|undefined {
      return show('primary', message, options);
    },

    success(message: string, options?: ExternalToast): string|number|undefined {
      return show('success', message, options);
    },

    error(message: string, options?: ExternalToast): string|number|undefined {
      return show('error', message, options);
    },

    warning(message: string, options?: ExternalToast): string|number|undefined {
      return show('warning', message, options);
    },

    default(message: string, options?: ExternalToast): string|number|undefined {
      return show('default', message, options);
    },
  };
}
