<script setup lang="ts">
import AppCheckboxInput from '@/components/form/AppCheckboxInput.vue';
import AppTextInput from '@/components/form/AppTextInput.vue';
import { getFullLogo } from '@/config/appLogos';
import { ApiValidationError, type ValidationErrors } from '@/types/api/http';

definePageMeta({
  layout: false,
});

const auth = useAuth();

const form = reactive({
  email: '',
  password: '',
  remember_login: false,
});

const errors = ref<ValidationErrors>({});

async function submitLogin(): Promise<void> {
  errors.value = {};

  try {
    await auth.login(form.email, form.password, form.remember_login);
  } catch (error) {
    if (error instanceof ApiValidationError) {
      errors.value = error.errors;
    }
  }
}
</script>

<template>
  <main class="app-login-page">
    <section class="app-login-card">
      <div class="app-login-header">
        <img
            class="app-login-logo"
            :src="getFullLogo()"
            alt="Minski"
        >
      </div>

      <form class="app-login-form" @submit.prevent="submitLogin">
        <AppTextInput
            name="email"
            label="E-mail"
            type="email"
            required
            :value="form.email"
            :error-message="errors.email"
            @update:value="(value) => form.email = value"
        />

        <AppTextInput
            name="password"
            label="Senha"
            type="password"
            required
            :value="form.password"
            :error-message="errors.password"
            @update:value="(value) => form.password = value"
        />

        <AppCheckboxInput
            name="remember_login"
            label="Manter conectado por mais tempo"
            :value="form.remember_login"
            @update:value="(value) => form.remember_login = value"
        />

        <button
            class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 app-login-submit-button"
            type="submit"
        >
          Entrar
        </button>
      </form>
    </section>
  </main>
</template>
