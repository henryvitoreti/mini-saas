import type { SelectOption } from "@/types/common/select";

export function getOptionLabel(options: SelectOption[], value: unknown): string {
    const normalizedValue = String(value ?? '');
    const option = options.find((item: SelectOption): boolean => {
        return String(item.value) === normalizedValue;
    });

    return option?.label ?? '-';
}