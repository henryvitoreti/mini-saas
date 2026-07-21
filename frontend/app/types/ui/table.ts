import type { VNodeChild } from 'vue';

export type TableRow = Record<string, unknown> & {
  id: number|string;
};

export type TableSortOrder = 'ASC'|'DESC';

export type TableFilterValue = string|number|boolean|null;

export type TableFilters = Record<string, TableFilterValue>;

export type TableDateRangeValue = [string|null, string|null];

export type TableFilterInputValue = string|number|null;

export type TableFilterStateValue = TableFilterValue|TableDateRangeValue;

export type TableColumnValueFormatter = (value: unknown, row: TableRow) => string;

export type TableColumnBadgeClassResolver = (value: unknown, row: TableRow) => string|null;

export type TableColumn = {
  label: string;
  name: string;
  field: string;
  priority: number|null;
  sortable: boolean|null;
  wrap: boolean|null;
  render: ((row: TableRow, value: unknown) => VNodeChild)|null;
  valueFormatter: TableColumnValueFormatter|null;
  badgeClassResolver: TableColumnBadgeClassResolver|null;
  showInDeleteModal: boolean|null;
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

export type TableFilterState = Record<string, TableFilterStateValue>;

export type TableFilterCountGroup = string[];

export type AppDataTableFilterSlotProps = {
  filterValues: TableFilterState;
  getFilterValue: (name: string) => TableFilterStateValue;
  getSelectFilterValue: (name: string) => TableFilterInputValue;
  getDateRangeFilterValue: (startName: string, endName: string) => TableDateRangeValue;
  updateFilterValue: (name: string, value: TableFilterStateValue) => void;
  updateDateRangeFilterValue: (startName: string, endName: string, value: string|TableDateRangeValue) => void;
};

export type AppDataTableProps = {
  title: string|null;
  subtitle: string|null;
  columns: TableColumn[];
  baseUrl: string;
  baseApiUrl: string;
  entityLabel: string|null;
  entityPluralLabel: string|null;
  hasShow: boolean|null;
  hasEdit: boolean|null;
  hasDelete: boolean|null;
  hasRestore: boolean|null;
  hasCreate: boolean|null;
  hasSearch: boolean|null;
  hasExport: boolean|null;
  hasFilters: boolean|null;
  searchPlaceholder: string|null;
  defaultFilters: TableFilters|null;
  filterCountGroups: TableFilterCountGroup[]|null;
  storageKey: string|null;
  defaultSortBy: string|null;
  defaultOrder: TableSortOrder|null;
  hasLimitSelector: boolean|null;
  defaultLimit: number|null;
  limitOptions: number[]|null;
  withDetails: boolean|null;
};
