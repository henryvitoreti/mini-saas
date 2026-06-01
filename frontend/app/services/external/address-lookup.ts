import type { IbgeCity, IbgeState } from '@/types/external/ibge';
import type { ViaCepAddress } from '@/types/external/via-cep';

const IBGE_BASE_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades';
const VIA_CEP_BASE_URL = 'https://viacep.com.br/ws';

export async function fetchIbgeStates(): Promise<IbgeState[]> {
  return await $fetch<IbgeState[]>(`${IBGE_BASE_URL}/estados`, {
    query: {
      orderBy: 'nome',
    },
  });
}

export async function fetchIbgeCitiesByState(state: string): Promise<IbgeCity[]> {
  return await $fetch<IbgeCity[]>(`${IBGE_BASE_URL}/estados/${state}/municipios`, {
    query: {
      orderBy: 'nome',
    },
  });
}

export async function fetchViaCepAddress(zipCode: string): Promise<ViaCepAddress> {
  return await $fetch<ViaCepAddress>(`${VIA_CEP_BASE_URL}/${zipCode}/json/`);
}
