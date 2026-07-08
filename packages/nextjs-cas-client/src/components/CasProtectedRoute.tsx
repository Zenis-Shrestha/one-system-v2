'use client';

/**
 * @module @cas-system/nextjs-cas-client/components/CasProtectedRoute
 * @description Client component that guards its children behind CAS
 * authentication.  Shows a loading state while the session is being
 * fetched and redirects to login when no valid session exists.
 *
 * @example
 * ```tsx
 * import { CasProtectedRoute } from '@cas-system/nextjs-cas-client';
 *
 * export default function SecretPage() {
 *   return (
 *     <CasProtectedRoute>
 *       <h1>Secret Content</h1>
 *     </CasProtectedRoute>
 *   );
 * }
 * ```
 */

import React, { useEffect } from 'react';
import { useCasAuthContext } from '../CasProvider';

/** Props for {@link CasProtectedRoute}. */
export interface CasProtectedRouteProps {
  children: React.ReactNode;

  /**
   * Optional list of required roles.  The user must possess **all** of
   * these roles to see the content.
   */
  roles?: string[];

  /**
   * Custom loading element shown while the session is being fetched.
   * @default A simple "Loading…" paragraph.
   */
  loadingFallback?: React.ReactNode;

  /**
   * Custom element shown when the user is authenticated but lacks the
   * required roles.
   * @default A simple "Access denied" paragraph.
   */
  unauthorizedFallback?: React.ReactNode;

  /**
   * URL to redirect to when not authenticated.
   * If omitted the provider's `login()` action is invoked instead.
   */
  redirectTo?: string;
}

/**
 * Client component that only renders its children when the user is
 * authenticated (and optionally has the required roles).
 */
export function CasProtectedRoute({
  children,
  roles,
  loadingFallback,
  unauthorizedFallback,
  redirectTo,
}: CasProtectedRouteProps) {
  const { user, isLoading, isAuthenticated, login } = useCasAuthContext();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      if (redirectTo) {
        window.location.href = redirectTo;
      } else {
        login(window.location.pathname + window.location.search);
      }
    }
  }, [isLoading, isAuthenticated, login, redirectTo]);

  // Still loading
  if (isLoading) {
    return <>{loadingFallback ?? <p>Loading…</p>}</>;
  }

  // Not authenticated — will redirect in useEffect
  if (!isAuthenticated) {
    return <>{loadingFallback ?? <p>Redirecting to login…</p>}</>;
  }

  // Role check
  if (roles && roles.length > 0) {
    const userRoles = user?.roles ?? [];
    const hasRequiredRoles = roles.every((r) => userRoles.includes(r));
    if (!hasRequiredRoles) {
      return (
        <>{unauthorizedFallback ?? <p>Access denied — insufficient permissions.</p>}</>
      );
    }
  }

  return <>{children}</>;
}
