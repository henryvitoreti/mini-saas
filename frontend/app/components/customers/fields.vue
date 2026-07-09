<script setup lang="ts">
import debounce from 'lodash/debounce';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppDateInput from '@/components/form/AppDateInput.vue';
import AppSelectInput from '@/components/form/AppSelectInput.vue';
import AppSelect2Input from '@/components/form/AppSelect2Input.vue';
import AppSwitchInput from '@/components/form/AppSwitchInput.vue';
import AppTextInput from '@/components/form/AppTextInput.vue';
import AppTextareaInput from '@/components/form/AppTextareaInput.vue';
import AppInfoTooltip from '@/components/ui/AppInfoTooltip.vue';
import { useCustomerForm } from '@/composables/forms/CustomerFormComposable';
import { fetchIbgeCitiesByState, fetchIbgeStates, fetchViaCepAddress } from '@/services/external/address-lookup';
import type { FieldsProps } from '@/types/forms/form';

const props = withDefaults(
  defineProps<FieldsProps>(),
  {
    id: null,
    isModal: false,
  },
);

const router = useRouter();
const appToast = useAppToast();
const customerForm = useCustomerForm();
const { attributes } = customerForm;
const isLoadingStates = ref(false);
const isLoadingCities = ref(false);

const isEditing = computed<boolean>(() => {
  return props.id !== null;
});

async function fetchStates(): Promise<void> {
  isLoadingStates.value = true;

  try {
    const states = await fetchIbgeStates();

    attributes.state.options = states.map((state) => ({
      label: `${state.sigla} - ${state.nome}`,
      value: state.sigla,
      disabled: null,
    }));
  } finally {
    isLoadingStates.value = false;
  }
}

async function fetchCities(state: string|null): Promise<void> {
  if (!state) {
    attributes.city.options = [];
    return;
  }

  isLoadingCities.value = true;

  try {
    const cities = await fetchIbgeCitiesByState(state);

    attributes.city.options = cities.map((city) => ({
      label: city.nome,
      value: city.nome,
      disabled: null,
    }));
  } finally {
    isLoadingCities.value = false;
  }
}

async function updateState(state: string): Promise<void> {
  attributes.state.value = state || null;
  attributes.city.value = null;
  await fetchCities(state);
}

function updateCustomerType(value: string): void {
  if (isEditing.value) {
    return;
  }

  customerForm.updateCustomerType(value);
}

function updateZipCode(zipCode: string): void {
  attributes.zipCode.value = zipCode || null;
  const normalizedZipCode = attributes.zipCode.value ?? '';

  if (normalizedZipCode.length !== 8) {
    return;
  }

  searchZipCode(normalizedZipCode);
}

const searchZipCode = debounce(async (zipCode: string): Promise<void> => {
  try {
    const address = await fetchViaCepAddress(zipCode);

    if (address.erro) {
      return;
    }

    attributes.street.value = address.logradouro ?? attributes.street.value;
    attributes.complement.value = address.complemento ?? attributes.complement.value;
    attributes.district.value = address.bairro ?? attributes.district.value;

    if (address.uf) {
      attributes.state.value = address.uf;
      await fetchCities(address.uf);
    }

    if (address.localidade) {
      attributes.city.value = address.localidade;
    }
  } catch (error) {
    console.error(error);
  }
}, 1500);

async function loadCustomer(): Promise<void> {
  if (props.id === null) {
    return;
  }

  const customer = await customerForm.load(props.id);

  if (customer === null) {
    await router.push('/clientes');
    return;
  }

  const state = attributes.state.value;

  if (state !== null) {
    await fetchCities(state);
  }
}

async function save(): Promise<void> {
  const customer = props.id === null
    ? await customerForm.create()
    : await customerForm.update(props.id);

  if (customer === null) {
    return;
  }

  appToast.success(isEditing.value ? 'Cliente atualizado com sucesso.' : 'Cliente cadastrado com sucesso.');
  await router.push('/clientes');
}

onMounted(async (): Promise<void> => {
  await fetchStates();
  await loadCustomer();
});

onBeforeUnmount((): void => {
  searchZipCode.cancel();
});
</script>

