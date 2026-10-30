import { router, usePage } from '@inertiajs/react';
import { cn } from '../lib/utils';
import { ChevronDownIcon } from './Icons';
import { useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { spring } from '../lib/motion';

/**
 * Currency selector. Prices are stored once in XOF and converted at display
 * time using the live rate, so switching currency never rewrites a listing.
 */
export default function CurrencySwitcher({ className }) {
    const { currency = 'XOF', currencies = {} } = usePage().props;
    const [open, setOpen] = useState(false);

    const active = currencies[currency] ?? { code: currency, symbol: currency };

    const choose = (code) => {
        setOpen(false);
        if (code === currency) return;

        router.post(`/currency/${code}`, {}, { preserveScroll: true, preserveState: false });
    };

    return (
        <div className={cn('relative', className)}>
            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                aria-haspopup="listbox"
                aria-expanded={open}
                aria-label="Currency"
                className="flex items-center gap-1.5 rounded-xl bg-white/[0.05] px-2.5 py-1.5 text-[0.6875rem] font-semibold uppercase tracking-[0.08em] text-body/85 ring-1 ring-white/10 transition-colors hover:bg-white/[0.08] hover:text-heading"
            >
                {active.code}
                <ChevronDownIcon className={cn('h-3.5 w-3.5 transition-transform', open && 'rotate-180')} />
            </button>

            <AnimatePresence>
                {open && (
                    <>
                        <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} aria-hidden="true" />

                        <motion.ul
                            role="listbox"
                            initial={{ opacity: 0, y: -6, scale: 0.97 }}
                            animate={{ opacity: 1, y: 0, scale: 1 }}
                            exit={{ opacity: 0, y: -6, scale: 0.97 }}
                            transition={spring}
                            className="absolute right-0 top-full z-50 mt-2 w-44 overflow-hidden rounded-[0.9rem] border border-white/[0.12] bg-void-raised/95 p-1 shadow-[0_24px_60px_-20px_rgba(0,0,0,0.95)] backdrop-blur-xl"
                        >
                            {Object.values(currencies).map((item) => (
                                <li key={item.code}>
                                    <button
                                        type="button"
                                        role="option"
                                        aria-selected={item.code === currency}
                                        onClick={() => choose(item.code)}
                                        className={cn(
                                            'flex w-full items-center justify-between rounded-[0.55rem] px-2.5 py-2 text-left text-[0.75rem] transition-colors',
                                            item.code === currency
                                                ? 'bg-neon/10 text-neon'
                                                : 'text-body/80 hover:bg-white/[0.06] hover:text-heading',
                                        )}
                                    >
                                        <span className="flex items-center gap-2">
                                            <span className="w-10 font-semibold">{item.code}</span>
                                            <span className="text-faint">{item.symbol}</span>
                                        </span>
                                        {item.code === currency && <span className="text-[0.625rem]">✓</span>}
                                    </button>
                                </li>
                            ))}
                        </motion.ul>
                    </>
                )}
            </AnimatePresence>
        </div>
    );
}
