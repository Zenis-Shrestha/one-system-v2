import { reactive } from 'vue';
import type { App, Plugin } from 'vue';
import { CasClient } from './cas-client';
import type { CasAuthState, CasPluginOptions } from './types';
import { CAS_AUTH_KEY } from './types';

/**
 * Vue 3 plugin that installs the CAS authentication layer.
 *
 * Usage:
 * ```ts
 * import { createApp } from 'vue';
 * import { CasPlugin } from '@cas-system/vue-cas-client';
 *
 * const app = createApp(App);
 *
 * app.use(CasPlugin, {
 *   serverUrl: 'https://cas.example.com',
 *   clientId: 'my-app',
 *   callbackUrl: 'https://my-app.com/auth/callback',
 *   backendValidateUrl: '/api/auth/validate',
 * });
 *
 * app.mount('#app');
 * ```
 *
 * After installation every descendant component can use `useCasAuth()` or
 * `useCasUser()` composables to access the reactive auth state.
 */
export const CasPlugin: Plugin<[CasPluginOptions]> = {
  install(app: App, options: CasPluginOptions) {
    // -- Validate required options ----------------------------------------
    if (!options?.serverUrl || !options?.clientId) {
      throw new Error(
        '[vue-cas-client] CasPlugin requires at least "serverUrl" and "clientId" in the options.',
      );
    }

    // -- Create client & reactive state -----------------------------------
    const client = new CasClient(options);

    const state = reactive<CasAuthState>({
      user: client.getUser(),
      isAuthenticated: client.isAuthenticated(),
      isLoading: false,
      error: null,
    });

    // -- Provide context to component tree --------------------------------
    app.provide(CAS_AUTH_KEY, { client, state });

    // -- Auto-handle callback ---------------------------------------------
    const autoHandle = options.autoHandleCallback !== false;

    if (autoHandle && typeof window !== 'undefined') {
      const token = client.extractTokenFromUrl();

      if (token) {
        state.isLoading = true;

        client
          .handleCallback()
          .then((user) => {
            state.user = user;
            state.isAuthenticated = true;
            state.error = null;
          })
          .catch((err: unknown) => {
            state.error =
              err instanceof Error ? err.message : 'Authentication failed.';
            state.isAuthenticated = false;
            state.user = null;
          })
          .finally(() => {
            state.isLoading = false;
          });
      }
    }
  },
};
