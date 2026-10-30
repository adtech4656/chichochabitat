import { useEffect, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import SiteLayout from '../Layouts/SiteLayout';
import LogoPreloader from '../Components/LogoPreloader';
import GlassSearchBar from '../Components/GlassSearchBar';
import PropertyCard from '../Components/PropertyCard';
import PropertyCardSkeleton from '../Components/PropertyCardSkeleton';
import { useAuthModal } from '../lib/auth-modal';
import { useTranslation } from '../lib/i18n';
import { useMoney } from '../lib/money';
import { page, spring, staggerParent } from '../lib/motion';
import {
    ArrowUpRightIcon,
    CameraIcon,
    HeartIcon,
    MapPinIcon,
    SearchIcon,
    ShieldCheckIcon,
} from '../Components/Icons';

const HERO_IMAGE =
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=2400&q=80';

const INTENTS = [
    { key: 'buy', labelKey: 'intent.buy', icon: '🏛' },
    { key: 'rent', labelKey: 'intent.rent', icon: '🔑' },
    { key: 'land', labelKey: 'intent.land', icon: '🌿' },
    { key: 'commercial', labelKey: 'intent.commercial', icon: '🏬' },
    { key: 'hospitality', labelKey: 'intent.hospitality', icon: '🏨' },
];

const TRUST = [
    { icon: ShieldCheckIcon, titleKey: 'trust.identityTitle', copyKey: 'trust.identityCopy' },
    { icon: CameraIcon, titleKey: 'trust.captureTitle', copyKey: 'trust.captureCopy' },
    { icon: MapPinIcon, titleKey: 'trust.approvedTitle', copyKey: 'trust.approvedCopy' },
];

const HOW_IT_WORKS = [
    { step: '01', titleKey: 'how.step1Title', copyKey: 'how.step1Copy' },
    { step: '02', titleKey: 'how.step2Title', copyKey: 'how.step2Copy' },
    { step: '03', titleKey: 'how.step3Title', copyKey: 'how.step3Copy' },
    { step: '04', titleKey: 'how.step4Title', copyKey: 'how.step4Copy' },
];

export default function Home({ properties = [], content = {}, filters = {}, categories = {}, market = [] }) {
    const { openAuth } = useAuthModal();
    const { t, locale } = useTranslation();
    const { format } = useMoney();
    const dateLocale = locale === 'fr' ? 'fr-FR' : 'en-GB';
    const reduceMotion = useReducedMotion();

    const [preloading, setPreloading] = useState(() => {
        if (typeof window === 'undefined') return true;

        return window.sessionStorage.getItem('cch:entered') !== '1';
    });

    const [loading, setLoading] = useState(true);

    /* Skeleton pass — mirrors the real network waterfall on first paint. */
    useEffect(() => {
        const timer = window.setTimeout(() => setLoading(false), reduceMotion ? 120 : 900);

        return () => window.clearTimeout(timer);
    }, [reduceMotion]);

    const finishIntro = () => {
        window.sessionStorage.setItem('cch:entered', '1');
        setPreloading(false);
    };

    const search = (values) => {
        router.get(
            '/properties',
            {
                type: values.type || undefined,
                city: values.location || undefined,
            },
            { preserveScroll: false },
        );
    };

    return (
        <>
            <AnimatePresence>{preloading && <LogoPreloader onDone={finishIntro} />}</AnimatePresence>

            <motion.div variants={page} initial="initial" animate="animate" id="top" className="pb-24 lg:pb-0">
                {/* ============================ HERO ============================ */}
                <section className="relative isolate overflow-hidden">
                    <div className="absolute inset-0 -z-10 overflow-hidden">
                        <div
                            className="absolute inset-0 animate-hero-drift bg-cover bg-center will-change-transform"
                            style={{ backgroundImage: `url(${HERO_IMAGE})` }}
                            aria-hidden="true"
                        />
                        <div className="absolute inset-0 hero-scrim" aria-hidden="true" />
                    </div>

                    <div className="mx-auto max-w-7xl px-6 pb-28 pt-36 sm:pt-40 lg:pb-36 lg:pt-48">
                        <motion.div
                            initial={{ opacity: 0, y: 22 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ ...spring, delay: 0.1 }}
                            className="inline-flex items-center gap-2 rounded-full border border-white/[0.14] bg-white/[0.06] px-3 py-1.5 backdrop-blur-xl"
                        >
                            <span className="relative flex h-1.5 w-1.5">
                                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-neon opacity-70" />
                                <span className="relative inline-flex h-1.5 w-1.5 rounded-full bg-neon" />
                            </span>
                            <span className="text-[0.6875rem] font-medium uppercase tracking-[0.18em] text-body">
                                {content.eyebrow ?? t('hero.badge')}
                            </span>
                        </motion.div>

                        <motion.h1
                            initial={{ opacity: 0, y: 26 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ ...spring, delay: 0.16 }}
                            className="mt-7 max-w-3xl font-display text-[clamp(2.6rem,7.2vw,5.25rem)] font-semibold leading-[0.96] tracking-[-0.045em]"
                        >
                            <span className="block text-heading">{content.title_line1 ?? t('hero.title1Fallback')}</span>
                            <span className="block bg-gradient-to-r from-heading via-neon-bright to-neon bg-clip-text text-transparent drop-shadow-[0_0_44px_rgba(102,187,106,0.28)]">
                                {content.title_line2 ?? t('hero.title2Fallback')}
                            </span>
                        </motion.h1>

                        <motion.p
                            initial={{ opacity: 0, y: 22 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ ...spring, delay: 0.22 }}
                            className="mt-6 max-w-xl text-[0.9375rem] leading-relaxed text-body/85 sm:text-base"
                        >
                            {content.subtitle ?? t('hero.subtitleFallback')}
                        </motion.p>
                        <div className="mt-9 max-w-4xl">
                            <GlassSearchBar
                                onSubmit={search}
                                cities={filters.cities ?? []}
                                types={filters.types ?? []}
                            />
                        </div>

                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ delay: 0.6, duration: 0.6 }}
                            className="mt-7 flex flex-wrap items-center gap-x-6 gap-y-2 text-[0.75rem] text-body/70"
                        >
                            {[t('hero.trust1'), t('hero.trust2'), t('hero.trust3')].map((item) => (
                                <span key={item} className="flex items-center gap-1.5">
                                    <span className="grid h-4 w-4 place-items-center rounded-full bg-neon/15 text-neon">
                                        <svg viewBox="0 0 12 12" className="h-2.5 w-2.5" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                            <path d="m2.5 6 2.2 2.2L9.5 3.6" />
                                        </svg>
                                    </span>
                                    {item}
                                </span>
                            ))}
                        </motion.div>

                        <div className="mt-12 grid max-w-2xl grid-cols-2 gap-x-8 gap-y-5 border-t border-white/[0.08] pt-7 sm:grid-cols-4">
                            {[
                                [content.stats?.listings ?? '0', t('stat.listings')],
                                [content.stats?.agents ?? '0', t('stat.agents')],
                                [content.stats?.approval === '—' ? '—' : `${content.stats?.approval ?? '0'}%`, t('stat.approval')],
                                [content.stats?.response ?? '—', t('stat.response')],
                            ].map(([value, label]) => (
                                <div key={label}>
                                    <p className="font-display text-[1.375rem] font-semibold tracking-[-0.02em] text-heading">
                                        {value}
                                    </p>
                                    <p className="mt-1 text-[0.6875rem] uppercase tracking-[0.12em] text-faint">
                                        {label}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ========================= INTENT RAIL ========================= */}
                <section className="mx-auto -mt-10 max-w-7xl px-6">
                    <div className="fade-rail -mx-6 overflow-x-auto px-6 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <div className="flex min-w-max gap-3">
                            {INTENTS.map((intent, index) => (
                                <motion.a
                                    key={intent.key}
                                    href="#properties"
                                    initial={{ opacity: 0, y: 16 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true, margin: '-40px' }}
                                    transition={{ ...spring, delay: index * 0.04 }}
                                    whileHover={{ scale: 1.02, y: -2 }}
                                    className="glass flex items-center gap-3 rounded-[1.15rem] px-4 py-3 transition-colors hover:border-white/20"
                                >
                                    <span className="grid h-9 w-9 place-items-center rounded-[0.7rem] bg-white/[0.06] text-base ring-1 ring-white/10">
                                        {intent.icon}
                                    </span>
                                    <span>
                                        <span className="block text-[0.8125rem] font-medium text-heading">
                                            {t(intent.labelKey)}
                                        </span>
                                        <span className="mt-0.5 block text-[0.6875rem] text-faint">
                                            {t('intent.available', { count: categories[intent.key] ?? 0 })}
                                        </span>
                                    </span>
                                </motion.a>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ========================== PROPERTIES ========================= */}
                <section id="properties" className="mx-auto max-w-7xl scroll-mt-28 px-6 pt-24">
                    <div className="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="text-[0.6875rem] font-medium uppercase tracking-[0.18em] text-neon/80">
                                                {t('home.featured')}
                            </p>
                            <h2 className="mt-2.5 max-w-xl font-display text-[clamp(1.75rem,3.4vw,2.5rem)] font-semibold leading-[1.05] tracking-[-0.035em] text-heading">
                                                {t('home.featuredTitle')}
                            </h2>
                        </div>

                        <Link
                            href="/properties"
                            className="group flex items-center gap-2 rounded-full border border-white/[0.12] bg-white/[0.04] px-4 py-2 text-[0.8125rem] font-medium text-heading backdrop-blur-xl transition-colors hover:border-white/25"
                        >
                            {t('home.viewAll')}
                            <ArrowUpRightIcon className="h-4 w-4 text-body transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </Link>
                    </div>

                    {loading ? (
                        <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {Array.from({ length: 6 }).map((_, index) => (
                                <PropertyCardSkeleton key={index} delay={index * 70} />
                            ))}
                        </div>
                    ) : (
                        <motion.div
                            variants={staggerParent}
                            initial="initial"
                            animate="animate"
                            className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            {properties.map((property, index) => (
                                <PropertyCard key={property.id} property={property} priority={index < 3} />
                            ))}
                        </motion.div>
                    )}

                    {!loading && properties.length === 0 && (
                        <div className="glass mt-8 flex flex-col items-center gap-3 rounded-[1.5rem] px-6 py-16 text-center">
                            <SearchIcon className="h-6 w-6 text-neon/70" />
                            <p className="font-display text-[1.0625rem] font-medium text-heading">
                                {t('home.empty')}
                            </p>
                            <p className="max-w-sm text-[0.8125rem] text-body/70">
                                {t('home.emptyHelp')}
                            </p>
                        </div>
                    )}
                </section>

                {/* ========================= VERIFICATION ======================== */}
                <section id="verified" className="mx-auto max-w-7xl scroll-mt-28 px-6 pt-24">
                    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                        <div>
                            <p className="text-[0.6875rem] font-medium uppercase tracking-[0.18em] text-neon/80">
                                {t('home.why')}
                            </p>
                            <h2 className="mt-2.5 font-display text-[clamp(1.75rem,3.4vw,2.5rem)] font-semibold leading-[1.05] tracking-[-0.035em] text-heading">
                                {t('home.whyTitle')}
                            </h2>
                            <p className="mt-4 max-w-md text-[0.9375rem] leading-relaxed text-body/75">
                                {t('home.whyCopy')}
                            </p>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-3">
                            {TRUST.map(({ icon: Icon, titleKey, copyKey }, index) => (
                                <motion.div
                                    key={titleKey}
                                    initial={{ opacity: 0, y: 22 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true, margin: '-60px' }}
                                    transition={{ ...spring, delay: index * 0.06 }}
                                    whileHover={{ scale: 1.02 }}
                                    className="glass rounded-[1.35rem] p-5"
                                >
                                    <span className="grid h-10 w-10 place-items-center rounded-[0.8rem] bg-neon/10 text-neon ring-1 ring-neon/25">
                                        <Icon className="h-5 w-5" />
                                    </span>
                                    <h3 className="mt-4 font-display text-[0.9375rem] font-semibold tracking-[-0.01em] text-heading">
                                        {t(titleKey)}
                                    </h3>
                                    <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-body/65">
                                        {t(copyKey)}
                                    </p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ========================= HOW IT WORKS ======================== */}
                <section id="how-it-works" className="mx-auto max-w-7xl scroll-mt-28 px-6 pt-24">
                    <div className="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="text-[0.6875rem] font-medium uppercase tracking-[0.18em] text-neon/80">
                                {t('home.how')}
                            </p>
                            <h2 className="mt-2.5 max-w-lg font-display text-[clamp(1.75rem,3.4vw,2.5rem)] font-semibold leading-[1.05] tracking-[-0.035em] text-heading">
                                {t('home.howTitle')}
                            </h2>
                        </div>
                        <p className="max-w-sm text-[0.8125rem] leading-relaxed text-body/60">
                            {t('home.howCopy')}
                        </p>
                    </div>

                    <div className="mt-9 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {HOW_IT_WORKS.map((item, index) => (
                            <motion.div
                                key={item.step}
                                initial={{ opacity: 0, y: 24 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true, margin: '-60px' }}
                                transition={{ ...spring, delay: index * 0.06 }}
                                whileHover={{ scale: 1.02 }}
                                className="glass group relative overflow-hidden rounded-[1.35rem] p-5"
                            >
                                {/* Connecting hairline between steps */}
                                {index < HOW_IT_WORKS.length - 1 && (
                                    <span
                                        className="pointer-events-none absolute -right-2 top-9 hidden h-px w-4 bg-white/15 xl:block"
                                        aria-hidden="true"
                                    />
                                )}

                                <span className="font-mono text-[0.6875rem] tracking-[0.2em] text-neon/70">
                                    {item.step}
                                </span>

                                <h3 className="mt-3 font-display text-[1rem] font-semibold tracking-[-0.015em] text-heading">
                                    {t(item.titleKey)}
                                </h3>
                                <p className="mt-2 text-[0.8125rem] leading-relaxed text-body/65">
                                    {t(item.copyKey)}
                                </p>

                                <span
                                    className="pointer-events-none absolute -bottom-10 -right-10 h-24 w-24 rounded-full opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                                    style={{ background: 'radial-gradient(circle, rgba(102,187,106,0.16) 0%, transparent 70%)' }}
                                    aria-hidden="true"
                                />
                            </motion.div>
                        ))}
                    </div>
                </section>

                {/* =========================== INSIGHTS ========================== */}
                <section id="insights" className="mx-auto max-w-7xl scroll-mt-28 px-6 pt-24">
                    <div className="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="text-[0.6875rem] font-medium uppercase tracking-[0.18em] text-neon/80">
                                {t('home.insights')}
                            </p>
                            <h2 className="mt-2.5 font-display text-[clamp(1.75rem,3.4vw,2.5rem)] font-semibold leading-[1.05] tracking-[-0.035em] text-heading">
                                {t('home.insightsTitle')}
                            </h2>
                        </div>
                        <Link
                            href="/properties"
                            className="group flex items-center gap-2 rounded-full border border-white/[0.12] bg-white/[0.04] px-4 py-2 text-[0.8125rem] font-medium text-heading transition-colors hover:border-white/25"
                        >
                            {t('home.insightsAction')}
                            <ArrowUpRightIcon className="h-4 w-4 text-body transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </Link>
                    </div>

                    <div className="mt-9 grid gap-4 lg:grid-cols-3">
                        {market.map((insight, index) => (
                            <motion.article
                                key={insight.id}
                                initial={{ opacity: 0, y: 24 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true, margin: '-60px' }}
                                transition={{ ...spring, delay: index * 0.06 }}
                                className="glass flex flex-col rounded-[1.35rem] p-5"
                            >
                                <div className="flex items-center justify-between gap-3">
                                    <span className="rounded-full bg-white/[0.06] px-2.5 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-body/80 ring-1 ring-white/10">
                                        {insight.region}
                                    </span>
                                    <span className="flex items-center gap-1 rounded-full bg-neon/10 px-2 py-0.5 text-[0.625rem] font-semibold text-neon ring-1 ring-neon/25">
                                        <svg viewBox="0 0 12 12" className="h-2.5 w-2.5" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                            <path d="M6 9.5V2.5M3 5.5 6 2.5l3 3" />
                                        </svg>
                                        {insight.intent_label}
                                    </span>
                                </div>

                                <h3 className="mt-3.5 font-display text-[1rem] font-semibold leading-snug tracking-[-0.015em] text-heading">
                                    <Link href={insight.href} className="transition-colors hover:text-neon">
                                        {insight.title}
                                    </Link>
                                </h3>
                                <p className="mt-2 flex-1 text-[0.8125rem] leading-relaxed text-body/65">
                                    {t('market.summary', { type: insight.type, area: insight.area })}
                                </p>

                                <p className="mt-4 flex items-center justify-between gap-2 border-t border-white/[0.07] pt-3 text-[0.625rem] uppercase tracking-[0.14em] text-faint">
                                    <span>{format(insight.price)}</span>
                                    <span>
                                        {insight.published
                                            ? new Date(insight.published).toLocaleDateString(dateLocale, {
                                                  day: 'numeric',
                                                  month: 'short',
                                              })
                                            : ''}{' '}
                                        · {t('market.verifiedOn')}
                                    </span>
                                </p>
                            </motion.article>
                        ))}
                    </div>
                </section>

                {/* ============================ CTA ============================= */}
                <section className="mx-auto max-w-7xl px-6 pt-24">
                    <div className="glass-strong relative overflow-hidden rounded-[1.75rem] px-7 py-12 sm:px-12 sm:py-14">
                        <div
                            className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full"
                            style={{ background: 'radial-gradient(circle, rgba(102,187,106,0.22) 0%, transparent 70%)' }}
                            aria-hidden="true"
                        />
                        <div className="relative flex flex-col items-start justify-between gap-7 lg:flex-row lg:items-center">
                            <div className="max-w-xl">
                                <h2 className="font-display text-[clamp(1.6rem,3vw,2.125rem)] font-semibold leading-[1.1] tracking-[-0.035em] text-heading">
                                    {t('home.ctaTitle')}
                                </h2>
                                <p className="mt-3 text-[0.875rem] leading-relaxed text-body/75">
                                    {t('home.ctaCopy')}
                                </p>
                            </div>

                            <motion.button
                                type="button"
                                onClick={() => openAuth('signup')}
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                                transition={spring}
                                className="shrink-0 rounded-full bg-neon px-6 py-3.5 text-[0.875rem] font-semibold text-void transition-shadow duration-300 hover:neon-glow"
                            >
                                {t('home.ctaButton')}
                            </motion.button>
                        </div>
                    </div>
                </section>
            </motion.div>

            <MobileTabBar />
        </>
    );
}

/**
 * Persistent mobile navigation. On a phone the four jobs — search, save,
 * talk, manage — are always one tap away.
 */
function MobileTabBar() {
    const { t } = useTranslation();

    const items = [
        { label: t('mobile.search'), href: '/properties', icon: SearchIcon },
        { label: t('mobile.saved'), href: '/account', icon: HeartIcon },
        { label: t('mobile.chats'), href: '/account', icon: ChatIcon },
        { label: t('mobile.profile'), href: '/account', icon: UserIcon },
    ];

    return (
        <nav
            aria-label={t('mobile.nav')}
            className="fixed inset-x-0 bottom-0 z-40 border-t border-white/[0.08] bg-void/85 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden"
        >
            <div className="grid grid-cols-4">
                {items.map(({ label, href, icon: Icon }) => (
                    <Link
                        key={label}
                        href={href}
                        className="flex flex-col items-center gap-1 py-2.5 text-[0.625rem] font-medium text-body/65 transition-colors hover:text-heading"
                    >
                        <Icon className="h-[1.15rem] w-[1.15rem]" />
                        {label}
                    </Link>
                ))}
            </div>
        </nav>
    );
}

function ChatIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <path d="M20.5 12a8 8 0 0 1-8 8H8l-4 3v-5.2A8 8 0 0 1 12.5 4a8 8 0 0 1 8 8Z" />
        </svg>
    );
}

function UserIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" {...props}>
            <circle cx="12" cy="8.5" r="3.5" />
            <path d="M5 20a7 7 0 0 1 14 0" />
        </svg>
    );
}

Home.layout = (pageContent) => <SiteLayout>{pageContent}</SiteLayout>;
