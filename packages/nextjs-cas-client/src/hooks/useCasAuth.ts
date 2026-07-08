'use client';

/**
 * @module @cas-system/nextjs-cas-client/hooks/useCasAuth
 * @description Client-side hook for accessing CAS authentication state and
 * actions (login, logout, refresh).
 *
 * @example
 * ```tsx
 * 'use client';
 * import { useCasAuth } from '@cas-system/nextjs-cas-client';
 *
 * export function Navbar() {
 *   const { user, isAuthenticated, login, logout } = useCasAuth();
 *
 *   return isAuthenticated ? (
 *     <button onClick={logout}>Sign out ({user?.username})</button>
 *   ) : (
 *     <button onClick={() => login()}>Sign in</button>
 *   );
 * }
 * ```
 */

import type { CasAuthContext } from '../types';
import { useCasAuthContext } from '../CasProvider';

/**
 * Access CAS authentication state and actions.
 *
 * This is a convenience re-export of the context hook.  Must be used inside
 * a `<CasProvider>`.
 *
 * @returns The full {@link CasAuthContext}.
 */
export function useCasAuth(): CasAuthContext {
  return useCasAuthContext();
}
