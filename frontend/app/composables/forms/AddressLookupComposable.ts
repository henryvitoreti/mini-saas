import debounce from 'lodash/debounce';
import { onBeforeUnmount, ref } from 'vue';
import { fetchIbgeCitiesByState, fetchIbgeStates, fetchViaCepAddress } from '@/services/external/address-lookup';
import type { SelectOption } from '@/types/common/select';
import type { FormAttribute } from '@/types/forms/form';

type AddressLookupTextAttribute = FormAttribute<string|null>;
type AddressLookupSelectAttribute = AddressLookupTextAttribute & {
  options: SelectOption[];
};

type AddressLookupAttributes = {
  zipCode: AddressLookupTextAttribute;
  state: AddressLookupSelectAttribute;
  city: AddressLookupSelectAttribute;
  street: AddressLookupTextAttribute;
  complement: AddressLookupTextAttribute;
  district: AddressLookupTextAttribute;
};

export function useAddressLookup(attributes: AddressLookupAttributes) {
  const isLoadingStates = ref(false);
  const isLoadingCities = ref(false);

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

  function updateZipCode(zipCode: string): void {
    attributes.zipCode.value = zipCode || null;
    const normalizedZipCode = attributes.zipCode.value ?? '';

    if (normalizedZipCode.length !== 8) {
      return;
    }

    searchZipCode(normalizedZipCode);
  }

  onBeforeUnmount((): void => {
    searchZipCode.cancel();
  });

  return {
    isLoadingStates,
    isLoadingCities,
    fetchStates,
    fetchCities,
    updateState,
    updateZipCode,
  };
}
