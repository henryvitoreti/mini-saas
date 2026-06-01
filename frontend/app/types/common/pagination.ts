export type PaginatedResponse<T> = T[] | {
  data?: T[];
  items?: T[];
  results?: T[];
  current_page?: number;
  last_page?: number;
  meta?: {
    current_page?: number;
    last_page?: number;
  };
};
