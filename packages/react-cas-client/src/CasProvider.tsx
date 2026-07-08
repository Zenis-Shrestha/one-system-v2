import {
  createContext,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from 'react';
import { CasClient } from './cas-client';
import type { CasConfig, CasContextValue, CasUser } from './types';

/**
 * React Context for CAS authentication state and actions.
 *
 * Access this through the {@link useCasAuth} or {@link useCasUser} hooks
 * rather than consuming it directly.
 */
export const CasContext = createContext<CasContextValue | null>(null);
CasContext.displayName = 'CasContext';

/**
 * Props for the {@link CasProvider} component.
 */
export interface CasProviderProps {
  /** CAS configuration (server URL, client ID, etc.). */
  config: CasConfig;

  /** Child components that need access to CAS auth state. */
  children: ReactNode;

  /**
   * Callback invoked after successful authentication.
   * Useful for analytics, redirects, or syncing with app state.
   */
  onAuthSuccess?: (user: CasUser) => void;

  /**
   * Callback invoked when an authentication error occurs.
   * Useful for error reporting or showing notifications.
   */
  onAuthError?: (error: string) => void;
}

/**
 * CAS Authentication Provider.
 *
 * Wrap your application (or a subtree) in `<CasProvider>` to enable CAS
 * authentication. On mount the provider will:
 *
 * 1. Check `sessionStorage` for an existing user session.
 * 2. If the URL contains a `?token=` parameter (i.e. the CAS redirect
 *    callback), automatically validate it via the backend and establish
 *    a session.
 *
 * @example
 * ```tsx
 * import { CasProvider } from '@cas-system/react-cas-client';
 *
 * function App() {
 *   return (
 *     <CasProvider
 *       config={{
 *         serverUrl: 'https://cas.example.com',
 *         clientId: 'my-app',
 *         callbackUrl: 'https://myapp.com/auth/callback',
 *         backendValidateUrl: '/api/auth/validate',
 *       }}
 *       onAuthSuccess={(user) => console.log('Logged in:', user.username)}
 *       onAuthError={(err) => console.error('Auth failed:', err)}
 *     >
 *       <YourApp />
 *     </CasProvider>
 *   );
 * }
 * ```
 */
export function CasProvider({
  config,
  children,
  onAuthSuccess,
  onAuthError,
}: CasProviderProps) {
  const [user, setUser] = useState<CasUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Stable reference to the CAS client — recreated only when config changes
  const client = useMemo(() => new CasClient(config), [config]);

  // Guard to ensure the callback is handled at most once (React strict-mode safe)
  const callbackHandled = useRef(false);

  // -------------------------------------------------------------------------
  // Initialisation
  // -------------------------------------------------------------------------
  useEffect(() => {
    let cancelled = false;

    async function init() {
      try {
        // 1. If there is a ?token= in the URL, handle the callback
        const token = client.extractTokenFromUrl();

        if (token && !callbackHandled.current) {
          callbackHandled.current = true;

          const validatedUser = await client.handleCallback();

          if (!cancelled && validatedUser) {
            setUser(validatedUser);
            setError(null);
            onAuthSuccess?.(validatedUser);
          }
        } else {
          // 2. Otherwise, try to restore a session from sessionStorage
          const existingUser = client.getUser();

          if (!cancelled && existingUser) {
            setUser(existingUser);
          }
        }
      } catch (err) {
        if (!cancelled) {
          const message =
            err instanceof Error ? err.message : 'Authentication failed';
          setError(message);
          setUser(null);
          onAuthError?.(message);
        }
      } finally {
        if (!cancelled) {
          setIsLoading(false);
        }
      }
    }

    init();

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [client]);

  // -------------------------------------------------------------------------
  // Actions
  // -------------------------------------------------------------------------

  const login = useCallback(
    (returnUrl?: string) => {
      client.login(returnUrl);
    },
    [client],
  );

  const logout = useCallback(
    async (redirectUrl?: string) => {
      await client.logout(redirectUrl);
      setUser(null);
      setError(null);
    },
    [client],
  );

  const hasRole = useCallback(
    (role: string): boolean => {
      return user?.roles?.includes(role) ?? false;
    },
    [user],
  );

  const hasAnyRole = useCallback(
    (roles: string[]): boolean => {
      if (!user?.roles) return false;
      return roles.some((role) => user.roles!.includes(role));
    },
    [user],
  );

  // -------------------------------------------------------------------------
  // Context value
  // -------------------------------------------------------------------------

  const value = useMemo<CasContextValue>(
    () => ({
      user,
      isAuthenticated: user !== null,
      isLoading,
      error,
      login,
      logout,
      hasRole,
      hasAnyRole,
    }),
    [user, isLoading, error, login, logout, hasRole, hasAnyRole],
  );

  return <CasContext.Provider value={value}>{children}</CasContext.Provider>;
}
