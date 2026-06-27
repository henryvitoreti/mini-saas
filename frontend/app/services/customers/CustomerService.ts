import { apiHttpClient, type ApiResponse } from '@/services/api/http-client';
import type { Customer, CustomerListParams } from '@/types/entities/customer';
import type { TablePagination } from '@/types/ui/table';

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

function normalizeParams(params: Partial<CustomerListParams>): Record<string, string|number|boolean> {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      return value !== null && value !== undefined && value !== '';
    }),
  ) as Record<string, string|number|boolean>;
}

export const CustomerService = {
  async index(params: Partial<CustomerListParams>): Promise<TablePagination> {
    const response = await apiHttpClient.get<ApiResponse<CustomerListResponse>>('/customers', {
      query: normalizeParams(params),
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

  async delete(id: number|string): Promise<void> {
    await apiHttpClient.delete<ApiResponse<[]>>(`/customers/${id}`);
  },
};
