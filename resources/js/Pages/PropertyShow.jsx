import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import SiteLayout from '../Layouts/SiteLayout';
import MapCanvas from '../Components/MapCanvas';
import PropertyCard from '../Components/PropertyCard';
import OfferModal from '../Components/OfferModal';
import { Avatar, Button, Divider, SectionHeading, VerifiedBadge } from '../Components/ui';
import {
    ArrowUpRightIcon,
    CameraIcon,
    CheckIcon,
    HeartIcon,
    MapPinIcon,
    ShieldCheckIcon,
} from '../Components/Icons';
import { page, spring, staggerParent } from '../lib/motion';
import { cn } from '../lib/utils';
import { useMoney } from '../lib/money';
import { useTranslation, useTypeLabel } from '../lib/i18n';

const VERIFICATION_ROWS = [
    { key: 'agent', icon: ShieldCheckIcon },
    { key: 'location', icon: MapPinIcon },
    { key: 'photos', icon: CameraIcon },
    { key: 'video', icon: CameraIcon },
    { key: 'documents', icon: ShieldCheckIcon },
];

export default function PropertyShow({ property, similar = [] }) {
    const { format } = useMoney();
    const { t, locale } = useTranslation();
    const typeLabel = useTypeLabel();
    const dateLocale = locale === 'fr' ? 'fr-FR' : 'en-GB';
    const [activeImage, setActiveImage] = useState(0);
    const [saved, setSaved] = useState(false);
    const [offerOpen, setOfferOpen] = useState(false);

    const gallery = property.gallery?.length > 0 ? property.gallery : [property.image];

    return (
        <motion.div variants={page} initial="initial" animate="animate" className="pb-28 lg:pb-0">
            <div className="mx-auto max-w-7xl px-6 pt-28 sm:pt-32">
                <nav className="flex items-center gap-2 text-[0.75rem] text-faint" aria-label={t('prop.breadcrumb')}>
                    <Link href="/" className="transition-colors hover:text-heading">
                        {t('prop.crumbHome')}
                    </Link>
                    <span aria-hidden="true">/</span>
                    <Link href="/properties" className="transition-colors hover:text-heading">
                        {t('prop.crumbProperties')}
                    </Link>
                    <span aria-hidden="true">/</span>
                    <span className="truncate text-body/80">{property.title}</span>
                </nav>

                {/* Gallery */}
                <div className="mt-5 grid gap-3 lg:grid-cols-[1.65fr_1fr]">
                    <div className="relative overflow-hidden rounded-[1.5rem] ring-1 ring-white/10">
                        <motion.img
                            key={activeImage}
                            src={gallery[activeImage]}
                            alt={t('prop.viewAlt', { title: property.title, n: activeImage + 1 })}
                            initial={{ opacity: 0, scale: 1.02 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
                            className="aspect-[16/10] h-full w-full object-cover"
                        />

                        <div className="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-void/60 to-transparent" />

                        <div className="absolute left-4 top-4 flex flex-wrap items-center gap-2">
                            <VerifiedBadge label={t('prop.fullyVerified')} />
                            <span className="rounded-full bg-void/60 px-2.5 py-1 text-[0.6875rem] font-medium text-heading backdrop-blur-md ring-1 ring-white/15">
                                {property.intent_label}
                            </span>
                        </div>

                        <button
                            type="button"
                            className="group absolute inset-0 grid place-items-center"
                            aria-label={t('prop.watchVerification')}
                        >
                            <span className="flex items-center gap-3 rounded-full bg-void/70 px-5 py-3 text-[0.8125rem] font-semibold text-heading backdrop-blur-xl ring-1 ring-white/20 transition-all duration-300 group-hover:bg-void/85 group-hover:neon-glow">
                                <span className="grid h-8 w-8 place-items-center rounded-full bg-neon text-void">
                                    <svg viewBox="0 0 12 12" className="ml-0.5 h-3 w-3" fill="currentColor" aria-hidden="true">
                                        <path d="M3 1.5 10 6l-7 4.5z" />
                                    </svg>
                                </span>
                                {t('prop.watchVerification')}
                            </span>
                        </button>

                        <div className="absolute bottom-4 right-4 rounded-full bg-void/65 px-3 py-1.5 text-[0.6875rem] font-medium text-body backdrop-blur-md ring-1 ring-white/12">
                            {activeImage + 1} / {gallery.length}
                        </div>
                    </div>

                    <div className="grid grid-cols-4 gap-3 lg:grid-cols-2 lg:auto-rows-fr">
                        {gallery.slice(0, 4).map((image, index) => (
                            <button
                                key={image}
                                type="button"
                                onClick={() => setActiveImage(index)}
                                aria-label={t('prop.viewImage', { n: index + 1 })}
                                aria-current={activeImage === index}
                                className={cn(
                                    'relative overflow-hidden rounded-[1rem] ring-1 transition-all duration-300',
                                    activeImage === index
                                        ? 'ring-2 ring-neon'
                                        : 'ring-white/10 hover:ring-white/30',
                                )}
                            >
                                <img
                                    src={image}
                                    alt=""
                                    loading="lazy"
                                    className="aspect-[4/3] h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                />
                            </button>
                        ))}
                    </div>
                </div>

                {/* Header */}
                <div className="mt-8 flex flex-wrap items-start justify-between gap-6">
                    <div className="min-w-0">
                        <h1 className="font-display text-[clamp(1.6rem,3.2vw,2.375rem)] font-semibold leading-[1.08] tracking-[-0.04em] text-heading">
                            {property.title}
                        </h1>

                        <p className="mt-2 flex items-center gap-1.5 text-[0.875rem] text-body/75">
                            <MapPinIcon className="h-4 w-4 text-neon/70" />
                            {property.location}
                            <span className="mx-1.5 text-white/20">·</span>
                            {t('prop.listedOn', {
                                date: new Date(property.published).toLocaleDateString(dateLocale, { day: 'numeric', month: 'short' }),
                            })}
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="text-right">
                            <p className="text-[0.6875rem] uppercase tracking-[0.14em] text-faint">
                                {property.intent_label}
                            </p>
                            <p className="mt-1 font-display text-[clamp(1.35rem,2.4vw,1.75rem)] font-semibold tracking-[-0.03em] text-heading">
                                {format(property.price)}
                            </p>
                        </div>

                        <motion.button
                            type="button"
                            onClick={() => setSaved((current) => !current)}
                            whileHover={{ scale: 1.05 }}
                            whileTap={{ scale: 0.94 }}
                            transition={spring}
                            aria-pressed={saved}
                            aria-label={saved ? t('prop.unsave') : t('prop.save')}
                            className={cn(
                                'grid h-11 w-11 place-items-center rounded-full ring-1 transition-colors',
                                saved ? 'bg-neon/12 text-neon ring-neon/40' : 'bg-white/[0.05] text-heading ring-white/12 hover:bg-white/[0.09]',
                            )}
                        >
                            <HeartIcon filled={saved} className="h-[1.1rem] w-[1.1rem]" />
                        </motion.button>
                    </div>
                </div>

                {/* Key facts */}
                <div className="mt-7 grid grid-cols-2 gap-px overflow-hidden rounded-[1.15rem] bg-white/[0.08] sm:grid-cols-3 lg:grid-cols-5">
                    {[
                        property.bedrooms > 0 && [property.bedrooms, t('prop.bedrooms')],
                        property.bathrooms > 0 && [property.bathrooms, t('prop.bathrooms')],
                        [`${property.area} m²`, t('prop.area')],
                        [typeLabel(property.type), t('prop.type')],
                        [`${property.views.toLocaleString()}`, t('prop.views')],
                    ]
                        .filter(Boolean)
                        .map(([value, label]) => (
                            <div key={label} className="bg-void px-5 py-4">
                                <p className="font-display text-[1.0625rem] font-semibold text-heading">{value}</p>
                                <p className="mt-0.5 text-[0.6875rem] uppercase tracking-[0.12em] text-faint">{label}</p>
                            </div>
                        ))}
                </div>

                <div className="mt-10 grid gap-10 lg:grid-cols-[1.6fr_1fr]">
                    {/* Left column */}
                    <div className="min-w-0 space-y-10">
                        <section>
                            <h2 className="font-display text-[1.125rem] font-semibold tracking-[-0.02em] text-heading">
                                {t('prop.about')}
                            </h2>
                            <p className="mt-3 text-[0.9375rem] leading-7 text-body/75">{property.description}</p>
                        </section>

                        <section>
                            <div className="flex items-center justify-between gap-3">
                                <h2 className="font-display text-[1.125rem] font-semibold tracking-[-0.02em] text-heading">
                                    {t('prop.amenities')}
                                </h2>
                                <span className="flex items-center gap-1.5 text-[0.6875rem] font-medium text-neon/85">
                                    <CheckIcon className="h-3.5 w-3.5" />
                                    {t('prop.photoVerified')}
                                </span>
                            </div>

                            <div className="mt-4 grid gap-2.5 sm:grid-cols-2">
                                {property.tags.map((tag) => (
                                    <div
                                        key={tag}
                                        className="flex items-center justify-between rounded-[0.8rem] bg-white/[0.04] px-3.5 py-2.5 ring-1 ring-white/8"
                                    >
                                        <span className="text-[0.8125rem] text-body/85">{tag}</span>
                                        <span className="grid h-5 w-5 place-items-center rounded-full bg-neon/15 text-neon">
                                            <CheckIcon className="h-3 w-3" />
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section>
                            <h2 className="font-display text-[1.125rem] font-semibold tracking-[-0.02em] text-heading">
                                {t('prop.location')}
                            </h2>
                            <p className="mt-2 text-[0.8125rem] text-body/65">
                                {t('prop.locationHelp')}
                            </p>
                            <div className="mt-4">
                                <MapCanvas
                                    height="h-72"
                                    accuracy
                                    caption={t('prop.captureCaption')}
                                    markers={[
                                        {
                                            id: property.id,
                                            lat: property.lat,
                                            lng: property.lng,
                                            primary: true,
                                            label: property.district,
                                        },
                                    ]}
                                />
                            </div>
                        </section>

                        {/* Verification panel — the trust core */}
                        <section className="glass-strong relative overflow-hidden rounded-[1.5rem] p-6">
                            <div
                                className="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full"
                                style={{ background: 'radial-gradient(circle, rgba(102,187,106,0.18) 0%, transparent 70%)' }}
                                aria-hidden="true"
                            />

                            <div className="relative">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div className="flex items-center gap-3">
                                        <span className="grid h-10 w-10 place-items-center rounded-[0.8rem] bg-neon/12 text-neon ring-1 ring-neon/25">
                                            <ShieldCheckIcon className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h2 className="font-display text-[1rem] font-semibold tracking-[-0.02em] text-heading">
                                                {t('prop.verification')}
                                            </h2>
                                            <p className="mt-0.5 text-[0.75rem] text-faint">
                                                {t('prop.reviewedBy', { name: property.verified.reviewer })}
                                            </p>
                                        </div>
                                    </div>

                                    <VerifiedBadge label={t('prop.approved')} />
                                </div>

                                <div className="mt-5 space-y-2.5">
                                    {VERIFICATION_ROWS.map(({ key, icon: Icon }) => (
                                        <div
                                            key={key}
                                            className="flex items-center gap-3 rounded-[0.8rem] bg-white/[0.03] px-3.5 py-3 ring-1 ring-white/8"
                                        >
                                            <Icon className="h-4 w-4 shrink-0 text-body/50" />

                                            <div className="min-w-0 flex-1">
                                                <p className="text-[0.8125rem] font-medium text-heading">
                                                    {t(`verify.${key}.label`)}
                                                </p>
                                                <p className="truncate text-[0.6875rem] text-faint">
                                                    {t(`verify.${key}.detail`)}
                                                </p>
                                            </div>

                                            <span className="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-neon/15 text-neon">
                                                <CheckIcon className="h-3.5 w-3.5" />
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                <p className="mt-4 text-[0.6875rem] leading-relaxed text-faint">
                                    {t('prop.reviewLocked', { date: property.verified.reviewed_at })}
                                </p>
                            </div>
                        </section>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-4">
                        <div className="glass sticky top-28 rounded-[1.35rem] p-5">
                            <div className="flex items-center gap-3">
                                <Avatar initials={property.agent.initials} size="lg" />
                                <div className="min-w-0">
                                    <p className="truncate font-display text-[0.9375rem] font-semibold text-heading">
                                        {property.agent.name}
                                    </p>
                                    <p className="truncate text-[0.75rem] text-body/65">{property.agent.agency}</p>
                                    <p className="mt-1 flex items-center gap-1.5 text-[0.6875rem] text-neon/85">
                                        <CheckIcon className="h-3 w-3" />
                                        {t('prop.kycDeals', { deals: property.agent.deals })}
                                    </p>
                                </div>
                            </div>

                            <Divider className="my-4" />

                            <div className="grid grid-cols-2 gap-3 text-center">
                                <div className="rounded-[0.7rem] bg-white/[0.04] py-2.5">
                                    <p className="font-display text-[0.9375rem] font-semibold text-heading">
                                        {property.agent.rating}
                                    </p>
                                    <p className="text-[0.625rem] uppercase tracking-wider text-faint">{t('prop.rating')}</p>
                                </div>
                                <div className="rounded-[0.7rem] bg-white/[0.04] py-2.5">
                                    <p className="font-display text-[0.9375rem] font-semibold text-heading">
                                        {property.agent.response.replace('Under ', '<')}
                                    </p>
                                    <p className="text-[0.625rem] uppercase tracking-wider text-faint">{t('prop.response')}</p>
                                </div>
                            </div>

                            <div className="mt-4 space-y-2">
                                <Button size="lg" className="w-full">
                                    {t('prop.messageAgent')}
                                </Button>
                                <Button variant="secondary" size="lg" className="w-full" onClick={() => setOfferOpen(true)}>
                                    {t('prop.makeOffer')}
                                </Button>
                            </div>

                            <p className="mt-3 text-center text-[0.6875rem] text-faint">
                                {t('prop.chatNotice')}
                            </p>
                        </div>

                        <div className="glass rounded-[1.35rem] p-5">
                            <p className="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-faint">
                                {t('prop.priceBreakdown')}
                            </p>
                            <div className="mt-3 space-y-2 text-[0.8125rem]">
                                <div className="flex justify-between text-body/80">
                                    <span>{t('prop.listingPrice')}</span>
                                    <span className="text-heading">{format(property.price)}</span>
                                </div>
                                <div className="flex justify-between text-body/80">
                                    <span>{t('prop.fee')}</span>
                                    <span className="text-faint">{t('prop.included')}</span>
                                </div>
                            </div>
                            <Divider className="my-3" />
                            <div className="flex justify-between text-[0.875rem] font-semibold text-heading">
                                <span>{t('prop.youPay')}</span>
                                <span>{format(property.price)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Similar */}
                {similar.length > 0 && (
                    <section className="mt-20">
                        <SectionHeading
                            eyebrow={t('prop.nearby')}
                            title={t('prop.similarTitle')}
                            action={
                                <Link
                                    href="/properties"
                                    className="group flex items-center gap-2 rounded-full border border-white/12 bg-white/[0.04] px-4 py-2 text-[0.8125rem] font-medium text-heading transition-colors hover:border-white/25"
                                >
                                    {t('prop.browseAll')}
                                    <ArrowUpRightIcon className="h-4 w-4 text-body transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                                </Link>
                            }
                        />

                        <motion.div
                            variants={staggerParent}
                            initial="initial"
                            whileInView="animate"
                            viewport={{ once: true, margin: '-60px' }}
                            className="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            {similar.map((item) => (
                                <PropertyCard key={item.id} property={item} />
                            ))}
                        </motion.div>
                    </section>
                )}
            </div>

            <OfferModal open={offerOpen} property={property} onClose={() => setOfferOpen(false)} />

            {/* Sticky mobile CTA */}
            <div className="fixed inset-x-0 bottom-0 z-40 border-t border-white/10 bg-void/85 px-4 py-3 backdrop-blur-xl lg:hidden">
                <div className="flex items-center gap-3">
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-[0.625rem] uppercase tracking-wider text-faint">
                            {property.intent_label}
                        </p>
                        <p className="truncate font-display text-[1rem] font-semibold text-heading">
                            {format(property.price)}
                        </p>
                    </div>
                    <Button size="md" className="shrink-0">
                        {t('prop.messageAgent')}
                    </Button>
                </div>
            </div>
        </motion.div>
    );
}

PropertyShow.layout = (pageContent) => <SiteLayout>{pageContent}</SiteLayout>;
