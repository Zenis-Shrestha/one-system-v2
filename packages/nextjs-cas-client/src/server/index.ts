/**
 * @module @cas-system/nextjs-cas-client/server
 * @description Server-only entry point for the Next.js CAS Client SDK.
 *
 * Exposes the {@link CasClient} (token validation / generation / logout) and
 * the cookie-based session helpers.  These modules require the
 * `clientSecret` / cookie signing secret and must never run in the browser.
 */

export { CasClient } from './cas-client';
export {
  getCasSession,
  setCasSession,
  clearCasSession,
  withCasAuth,
  getCasSessionFromRequest,
} from './auth';

export type {
  CasConfig,
  CasServerConfig,
  CasUser,
  CasSessionData,
  CasHandlerConfig,
} from '../types';
