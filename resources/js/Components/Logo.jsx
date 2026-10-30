import { useId } from 'react';
import { cn } from '../lib/utils';

/**
 * ChiChoc-Habitat mark: two mirrored "C" arcs that meet under a roofline,
 * reading simultaneously as a house and as the two C's of the brand.
 */
export function LogoMark({ className, glow = false }) {
    const gradientId = useId();

    return (
        <svg
            viewBox="0 0 48 48"
            fill="none"
            aria-hidden="true"
            className={cn('shrink-0', className)}
            style={glow ? { filter: 'drop-shadow(0 0 18px rgba(102,187,106,0.55))' } : undefined}
        >
            <defs>
                <linearGradient id={gradientId} x1="8" y1="6" x2="41" y2="43" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#7ED688" />
                    <stop offset="1" stopColor="#66BB6A" />
                </linearGradient>
            </defs>

            <g
                stroke={`url(#${gradientId})`}
                strokeWidth="3.2"
                strokeLinecap="round"
                strokeLinejoin="round"
            >
                {/* Left C */}
                <path d="M18.6 38.5H14a7.2 7.2 0 0 1-7.2-7.2v-5.4a7.2 7.2 0 0 1 7.2-7.2h4.6" />
                {/* Right, mirrored C */}
                <path d="M29.4 38.5H34a7.2 7.2 0 0 0 7.2-7.2v-5.4a7.2 7.2 0 0 0-7.2-7.2h-4.6" />
                {/* Roofline */}
                <path d="M8.4 20.4 24 7.6l15.6 12.8" />
            </g>

            {/* Keystone */}
            <path d="M24 18.6v19.9" stroke={`url(#${gradientId})`} strokeWidth="1.9" strokeLinecap="round" opacity="0.4" />
        </svg>
    );
}

export function Wordmark({ className, markClassName = 'h-9 w-9' }) {
    return (
        <span className={cn('inline-flex items-center gap-2.5', className)}>
            <LogoMark className={markClassName} />
            <span className="font-display text-[1.0625rem] font-semibold tracking-[-0.02em] text-heading">
                ChiChoc<span className="text-neon">-</span>Habitat
            </span>
        </span>
    );
}
