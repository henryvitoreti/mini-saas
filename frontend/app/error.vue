<script setup lang="ts">
import { getFullLogo } from '@/config/appLogos';
import type { AppError, ErrorContent } from '@/types/ui/error';

const props = defineProps<{
  error: AppError;
}>();

onMounted((): void => {
  const storedTheme = localStorage.getItem('theme');
  const isDarkTheme = useState<boolean>('theme-is-dark', () => false);

  isDarkTheme.value = storedTheme === 'dark';
  document.documentElement.setAttribute('data-theme', storedTheme === 'dark' ? 'dark' : 'light');
});

const statusCode = computed<number>(() => {
  return props.error.statusCode ?? 500;
});

const errorContent = computed<ErrorContent>(() => {
  const contentByStatus: Record<number, ErrorContent> = {
    401: {
      icon: 'fa-solid fa-lock',
      eyebrow: 'Acesso restrito',
      title: 'Sessão necessária',
      description: 'Entre novamente para continuar acessando esta área.',
    },
    403: {
      icon: 'fa-solid fa-shield-halved',
      eyebrow: 'Permissão negada',
      title: 'Você não tem acesso a esta área',
      description: 'Confira suas permissões ou volte para uma página disponível.',
    },
    404: {
      icon: 'fa-solid fa-map-location-dot',
      eyebrow: 'Página não encontrada',
      title: 'Não encontramos este endereço',
      description: 'A página pode ter sido removida, renomeada ou ainda não foi criada.',
    },
    500: {
      icon: 'fa-solid fa-triangle-exclamation',
      eyebrow: 'Erro interno',
      title: 'Não foi possível carregar a página',
      description: 'O servidor encontrou uma falha ao processar esta solicitação.',
    },
  };

  return contentByStatus[statusCode.value] ?? {
    icon: 'fa-solid fa-circle-exclamation',
    eyebrow: 'Falha inesperada',
    title: 'Algo saiu do fluxo esperado',
    description: props.error.statusMessage || props.error.message || 'Tente novamente em alguns instantes.',
  };
});

async function goHome(): Promise<void> {
  await clearError({ redirect: '/' });
}
</script>

<template>
  <main class="app-error-page">
    <section class="app-error-panel">
      <img
          class="app-error-logo"
          :src="getFullLogo()"
          alt="Minski"
      >

      <div class="app-error-icon">
        <i :class="errorContent.icon"></i>
      </div>

      <span class="app-error-eyebrow">
        {{ errorContent.eyebrow }}
      </span>

      <h1 class="app-error-title">
        {{ errorContent.title }}
      </h1>

      <p class="app-error-description">
        {{ errorContent.description }}
      </p>

      <div class="app-error-status">
        <span>Status</span>
        <strong>{{ statusCode }}</strong>
      </div>

      <div class="app-error-actions">
        <button class="btn btn-custom-primary" type="button" @click="goHome">
          <i class="fa-solid fa-house me-2"></i>
          Ir para o início
        </button>
      </div>
    </section>
  </main>
</template>
