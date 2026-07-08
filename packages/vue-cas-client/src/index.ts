/**
 * @module @cas-system/vue-cas-client
 *
 * Vue 3 CAS (Central Authentication System) Client SDK.
 *
 * Provides a complete authentication layer for Vue 3 applications that
 * integrate with a CAS SSO server:
 *
 * - **Plugin** — app-level installation via `app.use(CasPlugin, config)`.
 * - **Composables** — `useCasAuth()` and `useCasUser()` for component-level
 *   reactive auth state.
 * - **Router Guard** — `createCasAuthGuard()` to protect routes.
 * - **Pinia Store** — `useCasStore()` for Pinia-based state management.
 * - **Component** — `<CasProtectedView>` for slot-based access control.
 *
 * All exports are named for optimal tree-shaking.
 *
 * @packageDocumentation
 */

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------
export type {
  CasConfig,
  CasUser,
  CasAuthState,
  CasPluginOptions,
  CasGuardOptions,
  CasAuthContext,
} from './types';

export { CAS_AUTH_KEY } from './types';

// ---------------------------------------------------------------------------
// Core client
// ---------------------------------------------------------------------------
export { CasClient } from './cas-client';

// ---------------------------------------------------------------------------
// Vue plugin
// ---------------------------------------------------------------------------
export { CasPlugin } from './plugin';

// ---------------------------------------------------------------------------
// Composables
// ---------------------------------------------------------------------------
export { useCasAuth } from './composables/useCasAuth';
export type { UseCasAuthReturn } from './composables/useCasAuth';

export { useCasUser } from './composables/useCasUser';
export type { UseCasUserReturn } from './composables/useCasUser';

// ---------------------------------------------------------------------------
// Router guard
// ---------------------------------------------------------------------------
export { createCasAuthGuard } from './guards/casAuthGuard';

// ---------------------------------------------------------------------------
// Pinia store (optional — requires pinia)
// ---------------------------------------------------------------------------
export { useCasStore } from './store/cas.store';

// ---------------------------------------------------------------------------
// Components
// ---------------------------------------------------------------------------
export { default as CasProtectedView } from './components/CasProtectedView.vue';
