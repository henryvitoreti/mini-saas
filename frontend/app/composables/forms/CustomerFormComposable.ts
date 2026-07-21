import { reactive } from 'vue';
import { useBaseForm } from '@/composables/forms/BaseFormComposable';
import { customerTypeOptions } from '@/utils/entities/customer';
import { CustomerService } from '@/services/customers/CustomerService';
import {
  CUSTOMER_TYPE_COMPANY,
  CUSTOMER_TYPE_INDIVIDUAL,
  type Customer,
  type CustomerType,
} from '@/types/entities/customer';
import type { SelectOption } from '@/types/common/select';
import type { FormAttribute, FormInputMask } from '@/types/forms/form';

type CustomerFormTextAttribute = FormAttribute<string|null>;
type CustomerFormBooleanAttribute = FormAttribute<boolean|null>;
type CustomerFormTypeAttribute = FormAttribute<CustomerType|null> & {
  clearable: boolean;
  options: SelectOption[];
};
type CustomerFormSelectAttribute = CustomerFormTextAttribute & {
  options: SelectOption[];
};
type CustomerTypeFormConfig = {
  birthDateLabel: string;
  documentLabel: string;
  documentMask: FormInputMask;
  documentPlaceholder: string;
};

type CustomerFormAttributes = {
  name: CustomerFormTextAttribute;
  email: CustomerFormTextAttribute;
  isActive: CustomerFormBooleanAttribute;
  type: CustomerFormTypeAttribute;
  document: CustomerFormTextAttribute;
  birthDate: CustomerFormTextAttribute;
  phone: CustomerFormTextAttribute;
  secondaryPhone: CustomerFormTextAttribute;
  zipCode: CustomerFormTextAttribute;
  state: CustomerFormSelectAttribute;
  city: CustomerFormSelectAttribute;
  district: CustomerFormTextAttribute;
  street: CustomerFormTextAttribute;
  number: CustomerFormTextAttribute;
  complement: CustomerFormTextAttribute;
  notes: CustomerFormTextAttribute;
} & Record<string, FormAttribute>;

const PHONE_INPUT_MASK: FormInputMask = ['[(##) ####-####]', '[(##) #####-####]'];

const customerTypeFormConfigs: Record<CustomerType, CustomerTypeFormConfig> = {
  [CUSTOMER_TYPE_INDIVIDUAL]: {
    birthDateLabel: 'Data de nascimento',
    documentLabel: 'CPF',
    documentMask: '[###.###.###-##]',
    documentPlaceholder: '000.000.000-00',
  },
  [CUSTOMER_TYPE_COMPANY]: {
    birthDateLabel: 'Data de abertura',
    documentLabel: 'CNPJ',
    documentMask: '[##.###.###/####-##]',
    documentPlaceholder: '00.000.000/0000-00',
  },
};

function getCustomerType(value: string): CustomerType {
  return value === CUSTOMER_TYPE_COMPANY ? CUSTOMER_TYPE_COMPANY : CUSTOMER_TYPE_INDIVIDUAL;
}

function getCustomerTypeFormConfig(customerType: CustomerType|null): CustomerTypeFormConfig {
  if (customerType === CUSTOMER_TYPE_COMPANY) {
    return customerTypeFormConfigs[CUSTOMER_TYPE_COMPANY];
  }

  return customerTypeFormConfigs[CUSTOMER_TYPE_INDIVIDUAL];
}

