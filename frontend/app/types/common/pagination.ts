export type PaginatedResponse<T> = T[]|{
  data: T[]|null;
  items: T[]|null;
  results: T[]|null;
  current_page: number|null;
  last_page: number|null;
  meta: {
    current_page: number|null;
    last_page: number|null;
  }|null;
};
