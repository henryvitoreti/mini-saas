<script setup lang="ts">
import AppFormContainer from "@/components/form/AppFormContainer.vue";
import AppSelect2Input from "@/components/form/AppSelect2Input.vue";
import AppSwitchInput from "@/components/form/AppSwitchInput.vue";
import AppTextInput from "@/components/form/AppTextInput.vue";
import AppTextareaInput from "@/components/form/AppTextareaInput.vue";
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";
import debounce from 'lodash/debounce';
import { fetchIbgeCitiesByState, fetchIbgeStates, fetchViaCepAddress } from '@/services/external/address-lookup';
import type { SelectOption } from '@/types/common/select';
import type { CustomerForm } from '@/types/entities/customer';

const form = reactive<CustomerForm>({
  name: null,
  document: null,
  email: null,
  phone: null,
  secondary_phone: null,
  zip_code: null,
  street: null,
  number: null,
  complement: null,
  district: null,
  city: null,
  state: null,
  notes: null,
  is_active: true,
});

const stateOptions = ref<SelectOption[]>([]);
const cityOptions = ref<SelectOption[]>([]);
const isLoadingStates = ref(false);
const isLoadingCities = ref(false);

async function fetchStates(): Promise<void> {
  isLoadingStates.value = true;

  try {
    const states = await fetchIbgeStates();

    stateOptions.value = states.map((state) => ({
      label: `${state.sigla} - ${state.nome}`,
      value: state.sigla,
    }));
  } finally {
    isLoadingStates.value = false;
  }
}

async function fetchCities(state: string|null): Promise<void> {
  if (!state) {
    cityOptions.value = [];
    return;
  }

  isLoadingCities.value = true;

  try {
    const cities = await fetchIbgeCitiesByState(state);

    cityOptions.value = cities.map((city) => ({
      label: city.nome,
      value: city.nome,
    }));
  } finally {
    isLoadingCities.value = false;
  }
}

async function updateState(state: string): Promise<void> {
  form.state = state;
  form.city = null;
  await fetchCities(state);
}

function updateZipCode(zipCode: string): void {
  form.zip_code = zipCode || null;
  const normalizedZipCode = form.zip_code ?? '';

  if (normalizedZipCode.length !== 8) {
    return;
  }

  searchZipCode(normalizedZipCode);
}

function updateNumber(value: string): void {
  form.number = value || null;
}

const searchZipCode = debounce(async (zipCode: string): Promise<void> => {
  try {
    const address = await fetchViaCepAddress(zipCode);

    if (address.erro) {
      return;
    }

    form.street = address.logradouro ?? form.street;
    form.complement = address.complemento ?? form.complement;
    form.district = address.bairro ?? form.district;

    if (address.uf) {
      form.state = address.uf;
      await fetchCities(address.uf);
    }

    if (address.localidade) {
      form.city = address.localidade;
    }
  } catch (error) {
    console.error(error);
  }
}, 1500);

onMounted(() => {
  void fetchStates();
});
</script>

