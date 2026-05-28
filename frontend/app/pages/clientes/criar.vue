<script setup lang="ts">
import AppFormContainer from "@/components/form/AppFormContainer.vue";
import AppSelect2Input from "@/components/form/AppSelect2Input.vue";
import AppSwitchInput from "@/components/form/AppSwitchInput.vue";
import AppTextInput from "@/components/form/AppTextInput.vue";
import AppTextareaInput from "@/components/form/AppTextareaInput.vue";
import AppInfoTooltip from "@/components/ui/AppInfoTooltip.vue";

type SelectOption = {
  label: string;
  value: string;
};

type IbgeState = {
  id: number;
  sigla: string;
  nome: string;
};

type IbgeCity = {
  id: number;
  nome: string;
};

type ViaCepAddress = {
  erro?: boolean;
  cep?: string;
  logradouro?: string;
  complemento?: string;
  bairro?: string;
  localidade?: string;
  uf?: string;
};

const form = reactive({
  name: '',
  document: '',
  email: '',
  phone: '',
  secondary_phone: '',
  zip_code: '',
  street: '',
  number: '',
  complement: '',
  district: '',
  city: '',
  state: '',
  notes: '',
  is_active: true,
});

const stateOptions = ref<SelectOption[]>([]);
const cityOptions = ref<SelectOption[]>([]);
const isLoadingStates = ref(false);
const isLoadingCities = ref(false);
const isLoadingZipCode = ref(false);
let zipCodeSearchTimeout: ReturnType<typeof setTimeout> | undefined;

const fetchStates = async (): Promise<void> => {
  isLoadingStates.value = true;

  try {
    const states = await $fetch<IbgeState[]>('https://servicodados.ibge.gov.br/api/v1/localidades/estados', {
      query: {
        orderBy: 'nome',
      },
    });

    stateOptions.value = states.map((state) => ({
      label: `${state.sigla} - ${state.nome}`,
      value: state.sigla,
    }));
  } finally {
    isLoadingStates.value = false;
  }
};

