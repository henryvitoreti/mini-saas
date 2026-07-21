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