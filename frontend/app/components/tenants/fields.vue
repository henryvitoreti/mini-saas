<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppSelect2Input from '@/components/form/AppSelect2Input.vue';
import AppTextInput from '@/components/form/AppTextInput.vue';
import AppTextareaInput from '@/components/form/AppTextareaInput.vue';
import AppInfoTooltip from '@/components/ui/AppInfoTooltip.vue';
import { useAddressLookup } from '@/composables/forms/AddressLookupComposable';
import { useTenantForm } from '@/composables/forms/TenantFormComposable';
import type { TenantFormProps } from '@/types/forms/form';

const props = withDefaults(
  defineProps<TenantFormProps>(),
  {
    id: null,
    isModal: false,
  },
);

const router = useRouter();
const config = useRuntimeConfig();
const appToast = useAppToast();
const tenantForm = useTenantForm();
const { attributes } = tenantForm;
const isDomainManuallyEdited = ref(false);
const tenantDomain = ref<string|null>(null);
const {
  isLoadingStates,
  isLoadingCities,
  fetchStates,
  fetchCities,
  updateState,
  updateZipCode,
} = useAddressLookup(attributes);

const isEditing = computed<boolean>(() => {
  return props.id !== null;
});

const finalUrl = computed<string>(() => {
  const domain = isEditing.value ? tenantDomain.value : buildDomain(attributes.tenantId.value);

  return buildFinalUrl(domain);
});

function slugify(value: string): string {
  return value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .toLowerCase();
}

function getApiBaseUrl(): URL|null {
  if (!import.meta.client) {
    return null;
  }

  try {
    return new URL(String(config.public.apiBaseUrl), window.location.origin);
  } catch {
    return null;
  }
}

function buildDomain(tenantId: string|null): string|null {
  if (!tenantId) {
    return null;
  }

  const apiBaseUrl = getApiBaseUrl();

  if (apiBaseUrl === null) {
    return tenantId;
  }

  const frontendHostname = apiBaseUrl.hostname.startsWith('api.')
    ? apiBaseUrl.hostname.slice(4)
    : apiBaseUrl.hostname.replace('.api.', '.');

  return `${tenantId}.${frontendHostname}`;
}

function buildFinalUrl(domain: string|null): string {
  if (!domain) {
    return '-';
  }

  if (!import.meta.client) {
    return domain;
  }

  const apiBaseUrl = getApiBaseUrl();

  if (apiBaseUrl === null) {
    return domain;
  }

  const hasExplicitPort = /:\d+$/.test(domain);
  const port = apiBaseUrl.port && !hasExplicitPort ? `:${apiBaseUrl.port}` : '';

  return `${apiBaseUrl.protocol}//${domain}${port}`;
}

function updateCompanyName(value: string): void {
  attributes.companyName.value = value || null;

  if (isEditing.value) {
    return;
  }

  if (!isDomainManuallyEdited.value) {
    attributes.tenantId.value = slugify(value) || null;
  }
}

function updateTenantId(value: string): void {
  if (isEditing.value) {
    return;
  }

  isDomainManuallyEdited.value = true;
  attributes.tenantId.value = slugify(value) || null;
}

async function loadTenant(): Promise<void> {
  if (props.id === null) {
    return;
  }

  const tenant = await tenantForm.load(String(props.id));

  if (tenant === null) {
    await router.push('/dominios');
    return;
  }

  tenantDomain.value = tenant.domain;

  const state = attributes.state.value;

  if (state !== null) {
    await fetchCities(state);
  }
}

async function save(): Promise<void> {
  const tenant = props.id === null
    ? await tenantForm.create()
    : await tenantForm.update(String(props.id));

  if (tenant === null) {
    return;
  }

  appToast.success(isEditing.value ? 'Tenant atualizado com sucesso.' : 'Tenant cadastrado com sucesso.');
  await router.push('/dominios');
}

