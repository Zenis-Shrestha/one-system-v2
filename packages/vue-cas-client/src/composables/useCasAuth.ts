import { computed, inject, ref, type ComputedRef, type Ref } from 'vue';
import type { CasUser, CasAuthContext } from '../types';
import { CAS_AUTH_KEY } from '../types';

/**
 * Return type of {@link useCasAuth}.
 */
export interface UseCasAuthReturn {
  /** Reactive reference to the authenticated user (or `null`). */
  user: ComputedRef<CasUser | null>;

  /** `true` when a valid session exists. */
  isAuthenticated: ComputedRef<boolean>;

  /** `true` while an async auth operation is in-flight. */
  isLoading: ComputedRef<boolean>;

  /** The last error message, or `null`. */
  error: ComputedRef<string | null>;

  /**
   * Redirect to the CAS SSO login page.
   *
   * @param returnUrl - Optional post-login redirect URL.
   */
  login: (returnUrl?: string) => void;

  /**
   * Log out the current user.
   *
   * @param redirectUrl - Optional redirect URL after logout.
   */
  logout: (redirectUrl?: string) => Promise<void>;

  /**
   * Handle the CAS callback — extract, validate, and persist the token.
   *
   * @returns The authenticated user.
   */
  handleCallback: () => Promise<CasUser>;
}

/**
 * Primary Composition API composable for CAS authentication.
 *
 * Must be called inside a component that is a descendant of an `app` where
 * `CasPlugin` has been installed.
 *
 * @example
 * ```vue
 * <script setup lang="ts">
 * import { useCasAuth } from '@cas-system/vue-cas-client';
 *
 * const { user, isAuthenticated, login, logout } = useCasAuth();
 * </script>
 *
 * <template>
 *   <div v-if="isAuthenticated">
 *     Hello, {{ user?.username }}!
 *     <button @click="logout()">Logout</button>
 *   </div>
 *   <div v-else>
 *     <button @click="login()">Login with CAS</button>
 *   </div>
 * </template>
 * ```
 *
 * @throws If called outside the `CasPlugin` provider scope.
 */
export function useCasAuth(): UseCasAuthReturn {
  const ctx = inject<CasAuthContext>(CAS_AUTH_KEY);

  if (!ctx) {
    throw new Error(
      '[vue-cas-client] useCasAuth() was called outside the CasPlugin provider scope. ' +
        'Make sure you have called `app.use(CasPlugin, { ... })` before using this composable.',
    );
  }

  const { client, state } = ctx;

  // -- Reactive computed refs derived from the shared state ---------------
  const user = computed(() => state.user);
  const isAuthenticated = computed(() => state.isAuthenticated);
  const isLoading = computed(() => state.isLoading);
  const error = computed(() => state.error);

  // -- Actions -----------------------------------------------------------

  /**
   * Redirect to the CAS login page.
   */
  const login = (returnUrl?: string): void => {
    client.login(returnUrl);
  };

  /**
   * Log the user out and clear session.
   */
  const logout = async (redirectUrl?: string): Promise<void> => {
    state.isLoading = true;
    try {
      await client.logout(redirectUrl);
      state.user = null;
      state.isAuthenticated = false;
      state.error = null;
    } catch (err: unknown) {
      state.error = err instanceof Error ? err.message : 'Logout failed.';
    } finally {
      state.isLoading = false;
    }
  };

  /**
   * Process the CAS callback.
   */
  const handleCallback = async (): Promise<CasUser> => {
    state.isLoading = true;
    state.error = null;

    try {
      const user = await client.handleCallback();
      state.user = user;
      state.isAuthenticated = true;
      return user;
    } catch (err: unknown) {
      const message =
        err instanceof Error ? err.message : 'Authentication failed.';
      state.error = message;
      state.isAuthenticated = false;
      state.user = null;
      throw err;
    } finally {
      state.isLoading = false;
    }
  };

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    login,
    logout,
    handleCallback,
  };
}
