'use client';

/**
 * @module @cas-system/nextjs-cas-client/hooks/useCasUser
 * @description Client-side hook focused on the current CAS user and
 * role-based access control helpers.
 *
 * @example
 * ```tsx
 * 'use client';
 * import { useCasUser } from '@cas-system/nextjs-cas-client';
 *
 * export function AdminPanel() {
 *   const { user, hasRole, hasAnyRole } = useCasUser();
 *
 *   if (!hasRole('admin')) {
 *     return <p>Access denied</p>;
 *   }
 *
 *   return <h1>Welcome admin {user?.username}</h1>;
 * }
 * ```
 */

import { useCallback, useMemo } from 'react';
import type { CasUser } from '../types';
import { useCasAuthContext } from '../CasProvider';

/** Return type of {@link useCasUser}. */
export interface UseCasUserReturn {
  /** The current user, or `null` when not authenticated. */
  user: CasUser | null;

  /** Whether the initial session fetch is still loading. */
  isLoading: boolean;

  /** Whether a valid session exists. */
  isAuthenticated: boolean;

  /**
   * Check whether the current user has **all** of the specified roles.
   *
   * @param roles - Required roles.
   * @returns `true` if the user possesses every role (or the list is empty).
   */
  hasRole: (...roles: string[]) => boolean;

  /**
   * Check whether the current user has **at least one** of the specified roles.
   *
   * @param roles - Roles to check.
   * @returns `true` if the user possesses any one role.
   */
  hasAnyRole: (...roles: string[]) => boolean;

  /** All roles assigned to the current user (empty array when unauthenticated). */
  roles: string[];
}

/**
 * Hook for accessing the current CAS user and performing role checks.
 *
 * Must be used inside a `<CasProvider>`.
 */
export function useCasUser(): UseCasUserReturn {
  const { user, isLoading, isAuthenticated } = useCasAuthContext();

  const roles = useMemo(() => user?.roles ?? [], [user]);

  const hasRole = useCallback(
    (...required: string[]) => {
      if (!user?.roles) return required.length === 0;
      return required.every((r) => user.roles!.includes(r));
    },
    [user],
  );

  const hasAnyRole = useCallback(
    (...candidates: string[]) => {
      if (!user?.roles) return false;
      return candidates.some((r) => user.roles!.includes(r));
    },
    [user],
  );

  return { user, isLoading, isAuthenticated, hasRole, hasAnyRole, roles };
}