export function useCustomerForm() {
  const defaultTypeConfig = customerTypeFormConfigs[CUSTOMER_TYPE_INDIVIDUAL];
  const attributes = reactive<CustomerFormAttributes>({
    name: {
      responseKey: 'name',
      payloadKey: 'name',
      value: null,
      label: 'Nome',
      required: true,
      errorMessage: null,
    },
    email: {
      responseKey: 'email',
      payloadKey: 'email',
      value: null,
      label: 'E-mail',
      required: true,
      errorMessage: null,
    },
    isActive: {
      responseKey: 'is_active',
      payloadKey: 'is_active',
      value: true,
      label: 'Ativo',
      required: true,
      errorMessage: null,
    },
    type: {
      responseKey: 'type',
      payloadKey: 'type',
      value: CUSTOMER_TYPE_INDIVIDUAL,
      label: 'Tipo de pessoa',
      required: true,
      errorMessage: null,
      clearable: false,
      options: customerTypeOptions,
    },
    document: {
      responseKey: 'document_raw',
      payloadKey: 'document',
      value: null,
      label: defaultTypeConfig.documentLabel,
      placeholder: defaultTypeConfig.documentPlaceholder,
      required: true,
      errorMessage: null,
      mask: defaultTypeConfig.documentMask,
    },
    birthDate: {
      responseKey: 'birth_date_raw',
      payloadKey: 'birth_date',
      value: null,
      label: defaultTypeConfig.birthDateLabel,
      placeholder: 'dd/mm/aaaa',
      errorMessage: null,
    },
    phone: {
      responseKey: 'phone_raw',
      payloadKey: 'phone',
      value: null,
      label: 'Telefone',
      placeholder: '(44) 98888-8888',
      required: true,
      errorMessage: null,
      mask: PHONE_INPUT_MASK,
    },
    secondaryPhone: {
      responseKey: 'secondary_phone_raw',
      payloadKey: 'secondary_phone',
      value: null,
      label: 'Telefone Secundário',
      placeholder: '(44) 98888-8888',
      errorMessage: null,
      mask: PHONE_INPUT_MASK,
    },
    zipCode: {
      responseKey: 'zip_code_raw',
      payloadKey: 'zip_code',
      value: null,
      label: 'CEP',
      placeholder: '00000-000',
      required: true,
      errorMessage: null,
      mask: '[#####-###]',
      startIcon: 'fa-solid fa-location-dot',
      tip: 'Preenche rua, bairro, cidade e estado quando encontrado.',
    },
    state: {
      responseKey: 'state',
      payloadKey: 'state',
      value: null,
      label: 'Estado',
      placeholder: 'Selecione',
      required: true,
      errorMessage: null,
      options: [],
    },
    city: {
      responseKey: 'city',
      payloadKey: 'city',
      value: null,
      label: 'Cidade',
      placeholder: 'Selecione um estado',
      required: true,
      errorMessage: null,
      options: [],
    },
    district: {
      responseKey: 'district',
      payloadKey: 'district',
      value: null,
      label: 'Bairro',
      required: true,
      errorMessage: null,
    },
    street: {
      responseKey: 'street',
      payloadKey: 'street',
      value: null,
      label: 'Rua',
      required: true,
      errorMessage: null,
    },
    number: {
      responseKey: 'number',
      payloadKey: 'number',
      value: null,
      label: 'Número',
      errorMessage: null,
      mask: '[############]',
    },
    complement: {
      responseKey: 'complement',
      payloadKey: 'complement',
      value: null,
      label: 'Complemento',
      errorMessage: null,
    },
    notes: {
      responseKey: 'notes',
      payloadKey: 'notes',
      value: null,
      label: 'Observações',
      placeholder: 'Informações complementares sobre o cliente',
      errorMessage: null,
    },
  });
  const baseForm = useBaseForm(attributes);

  function syncCustomerTypeAttributes(): void {
    const typeConfig = getCustomerTypeFormConfig(attributes.type.value);

    attributes.document.label = typeConfig.documentLabel;
    attributes.document.mask = typeConfig.documentMask;
    attributes.document.placeholder = typeConfig.documentPlaceholder;
    attributes.birthDate.label = typeConfig.birthDateLabel;
  }

  function updateCustomerType(value: string): void {
    attributes.type.value = getCustomerType(value);
    attributes.document.value = null;
    syncCustomerTypeAttributes();
  }

  async function load(id: number): Promise<Customer|null> {
    let response: Customer|null = null;

    try {
      response = await CustomerService.show(id);
      baseForm.fillAttributes(response);
      syncCustomerTypeAttributes();
    } catch (error) {
      baseForm.handleError(error, 'Ocorreu um erro inesperado ao buscar cliente.');
    }

    return response;
  }

  async function create(): Promise<Customer|null> {
    let response: Customer|null = null;

    try {
      const payload = baseForm.handleDoSendAttributes();

      response = await CustomerService.create(payload);
    } catch (error) {
      baseForm.handleError(error, 'Ocorreu um erro inesperado ao criar cliente.');
    }

    return response;
  }

  async function update(id: number): Promise<Customer|null> {
    let response: Customer|null = null;

    try {
      const payload = baseForm.handleDoSendAttributes();

      response = await CustomerService.update(id, payload);
    } catch (error) {
      baseForm.handleError(error, 'Ocorreu um erro inesperado ao atualizar cliente.');
    }

    return response;
  }

  return {
    ...baseForm,
    attributes,
    updateCustomerType,
    load,
    create,
    update,
  };
}
