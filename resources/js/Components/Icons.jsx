/**
 * Inline icon set — no icon-font dependency, inherits currentColor.
 * All icons are 24×24 with a 1.75 stroke for an SF-Symbols-like weight.
 */

const base = {
    width: 24,
    height: 24,
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 1.75,
    strokeLinecap: 'round',
    strokeLinejoin: 'round',
    'aria-hidden': true,
};

export function MapPinIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z" />
            <circle cx="12" cy="10" r="2.6" />
        </svg>
    );
}

export function HeartIcon({ filled = false, ...props }) {
    return (
        <svg {...base} {...props} fill={filled ? 'currentColor' : 'none'}>
            <path d="M12 20.2s-7.4-4.6-7.4-9.6a4.2 4.2 0 0 1 7.4-2.7 4.2 4.2 0 0 1 7.4 2.7c0 5-7.4 9.6-7.4 9.6Z" />
        </svg>
    );
}

export function ArrowUpRightIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="M7 17 17 7M9 7h8v8" />
        </svg>
    );
}

export function SearchIcon(props) {
    return (
        <svg {...base} {...props}>
            <circle cx="11" cy="11" r="6.5" />
            <path d="m16 16 4 4" />
        </svg>
    );
}

export function CloseIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="m6 6 12 12M18 6 6 18" />
        </svg>
    );
}

export function EyeIcon({ open = true, ...props }) {
    return (
        <svg {...base} {...props}>
            {open ? (
                <>
                    <path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12Z" />
                    <circle cx="12" cy="12" r="3" />
                </>
            ) : (
                <>
                    <path d="M4 4l16 16" />
                    <path d="M9.9 5.2A9.9 9.9 0 0 1 12 5c6 0 9.5 7 9.5 7a17 17 0 0 1-3 4.1M6.2 7.4A17 17 0 0 0 2.5 12S6 19 12 19a9.7 9.7 0 0 0 4-.9" />
                </>
            )}
        </svg>
    );
}

export function ShieldCheckIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="M12 3.2 5 6v6c0 4.4 3 7.9 7 8.8 4-.9 7-4.4 7-8.8V6l-7-2.8Z" />
            <path d="m9 12 2.2 2.2L15.4 10" />
        </svg>
    );
}

export function CameraIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="M4 8.5h2.5l1.3-2h8.4l1.3 2H20a1.5 1.5 0 0 1 1.5 1.5v7A1.5 1.5 0 0 1 20 18.5H4A1.5 1.5 0 0 1 2.5 17v-7A1.5 1.5 0 0 1 4 8.5Z" />
            <circle cx="12" cy="13" r="3.2" />
        </svg>
    );
}

export function ChevronDownIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="m6 9.5 6 6 6-6" />
        </svg>
    );
}

export function MenuIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="M4 7h16M4 12h16M4 17h10" />
        </svg>
    );
}

export function CheckIcon(props) {
    return (
        <svg {...base} {...props}>
            <path d="m5 12.5 4.2 4.2L19 7" />
        </svg>
    );
}

export function AppleIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {...props}>
            <path d="M16.4 12.7c0-2.3 1.9-3.4 2-3.5-1.1-1.6-2.8-1.8-3.4-1.9-1.4-.1-2.8.9-3.5.9-.7 0-1.8-.9-3-.8-1.5 0-2.9.9-3.7 2.3-1.6 2.7-.4 6.8 1.1 9 .7 1.1 1.6 2.3 2.8 2.3 1.1 0 1.6-.7 3-.7s1.8.7 3 .7c1.2 0 2-1.1 2.8-2.2.9-1.3 1.2-2.5 1.3-2.6-.1 0-2.4-1-2.4-3.5ZM14.2 5.6c.6-.8 1-1.9.9-3-.9 0-2 .6-2.7 1.4-.6.7-1.1 1.8-.9 2.9 1 .1 2-.5 2.7-1.3Z" />
        </svg>
    );
}

export function GoogleIcon(props) {
    return (
        <svg viewBox="0 0 24 24" aria-hidden="true" {...props}>
            <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.6 3.7-5.5 3.7-3.3 0-6-2.7-6-6.1s2.7-6.1 6-6.1c1.9 0 3.1.8 3.9 1.5l2.7-2.6C16.9 2.9 14.7 2 12 2 6.9 2 2.8 6.1 2.8 11.7S6.9 21.4 12 21.4c5.9 0 9.8-4.1 9.8-9.9 0-.7-.1-1.2-.2-1.7H12Z" />
        </svg>
    );
}
