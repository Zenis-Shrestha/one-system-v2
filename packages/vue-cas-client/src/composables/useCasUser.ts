import { computed, inject, type ComputedRef } from 'vue';
import type { CasUser, CasAuthContext } from '../types';
import { CAS_AUTH_KEY } from '../types';

/**
 * Return type of {@link useCasUser}.
 */
export interface UseCasUserReturn {
  /** Reactive reference to the authenticated user (or `null`). */
  user: ComputedRef<CasUser | null>;

  /** Reactive list of the user's roles. Empty array when unauthenticated. */
  roles: ComputedRef<string[]>;

  /**
   * Check if the user has a specific role.
   *
   * @param role - Role name to check.
   * @returns Reactive `ComputedRef<boolean>`.
   */
  hasRole: (role: string) => ComputedRef<boolean>;

  /**
   * Check if the user has **at least one** of the given roles.
   *
   * @param roles - Array of role names.
   * @returns Reactive `ComputedRef<boolean>`.
   */
  hasAnyRole: (roles: string[]) => ComputedRef<boolean>;

  /**
   * Check if the user has **all** of the given roles.
   *
   * @param roles - Array of role names.
   * @returns Reactive `ComputedRef<boolean>`.
   */
  hasAllRoles: (roles: string[]) => ComputedRef<boolean>;
}

/**
 * User-focused composable providing reactive access to the current user
 * and convenient role-checking helpers.
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useCasUser } from '@cas-system/vue-cas-client';
 *
 * const { user, roles, hasRole, hasAnyRole } = useCasUser();
 *
 * const isAdmin = hasRole('admin');
 * const canEdit = hasAnyRole(['editor', 'admin']);
 * </script>
 *
 * <template>
 *   <p v-if="user">Logged in as {{ user.username }}</p>
 *   <AdminPanel v-if="isAdmin" />
 * </template>
 * ```
 *
 * @throws If called outside the `CasPlugin` provider scope.
 */
export function useCasUser(): UseCasUserReturn {
  const ctx = inject<CasAuthContext>(CAS_AUTH_KEY);

  if (!ctx) {
    throw new Error(
      '[vue-cas-client] useCasUser() was called outside the CasPlugin provider scope. ' +
        'Make sure you have called `app.use(CasPlugin, { ... })` before using this composable.',
    );
  }

  const { state } = ctx;

  const user = computed(() => state.user);

  const roles = computed<string[]>(() => state.user?.roles ?? []);

  const hasRole = (role: string): ComputedRef<boolean> =>
    computed(() => roles.value.includes(role));

  const hasAnyRole = (targetRoles: string[]): ComputedRef<boolean> =>
    computed(() => targetRoles.some((r) => roles.value.includes(r)));

  const hasAllRoles = (targetRoles: string[]): ComputedRef<boolean> =>
    computed(() => targetRoles.every((r) => roles.value.includes(r)));

  return {
    user,
    roles,
    hasRole,
    hasAnyRole,
    hasAllRoles,
  };
}
