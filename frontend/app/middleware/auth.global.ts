export default defineNuxtRouteMiddleware(async (to) => {
  if (!import.meta.client) {
    return;
  }

  const auth = useAuth();
  const isLoginRoute = to.path === '/login';
  const isTokenValid = await auth.validateToken();

  if (!isTokenValid && !isLoginRoute) {
    return navigateTo('/login');
  }

  if (isTokenValid && isLoginRoute) {
    return navigateTo('/');
  }
});
