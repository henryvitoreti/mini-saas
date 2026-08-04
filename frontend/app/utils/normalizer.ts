export function normalizeBooleanValue(value: unknown): boolean|null {
    const trueValues: unknown[] = [true, 'true', 1, '1'];
    const falseValues: unknown[] = [false, 'false', 0, '0'];

    if (trueValues.includes(value)) {
        return true;
    }

    if (falseValues.includes(value)) {
        return false;
    }

    return null;
}

type QueryParamInput = string|number|boolean|null|undefined;

export function normalizeQueryParams(params: Record<string, QueryParamInput>): Record<string, string|number|boolean> {
    return Object.fromEntries(Object.entries(params).filter(([, value]): boolean => {
        return value !== null && value !== undefined && value !== '';
    })) as Record<string, string|number|boolean>;
}