<template>
  <AppFormContainer
      title="Criar cliente"
      :breadcrumbs="[
      { label: 'Clientes', to: '/clientes' },
      { label: 'Criar cliente' },
    ]"
  >
    <div class="app-form-stack">
      <section class="app-form-card">
        <div class="app-form-card-header">
          <div class="app-form-card-title-row">
            <h2 class="app-form-card-title">
              Dados principais
            </h2>

            <AppInfoTooltip text="Informações usadas para identificação e contato." />
          </div>
        </div>

        <div class="app-form-card-body">
          <div class="row g-3">
            <div class="col-12 col-md-5">
              <AppTextInput
                  name="name"
                  label="Nome"
                  required
                  :value="form.name"
                  @update:value="(value) => form.name = value"
              />
            </div>

            <div class="col-12 col-sm-9 col-md-5">
              <AppTextInput
                  name="email"
                  label="E-mail"
                  type="email"
                  required
                  :value="form.email"
                  @update:value="(value) => form.email = value"
              />
            </div>

            <div class="col-12 col-sm-3 col-md-2">
              <AppSwitchInput
                  name="is_active"
                  label="Ativo"
                  required
                  :value="form.is_active"
                  @update:value="(value) => form.is_active = value"
              />
            </div>

            <div class="col-12 col-lg-4">
              <AppTextInput
                  name="document"
                  label="CPF"
                  mask="[###.###.###-##]"
                  placeholder="000.000.000-00"
                  required
                  :value="form.document"
                  @update:value="(value) => form.document = value"
              />
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
              <AppTextInput
                  name="phone"
                  label="Telefone"
                  mask="[(##) #####-####]"
                  placeholder="(44) 98888-8888"
                  required
                  :value="form.phone"
                  @update:value="(value) => form.phone = value"
              />
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
              <AppTextInput
                  name="secondary_phone"
                  label="Telefone Secundário"
                  mask="[(##) #####-####]"
                  placeholder="(44) 98888-8888"
                  :value="form.secondary_phone"
                  @update:value="(value) => form.secondary_phone = value"
              />
            </div>
          </div>
        </div>
      </section>

      <section class="app-form-card">
        <div class="app-form-card-header">
          <div class="app-form-card-title-row">
            <h2 class="app-form-card-title">
              Endereço
            </h2>
          </div>
        </div>

        <div class="app-form-card-body">
          <div class="row g-3">
            <div class="col-12 col-lg-4">
              <AppTextInput
                  name="zip_code"
                  label="CEP"
                  mask="[#####-###]"
                  placeholder="00000-000"
                  start-icon="fa-solid fa-location-dot"
                  required
                  :value="form.zip_code"
                  tip="Preenche rua, bairro, cidade e estado quando encontrado."
                  @update:value="updateZipCode"
              />
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
              <AppSelect2Input
                  name="state"
                  label="Estado"
                  placeholder="Selecione"
                  required
                  :disabled="isLoadingStates"
                  :options="stateOptions"
                  :value="form.state"
                  @update:value="updateState"
              />
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
              <AppSelect2Input
                  name="city"
                  label="Cidade"
                  :placeholder="form.state ? 'Selecione' : 'Selecione um estado'"
                  required
                  :disabled="!form.state || isLoadingCities"
                  :options="cityOptions"
                  :value="form.city"
                  @update:value="(value) => form.city = value"
              />
            </div>

            <div class="col-12 col-lg-5">
              <AppTextInput
                  name="district"
                  label="Bairro"
                  required
                  :value="form.district"
                  @update:value="(value) => form.district = value"
              />
            </div>

            <div class="col-12 col-sm-8 col-lg-5">
              <AppTextInput
                  name="street"
                  label="Rua"
                  required
                  :value="form.street"
                  @update:value="(value) => form.street = value"
              />
            </div>

            <div class="col-12 col-sm-4 col-lg-2">
              <AppTextInput
                  name="number"
                  label="Número"
                  mask="[############]"
                  :value="form.number"
                  @update:value="updateNumber"
              />
            </div>

            <div class="col-12">
              <AppTextInput
                  name="complement"
                  label="Complemento"
                  :value="form.complement"
                  @update:value="(value) => form.complement = value"
              />
            </div>
          </div>
        </div>
      </section>

      <section class="app-form-card">
        <div class="app-form-card-header">
          <h2 class="app-form-card-title">
            Extra
          </h2>
        </div>

        <div class="app-form-card-body">
          <div class="row g-3">
            <div class="col-12">
              <AppTextareaInput
                  name="notes"
                  label="Observações"
                  placeholder="Informações complementares sobre o cliente"
                  :rows="5"
                  :value="form.notes"
                  @update:value="(value) => form.notes = value"
              />
            </div>
          </div>
        </div>
      </section>
    </div>

    <template #footer>
      <NuxtLink to="/clientes" class="btn btn-secondary app-form-icon-button" aria-label="Voltar">
        <i class="fa-solid fa-angle-left" aria-hidden="true" />
      </NuxtLink>

      <button class="btn btn-primary app-form-action-button save-button" type="button">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true" />
        Salvar
      </button>
    </template>
  </AppFormContainer>
</template>
