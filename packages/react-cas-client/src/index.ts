/**
 * @module @cas-system/react-cas-client
 * @description React SDK for CAS (Central Authentication System) integration.
 *
 * Provides hooks, components, and a context provider for seamless SSO
 * authentication with a CAS server.
 *
 * @example
 * ```tsx
 * import {
 *   CasProvider,
 *   useCasAuth,
 *   CasProtectedRoute,
 * } from '@cas-system/react-cas-client';
 * ```
 */

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------
export type {
  CasConfig,
  CasUser,
  CasAuthState,
  CasAuthActions,
  CasContextValue,
} from './types';

// ---------------------------------------------------------------------------
// Core client
// ---------------------------------------------------------------------------
export { CasClient } from './cas-client';

// ---------------------------------------------------------------------------
// React Context & Provider
// ---------------------------------------------------------------------------
export { CasProvider, CasContext } from './CasProvider';
export type { CasProviderProps } from './CasProvider';

// ---------------------------------------------------------------------------
// Hooks
// ---------------------------------------------------------------------------
export { useCasAuth } from './hooks/useCasAuth';
export { useCasUser } from './hooks/useCasUser';
export type { UseCasUserReturn } from './hooks/useCasUser';

// ---------------------------------------------------------------------------
// Components
// ---------------------------------------------------------------------------
export { CasProtectedRoute } from './components/CasProtectedRoute';
export type { CasProtectedRouteProps } from './components/CasProtectedRoute';

export { CasLoginButton } from './components/CasLoginButton';
export type { CasLoginButtonProps } from './components/CasLoginButton';
