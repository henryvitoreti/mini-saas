export type TableRow = Record<string, unknown>;

export type TableColumn = {
  label: string;
  name: string;
  field: string;
  wrap?: boolean;
  render?: (row: TableRow, value: unknown) => string|number|boolean|null;
};

export type TablePagination = {
  data: TableRow[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number|null;
  to: number|null;
  prev_page_url: string|null;
  next_page_url: string|null;
};
