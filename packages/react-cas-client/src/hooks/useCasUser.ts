import { useMemo } from 'react';
import { useCasAuth } from './useCasAuth';
import type { CasUser } from '../types';

/**
 * Return type for the {@link useCasUser} hook.
 */
export interface UseCasUserReturn {
  /** The currently authenticated user, or `null`. */
  user: CasUser | null;

  /** Whether a user session exists. */
  isAuthenticated: boolean;

  /** Whether the SDK is currently loading (e.g. validating a callback token). */
  isLoading: boolean;

  /**
   * Check if the user has a specific role.
   * @param role - The role name to check.
   */
  hasRole: (role: string) => boolean;

  /**
   * Check if the user has at least one of the given roles.
   * @param roles - The role names to check.
   */
  hasAnyRole: (roles: string[]) => boolean;

  /**
   * Check if the user has all of the given roles.
   * @param roles - The role names to check.
   */
  hasAllRoles: (roles: string[]) => boolean;
}

/**
 * Hook that returns just the user data and role-checking helpers.
 *
 * This is a convenience wrapper around {@link useCasAuth} for components
 * that only need to read user information without triggering login/logout.
 *
 * Must be used within a `<CasProvider>`.
 *
 * @returns An object with `user`, `isAuthenticated`, `isLoading`, and
 *   role-checking functions.
 *
 * @example
 * ```tsx
 * import { useCasUser } from '@cas-system/react-cas-client';
 *
 * function ProfileBadge() {
 *   const { user, hasRole } = useCasUser();
 *
 *   if (!user) return null;
 *
 *   return (
 *     <span>
 *       {user.username}
 *       {hasRole('admin') && ' 👑'}
 *     </span>
 *   );
 * }
 * ```
 */
export function useCasUser(): UseCasUserReturn {
  const { user, isAuthenticated, isLoading, hasRole, hasAnyRole } =
    useCasAuth();

  const hasAllRoles = useMemo(() => {
    return (roles: string[]): boolean => {
      if (!user?.roles) return false;
      return roles.every((role) => user.roles!.includes(role));
    };
  }, [user]);

  return useMemo(
    () => ({
      user,
      isAuthenticated,
      isLoading,
      hasRole,
      hasAnyRole,
      hasAllRoles,
    }),
    [user, isAuthenticated, isLoading, hasRole, hasAnyRole, hasAllRoles],
  );
}
