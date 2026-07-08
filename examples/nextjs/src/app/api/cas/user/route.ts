/**
 * Current user — GET /api/cas/user
 *
 * `createUserHandler` reads the app's signed session cookie and returns
 * `{ user }` (200) when authenticated, or `{ user: null }` (401) otherwise.
 * The client-side <CasProvider> calls this on mount to hydrate auth state.
 */
import { createUserHandler } from '@cas-system/nextjs-cas-client/handlers';

export const GET = createUserHandler();
