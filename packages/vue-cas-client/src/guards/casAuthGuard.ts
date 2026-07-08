import type { NavigationGuardWithThis, RouteLocationNormalized } from 'vue-router';
import type { CasGuardOptions, CasAuthContext } from '../types';
import { CAS_AUTH_KEY } from '../types';

/**
 * Augment Vue Router's `RouteMeta` interface so that TypeScript recognises
 * `meta.requiresAuth` and `meta.roles` on route definitions.
 */
declare module 'vue-router' {
  interface RouteMeta {
    /**
     * When `true`, the route requires an authenticated CAS session.
     */
    requiresAuth?: boolean;

    /**
     * Roles required to access this route. The user must have **all** of
     * the specified roles.
     */
    roles?: string[];
  }
}

/**
 * Factory function that creates a Vue Router `beforeEach` navigation guard
 * for CAS authentication.
 *
 * The guard inspects `meta.requiresAuth` (and optionally `meta.roles`) on
 * each route record. If the user is not authenticated, they are redirected
 * to the CAS login page (unless `redirectToLogin` is `false`).
 *
 * @param casContext - The CAS auth context (client + reactive state).
 *   You can obtain this by calling `inject(CAS_AUTH_KEY)` in your router
 *   setup, or by passing the context from the plugin.
 * @param options - Optional guard configuration.
 * @returns A `beforeEach` navigation guard function.
 *
 * @example
 * ```ts
 * // router/index.ts
 * import { createRouter, createWebHistory } from 'vue-router';
 * import { createCasAuthGuard, CAS_AUTH_KEY } from '@cas-system/vue-cas-client';
 *
 * export function setupRouter(app) {
 *   const router = createRouter({ ... });
 *
 *   // The guard needs access to the injected CAS context.
 *   // Because router guards run outside the component tree, we read
 *   // the context from the app instance.
 *   const casContext = app._context.provides[CAS_AUTH_KEY];
 *
 *   router.beforeEach(createCasAuthGuard(casContext, {
 *     redirectToLogin: true,
 *   }));
 *
 *   return router;
 * }
 * ```
 *
 * @example Route definition with meta
 * ```ts
 * const routes = [
 *   { path: '/',       component: Home },
 *   { path: '/login',  component: Login },
 *   {
 *     path: '/dashboard',
 *     component: Dashboard,
 *     meta: { requiresAuth: true },
 *   },
 *   {
 *     path: '/admin',
 *     component: Admin,
 *     meta: { requiresAuth: true, roles: ['admin'] },
 *   },
 * ];
 * ```
 */
export function createCasAuthGuard(
  casContext: CasAuthContext,
  options: CasGuardOptions = {},
): NavigationGuardWithThis<undefined> {
  const { redirectToLogin = true } = options;

  return (_to: RouteLocationNormalized) => {
    // Walk the matched route records — any record may declare `requiresAuth`.
    const requiresAuth = _to.matched.some(
      (record) => record.meta?.requiresAuth,
    );

    if (!requiresAuth) {
      return true; // Route is public — allow.
    }

    const { client, state } = casContext;

    // -- Check authentication --------------------------------------------
    if (!state.isAuthenticated) {
      if (redirectToLogin) {
        // Redirect to CAS login; after login the user comes back to the
        // originally requested path.
        const loginUrl =
          options.loginUrl ?? client.getLoginUrl(window.location.href);
        window.location.href = loginUrl;
        return false;
      }
      return false;
    }

    // -- Check roles ------------------------------------------------------
    // Merge guard-level roles with per-route roles.
    const requiredRoles: string[] = [
      ...(options.roles ?? []),
      ...(_to.meta?.roles ?? []),
    ];

    if (requiredRoles.length > 0) {
      const userRoles = state.user?.roles ?? [];
      const hasAll = requiredRoles.every((role) => userRoles.includes(role));

      if (!hasAll) {
        // Authenticated but missing required roles — reject.
        return false;
      }
    }

    return true;
  };
}