onMounted(async (): Promise<void> => {
  await fetchStates();
  await loadTenant();
});
</script>
<!-- TODO usar FilePond para upload e edição de imagens -->
<!-- TODO adicionar sistema de cards para gerenciar multiplos usuários do sistema -->
<template>
  <form id="tenant-form" class="app-form-stack" @submit.prevent="save">
    <section class="app-form-card">
      <div class="app-form-card-header">
        <div class="app-form-card-title-row">
          <h2 class="app-form-card-title">
            Dados da empresa
          </h2>

          <AppInfoTooltip text="Informações cadastrais e de acesso da empresa." />
        </div>
      </div>

      <div class="app-form-card-body">
        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <AppTextInput
                name="company.name"
                :label="attributes.companyName.label"
                :required="attributes.companyName.required"
                :value="attributes.companyName.value"
                :error-message="attributes.companyName.errorMessage"
                @update:value="updateCompanyName"
            />
          </div>

          <div class="col-12 col-lg-6">
            <AppTextInput
                name="id"
                :label="attributes.tenantId.label"
                :required="attributes.tenantId.required"
                :tip="attributes.tenantId.tip"
                :disabled="isEditing"
                :value="attributes.tenantId.value"
                :error-message="attributes.tenantId.errorMessage"
                @update:value="updateTenantId"
            />

            <div class="form-text">
              URL final: <strong>{{ finalUrl }}</strong>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <AppSelect2Input
                name="company.role_id"
                :label="attributes.companyRoleId.label"
                :placeholder="attributes.companyRoleId.placeholder"
                :required="attributes.companyRoleId.required"
                api-url="/roles/options"
                v-model:value="attributes.companyRoleId.value"
                :error-message="attributes.companyRoleId.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppTextInput
                name="company.document"
                :label="attributes.companyDocument.label"
                :mask="attributes.companyDocument.mask"
                :placeholder="attributes.companyDocument.placeholder"
                :required="attributes.companyDocument.required"
                :disabled="isEditing"
                v-model:value="attributes.companyDocument.value"
                :error-message="attributes.companyDocument.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppTextInput
                name="company.email"
                type="email"
                :label="attributes.companyEmail.label"
                :required="attributes.companyEmail.required"
                v-model:value="attributes.companyEmail.value"
                :error-message="attributes.companyEmail.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6">
            <AppTextInput
                name="company.phone"
                :label="attributes.companyPhone.label"
                :mask="attributes.companyPhone.mask"
                :placeholder="attributes.companyPhone.placeholder"
                :required="attributes.companyPhone.required"
                v-model:value="attributes.companyPhone.value"
                :error-message="attributes.companyPhone.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6">
            <AppTextInput
                name="company.secondary_phone"
                :label="attributes.companySecondaryPhone.label"
                :mask="attributes.companySecondaryPhone.mask"
                :placeholder="attributes.companySecondaryPhone.placeholder"
                v-model:value="attributes.companySecondaryPhone.value"
                :error-message="attributes.companySecondaryPhone.errorMessage"
            />
          </div>

          <div class="col-12 col-lg-4">
            <AppTextInput
                name="company.zip_code"
                :label="attributes.zipCode.label"
                :mask="attributes.zipCode.mask"
                :placeholder="attributes.zipCode.placeholder"
                :start-icon="attributes.zipCode.startIcon"
                :value="attributes.zipCode.value"
                :tip="attributes.zipCode.tip"
                :error-message="attributes.zipCode.errorMessage"
                @update:value="updateZipCode"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppSelect2Input
                name="company.state"
                :label="attributes.state.label"
                :placeholder="attributes.state.placeholder"
                :disabled="isLoadingStates"
                :options="attributes.state.options"
                :value="attributes.state.value"
                :error-message="attributes.state.errorMessage"
                @update:value="updateState"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppSelect2Input
                name="company.city"
                :label="attributes.city.label"
                :placeholder="attributes.state.value ? 'Selecione' : attributes.city.placeholder"
                :disabled="!attributes.state.value || isLoadingCities"
                :options="attributes.city.options"
                v-model:value="attributes.city.value"
                :error-message="attributes.city.errorMessage"
            />
          </div>

          <div class="col-12 col-lg-5">
            <AppTextInput
                name="company.district"
                :label="attributes.district.label"
                v-model:value="attributes.district.value"
                :error-message="attributes.district.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-8 col-lg-5">
            <AppTextInput
                name="company.street"
                :label="attributes.street.label"
                v-model:value="attributes.street.value"
                :error-message="attributes.street.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-4 col-lg-2">
            <AppTextInput
                name="company.number"
                :label="attributes.number.label"
                :mask="attributes.number.mask"
                v-model:value="attributes.number.value"
                :error-message="attributes.number.errorMessage"
            />
          </div>

          <div class="col-12">
            <AppTextInput
                name="company.complement"
                :label="attributes.complement.label"
                v-model:value="attributes.complement.value"
                :error-message="attributes.complement.errorMessage"
            />
          </div>

          <div class="col-12">
            <AppTextareaInput
                name="company.notes"
                :label="attributes.notes.label"
                :placeholder="attributes.notes.placeholder"
                :rows="4"
                v-model:value="attributes.notes.value"
                :error-message="attributes.notes.errorMessage"
            />
          </div>
        </div>
      </div>
    </section>

    <section v-if="!isEditing" class="app-form-card">
      <div class="app-form-card-header">
        <h2 class="app-form-card-title">
          Usuário de acesso
        </h2>
      </div>

      <div class="app-form-card-body">
        <div class="row g-3">
          <div class="col-12 col-lg-4">
            <AppTextInput
                name="user.name"
                :label="attributes.userName.label"
                :required="attributes.userName.required"
                v-model:value="attributes.userName.value"
                :error-message="attributes.userName.errorMessage"
            />
          </div>

          <div class="col-12 col-lg-4">
            <AppTextInput
                name="user.email"
                type="email"
                :label="attributes.userEmail.label"
                :required="attributes.userEmail.required"
                v-model:value="attributes.userEmail.value"
                :error-message="attributes.userEmail.errorMessage"
            />
          </div>

          <div class="col-12 col-lg-4">
            <AppTextInput
                name="user.password"
                type="password"
                :label="attributes.userPassword.label"
                :required="attributes.userPassword.required"
                v-model:value="attributes.userPassword.value"
                :error-message="attributes.userPassword.errorMessage"
            />
          </div>
        </div>
      </div>
    </section>
  </form>
</template>
