import { useState } from 'react';
import { motion } from 'framer-motion';
import { ArrowUpRightIcon, HeartIcon, MapPinIcon } from './Icons';
import { cn } from '../lib/utils';
import { useMoney } from '../lib/money';
import { useTranslation } from '../lib/i18n';
import { spring, staggerChild } from '../lib/motion';

/**
 * Flow 1 — Property card.
 * Glass surface, lazy-loaded imagery with a shimmer hand-off, and a spring
 * hover lift (scale 1.02) shared by every interactive surface in the product.
 */
export default function PropertyCard({ property, priority = false, onSave }) {
    const { format } = useMoney();
    const { t } = useTranslation();
    const [imageLoaded, setImageLoaded] = useState(false);
    const [saved, setSaved] = useState(Boolean(property.saved));

    const toggleSave = (event) => {
        event.preventDefault();
        event.stopPropagation();
        setSaved((current) => !current);
        onSave?.(property, !saved);
    };

    return (
        <motion.article
            variants={staggerChild}
            whileHover={{ scale: 1.02, y: -4 }}
            transition={spring}
            className="group glass relative flex flex-col overflow-hidden rounded-[1.5rem] will-change-transform hover:border-white/[0.18]"
        >
            <a href={property.href ?? '#'} className="flex flex-1 flex-col focus-visible:rounded-[1.5rem]">
                <div className="relative aspect-[4/3] overflow-hidden bg-void-raised">
                    {/* Shimmer until the decoded bitmap is painted. */}
                    {!imageLoaded && <div className="absolute inset-0 shimmer-surface" aria-hidden="true" />}

                    <img
                        src={property.image}
                        alt=""
                        loading={priority ? 'eager' : 'lazy'}
                        decoding="async"
                        fetchPriority={priority ? 'high' : 'auto'}
                        onLoad={() => setImageLoaded(true)}
                        className={cn(
                            'h-full w-full object-cover transition-[transform,opacity] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]',
                            imageLoaded ? 'scale-100 opacity-100' : 'scale-[1.04] opacity-0',
                            'group-hover:scale-[1.05]',
                        )}
                    />

                    {/* Legibility scrim for the badge row */}
                    <div className="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-void/70 to-transparent" />

                    <div className="absolute left-4 top-4 flex items-center gap-1.5 rounded-full bg-void/55 px-2.5 py-1 text-[0.6875rem] font-medium tracking-[0.01em] text-heading backdrop-blur-md ring-1 ring-white/15">
                        <span className="relative flex h-1.5 w-1.5">
                            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-neon opacity-70" />
                            <span className="relative inline-flex h-1.5 w-1.5 rounded-full bg-neon" />
                        </span>
                        {t('prop.verified')}
                    </div>

                    <motion.button
                        type="button"
                        onClick={toggleSave}
                        whileHover={{ scale: 1.08 }}
                        whileTap={{ scale: 0.92 }}
                        transition={spring}
                        aria-label={saved ? t('prop.unsave') : t('prop.save')}
                        aria-pressed={saved}
                        className={cn(
                            'absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-void/55 backdrop-blur-md ring-1 transition-colors',
                            saved
                                ? 'text-neon ring-neon/50'
                                : 'text-heading/80 ring-white/15 hover:text-heading',
                        )}
                    >
                        <HeartIcon filled={saved} className="h-[1.05rem] w-[1.05rem]" />
                    </motion.button>

                    {/* Bottom fade so the photo dissolves into the glass body */}
                    <div className="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-void/80 to-transparent" />
                </div>

                <div className="flex flex-1 flex-col p-5">
                    <p className="flex items-center gap-1.5 text-[0.75rem] text-faint">
                        <MapPinIcon className="h-3.5 w-3.5" />
                        {property.location}
                    </p>

                    <h3 className="mt-2 line-clamp-1 font-display text-[1.0625rem] font-semibold tracking-[-0.02em] text-heading">
                        {property.title}
                    </h3>

                    <p className="mt-1.5 text-[0.8125rem] text-body">
                        {property.bedrooms} {t('prop.bedsShort')}
                        <span className="mx-1.5 text-white/20">·</span>
                        {property.bathrooms} {t('prop.bathsShort')}
                        <span className="mx-1.5 text-white/20">·</span>
                        {property.area} m²
                    </p>

                    <div className="mt-auto flex items-end justify-between gap-3 pt-5">
                        <div>
                            <p className="text-[0.6875rem] uppercase tracking-[0.14em] text-faint">
                                {property.intent_label ?? property.intent ?? t('prop.sale')}
                            </p>
                            <p className="mt-1 font-display text-[1.0625rem] font-semibold tracking-[-0.01em] text-heading">
                                {format(property.price)}
                            </p>
                        </div>

                        <span className="grid h-10 w-10 place-items-center rounded-full bg-white/[0.06] text-heading ring-1 ring-white/10 transition-all duration-300 group-hover:bg-neon group-hover:text-void group-hover:ring-neon group-hover:neon-glow">
                            <ArrowUpRightIcon className="h-[1.05rem] w-[1.05rem]" />
                        </span>
                    </div>
                </div>
            </a>
        </motion.article>
    );
}
