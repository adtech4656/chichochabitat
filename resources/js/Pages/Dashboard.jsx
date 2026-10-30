import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import SiteLayout from '../Layouts/SiteLayout';
import PropertyCard from '../Components/PropertyCard';
import { Avatar, Button, Divider, EmptyState, StatusPill } from '../Components/ui';
import {
    ArrowUpRightIcon,
    CheckIcon,
    HeartIcon,
    ShieldCheckIcon,
} from '../Components/Icons';
import { page, spring, staggerParent } from '../lib/motion';
import { useTranslation } from '../lib/i18n';
import { cn } from '../lib/utils';
import { useMoney } from '../lib/money';

const TABS = [
    { key: 'saved', labelKey: 'account.saved', icon: HeartIcon },
    { key: 'offers', labelKey: 'account.offers', icon: ArrowUpRightIcon },
    { key: 'chats', labelKey: 'account.chats', icon: ShieldCheckIcon },
    { key: 'profile', labelKey: 'account.profile', icon: CheckIcon },
];

export default function Dashboard({ saved = [], offers: initialOffers = [], chats: initialChats = [], customer }) {
    const { t, locale } = useTranslation();
    const { format } = useMoney();
    const dateLocale = locale === 'fr' ? 'fr-FR' : 'en-GB';
    const [tab, setTab] = useState('saved');
    const [offers, setOffers] = useState(initialOffers);
    const [chats, setChats] = useState(initialChats);
    const [activeChatId, setActiveChatId] = useState(
        initialChats.find((chat) => chat.status === 'closed')?.id ?? initialChats[0]?.id ?? null,
    );

    const activeChat = useMemo(
        () => chats.find((chat) => chat.id === activeChatId) ?? null,
        [chats, activeChatId],
    );

    /**
     * Demonstrates the acceptance rule from the brief: when an agent accepts,
     * the thread closes, disappears from the customer's chat list, and the
     * transaction record is preserved.
     */
    const previewAcceptance = (offer) => {
        const reference = `CCH-2026-${String(500 + offer.id).slice(-4)}`;
        const deal = {
            reference,
            closed: new Date().toLocaleString(dateLocale, {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }),
            terms: offer.property.intent === 'rent' ? t('account.termsLease') : t('account.termsSale'),
        };

        setOffers((current) =>
            current.map((item) => (item.id === offer.id ? { ...item, status: 'accepted', deal } : item)),
        );

        const chat = chats.find((item) => item.property.slug === offer.property.slug);

        if (chat) {
            setChats((current) =>
                current.map((item) =>
                    item.id === chat.id
                        ? { ...item, status: 'closed', closed_reason: t('account.offerAccepted'), deal, messages: [] }
                        : item,
                ),
            );
            setActiveChatId(chat.id);
            setTab('chats');
        }
    };

    const openDealChat = (offer) => {
        const chat = chats.find((item) => item.property.slug === offer.property.slug);
        if (chat) {
            setActiveChatId(chat.id);
        }
        setTab('chats');
    };

    const openChats = chats.filter((chat) => chat.status === 'open');
    const closedChats = chats.filter((chat) => chat.status === 'closed');
    const pendingOffers = offers.filter((offer) => offer.status === 'pending');

    return (
        <motion.div variants={page} initial="initial" animate="animate">
            <div className="mx-auto max-w-7xl px-6 pt-28 sm:pt-32">
                <div className="flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <p className="text-[0.6875rem] font-medium uppercase tracking-[0.18em] text-neon/80">
                            {t('account.title')}
                        </p>
                        <h1 className="mt-2.5 font-display text-[clamp(1.75rem,3.4vw,2.5rem)] font-semibold leading-[1.05] tracking-[-0.04em] text-heading">
                            {t('account.greeting', { name: customer.name.split(' ')[0] })}
                        </h1>
                        <p className="mt-2 text-[0.875rem] text-body/70">
                            {pendingOffers.length} offer{pendingOffers.length === 1 ? '' : 's'} awaiting a reply
                            <span className="mx-2 text-white/20">·</span>
                            {openChats.length} open chat{openChats.length === 1 ? '' : 's'}
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="hidden text-right sm:block">
                            <p className="text-[0.8125rem] font-medium text-heading">{customer.name}</p>
                            <p className="text-[0.6875rem] text-faint">Member since {customer.member_since}</p>
                        </div>
                        <Avatar initials={customer.initials} />
                    </div>
                </div>

                <div className="mt-9 grid gap-6 lg:grid-cols-[236px_1fr]">
                    {/* Sidebar */}
                    <aside>
                        <div className="glass sticky top-28 rounded-[1.35rem] p-3">
                            <nav className="space-y-1" aria-label="Account sections">
                                {TABS.map(({ key, labelKey, icon: Icon }) => (
                                    <button
                                        key={key}
                                        type="button"
                                        onClick={() => setTab(key)}
                                        aria-current={tab === key}
                                        className="relative flex w-full items-center gap-2.5 rounded-[0.8rem] px-3 py-2.5 text-left text-[0.8125rem] font-medium transition-colors"
                                    >
                                        {tab === key && (
                                            <motion.span
                                                layoutId="account-tab"
                                                transition={spring}
                                                className="absolute inset-0 rounded-[0.8rem] bg-white/[0.08] ring-1 ring-white/12"
                                            />
                                        )}
                                        <Icon
                                            className={cn(
                                                'relative h-4 w-4',
                                                tab === key ? 'text-neon' : 'text-body/55',
                                            )}
                                        />
                                        <span className={cn('relative', tab === key ? 'text-heading' : 'text-body/75')}>
                                            {t(labelKey)}
                                        </span>

                                        {key === 'offers' && pendingOffers.length > 0 && (
                                            <span className="relative ml-auto rounded-full bg-caution/15 px-1.5 py-0.5 text-[0.625rem] font-semibold text-caution">
                                                {pendingOffers.length}
                                            </span>
                                        )}
                                    </button>
                                ))}
                            </nav>

                            <Divider className="my-3" />

                            <Link
                                href="/agent/kyc"
                                className="group flex items-start gap-2.5 rounded-[0.8rem] bg-gradient-to-br from-neon/12 to-transparent p-3 ring-1 ring-neon/20 transition-colors hover:ring-neon/35"
                            >
                                <ShieldCheckIcon className="mt-0.5 h-4 w-4 shrink-0 text-neon" />
                                <span>
                                    <span className="block text-[0.8125rem] font-semibold text-heading">
                                        Become an agent
                                    </span>
                                    <span className="mt-0.5 flex items-center gap-1 text-[0.6875rem] text-body/60">
                                        Start KYC
                                        <ArrowUpRightIcon className="h-3 w-3 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                                    </span>
                                </span>
                            </Link>
                        </div>
                    </aside>

                    {/* Content */}
                    <div className="min-w-0">
                        <AnimatePresence mode="wait">
                            {tab === 'saved' && (
                                <motion.section
                                    key="saved"
                                    initial={{ opacity: 0, y: 14 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -10 }}
                                    transition={{ duration: 0.24, ease: [0.22, 1, 0.36, 1] }}
                                >
                                    <PanelHeading
                                        title={t('account.savedTitle')}
                                        meta={t('account.savedMeta', { count: saved.length })}
                                    />

                                    {saved.length === 0 ? (
                                        <EmptyState
                                            icon={HeartIcon}
                                            title={t('account.nothingSaved')}
                                            description={t('account.nothingSavedHelp')}
                                        />
                                    ) : (
                                        <motion.div
                                            variants={staggerParent}
                                            initial="initial"
                                            animate="animate"
                                            className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
                                        >
                                            {saved.filter(Boolean).map((property) => (
                                                <PropertyCard key={property.id} property={property} />
                                            ))}
                                        </motion.div>
                                    )}
                                </motion.section>
                            )}

                            {tab === 'offers' && (
                                <motion.section
                                    key="offers"
                                    initial={{ opacity: 0, y: 14 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -10 }}
                                    transition={{ duration: 0.24, ease: [0.22, 1, 0.36, 1] }}
                                    className="space-y-3"
                                >
                                    <PanelHeading
                                        title={t('account.offers')}
                                        meta={t('account.offersMeta', { count: offers.length })}
                                    />

                                    {offers.length === 0 && (
                                        <EmptyState
                                            icon={ArrowUpRightIcon}
                                            title={t('account.noOffers')}
                                            description={t('account.noOffersHelp')}
                                            action={
                                                <Button as="a" href="/properties" variant="secondary" size="sm" className="mt-2">
                                                    {t('account.browseProperties')}
                                                </Button>
                                            }
                                        />
                                    )}

                                    {offers.map((offer) => (
                                        <motion.article
                                            key={offer.id}
                                            layout
                                            transition={spring}
                                            className="glass rounded-[1.15rem] p-4"
                                        >
                                            <div className="flex flex-wrap items-center gap-4">
                                                <Link
                                                    href={offer.property.href}
                                                    className="h-16 w-24 shrink-0 overflow-hidden rounded-[0.7rem] bg-void-raised"
                                                >
                                                    <img
                                                        src={offer.property.image}
                                                        alt=""
                                                        loading="lazy"
                                                        className="h-full w-full object-cover"
                                                    />
                                                </Link>

                                                <div className="min-w-0 flex-1">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <Link
                                                            href={offer.property.href}
                                                            className="truncate font-display text-[0.9375rem] font-semibold text-heading hover:text-neon"
                                                        >
                                                            {offer.property.title}
                                                        </Link>
                                                        <StatusPill status={offer.status} />
                                                    </div>
                                                    <p className="mt-1 truncate text-[0.75rem] text-body/65">
                                                        {offer.property.location}
                                                        <span className="mx-1.5 text-white/20">·</span>
                                                        {offer.placed}
                                                    </p>
                                                </div>

                                                <div className="text-right">
                                                    <p className="text-[0.625rem] uppercase tracking-wider text-faint">
                                                        Your offer
                                                    </p>
                                                    <p className="mt-0.5 font-display text-[0.9375rem] font-semibold text-heading">
                                                        {format(offer.amount)}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-white/[0.07] pt-3">
                                                {offer.status === 'accepted' ? (
                                                    <>
                                                        <p className="flex items-center gap-1.5 text-[0.75rem] text-neon">
                                                            <CheckIcon className="h-3.5 w-3.5" />
                                                            {t('account.dealConfirmedChat')}
                                                        </p>
                                                        <Button size="sm" variant="secondary" onClick={() => openDealChat(offer)}>
                                                            {t('account.viewReceipt')}
                                                        </Button>
                                                    </>
                                                ) : (
                                                    <>
                                                        <p className="text-[0.75rem] text-body/60">
                                                            {t('account.awaiting', { name: offer.agent.name })}
                                                        </p>
                                                        <button
                                                            type="button"
                                                            onClick={() => previewAcceptance(offer)}
                                                            className="text-[0.75rem] font-medium text-neon/80 transition-colors hover:text-neon"
                                                        >
                                                            {t('account.previewAcceptance')}
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </motion.article>
                                    ))}
                                </motion.section>
                            )}

                            {tab === 'chats' && (
                                <motion.section
                                    key="chats"
                                    initial={{ opacity: 0, y: 14 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -10 }}
                                    transition={{ duration: 0.24, ease: [0.22, 1, 0.36, 1] }}
                                >
                                    <PanelHeading
                                        title={t('account.conversations')}
                                        meta={t('account.conversationsMeta', {
                                            open: openChats.length,
                                            archived: closedChats.length,
                                        })}
                                    />

                                    <div className="grid gap-4 lg:grid-cols-[280px_1fr]">
                                        {/* Thread list */}
                                        <div className="space-y-2">
                                            {chats.map((chat) => (
                                                <motion.button
                                                    key={chat.id}
                                                    layout
                                                    transition={spring}
                                                    type="button"
                                                    onClick={() => setActiveChatId(chat.id)}
                                                    className={cn(
                                                        'flex w-full items-center gap-3 rounded-[0.9rem] p-3 text-left transition-colors',
                                                        activeChatId === chat.id
                                                            ? 'bg-white/[0.07] ring-1 ring-white/15'
                                                            : 'hover:bg-white/[0.04]',
                                                    )}
                                                >
                                                    <Avatar initials={chat.agent.initials} size="sm" />
                                                    <span className="min-w-0 flex-1">
                                                        <span className="block truncate text-[0.8125rem] font-medium text-heading">
                                                            {chat.agent.name}
                                                        </span>
                                                        <span className="block truncate text-[0.6875rem] text-faint">
                                                            {chat.property.title}
                                                        </span>
                                                    </span>
                                                    {chat.status === 'closed' ? (
                                                        <CheckIcon className="h-4 w-4 shrink-0 text-neon" />
                                                    ) : (
                                                        chat.unread > 0 && (
                                                            <span className="grid h-5 min-w-5 shrink-0 place-items-center rounded-full bg-neon px-1 text-[0.625rem] font-bold text-void">
                                                                {chat.unread}
                                                            </span>
                                                        )
                                                    )}
                                                </motion.button>
                                            ))}
                                        </div>

                                        {/* Thread / receipt */}
                                        <div className="glass min-h-[26rem] rounded-[1.15rem] p-5">
                                            <AnimatePresence mode="wait">
                                                {activeChat && activeChat.status === 'closed' ? (
                                                    <DealReceipt
                                                        key={`deal-${activeChat.id}`}
                                                        chat={activeChat}
                                                    />
                                                ) : activeChat ? (
                                                    <motion.div
                                                        key={`thread-${activeChat.id}`}
                                                        initial={{ opacity: 0, y: 12 }}
                                                        animate={{ opacity: 1, y: 0 }}
                                                        exit={{ opacity: 0, y: -8 }}
                                                        transition={spring}
                                                        className="flex h-full flex-col"
                                                    >
                                                        <div className="flex items-center gap-3 border-b border-white/[0.07] pb-4">
                                                            <Avatar initials={activeChat.agent.initials} size="sm" />
                                                            <div className="min-w-0">
                                                                <p className="truncate text-[0.875rem] font-medium text-heading">
                                                                    {activeChat.agent.name}
                                                                </p>
                                                                <p className="truncate text-[0.6875rem] text-faint">
                                                                    {activeChat.property.title}
                                                                </p>
                                                            </div>
                                                            <StatusPill status="open" />
                                                        </div>

                                                        <div className="flex-1 space-y-3 py-4">
                                                            {activeChat.messages.map((message, index) => (
                                                                <div
                                                                    key={index}
                                                                    className={cn(
                                                                        'flex',
                                                                        message.from === 'me' ? 'justify-end' : 'justify-start',
                                                                    )}
                                                                >
                                                                    <div
                                                                        className={cn(
                                                                            'max-w-[80%] rounded-[1rem] px-3.5 py-2.5 text-[0.8125rem] leading-relaxed',
                                                                            message.from === 'me'
                                                                                ? 'bg-neon text-void'
                                                                                : 'bg-white/[0.06] text-body/90 ring-1 ring-white/8',
                                                                        )}
                                                                    >
                                                                        {message.text}
                                                                        <span
                                                                            className={cn(
                                                                                'mt-1 block text-[0.625rem]',
                                                                                message.from === 'me' ? 'text-void/60' : 'text-faint',
                                                                            )}
                                                                        >
                                                                            {message.at}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>

                                                        <div className="flex items-center gap-2 border-t border-white/[0.07] pt-4">
                                                            <input
                                                                type="text"
                                                                placeholder={t('account.messagePlaceholder')}
                                                                className="flex-1 rounded-full border border-white/10 bg-white/[0.04] px-4 py-2.5 text-[0.8125rem] text-heading outline-none placeholder:text-body/30 focus:border-neon"
                                                            />
                                                            <Button size="md">{t('account.send')}</Button>
                                                        </div>
                                                    </motion.div>
                                                ) : (
                                                    <EmptyState
                                                        key="no-chat"
                                                        title={t('account.noConversations')}
                                                        description={t('account.noConversationsHelp')}
                                                    />
                                                )}
                                            </AnimatePresence>
                                        </div>
                                    </div>
                                </motion.section>
                            )}

                            {tab === 'profile' && (
                                <motion.section
                                    key="profile"
                                    initial={{ opacity: 0, y: 14 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -10 }}
                                    transition={{ duration: 0.24, ease: [0.22, 1, 0.36, 1] }}
                                >
                                    <PanelHeading title={t('account.profile')} meta={t('account.profileMeta')} />

                                    <div className="glass rounded-[1.15rem] p-5">
                                        <div className="flex items-center gap-4">
                                            <Avatar initials={customer.initials} size="lg" />
                                            <div>
                                                <p className="font-display text-[1.0625rem] font-semibold text-heading">
                                                    {customer.name}
                                                </p>
                                                <p className="text-[0.75rem] text-body/65">
                                                    {t('account.verifiedSince', { date: customer.member_since })}
                                                </p>
                                            </div>
                                        </div>

                                        <Divider className="my-5" />

                                        <dl className="grid gap-4 sm:grid-cols-2">
                                            {[
                                                [t('auth.email'), customer.email],
                                                [t('auth.phone'), customer.phone],
                                                [t('common.language'), 'Français · English'],
                                                [t('common.currency'), 'XOF · USD · EUR'],
                                            ].map(([label, value]) => (
                                                <div key={label}>
                                                    <dt className="text-[0.625rem] uppercase tracking-[0.14em] text-faint">
                                                        {label}
                                                    </dt>
                                                    <dd className="mt-1 text-[0.875rem] text-heading">{value}</dd>
                                                </div>
                                            ))}
                                        </dl>
                                    </div>
                                </motion.section>
                            )}
                        </AnimatePresence>
                    </div>
                </div>
            </div>
        </motion.div>
    );
}

function PanelHeading({ title, meta }) {
    return (
        <div className="mb-4 flex items-baseline justify-between gap-3">
            <h2 className="font-display text-[1.125rem] font-semibold tracking-[-0.02em] text-heading">
                {title}
            </h2>
            <span className="text-[0.6875rem] uppercase tracking-[0.12em] text-faint">{meta}</span>
        </div>
    );
}

/**
 * The chat thread does not merely disappear when an offer is accepted — it
 * morphs into a permanent transaction record via a shared layout animation.
 */
function DealReceipt({ chat }) {
    const { format } = useMoney();
    const { t } = useTranslation();
    const { deal, property } = chat;

    return (
        <motion.div
            layout
            initial={{ opacity: 0, scale: 0.97 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={spring}
            className="relative flex h-full flex-col"
        >
            <div
                className="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full"
                style={{ background: 'radial-gradient(circle, rgba(102,187,106,0.22) 0%, transparent 70%)' }}
                aria-hidden="true"
            />

            <div className="relative flex flex-1 flex-col items-center justify-center py-6 text-center">
                <motion.span
                    initial={{ scale: 0.5, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ ...spring, delay: 0.08 }}
                    className="grid h-16 w-16 place-items-center rounded-full bg-neon/15 text-neon ring-1 ring-neon/35 neon-glow-soft"
                >
                    <CheckIcon className="h-7 w-7" />
                </motion.span>

                <motion.h3
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ ...spring, delay: 0.14 }}
                    className="mt-5 font-display text-[1.375rem] font-semibold tracking-[-0.03em] text-heading"
                >
                    {t('account.dealConfirmed')}
                </motion.h3>

                <motion.p
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ ...spring, delay: 0.18 }}
                    className="mt-1.5 max-w-xs text-[0.8125rem] leading-relaxed text-body/70"
                >
                    {t('account.dealCopy')}
                </motion.p>

                <motion.div
                    initial={{ opacity: 0, y: 14 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ ...spring, delay: 0.22 }}
                    className="mt-6 w-full max-w-sm rounded-[1rem] bg-white/[0.04] p-4 text-left ring-1 ring-white/10"
                >
                    <ReceiptRow label={t('receipt.reference')} value={deal.reference} mono />
                    <ReceiptRow label={t('receipt.property')} value={property.title} />
                    <ReceiptRow label={t('receipt.agreedPrice')} value={format(property.price)} />
                    <ReceiptRow label={t('receipt.terms')} value={deal.terms} />
                    <ReceiptRow label={t('receipt.closed')} value={deal.closed} last />
                </motion.div>

                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ ...spring, delay: 0.28 }}
                    className="mt-5 flex flex-wrap items-center justify-center gap-2"
                >
                    <Button size="sm" variant="secondary">
                        {t('receipt.download')}
                    </Button>
                    <Button size="sm" variant="outline" as="a" href="/properties">
                        {t('receipt.browseSimilar')}
                    </Button>
                </motion.div>

                <p className="mt-5 flex items-center gap-1.5 text-[0.6875rem] text-faint">
                    <ShieldCheckIcon className="h-3.5 w-3.5 text-neon/60" />
                    {t('receipt.auditLog')}
                </p>
            </div>
        </motion.div>
    );
}

function ReceiptRow({ label, value, mono = false, last = false }) {
    return (
        <div className={cn('flex items-baseline justify-between gap-4 py-1.5', !last && 'border-b border-white/[0.06]')}>
            <span className="text-[0.6875rem] uppercase tracking-[0.1em] text-faint">{label}</span>
            <span
                className={cn(
                    'text-right text-[0.8125rem] text-heading',
                    mono && 'font-mono text-[0.75rem] tracking-tight',
                )}
            >
                {value}
            </span>
        </div>
    );
}

Dashboard.layout = (pageContent) => <SiteLayout>{pageContent}</SiteLayout>;
