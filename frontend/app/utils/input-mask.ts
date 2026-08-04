import type { FormInputMask } from '@/types/forms/form';

export const CPF_INPUT_MASK = '[###.###.###-##]';
export const CNPJ_INPUT_MASK = '[AA.AAA.AAA/AAAA-##]';
export const PHONE_INPUT_MASK: FormInputMask = ['[(##) ####-####]', '[(##) #####-####]'];
export const ZIP_CODE_INPUT_MASK = '[#####-###]';
export const ADDRESS_NUMBER_INPUT_MASK = '[############]';

export const CNPJ_INPUT_PLACEHOLDER = 'AA.AAA.AAA/AAAA-00';
