import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Customer, CustomerListParams } from '@/types/entities/customer';
import type { FormPayload } from '@/types/forms/form';
import type { TablePagination } from '@/types/ui/table';
import { normalizeQueryParams } from '@/utils/normalizer';

type CustomerListResponse = {
  items: Customer[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number|null;
  to: number|null;
  prev_page_url: string|null;
  next_page_url: string|null;
};

export const CustomerService = {
  async index(params: Partial<CustomerListParams>): Promise<TablePagination> {
    const response = await apiHttpClient.get<ApiResponse<CustomerListResponse>>('/customers', {
      query: normalizeQueryParams(params),
      showGlobalLoading: false,
    });

    return {
      data: response.data.items,
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
      prev_page_url: response.data.prev_page_url,
      next_page_url: response.data.next_page_url,
    };
  },

  async show(id: number): Promise<Customer> {
    const response = await apiHttpClient.get<ApiResponse<Customer>>(`/customers/${id}`);
    return response.data;
  },

  async create(payload: FormPayload): Promise<Customer> {
    const response = await apiHttpClient.post<ApiResponse<Customer>>('/customers', payload);
    return response.data;
  },

  async update(id: number, payload: FormPayload): Promise<Customer> {
    const response = await apiHttpClient.put<ApiResponse<Customer>>(`/customers/${id}`, payload);
    return response.data;
  },

  async delete(id: number): Promise<void> {
    await apiHttpClient.delete<ApiResponse<[]>>(`/customers/${id}`);
  },
};
