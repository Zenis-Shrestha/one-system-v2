/**
 * @module @cas-system/nextjs-cas-client/server/auth
 * @description Server-side session helpers for reading / writing CAS session
 * data to Next.js cookies.
 *
 * The session is stored as a base-64 encoded JSON blob (signed with HMAC)
 * inside an `HttpOnly`, `Secure`, `SameSite=Lax` cookie.  This gives us
 * tamper-proof cookies without requiring any external crypto library.
 *
 * **Security model:**
 * - The cookie is `HttpOnly` — JavaScript in the browser cannot read it.
 * - A separate HMAC signature cookie prevents tampering.
 * - Token validation always happens server-side via {@link CasClient}.
 */

import type { ReadonlyRequestCookies } from 'next/dist/server/web/spec-extension/adapters/request-cookies';
import type { CasUser, CasSessionData } from '../types';
import { NextRequest, NextResponse } from 'next/server';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

/** Name of the cookie that stores the session payload. */
const SESSION_COOKIE = 'cas_session';

/** Name of the cookie that stores the HMAC signature of the payload. */
const SIGNATURE_COOKIE = 'cas_session_sig';

/** Secret used for HMAC signing — falls back to a build-time constant. */
function getSigningSecret(): string {
  const secret = process.env.CAS_COOKIE_SECRET ?? process.env.CAS_CLIENT_SECRET;
  if (!secret) {
    throw new Error(
      '[CasAuth] Neither CAS_COOKIE_SECRET nor CAS_CLIENT_SECRET environment variable is set. ' +
        'One of these is required for cookie signing.',
    );
  }
  return secret;
}

// ---------------------------------------------------------------------------
// Internal crypto helpers
// ---------------------------------------------------------------------------

/** @internal */
async function signPayload(payload: string, secret: string): Promise<string> {
  const encoder = new TextEncoder();
  const key = await crypto.subtle.importKey(
    'raw',
    encoder.encode(secret),
    { name: 'HMAC', hash: 'SHA-256' },
    false,
    ['sign'],
  );
  const sig = await crypto.subtle.sign('HMAC', key, encoder.encode(payload));
  return btoa(String.fromCharCode(...new Uint8Array(sig)));
}

/** @internal */
async function verifyPayload(
  payload: string,
  signature: string,
  secret: string,
): Promise<boolean> {
  const expected = await signPayload(payload, secret);
  // Constant-time-ish comparison (good enough for HMAC output).
  if (expected.length !== signature.length) return false;
  let mismatch = 0;
  for (let i = 0; i < expected.length; i++) {
    mismatch |= expected.charCodeAt(i) ^ signature.charCodeAt(i);
  }
  return mismatch === 0;
}

// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

/**
 * Read the CAS session from Next.js **request** cookies.
 *
 * Works in Server Components, Route Handlers, and middleware — anywhere
 * you have access to the Next.js `cookies()` helper.
 *
 * @param cookies - The Next.js `cookies()` read-only store.
 * @returns The session data, or `null` if no valid session exists.
 *
 * @example
 * ```ts
 * import { cookies } from 'next/headers';
 * import { getCasSession } from '@cas-system/nextjs-cas-client/server';
 *
 * export default async function DashboardPage() {
 *   const session = await getCasSession(cookies());
 *   if (!session) redirect('/login');
 *   return <h1>Hello {session.user.username}</h1>;
 * }
 * ```
 */
export async function getCasSession(
  cookies: ReadonlyRequestCookies,
): Promise<CasSessionData | null> {
  try {
    const sessionCookie = cookies.get(SESSION_COOKIE);
    const sigCookie = cookies.get(SIGNATURE_COOKIE);
    if (!sessionCookie?.value || !sigCookie?.value) return null;

    const secret = getSigningSecret();
    const valid = await verifyPayload(
      sessionCookie.value,
      sigCookie.value,
      secret,
    );
    if (!valid) {
      console.warn('[CasAuth] Cookie signature mismatch — session rejected.');
      return null;
    }

    const decoded = atob(sessionCookie.value);
    const data = JSON.parse(decoded) as CasSessionData;
    return data;
  } catch {
    return null;
  }
}

/**
 * Write the CAS session into Next.js **response** cookies.
 *
 * @param cookies - The Next.js `cookies()` store (must be writable — only
 *                  available inside Route Handlers and Server Actions).
 * @param user    - The authenticated user.
 * @param token   - The raw JWT token.
 *
 * @example
 * ```ts
 * import { cookies } from 'next/headers';
 * import { setCasSession } from '@cas-system/nextjs-cas-client/server';
 *
 * await setCasSession(cookies(), user, token);
 * ```
 */
