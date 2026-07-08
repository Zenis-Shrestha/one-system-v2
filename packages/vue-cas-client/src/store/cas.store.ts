import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { CasClient } from '../cas-client';
import type { CasConfig, CasUser } from '../types';

/**
 * Pinia store for CAS authentication state management.
 *
 * This is an **optional** alternative to the plugin + composable approach.
 * It can be used standalone or alongside the Vue plugin.
 *
 * > **Note** — This store requires `pinia` to be installed.
 *
 * @example
 * ```ts
 * import { useCasStore } from '@cas-system/vue-cas-client';
 *
 * const auth = useCasStore();
 *
 * // Initialize once (e.g. in App.vue setup)
 * auth.init({
 *   serverUrl: 'https://cas.example.com',
 *   clientId: 'my-app',
 *   backendValidateUrl: '/api/auth/validate',
 * });
 *
 * // Actions
 * auth.login();
 * await auth.handleCallback();
 * await auth.logout();
 * ```
 */
export const useCasStore = defineStore('cas-auth', () => {
  // -----------------------------------------------------------------------
  // State
  // -----------------------------------------------------------------------

  /** The authenticated user, or `null`. */
  const user = ref<CasUser | null>(null);

  /** The current JWT token, or `null`. */
  const token = ref<string | null>(null);

  /** Whether the user is authenticated. */
  const isAuthenticated = ref(false);

  /** Whether an async auth operation is in-flight. */
  const isLoading = ref(false);

  /** Last error message, or `null`. */
  const error = ref<string | null>(null);

  /** @internal The underlying CAS client instance. */
  let client: CasClient | null = null;

  // -----------------------------------------------------------------------
  // Getters
  // -----------------------------------------------------------------------

  /** The currently authenticated user (alias for `user`). */
  const currentUser = computed(() => user.value);

  /**
   * Returns a function to check whether the user has a specific role.
   *
   * @example
   * ```ts
   * if (auth.hasRole('admin')) { ... }
   * ```
   */
  const hasRole = computed(() => {
    return (role: string): boolean => {
      return user.value?.roles?.includes(role) ?? false;
    };
  });

  /**
   * Returns a function to check whether the user has **at least one** of
   * the given roles.
   */
  const hasAnyRole = computed(() => {
    return (roles: string[]): boolean => {
      if (!user.value?.roles) return false;
      return roles.some((r) => user.value!.roles!.includes(r));
    };
  });

  // -----------------------------------------------------------------------
  // Actions
  // -----------------------------------------------------------------------

  /**
   * Initialise the store with CAS configuration.
   *
   * Must be called once before any other action. Creates the underlying
   * `CasClient` and hydrates state from `sessionStorage`.
   *
   * @param config - CAS configuration.
   */
  function init(config: CasConfig): void {
    client = new CasClient(config);
    // Hydrate from sessionStorage
    user.value = client.getUser();
    token.value = client.getToken();
    isAuthenticated.value = client.isAuthenticated();
  }

  /**
   * Ensure the client has been initialised.
   *
   * @internal
   */
  function requireClient(): CasClient {
    if (!client) {
      throw new Error(
        '[vue-cas-client] CAS store has not been initialised. Call `init()` first.',
      );
    }
    return client;
  }

  /**
   * Redirect to the CAS login page.
   *
   * @param returnUrl - Optional post-login redirect URL.
   */
  function login(returnUrl?: string): void {
    requireClient().login(returnUrl);
  }

  /**
   * Handle the CAS callback — validate token and persist session.
   */
  async function handleCallback(): Promise<CasUser> {
    const c = requireClient();
    isLoading.value = true;
    error.value = null;

    try {
      const validatedUser = await c.handleCallback();
      user.value = validatedUser;
      token.value = c.getToken();
      isAuthenticated.value = true;
      return validatedUser;
    } catch (err: unknown) {
      error.value =
        err instanceof Error ? err.message : 'Authentication failed.';
      isAuthenticated.value = false;
      user.value = null;
      token.value = null;
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Log out the current user.
   *
   * @param redirectUrl - Optional redirect URL after logout.
   */
  async function logout(redirectUrl?: string): Promise<void> {
    const c = requireClient();
    isLoading.value = true;

    try {
      await c.logout(redirectUrl);
      user.value = null;
      token.value = null;
      isAuthenticated.value = false;
      error.value = null;
    } catch (err: unknown) {
      error.value = err instanceof Error ? err.message : 'Logout failed.';
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Re-check auth state from `sessionStorage`.
   *
   * Useful after navigating between pages or when you suspect the session
   * may have changed externally.
   */
  function checkAuth(): void {
    const c = requireClient();
    user.value = c.getUser();
    token.value = c.getToken();
    isAuthenticated.value = c.isAuthenticated();
  }

  return {
    // State
    user,
    token,
    isAuthenticated,
    isLoading,
    error,

    // Getters
    currentUser,
    hasRole,
    hasAnyRole,

    // Actions
    init,
    login,
    handleCallback,
    logout,
    checkAuth,
  };
});