const fetchCities = async (state: string): Promise<void> => {
  if (!state) {
    cityOptions.value = [];
    return;
  }

  isLoadingCities.value = true;

  try {
    const cities = await $fetch<IbgeCity[]>(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${state}/municipios`, {
      query: {
        orderBy: 'nome',
      },
    });

    cityOptions.value = cities.map((city) => ({
      label: city.nome,
      value: city.nome,
    }));
  } finally {
    isLoadingCities.value = false;
  }
};

const updateState = async (state: string): Promise<void> => {
  form.state = state;
  form.city = '';
  await fetchCities(state);
};

const searchZipCode = async (zipCode: string): Promise<void> => {
  if (zipCode.length !== 8) {
    return;
  }

  isLoadingZipCode.value = true;

  try {
    const address = await $fetch<ViaCepAddress>(`https://viacep.com.br/ws/${zipCode}/json/`);

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
  } finally {
    isLoadingZipCode.value = false;
  }
};

watch(
  () => form.zip_code,
  (zipCode): void => {
    if (zipCodeSearchTimeout) {
      clearTimeout(zipCodeSearchTimeout);
    }

    zipCodeSearchTimeout = setTimeout(() => {
      void searchZipCode(zipCode);
    }, 350);
  },
);

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
          <div class="app-form-grid">
            <AppTextInput
                class="app-form-col-5"
                name="name"
                label="Nome"
                start-icon="fa-solid fa-user"
                required
                :value="form.name"
                @update:value="(value) => form.name = value"
            />

            <AppTextInput
                class="app-form-col-5"
                name="email"
                label="E-mail"
                type="email"
                start-icon="fa-solid fa-envelope"
                required
                :value="form.email"
                @update:value="(value) => form.email = value"
            />

            <AppSwitchInput
                class="app-form-col-2"
                name="is_active"
                label="Ativo"
                required
                :value="form.is_active"
                @update:value="(value) => form.is_active = value"
            />

            <AppTextInput
                class="app-form-col-4"
                name="document"
                label="CPF"
                mask="[###.###.###-##]"
                placeholder="000.000.000-00"
                required
                :value="form.document"
                @update:value="(value) => form.document = value"
            />

            <AppTextInput
                class="app-form-col-4"
                name="phone"
                label="Telefone"
                mask="[(##) #####-####]"
                placeholder="(44) 98888-8888"
                start-icon="fa-solid fa-phone"
                required
                :value="form.phone"
                @update:value="(value) => form.phone = value"
            />

            <AppTextInput
                class="app-form-col-4"
                name="secondary_phone"
                label="Telefone Secundário"
                mask="[(##) #####-####]"
                placeholder="(44) 98888-8888"
                start-icon="fa-solid fa-phone"
                :value="form.secondary_phone"
                @update:value="(value) => form.secondary_phone = value"
            />
          </div>
        </div>
      </section>

      <section class="app-form-card">
        <div class="app-form-card-header">
          <div class="app-form-card-title-row">
            <h2 class="app-form-card-title">
              Endereço
            </h2>

            <AppInfoTooltip text="O CEP preenche rua, bairro, cidade e estado quando encontrado." />
          </div>
        </div>

        <div class="app-form-card-body">
          <div class="app-form-grid">
            <AppTextInput
                class="app-form-col-4"
                name="zip_code"
                label="CEP"
                mask="[#####-###]"
                placeholder="00000-000"
                start-icon="fa-solid fa-location-dot"
                :tip="isLoadingZipCode ? 'Buscando CEP...' : undefined"
                required
                :value="form.zip_code"
                @update:value="(value) => form.zip_code = value"
            />

            <AppSelect2Input
                class="app-form-col-4"
                name="state"
                label="Estado"
                placeholder="Selecione"
                required
                :disabled="isLoadingStates"
                :options="stateOptions"
                :value="form.state"
                @update:value="updateState"
            />

            <AppSelect2Input
                class="app-form-col-4"
                name="city"
                label="Cidade"
                :placeholder="form.state ? 'Selecione' : 'Selecione um estado'"
                required
                :disabled="!form.state || isLoadingCities"
                :options="cityOptions"
                :value="form.city"
                @update:value="(value) => form.city = value"
            />

            <AppTextInput
                class="app-form-col-5"
                name="district"
                label="Bairro"
                required
                :value="form.district"
                @update:value="(value) => form.district = value"
            />

            <AppTextInput
                class="app-form-col-5"
                name="street"
                label="Rua"
                required
                :value="form.street"
                @update:value="(value) => form.street = value"
            />

            <AppTextInput
                class="app-form-col-2"
                name="number"
                label="Número"
                required
                :value="form.number"
                @update:value="(value) => form.number = value"
            />

            <AppTextInput
                class="app-form-col-12"
                name="complement"
                label="Complemento"
                :value="form.complement"
                @update:value="(value) => form.complement = value"
            />
          </div>
        </div>
      </section>

      <section class="app-form-card">
        <div class="app-form-card-header">
          <h2 class="app-form-card-title">
            Observações
          </h2>
        </div>

        <div class="app-form-card-body">
          <AppTextareaInput
              name="notes"
              label="Observações"
              placeholder="Informações complementares sobre o cliente"
              :rows="5"
              :value="form.notes"
              @update:value="(value) => form.notes = value"
          />
        </div>
      </section>
    </div>

    <template #footer>
      <NuxtLink to="/clientes" class="btn btn-secondary app-form-icon-button me-auto" aria-label="Voltar">
        <i class="fa-solid fa-angle-left" aria-hidden="true" />
      </NuxtLink>

      <button class="btn btn-primary app-form-action-button" type="button">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true" />
        Salvar
      </button>
    </template>
  </AppFormContainer>
</template>

<style scoped>
.app-form-icon-button,
.app-form-action-button {
  min-height: 2.375rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.app-form-icon-button {
  width: 2.375rem;
  height: 2.375rem;
  padding: 0;
}

.app-form-action-button {
  gap: 0.5rem;
}
</style>
