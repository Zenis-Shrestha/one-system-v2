import { useContext } from 'react';
import { CasContext } from '../CasProvider';
import type { CasContextValue } from '../types';

/**
 * Hook that provides the full CAS authentication state and actions.
 *
 * Must be used within a `<CasProvider>`.
 *
 * @returns The current {@link CasContextValue} containing `user`,
 *   `isAuthenticated`, `isLoading`, `error`, `login`, `logout`,
 *   `hasRole`, and `hasAnyRole`.
 *
 * @throws {Error} If called outside of a `<CasProvider>`.
 *
 * @example
 * ```tsx
 * import { useCasAuth } from '@cas-system/react-cas-client';
 *
 * function Header() {
 *   const { user, isAuthenticated, login, logout } = useCasAuth();
 *
 *   if (!isAuthenticated) {
 *     return <button onClick={() => login()}>Sign in</button>;
 *   }
 *
 *   return (
 *     <div>
 *       Welcome, {user?.username}
 *       <button onClick={() => logout()}>Sign out</button>
 *     </div>
 *   );
 * }
 * ```
 */
export function useCasAuth(): CasContextValue {
  const context = useContext(CasContext);

  if (!context) {
    throw new Error(
      'useCasAuth() must be used within a <CasProvider>. ' +
        'Wrap your component tree with <CasProvider config={...}>.',
    );
  }

  return context;
}
