import { usePage } from '@inertiajs/react';

/**
 * Formats a price that is stored in the base currency (XOF) into whatever the
 * visitor has selected, using the live rate served by the backend.
 */
export function useMoney() {
    const { currency = 'XOF', currencies = {}, locale = 'en' } = usePage().props;

    const meta = currencies?.[currency] ?? { code: currency, rate: 1, decimals: 0, symbol: currency };
    const rate = Number(meta.rate ?? 1) || 1;
    const decimals = Number(meta.decimals ?? 0);

    const format = (amountInBase) => {
        const value = Number(amountInBase ?? 0) * rate;

        try {
            return new Intl.NumberFormat(locale === 'fr' ? 'fr-FR' : 'en-GB', {
                style: 'currency',
                currency,
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(value);
        } catch {
            return `${value.toLocaleString()} ${currency}`;
        }
    };

    return { format, currency, meta, rate };
}