<template>
  <form id="customer-form" class="app-form-stack" @submit.prevent="save">
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
                :label="attributes.name.label"
                :required="attributes.name.required"
                v-model:value="attributes.name.value"
                :error-message="attributes.name.errorMessage"
            />
          </div>

          <div class="col-8 col-sm-9 col-md-5">
            <AppTextInput
                name="email"
                :label="attributes.email.label"
                type="email"
                :required="attributes.email.required"
                v-model:value="attributes.email.value"
                :error-message="attributes.email.errorMessage"
            />
          </div>

          <div class="col-4 col-sm-3 col-md-2">
            <AppSwitchInput
                name="is_active"
                :label="attributes.isActive.label"
                :required="attributes.isActive.required"
                v-model:value="attributes.isActive.value"
                :error-message="attributes.isActive.errorMessage"
            />
          </div>

          <div class="col-12 col-lg-4">
            <AppSelectInput
                name="type"
                :label="attributes.type.label"
                :required="attributes.type.required"
                :clearable="attributes.type.clearable"
                :disabled="isEditing"
                :options="attributes.type.options"
                :value="attributes.type.value"
                :error-message="attributes.type.errorMessage"
                @update:value="updateCustomerType"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppTextInput
                name="document"
                :label="attributes.document.label"
                :mask="attributes.document.mask"
                :placeholder="attributes.document.placeholder"
                :disabled="isEditing"
                :required="attributes.document.required"
                v-model:value="attributes.document.value"
                :error-message="attributes.document.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppDateInput
                name="birth_date"
                :label="attributes.birthDate.label"
                :placeholder="attributes.birthDate.placeholder"
                v-model:value="attributes.birthDate.value"
                :error-message="attributes.birthDate.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6">
            <AppTextInput
                name="phone"
                :label="attributes.phone.label"
                :mask="attributes.phone.mask"
                :placeholder="attributes.phone.placeholder"
                :required="attributes.phone.required"
                v-model:value="attributes.phone.value"
                :error-message="attributes.phone.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-6">
            <AppTextInput
                name="secondary_phone"
                :label="attributes.secondaryPhone.label"
                :mask="attributes.secondaryPhone.mask"
                :placeholder="attributes.secondaryPhone.placeholder"
                v-model:value="attributes.secondaryPhone.value"
                :error-message="attributes.secondaryPhone.errorMessage"
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
                :label="attributes.zipCode.label"
                :mask="attributes.zipCode.mask"
                :placeholder="attributes.zipCode.placeholder"
                :start-icon="attributes.zipCode.startIcon"
                :required="attributes.zipCode.required"
                :value="attributes.zipCode.value"
                :tip="attributes.zipCode.tip"
                :error-message="attributes.zipCode.errorMessage"
                @update:value="updateZipCode"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppSelect2Input
                name="state"
                :label="attributes.state.label"
                :placeholder="attributes.state.placeholder"
                :required="attributes.state.required"
                :disabled="isLoadingStates"
                :options="attributes.state.options"
                :value="attributes.state.value"
                :error-message="attributes.state.errorMessage"
                @update:value="updateState"
            />
          </div>

          <div class="col-12 col-sm-6 col-lg-4">
            <AppSelect2Input
                name="city"
                :label="attributes.city.label"
                :placeholder="attributes.state.value ? 'Selecione' : attributes.city.placeholder"
                :required="attributes.city.required"
                :disabled="!attributes.state.value || isLoadingCities"
                :options="attributes.city.options"
                v-model:value="attributes.city.value"
                :error-message="attributes.city.errorMessage"
            />
          </div>

          <div class="col-12 col-lg-5">
            <AppTextInput
                name="district"
                :label="attributes.district.label"
                :required="attributes.district.required"
                v-model:value="attributes.district.value"
                :error-message="attributes.district.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-8 col-lg-5">
            <AppTextInput
                name="street"
                :label="attributes.street.label"
                :required="attributes.street.required"
                v-model:value="attributes.street.value"
                :error-message="attributes.street.errorMessage"
            />
          </div>

          <div class="col-12 col-sm-4 col-lg-2">
            <AppTextInput
                name="number"
                :label="attributes.number.label"
                :mask="attributes.number.mask"
                v-model:value="attributes.number.value"
                :error-message="attributes.number.errorMessage"
            />
          </div>

          <div class="col-12">
            <AppTextInput
                name="complement"
                :label="attributes.complement.label"
                v-model:value="attributes.complement.value"
                :error-message="attributes.complement.errorMessage"
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
                :label="attributes.notes.label"
                :placeholder="attributes.notes.placeholder"
                :rows="5"
                v-model:value="attributes.notes.value"
                :error-message="attributes.notes.errorMessage"
            />
          </div>
        </div>
      </div>
    </section>
  </form>
</template>
