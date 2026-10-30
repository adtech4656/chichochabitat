import { forwardRef, useEffect, useId, useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import { AppleIcon, CloseIcon, EyeIcon, GoogleIcon, ShieldCheckIcon } from './Icons';
import { cn } from '../lib/utils';
import { modalPanel, spring } from '../lib/motion';
import { useTranslation } from '../lib/i18n';

/**
 * Flow 2 — Auth.
 * Dark glass modal over a blurred scrim. Fields sit on a near-black well and
 * bloom green on focus; the primary action carries the neon drop-shadow.
 *
 * Posts to the real /register and /login endpoints; validation errors are
 * surfaced inline from Inertia's shared error bag.
 */
export default function AuthModal({ open, mode = 'signin', onClose, onModeChange }) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
    });

    const [showPassword, setShowPassword] = useState(false);

    const panelRef = useRef(null);
    const firstFieldRef = useRef(null);
    const previouslyFocused = useRef(null);
    const titleId = useId();
    const reduceMotion = useReducedMotion();

    const isSignup = mode === 'signup';

    /* Lock scroll + restore focus + wire Escape while the dialog is open. */
    useEffect(() => {
        if (!open) return undefined;

        previouslyFocused.current = document.activeElement;
        const { overflow, paddingRight } = document.body.style;
        const scrollbar = window.innerWidth - document.documentElement.clientWidth;

        document.body.style.overflow = 'hidden';
        if (scrollbar > 0) document.body.style.paddingRight = `${scrollbar}px`;

        const focusTimer = window.setTimeout(() => firstFieldRef.current?.focus(), 90);

        const onKeyDown = (event) => {
            if (event.key === 'Escape') onClose?.();
        };
        window.addEventListener('keydown', onKeyDown);

        return () => {
            window.clearTimeout(focusTimer);
            window.removeEventListener('keydown', onKeyDown);
            document.body.style.overflow = overflow;
            document.body.style.paddingRight = paddingRight;
            previouslyFocused.current?.focus?.();
        };
    }, [open, onClose]);

    /* Reset transient state whenever the dialog or tab changes. */
    useEffect(() => {
        if (!open) return;

        clearErrors();
        const timer = window.setTimeout(() => firstFieldRef.current?.focus(), 90);

        return () => window.clearTimeout(timer);
    }, [open, mode, clearErrors]);

    const setField = (key) => (event) => setData(key, event.target.value);

    const handleSubmit = (event) => {
        event.preventDefault();

        post(isSignup ? '/register' : '/login', {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                reset();
                onClose?.();
            },
        });
    };

    const switchMode = (next) => {
        if (next === mode) return;
        reset();
        clearErrors();
        onModeChange?.(next);
    };

    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    className="fixed inset-0 z-[110] flex items-end justify-center p-4 sm:items-center sm:p-6"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2 }}
                >
                    {/* Scrim */}
                    <motion.div
                        className="absolute inset-0 bg-void-deep/70 backdrop-blur-xl"
                        onMouseDown={(event) => {
                            if (event.target === event.currentTarget) onClose?.();
                        }}
                        aria-hidden="true"
                    />

                    <motion.div
                        ref={panelRef}
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby={titleId}
                        variants={reduceMotion ? undefined : modalPanel}
                        initial="initial"
                        animate="animate"
                        exit="exit"
                        className="glass-strong relative w-full max-w-[27rem] overflow-hidden rounded-[1.75rem] p-6 shadow-[0_40px_120px_-30px_rgba(0,0,0,0.95)] sm:p-7"
                    >
                        {/* Ambient green bloom + top-edge highlight */}
                        <div
                            className="pointer-events-none absolute -top-24 left-1/2 h-48 w-[26rem] -translate-x-1/2 rounded-full"
                            style={{
                                background:
                                    'radial-gradient(circle, rgba(102,187,106,0.20) 0%, transparent 68%)',
                            }}
                            aria-hidden="true"
                        />
                        <div className="pointer-events-none absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-white/35 to-transparent" />

                        <button
                            type="button"
                            onClick={onClose}
                            aria-label={t('common.close')}
                            className="absolute right-4 top-4 grid h-8 w-8 place-items-center rounded-full text-body/70 transition-colors hover:bg-white/10 hover:text-heading"
                        >
                            <CloseIcon className="h-4 w-4" />
                        </button>

                        <div className="relative">
                            <p className="text-[0.625rem] font-medium uppercase tracking-[0.2em] text-neon/80">
                                {t('auth.secure')}
                            </p>
                            <h2
                                id={titleId}
                                className="mt-2 font-display text-[1.5rem] font-semibold tracking-[-0.03em] text-heading"
                            >
                                {isSignup ? t('auth.signupTitle') : t('auth.signinTitle')}
                            </h2>
                            <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-body/80">
                                {isSignup ? t('auth.signupCopy') : t('auth.signinCopy')}
                            </p>

                            {/* Segmented control */}
                            <div className="mt-6 grid grid-cols-2 gap-1 rounded-[0.9rem] bg-white/[0.04] p-1 ring-1 ring-white/10">
                                {[
                                    { key: 'signin', label: t('auth.signinTab') },
                                    { key: 'signup', label: t('auth.signupTab') },
                                ].map((tab) => (
                                    <button
                                        key={tab.key}
                                        type="button"
                                        onClick={() => switchMode(tab.key)}
                                        className="relative rounded-[0.7rem] px-3 py-2 text-[0.8125rem] font-medium transition-colors"
                                    >
                                        {mode === tab.key && (
                                            <motion.span
                                                layoutId="auth-tab-pill"
                                                className="absolute inset-0 rounded-[0.7rem] bg-white/[0.09] ring-1 ring-white/15"
                                                transition={spring}
                                            />
                                        )}
                                        <span
                                            className={cn(
                                                'relative',
                                                mode === tab.key ? 'text-heading' : 'text-body/70',
                                            )}
                                        >
                                            {tab.label}
                                        </span>
                                    </button>
                                ))}
                            </div>

                            <form onSubmit={handleSubmit} className="mt-5 space-y-3.5">
                                {isSignup && (
                                    <Field
                                        ref={firstFieldRef}
                                        id="auth-name"
                                        label={t('auth.fullName')}
                                        autoComplete="name"
                                        value={data.name}
                                        onChange={setField('name')}
                                        required
                                    />
                                )}

                                <Field
                                    ref={isSignup ? undefined : firstFieldRef}
                                    id="auth-email"
                                    label={t('auth.email')}
                                    type="email"
                                    autoComplete="email"
                                    value={data.email}
                                    onChange={setField('email')}
                                    required
                                />

                                {isSignup && (
                                    <Field
                                        id="auth-phone"
                                        label={t('auth.phone')}
                                        type="tel"
                                        autoComplete="tel"
                                        value={data.phone}
                                        onChange={setField('phone')}
                                        placeholder="07 00 00 00 00"
                                        prefix="+225"
                                    />
                                )}

                                <div>
                                    <label
                                        htmlFor="auth-password"
                                        className="mb-1.5 block text-[0.75rem] font-medium text-body/80"
                                    >
                                        {t('auth.password')}
                                    </label>

                                    <div className="group relative">
                                        <input
                                            id="auth-password"
                                            type={showPassword ? 'text' : 'password'}
                                            autoComplete={isSignup ? 'new-password' : 'current-password'}
                                            value={data.password}
                                            onChange={setField('password')}
                                            required
                                            minLength={8}
                                            placeholder="••••••••"
                                            className="w-full rounded-[0.8rem] border border-white/10 bg-white/[0.04] px-3.5 py-3 pr-11 text-[0.9375rem] text-heading outline-none transition-[border-color,box-shadow,background-color] duration-300 placeholder:text-body/30 hover:bg-white/[0.055] focus:border-neon focus:bg-white/[0.06] focus:shadow-[0_0_0_3px_rgba(102,187,106,0.18),0_0_22px_-4px_rgba(102,187,106,0.55)]"
                                        />

                                        <button
                                            type="button"
                                            onClick={() => setShowPassword((current) => !current)}
                                            aria-label={showPassword ? t('auth.hidePassword') : t('auth.showPassword')}
                                            className="absolute right-2.5 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-full text-body/60 transition-colors hover:text-heading"
                                        >
                                            <EyeIcon open={!showPassword} className="h-4 w-4" />
                                        </button>
                                    </div>

                                    {isSignup && (
                                        <p className="mt-1.5 text-[0.6875rem] text-faint">
                                            {t('auth.minPassword')}
                                        </p>
                                    )}
                                </div>

                                {!isSignup && (
                                    <div className="flex items-center justify-between pt-0.5">
                                        <label className="flex cursor-pointer items-center gap-2 text-[0.75rem] text-body/75">
                                            <input
                                                type="checkbox"
                                                className="h-3.5 w-3.5 rounded border-white/20 bg-white/5 accent-neon"
                                            />
                                            {t('auth.remember')}
                                        </label>
                                        <button
                                            type="button"
                                            className="text-[0.75rem] font-medium text-neon/90 transition-colors hover:text-neon"
                                        >
                                            {t('auth.forgot')}
                                        </button>
                                    </div>
                                )}

                                {Object.keys(errors).length > 0 && (
                                    <p role="alert" className="rounded-[0.7rem] bg-danger/10 px-3 py-2 text-[0.75rem] text-danger ring-1 ring-danger/25">
                                        {Object.values(errors)[0]}
                                    </p>
                                )}

                                <motion.button
                                    type="submit"
                                    disabled={processing}
                                    whileHover={{ scale: processing ? 1 : 1.02 }}
                                    whileTap={{ scale: processing ? 1 : 0.98 }}
                                    transition={spring}
                                    className={cn(
                                        'mt-1 flex w-full items-center justify-center gap-2 rounded-[0.85rem] bg-neon px-4 py-3.5 text-[0.875rem] font-semibold text-void transition-shadow duration-300',
                                        'hover:neon-glow disabled:cursor-not-allowed disabled:opacity-70',
                                    )}
                                >
                                    {processing && (
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-void/30 border-t-void" />
                                    )}
                                    {processing ? t('auth.wait') : isSignup ? t('auth.createAccount') : t('auth.signinTab')}
                                </motion.button>
                            </form>

                            <div className="my-5 flex items-center gap-3">
                                <span className="h-px flex-1 bg-white/10" />
                                <span className="text-[0.6875rem] uppercase tracking-[0.14em] text-faint">{t('auth.or')}</span>
                                <span className="h-px flex-1 bg-white/10" />
                            </div>

                            <div className="grid grid-cols-2 gap-2.5">
                                <SocialButton icon={<AppleIcon className="h-4 w-4" />} label="Apple" />
                                <SocialButton icon={<GoogleIcon className="h-4 w-4" />} label="Google" />
                            </div>

                            <p className="mt-5 flex items-start gap-2 text-[0.6875rem] leading-relaxed text-faint">
                                <ShieldCheckIcon className="mt-px h-3.5 w-3.5 shrink-0 text-neon/70" />
                                <span>{t('auth.securityNote')}</span>
                            </p>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}

