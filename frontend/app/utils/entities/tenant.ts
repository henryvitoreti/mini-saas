import type { SelectOption } from "@/types/common/select";
import { getOptionLabel } from "@/utils/label-handler";
import { normalizeBooleanValue } from "@/utils/normalizer";

export const tenantStatusOptions: SelectOption[] = [
    {
        label: 'Ativo',
        value: 'true',
        disabled: null,
    },
    {
        label: 'Desativado',
        value: 'false',
        disabled: null,
    },
];

export function getTenantStatusLabel(value: unknown): string {
    const status = normalizeBooleanValue(value);
    return status === null ? '-' : getOptionLabel(tenantStatusOptions, String(status));
}

export function getTenantStatusBadgeClass(value: unknown): string {
    return normalizeBooleanValue(value) ? 'is-success' : 'is-muted';
}