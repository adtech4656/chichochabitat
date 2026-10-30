import { createContext, useCallback, useContext, useMemo, useState } from 'react';

const AuthModalContext = createContext(null);

/**
 * Auth modal state lives above the page so any surface (nav, hero CTA,
 * property card) can open it without prop-drilling.
 */
export function AuthModalProvider({ children }) {
    const [state, setState] = useState({ open: false, mode: 'signin' });

    const openAuth = useCallback((mode = 'signin') => {
        setState({ open: true, mode });
    }, []);

    const closeAuth = useCallback(() => {
        setState((current) => ({ ...current, open: false }));
    }, []);

    const value = useMemo(
        () => ({ ...state, openAuth, closeAuth }),
        [state, openAuth, closeAuth],
    );

    return <AuthModalContext.Provider value={value}>{children}</AuthModalContext.Provider>;
}

export function useAuthModal() {
    const context = useContext(AuthModalContext);

    if (!context) {
        throw new Error('useAuthModal must be used within <AuthModalProvider>.');
    }

    return context;
}