const Field = forwardRef(function Field(
    { id, label, prefix, className, ...props },
    ref,
) {
    return (
        <div>
            <label htmlFor={id} className="mb-1.5 block text-[0.75rem] font-medium text-body/80">
                {label}
            </label>

            <div
                className={cn(
                    'flex items-center rounded-[0.8rem] border border-white/10 bg-white/[0.04] transition-[border-color,box-shadow,background-color] duration-300',
                    'hover:bg-white/[0.055]',
                    'focus-within:border-neon focus-within:bg-white/[0.06] focus-within:shadow-[0_0_0_3px_rgba(102,187,106,0.18),0_0_22px_-4px_rgba(102,187,106,0.55)]',
                )}
            >
                {prefix && (
                    <span className="border-r border-white/10 py-3 pl-3.5 pr-2.5 text-[0.875rem] text-body/70">
                        {prefix}
                    </span>
                )}

                <input
                    id={id}
                    ref={ref}
                    className={cn(
                        'w-full bg-transparent px-3.5 py-3 text-[0.9375rem] text-heading outline-none placeholder:text-body/30',
                        prefix && 'pl-3',
                        className,
                    )}
                    {...props}
                />
            </div>
        </div>
    );
});

function SocialButton({ icon, label }) {
    return (
        <motion.button
            type="button"
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
            transition={spring}
            className="flex items-center justify-center gap-2 rounded-[0.8rem] border border-white/10 bg-white/[0.03] px-3 py-2.5 text-[0.8125rem] font-medium text-heading transition-colors hover:bg-white/[0.07]"
        >
            {icon}
            {label}
        </motion.button>
    );
}
