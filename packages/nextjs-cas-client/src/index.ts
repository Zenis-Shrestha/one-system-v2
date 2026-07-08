/**
 * @module @cas-system/nextjs-cas-client
 * @description Public entry point for the Next.js CAS Client SDK.
 *
 * Re-exports the client-side provider, hooks, components, and shared types.
 * Server-only helpers live under `@cas-system/nextjs-cas-client/server`,
 * route handlers under `/handlers`, and middleware under `/middleware`.
 */

// Provider + context hook
export { CasProvider, useCasAuthContext } from './CasProvider';
export type { CasProviderProps } from './CasProvider';

// Hooks
export { useCasAuth } from './hooks/useCasAuth';
export { useCasUser } from './hooks/useCasUser';
export type { UseCasUserReturn } from './hooks/useCasUser';

// Components
export { CasLoginButton } from './components/CasLoginButton';
export type { CasLoginButtonProps } from './components/CasLoginButton';
export { CasProtectedRoute } from './components/CasProtectedRoute';
export type { CasProtectedRouteProps } from './components/CasProtectedRoute';

// Shared types
export type {
  CasConfig,
  CasServerConfig,
  CasUser,
  CasSessionData,
  CasMiddlewareConfig,
  CasAuthContext,
  CasHandlerConfig,
} from './types';
