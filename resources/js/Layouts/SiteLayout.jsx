import { useEffect, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import { LogoMark } from '../Components/Logo';
import AuthModal from '../Components/AuthModal';
import LanguageSwitcher from '../Components/LanguageSwitcher';
import CurrencySwitcher from '../Components/CurrencySwitcher';
import { AuthModalProvider, useAuthModal } from '../lib/auth-modal';
import { useTranslation } from '../lib/i18n';
import { spring } from '../lib/motion';
import { cn } from '../lib/utils';
import { ChevronDownIcon, CloseIcon, MenuIcon } from '../Components/Icons';

const NAV = [
    { key: 'nav.buy', href: '/properties?intent=sale' },
    { key: 'nav.rent', href: '/properties?intent=rent' },
    { key: 'nav.land', href: '/properties?type=Land' },
    { key: 'nav.commercial', href: '/properties?type=Shop' },
];

function FloatingNav() {
    const { openAuth } = useAuthModal();
    const { t } = useTranslation();
    const { auth, locale } = usePage().props;
    const user = auth?.user ?? null;

    const [scrolled, setScrolled] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 12);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    /* Close the sheet when the viewport grows past the breakpoint. */
    useEffect(() => {
        const mq = window.matchMedia('(min-width: 1024px)');
        const onChange = () => setMenuOpen(false);
        mq.addEventListener('change', onChange);

        return () => mq.removeEventListener('change', onChange);
    }, []);

    return (
        <div className="pointer-events-none fixed inset-x-0 top-0 z-50 flex flex-col items-center px-3 pt-3 sm:px-4 sm:pt-5">
            <motion.header
                initial={{ y: -24, opacity: 0 }}
                animate={{ y: 0, opacity: 1 }}
                transition={{ ...spring, delay: 0.05 }}
                className={cn(
                    'pointer-events-auto w-full max-w-6xl overflow-hidden transition-[background-color,box-shadow,border-color] duration-500',
                    'rounded-[1.1rem] lg:rounded-full',
                    scrolled || menuOpen
                        ? 'border border-white/[0.14] bg-void/85 shadow-[0_18px_50px_-20px_rgba(0,0,0,0.9)] backdrop-blur-2xl'
                        : 'border border-white/[0.1] bg-white/[0.045] backdrop-blur-xl',
                )}
            >
                <div className="flex items-center justify-between gap-3 px-3 py-2.5 sm:px-4">
                    <Link href="/" className="flex shrink-0 items-center gap-2.5">
                        <LogoMark className="h-8 w-8" />
                        <span className="font-display text-[0.9375rem] font-semibold tracking-[-0.02em] text-heading">
                            ChiChoc<span className="text-neon">-</span>Habitat
                        </span>
                    </Link>

                    <nav className="hidden items-center gap-1 lg:flex" aria-label="Primary">
                        {NAV.map((item) => (
                            <Link
                                key={item.key}
                                href={item.href}
                                className="rounded-full px-3.5 py-1.5 text-[0.8125rem] font-medium text-body/85 transition-colors duration-200 hover:bg-white/[0.06] hover:text-heading"
                            >
                                {t(item.key)}
                            </Link>
                        ))}
                    </nav>

                    <div className="flex items-center gap-2">
                        <LanguageSwitcher locale={locale} compact className="hidden sm:flex" />
                        <CurrencySwitcher className="hidden sm:block" />

                        {user ? (
                            <>
                                <Link
                                    href="/account"
                                    className="hidden items-center gap-2 rounded-xl bg-white/[0.05] py-1 pl-1 pr-3 ring-1 ring-white/10 transition-colors hover:bg-white/[0.09] sm:flex"
                                >
                                    <span className="grid h-7 w-7 place-items-center rounded-lg bg-gradient-to-br from-neon/30 to-neon/5 text-[0.625rem] font-semibold text-neon ring-1 ring-neon/25">
                                        {user.initials}
                                    </span>
                                    <span className="max-w-[7rem] truncate text-[0.8125rem] font-medium text-heading">
                                        {user.name.split(' ')[0]}
                                    </span>
                                </Link>

                                <button
                                    type="button"
                                    onClick={() => router.post('/logout')}
                                    className="hidden rounded-xl px-3 py-1.5 text-[0.8125rem] font-medium text-body/80 transition-colors hover:bg-white/[0.06] hover:text-heading sm:block"
                                >
                                    {t('nav.signOut')}
                                </button>
                            </>
                        ) : (
                            <>
                                <button
                                    type="button"
                                    onClick={() => openAuth('signin')}
                                    className="hidden rounded-xl px-3 py-1.5 text-[0.8125rem] font-medium text-body/85 transition-colors hover:bg-white/[0.06] hover:text-heading sm:block"
                                >
                                    {t('nav.signIn')}
                                </button>

                                <motion.button
                                    type="button"
                                    onClick={() => openAuth('signup')}
                                    whileHover={{ scale: 1.02 }}
                                    whileTap={{ scale: 0.98 }}
                                    transition={spring}
                                    className="rounded-xl bg-neon px-4 py-2 text-[0.8125rem] font-semibold text-void transition-shadow duration-300 hover:neon-glow"
                                >
                                    {t('nav.getStarted')}
                                </motion.button>
                            </>
                        )}

                        {/* Rectangular trigger — deliberately not a circular icon button. */}
                        <button
                            type="button"
                            onClick={() => setMenuOpen((current) => !current)}
                            aria-label={menuOpen ? t('nav.close') : t('nav.menu')}
                            aria-expanded={menuOpen}
                            aria-controls="mobile-nav"
                            className={cn(
                                'flex items-center gap-1.5 rounded-xl px-2.5 py-2 text-[0.75rem] font-semibold transition-colors lg:hidden',
                                menuOpen
                                    ? 'bg-neon/10 text-neon ring-1 ring-neon/30'
                                    : 'text-body/85 ring-1 ring-white/10 hover:bg-white/[0.07] hover:text-heading',
                            )}
                        >
                            {menuOpen ? <CloseIcon className="h-4 w-4" /> : <MenuIcon className="h-4 w-4" />}
                            <span className="hidden sm:inline">{menuOpen ? t('nav.close') : t('nav.menu')}</span>
                        </button>
                    </div>
                </div>

                {/* Full-width sheet, not a floating dropdown. */}
                <AnimatePresence initial={false}>
                    {menuOpen && (
                        <motion.div
                            id="mobile-nav"
                            key="sheet"
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: 'auto', opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }}
                            className="overflow-hidden border-t border-white/[0.08] bg-void/60 lg:hidden"
                        >
                            <div className="px-3 py-3 sm:px-4">
                                <p className="px-1 pb-2 text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-faint">
                                    {t('footer.explore')}
                                </p>

                                <div className="grid gap-1">
                                    {NAV.map((item) => (
                                        <Link
                                            key={item.key}
                                            href={item.href}
                                            onClick={() => setMenuOpen(false)}
                                            className="flex items-center justify-between rounded-xl px-3 py-3 text-[0.875rem] font-medium text-heading transition-colors hover:bg-white/[0.06]"
                                        >
                                            {t(item.key)}
                                            <ChevronDownIcon className="h-4 w-4 -rotate-90 text-body/40" />
                                        </Link>
                                    ))}

                                    <Link
                                        href="/account"
                                        onClick={() => setMenuOpen(false)}
                                        className="flex items-center justify-between rounded-xl px-3 py-3 text-[0.875rem] font-medium text-heading transition-colors hover:bg-white/[0.06]"
                                    >
                                        {t('nav.account')}
                                        <ChevronDownIcon className="h-4 w-4 -rotate-90 text-body/40" />
                                    </Link>
                                </div>

                                <div className="my-3 h-px bg-white/[0.08]" />

                                <div className="flex items-center justify-between gap-3 px-1">
                                    <span className="text-[0.75rem] text-body/70">{t('common.language')}</span>
                                    <LanguageSwitcher locale={locale} />
                                </div>

                                <div className="mt-2 flex items-center justify-between gap-3 px-1">
                                    <span className="text-[0.75rem] text-body/70">{t('common.currency')}</span>
                                    <CurrencySwitcher />
                                </div>

                                <div className="mt-3 grid gap-2">
                                    {user ? (
                                        <>
                                            {user.role !== 'customer' && (
                                                <Link
                                                    href={user.backend_url ?? '/agent'}
                                                    onClick={() => setMenuOpen(false)}
                                                    className="rounded-xl bg-white/[0.06] px-3 py-3 text-center text-[0.8125rem] font-semibold text-heading ring-1 ring-white/10"
                                                >
                                                    {t('nav.console')}
                                                </Link>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setMenuOpen(false);
                                                    router.post('/logout');
                                                }}
                                                className="rounded-xl bg-white/[0.04] px-3 py-3 text-[0.8125rem] font-semibold text-body ring-1 ring-white/10"
                                            >
                                                {t('nav.signOut')}
                                            </button>
                                        </>
                                    ) : (
                                        <>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setMenuOpen(false);
                                                    openAuth('signin');
                                                }}
                                                className="rounded-xl bg-white/[0.05] px-3 py-3 text-[0.8125rem] font-semibold text-heading ring-1 ring-white/10"
                                            >
                                                {t('nav.signIn')}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setMenuOpen(false);
                                                    openAuth('signup');
                                                }}
                                                className="rounded-xl bg-neon px-3 py-3 text-[0.8125rem] font-semibold text-void"
                                            >
                                                {t('nav.getStarted')}
                                            </button>
                                        </>
                                    )}
                                </div>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </motion.header>
        </div>
    );
}

