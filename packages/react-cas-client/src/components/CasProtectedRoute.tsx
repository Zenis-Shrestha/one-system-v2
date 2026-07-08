import type { ReactNode } from 'react';
import { useCasAuth } from '../hooks/useCasAuth';

/**
 * Props for the {@link CasProtectedRoute} component.
 */
export interface CasProtectedRouteProps {
  /** The content to render when the user is authenticated (and authorised). */
  children: ReactNode;

  /**
   * Component to render while the auth state is loading (e.g. during token
   * validation). Defaults to `null` (renders nothing).
   */
  fallback?: ReactNode;

  /**
   * Roles required to access this route. If specified, the user must have
   * **at least one** of these roles to see the children.
   */
  roles?: string[];

  /**
   * Component to render when the user is authenticated but lacks the
   * required roles. If omitted, the user is redirected to the CAS login page.
   */
  unauthorizedComponent?: ReactNode;
}

/**
 * Protects a route (or any component subtree) behind CAS authentication.
 *
 * - If the user is **not authenticated**, they are automatically redirected
 *   to the CAS SSO login page.
 * - If **roles** are specified and the user does not have any of them,
 *   either `unauthorizedComponent` is rendered or the user is redirected.
 * - While auth state is loading, `fallback` is rendered (default: nothing).
 *
 * @example
 * ```tsx
 * import { CasProtectedRoute } from '@cas-system/react-cas-client';
 *
 * // Basic protection
 * <CasProtectedRoute fallback={<Spinner />}>
 *   <Dashboard />
 * </CasProtectedRoute>
 *
 * // Role-based protection
 * <CasProtectedRoute
 *   roles={['admin', 'manager']}
 *   unauthorizedComponent={<ForbiddenPage />}
 *   fallback={<Spinner />}
 * >
 *   <AdminPanel />
 * </CasProtectedRoute>
 * ```
 */
export function CasProtectedRoute({
  children,
  fallback = null,
  roles,
  unauthorizedComponent,
}: CasProtectedRouteProps) {
  const { isAuthenticated, isLoading, login, hasAnyRole } = useCasAuth();

  // Still resolving auth state — show fallback
  if (isLoading) {
    return <>{fallback}</>;
  }

  // Not authenticated — redirect to CAS login
  if (!isAuthenticated) {
    login();
    return <>{fallback}</>;
  }

  // Authenticated but missing required roles
  if (roles && roles.length > 0 && !hasAnyRole(roles)) {
    if (unauthorizedComponent) {
      return <>{unauthorizedComponent}</>;
    }
    // No custom unauthorized view — redirect to login as a fallback
    login();
    return <>{fallback}</>;
  }

  // Authenticated and authorised
  return <>{children}</>;
}
