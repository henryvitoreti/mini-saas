const MUTABLE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

export function useAppRequestLoading() {
  const activeRequestCount = useState<number>('app.active-mutable-request-count', () => 0);

  const isLoading = computed<boolean>(() => activeRequestCount.value > 0);

  function shouldShowLoading(method: string, showGlobalLoading: boolean = true): boolean {
    return showGlobalLoading && MUTABLE_METHODS.includes(method.toUpperCase());
  }

  function startLoading(): void {
    activeRequestCount.value++;
  }

  function stopLoading(): void {
    activeRequestCount.value = Math.max(0, activeRequestCount.value - 1);
  }

  return {
    isLoading,
    shouldShowLoading,
    startLoading,
    stopLoading,
  };
}
