import { reactive } from 'vue';
import { useBaseForm } from '@/composables/forms/BaseFormComposable';
import { TenantService } from '@/services/TenantService';
import type { SelectOption } from '@/types/common/select';
import type { Tenant } from '@/types/entities/tenant';
import type { FormAttribute, FormInputMask, FormPayload } from '@/types/forms/form';

type TenantFormTextAttribute = FormAttribute<string|null>;
type TenantFormOptionValueAttribute = FormAttribute<string|number|null>;
type TenantFormSelectAttribute = TenantFormTextAttribute & {
  options: SelectOption[];
};

type TenantFormAttributes = {
  tenantId: TenantFormTextAttribute;
  companyRoleId: TenantFormOptionValueAttribute;
  companyName: TenantFormTextAttribute;
  companyDocument: TenantFormTextAttribute;
  companyEmail: TenantFormTextAttribute;
  companyPhone: TenantFormTextAttribute;
  companySecondaryPhone: TenantFormTextAttribute;
  zipCode: TenantFormTextAttribute;
  state: TenantFormSelectAttribute;
  city: TenantFormSelectAttribute;
  district: TenantFormTextAttribute;
  street: TenantFormTextAttribute;
  number: TenantFormTextAttribute;
  complement: TenantFormTextAttribute;
  logoPath: TenantFormTextAttribute;
  notes: TenantFormTextAttribute;
  userName: TenantFormTextAttribute;
  userEmail: TenantFormTextAttribute;
  userPassword: TenantFormTextAttribute;
} & Record<string, FormAttribute>;

const PHONE_INPUT_MASK: FormInputMask = ['[(##) ####-####]', '[(##) #####-####]'];

export function useTenantForm() {
  const attributes = reactive<TenantFormAttributes>({
    tenantId: {
      responseKey: 'id',
      payloadKey: 'id',
      value: null,
      label: 'Identificador do tenant',
      required: true,
      tip: 'Usado para gerar o domínio técnico do tenant.',
      errorMessage: null,
    },
    companyName: {
      responseKey: 'name',
      payloadKey: 'company.name',
      value: null,
      label: 'Nome da empresa',
      required: true,
      errorMessage: null,
    },
    companyRoleId: {
      responseKey: 'role_id',
      payloadKey: 'company.role_id',
      value: null,
      label: 'Perfil de acesso',
      placeholder: 'Selecione',
      required: true,
      errorMessage: null,
    },
    companyDocument: {
      responseKey: 'document',
      payloadKey: 'company.document',
      value: null,
      label: 'CNPJ',
      placeholder: '00.000.000/0000-00',
      required: true,
      errorMessage: null,
      mask: '[##.###.###/####-##]',
    },
    companyEmail: {
      responseKey: 'email',
      payloadKey: 'company.email',
      value: null,
      label: 'E-mail',
      required: true,
      errorMessage: null,
    },
    companyPhone: {
      responseKey: 'phone',
      payloadKey: 'company.phone',
      value: null,
      label: 'Telefone',
      placeholder: '(44) 98888-8888',
      required: true,
      errorMessage: null,
      mask: PHONE_INPUT_MASK,
    },
    companySecondaryPhone: {
      responseKey: 'secondary_phone',
      payloadKey: 'company.secondary_phone',
      value: null,
      label: 'Telefone secundário',
      placeholder: '(44) 98888-8888',
      errorMessage: null,
      mask: PHONE_INPUT_MASK,
    },
    zipCode: {
      responseKey: 'zip_code',
      payloadKey: 'company.zip_code',
      value: null,
      label: 'CEP',
      placeholder: '00000-000',
      errorMessage: null,
      mask: '[#####-###]',
      startIcon: 'fa-solid fa-location-dot',
      tip: 'Preenche rua, bairro, cidade e estado quando encontrado.',
    },
    state: {
      responseKey: 'state',
      payloadKey: 'company.state',
      value: null,
      label: 'Estado',
      placeholder: 'Selecione',
      errorMessage: null,
      options: [],
    },
    city: {
      responseKey: 'city',
      payloadKey: 'company.city',
      value: null,
      label: 'Cidade',
      placeholder: 'Selecione um estado',
      errorMessage: null,
      options: [],
    },
    district: {
      responseKey: 'district',
      payloadKey: 'company.district',
      value: null,
      label: 'Bairro',
      errorMessage: null,
    },
    street: {
      responseKey: 'street',
      payloadKey: 'company.street',
      value: null,
      label: 'Rua',
      errorMessage: null,
    },
    number: {
      responseKey: 'number',
      payloadKey: 'company.number',
      value: null,
      label: 'Número',
      errorMessage: null,
      mask: '[############]',
    },
    complement: {
      responseKey: 'complement',
      payloadKey: 'company.complement',
      value: null,
      label: 'Complemento',
      errorMessage: null,
    },
    logoPath: {
      responseKey: 'logo_path',
      payloadKey: 'company.logo_path',
      value: null,
      label: 'Logo',
      placeholder: 'Caminho do arquivo de logo',
      errorMessage: null,
    },
    notes: {
      responseKey: 'notes',
      payloadKey: 'company.notes',
      value: null,
      label: 'Observações',
      placeholder: 'Informações complementares sobre a empresa',
      errorMessage: null,
    },
    userName: {
      responseKey: 'user_name',
      payloadKey: 'user.name',
      value: null,
      label: 'Nome do usuário',
      required: true,
      errorMessage: null,
    },
    userEmail: {
      responseKey: 'user_email',
      payloadKey: 'user.email',
      value: null,
      label: 'E-mail do usuário',
      required: true,
      errorMessage: null,
    },
    userPassword: {
      responseKey: 'user_password',
      payloadKey: 'user.password',
      value: null,
      label: 'Senha',
      required: true,
      errorMessage: null,
    },
  });
  const baseForm = useBaseForm(attributes);

  function formatUpdatePayload(payload: FormPayload): FormPayload {
    delete payload.id;
    delete payload.user;

    if (
      typeof payload.company === 'object'
      && payload.company !== null
      && !Array.isArray(payload.company)
    ) {
      delete (payload.company as FormPayload).document;
    }

    return payload;
  }

  async function load(id: string): Promise<Tenant|null> {
    let response: Tenant|null = null;

    try {
      response = await TenantService.show(id);
      baseForm.fillAttributes(response);
    } catch (error) {
      baseForm.handleError(error, 'Ocorreu um erro inesperado ao buscar tenant.');
    }

    return response;
  }

  async function create(): Promise<Tenant|null> {
    let response: Tenant|null = null;

    try {
      const payload = baseForm.handleDoSendAttributes();

      response = await TenantService.create(payload);
    } catch (error) {
      baseForm.handleError(error, 'Ocorreu um erro inesperado ao criar tenant.');
    }

    return response;
  }

  async function update(id: string): Promise<Tenant|null> {
    let response: Tenant|null = null;

    try {
      const payload = baseForm.handleDoSendAttributes(formatUpdatePayload);

      response = await TenantService.update(id, payload);
    } catch (error) {
      baseForm.handleError(error, 'Ocorreu um erro inesperado ao atualizar tenant.');
    }

    return response;
  }

  return {
    ...baseForm,
    attributes,
    load,
    create,
    update,
  };
}