export async function setCasSession(
  cookies: ReadonlyRequestCookies,
  user: CasUser,
  token: string,
): Promise<void> {
  const data: CasSessionData = { user, token, createdAt: Date.now() };
  const payload = btoa(JSON.stringify(data));
  const secret = getSigningSecret();
  const sig = await signPayload(payload, secret);

  const cookieOptions = {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax' as const,
    path: '/',
    maxAge: 60 * 60 * 24 * 7, // 7 days
  };

  (cookies as any).set(SESSION_COOKIE, payload, cookieOptions);
  (cookies as any).set(SIGNATURE_COOKIE, sig, cookieOptions);
}

/**
 * Delete the CAS session cookies.
 *
 * @param cookies - The Next.js `cookies()` store.
 *
 * @example
 * ```ts
 * import { cookies } from 'next/headers';
 * import { clearCasSession } from '@cas-system/nextjs-cas-client/server';
 *
 * clearCasSession(cookies());
 * ```
 */
export function clearCasSession(cookies: ReadonlyRequestCookies): void {
  (cookies as any).delete(SESSION_COOKIE);
  (cookies as any).delete(SIGNATURE_COOKIE);
}

// ---------------------------------------------------------------------------
// HOF: withCasAuth
// ---------------------------------------------------------------------------

/** Typed Next.js App Router route handler signature. */
type RouteHandler = (
  req: NextRequest,
  ctx?: { params?: Record<string, string> },
) => Promise<NextResponse | Response> | NextResponse | Response;

/** A route handler that receives the authenticated user as a 3rd argument. */
type AuthenticatedHandler = (
  req: NextRequest,
  ctx: { params?: Record<string, string> },
  user: CasUser,
) => Promise<NextResponse | Response> | NextResponse | Response;

/**
 * Higher-order function that wraps a Next.js App Router route handler,
 * automatically extracting and verifying the CAS session and injecting the
 * `CasUser` into the handler.
 *
 * If no valid session exists, responds with `401 Unauthorized`.
 *
 * @param handler - The protected route handler.
 * @returns A standard Next.js route handler.
 *
 * @example
 * ```ts
 * // app/api/admin/route.ts
 * import { withCasAuth } from '@cas-system/nextjs-cas-client/server';
 *
 * export const GET = withCasAuth(async (req, ctx, user) => {
 *   return NextResponse.json({ message: `Hello ${user.username}` });
 * });
 * ```
 */
export function withCasAuth(handler: AuthenticatedHandler): RouteHandler {
  return async (req: NextRequest, ctx?: { params?: Record<string, string> }) => {
    // In App Router route handlers, we can read cookies directly from the request.
    const sessionCookie = req.cookies.get(SESSION_COOKIE);
    const sigCookie = req.cookies.get(SIGNATURE_COOKIE);

    if (!sessionCookie?.value || !sigCookie?.value) {
      return NextResponse.json(
        { error: 'Unauthorized' },
        { status: 401 },
      );
    }

    try {
      const secret = getSigningSecret();
      const valid = await verifyPayload(
        sessionCookie.value,
        sigCookie.value,
        secret,
      );
      if (!valid) {
        return NextResponse.json(
          { error: 'Unauthorized — invalid session' },
          { status: 401 },
        );
      }

      const decoded = atob(sessionCookie.value);
      const data = JSON.parse(decoded) as CasSessionData;

      return handler(req, ctx ?? {}, data.user);
    } catch {
      return NextResponse.json(
        { error: 'Unauthorized — session parse error' },
        { status: 401 },
      );
    }
  };
}

/**
 * Read the CAS session from a plain `NextRequest` (useful in middleware
 * and edge functions where the `cookies()` helper is not available).
 *
 * @param request - The incoming Next.js request.
 * @returns Session data or `null`.
 *
 * @internal
 */
export async function getCasSessionFromRequest(
  request: NextRequest,
): Promise<CasSessionData | null> {
  try {
    const sessionValue = request.cookies.get(SESSION_COOKIE)?.value;
    const sigValue = request.cookies.get(SIGNATURE_COOKIE)?.value;
    if (!sessionValue || !sigValue) return null;

    const secret = getSigningSecret();
    const valid = await verifyPayload(sessionValue, sigValue, secret);
    if (!valid) return null;

    const decoded = atob(sessionValue);
    return JSON.parse(decoded) as CasSessionData;
  } catch {
    return null;
  }
}
