'use client';

/**
 * @module @cas-system/nextjs-cas-client/CasProvider
 * @description Client-side React context provider that manages CAS
 * authentication state.  Fetches the current session from
 * `/api/cas/user` on mount and exposes login / logout / refresh actions.
 *
 * @example
 * ```tsx
 * // app/layout.tsx
 * import { CasProvider } from '@cas-system/nextjs-cas-client';
 *
 * export default function RootLayout({ children }: { children: React.ReactNode }) {
 *   return (
 *     <html>
 *       <body>
 *         <CasProvider>{children}</CasProvider>
 *       </body>
 *     </html>
 *   );
 * }
 * ```
 */

import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import type { CasAuthContext, CasUser } from './types';

// ---------------------------------------------------------------------------
// Context
// ---------------------------------------------------------------------------

const CasContext = createContext<CasAuthContext | undefined>(undefined);

// ---------------------------------------------------------------------------
// Provider props
// ---------------------------------------------------------------------------

/** Props accepted by {@link CasProvider}. */
export interface CasProviderProps {
  children: React.ReactNode;

  /**
   * Custom endpoint that returns the current user JSON.
   * @default '/api/cas/user'
   */
  userEndpoint?: string;

  /**
   * Custom endpoint for logout.
   * @default '/api/cas/logout'
   */
  logoutEndpoint?: string;

  /**
   * CAS login URL (client-side redirect).
   * When omitted the provider builds it from `casServerUrl` and `casClientId`.
   */
  loginUrl?: string;

  /** CAS server URL — used to build the login redirect when `loginUrl` is not provided. */
  casServerUrl?: string;

  /** CAS client ID — used to build the login redirect when `loginUrl` is not provided. */
  casClientId?: string;

  /** Callback URL — used to build the login redirect when `loginUrl` is not provided. */
  casCallbackUrl?: string;
}

// ---------------------------------------------------------------------------
// Provider component
// ---------------------------------------------------------------------------

/**
 * Client-side authentication provider.
 *
 * Wrap your application (typically in `app/layout.tsx`) with this provider to
 * make CAS auth state available to all client components via the
 * {@link useCasAuth} and {@link useCasUser} hooks.
 */
export function CasProvider({
  children,
  userEndpoint = '/api/cas/user',
  logoutEndpoint = '/api/cas/logout',
  loginUrl: loginUrlProp,
  casServerUrl,
  casClientId,
  casCallbackUrl,
}: CasProviderProps) {
  const [user, setUser] = useState<CasUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  // -----------------------------------------------------------------------
  // Fetch current session
  // -----------------------------------------------------------------------

  const fetchUser = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    try {
      const res = await fetch(userEndpoint, {
        credentials: 'same-origin',
        cache: 'no-store',
      });
      if (res.ok) {
        const data = (await res.json()) as { user: CasUser };
        setUser(data.user ?? null);
      } else {
        setUser(null);
      }
    } catch (err) {
      setUser(null);
      setError(
        err instanceof Error ? err : new Error('Failed to fetch CAS session'),
      );
    } finally {
      setIsLoading(false);
    }
  }, [userEndpoint]);

  useEffect(() => {
    void fetchUser();
  }, [fetchUser]);

  // -----------------------------------------------------------------------
  // Actions
  // -----------------------------------------------------------------------

  const login = useCallback(
    (returnUrl?: string) => {
      if (loginUrlProp) {
        const url = new URL(loginUrlProp);
        if (returnUrl) url.searchParams.set('returnUrl', returnUrl);
        window.location.href = url.toString();
        return;
      }

      if (casServerUrl && casClientId) {
        const callback =
          casCallbackUrl ??
          `${window.location.origin}/api/cas/callback`;

        const callbackWithReturn = new URL(callback);
        if (returnUrl) callbackWithReturn.searchParams.set('returnUrl', returnUrl);

        const params = new URLSearchParams({
          client_id: casClientId,
          response_type: 'token',
          redirect_uri: callbackWithReturn.toString(),
        });
        window.location.href = `${casServerUrl.replace(/\/+$/, '')}/sso/login?${params.toString()}`;
        return;
      }

      // Fallback — redirect to a local login page
      const url = new URL('/login', window.location.origin);
      if (returnUrl) url.searchParams.set('returnUrl', returnUrl);
      window.location.href = url.toString();
    },
    [loginUrlProp, casServerUrl, casClientId, casCallbackUrl],
  );

  const logout = useCallback(async () => {
    try {
      await fetch(logoutEndpoint, {
        method: 'POST',
        credentials: 'same-origin',
      });
    } catch {
      // Ignore — cookie might already be gone
    }
    setUser(null);
    window.location.href = '/';
  }, [logoutEndpoint]);

  const refresh = useCallback(async () => {
    await fetchUser();
  }, [fetchUser]);

  // -----------------------------------------------------------------------
  // Memoised context value
  // -----------------------------------------------------------------------

  const value = useMemo<CasAuthContext>(
    () => ({
      user,
      isLoading,
      isAuthenticated: !!user,
      error,
      login,
      logout,
      refresh,
    }),
    [user, isLoading, error, login, logout, refresh],
  );

  return <CasContext.Provider value={value}>{children}</CasContext.Provider>;
}

// ---------------------------------------------------------------------------
// Hook (also re-exported from hooks/)
// ---------------------------------------------------------------------------

/**
 * Access the CAS auth context.  Must be called inside a `<CasProvider>`.
 * @throws If used outside of a `CasProvider`.
 */
export function useCasAuthContext(): CasAuthContext {
  const ctx = useContext(CasContext);
  if (!ctx) {
    throw new Error(
      'useCasAuthContext must be used within a <CasProvider>. ' +
        'Wrap your app in <CasProvider> in your root layout.',
    );
  }
  return ctx;
}