function AuthHost() {
    const { open, mode, closeAuth, openAuth } = useAuthModal();

    return (
        <AuthModal
            open={open}
            mode={mode}
            onClose={closeAuth}
            onModeChange={(next) => openAuth(next)}
        />
    );
}

export default function SiteLayout({ children }) {
    const { t } = useTranslation();
    const { footerPages = {}, locale, site } = usePage().props;

    const columns = [
        {
            title: t('footer.explore'),
            links: [
                { label: t('footer.buyHome'), href: '/properties?intent=sale' },
                { label: t('footer.rentHome'), href: '/properties?intent=rent' },
                { label: t('footer.land'), href: '/properties?type=Land' },
                { label: t('footer.commercial'), href: '/properties?type=Shop' },
            ],
        },
        ...['trust', 'company', 'legal'].map((group) => ({
            title: t(`footer.${group}`),
            links: (footerPages?.[group] ?? []).map((page) => ({
                label: page.title,
                href: `/pages/${page.slug}`,
            })),
        })),
    ];

    return (
        <AuthModalProvider>
            <div className="relative flex min-h-dvh flex-col">
                <FloatingNav />

                <main className="flex-1">{children}</main>

                <footer className="mt-24 border-t border-white/[0.08]">
                    <div className="mx-auto grid max-w-7xl gap-10 px-6 py-14 sm:grid-cols-2 lg:grid-cols-5">
                        <div className="lg:col-span-2">
                            <div className="flex items-center gap-2.5">
                                <LogoMark className="h-8 w-8" />
                                <span className="font-display text-[0.9375rem] font-semibold tracking-[-0.02em] text-heading">
                                    ChiChoc<span className="text-neon">-</span>Habitat
                                </span>
                            </div>

                            <p className="mt-4 max-w-xs text-[0.8125rem] leading-relaxed text-body/65">
                                {site?.tagline}
                            </p>

                            {site?.email && (
                                <p className="mt-3 text-[0.75rem] text-body/55">
                                    {site.email}
                                    {site.phone ? ` · ${site.phone}` : ''}
                                </p>
                            )}

                            <div className="mt-5">
                                <LanguageSwitcher locale={locale} />
                            </div>
                        </div>

                        {columns.map((column) => (
                            <div key={column.title}>
                                <p className="text-[0.6875rem] font-medium uppercase tracking-[0.16em] text-faint">
                                    {column.title}
                                </p>
                                <ul className="mt-4 space-y-2.5">
                                    {column.links.map((link) => (
                                        <li key={link.href + link.label}>
                                            <Link
                                                href={link.href}
                                                className="text-[0.8125rem] text-body/70 transition-colors hover:text-heading"
                                            >
                                                {link.label}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>

                    <div className="border-t border-white/[0.06]">
                        <div className="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-5 text-[0.75rem] text-faint sm:flex-row sm:items-center sm:justify-between">
                            <p>© {new Date().getFullYear()} ChiChoc-Habitat. {t('footer.rights')}.</p>
                            <p className="flex items-center gap-2">
                                <span className="inline-block h-1.5 w-1.5 rounded-full bg-neon" />
                                Côte d’Ivoire · XOF · {locale === 'fr' ? 'Français' : 'English'}
                            </p>
                        </div>
                    </div>
                </footer>

                <AuthHost />
            </div>
        </AuthModalProvider>
    );
}
