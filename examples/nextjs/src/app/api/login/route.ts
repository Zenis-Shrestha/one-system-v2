/**
 * Local username/password login — POST /api/login
 *
 * This single route serves TWO callers, distinguished by the request body:
 *
 *  1. BROWSER FORM LOGIN (the app's own local accounts)
 *     The `/login` page posts `username` + `password` (form-encoded). On valid
 *     credentials we establish the app's OWN local session — the SAME signed,
 *     HttpOnly cookie the CAS callback uses (via `setCasSession`) — then 303
 *     redirect to the dashboard. On failure we redirect back to `/login?error=…`
 *     so the form can re-render with a message.
 *
 *  2. CAS LINK-VALIDATION CONTRACT (server-to-server)
 *     The CAS server posts `{ username, password, client_validation: true }`
 *     (JSON). We detect the `client_validation` field (and/or an
 *     `Accept: application/json` header), validate against the local store, and
 *     respond:
 *         200 { "success": true }   — valid credentials
 *         401 { "success": false }  — invalid credentials
 *     This path NEVER creates a browser session.
 *
 * Both paths validate against the local SQLite user store (see lib/db.ts).
 */

import { NextRequest, NextResponse } from 'next/server';
import { cookies } from 'next/headers';
import { setCasSession } from '@cas-system/nextjs-cas-client/server';
import type { CasUser } from '@cas-system/nextjs-cas-client/server';
import { validateLocalCredentials } from '@/lib/db';

// Local accounts live in a SQLite file on disk — this route must run on Node.
export const runtime = 'nodejs';
export const dynamic = 'force-dynamic';

/** Where to land after a successful local browser login. */
const AFTER_LOGIN_URL = '/dashboard';

/**
 * Parsed login input plus a flag indicating whether this is the CAS
 * server-to-server validation call (vs. a browser form submission).
 */
interface ParsedLogin {
  username: string;
  password: string;
  isValidation: boolean;
}

/**
 * Read username/password from either a JSON body or a URL-encoded form body,
 * and decide whether this is the CAS validation contract.
 *
 * Validation is detected by the presence of a truthy `client_validation` field
 * in the body OR an `Accept: application/json` request header.
 */
async function parseLogin(request: NextRequest): Promise<ParsedLogin> {
  const contentType = request.headers.get('content-type') ?? '';
  const accept = request.headers.get('accept') ?? '';

  let username = '';
  let password = '';
  let clientValidation = false;

  if (contentType.includes('application/json')) {
    const body = (await request.json().catch(() => ({}))) as Record<string, unknown>;
    username = typeof body.username === 'string' ? body.username : '';
    password = typeof body.password === 'string' ? body.password : '';
    clientValidation = Boolean(body.client_validation);
  } else {
    // Covers application/x-www-form-urlencoded and multipart/form-data.
    const form = await request.formData();
    username = String(form.get('username') ?? '');
    password = String(form.get('password') ?? '');
    clientValidation = Boolean(form.get('client_validation'));
  }

  const isValidation = clientValidation || accept.includes('application/json');

  return { username, password, isValidation };
}

export async function POST(request: NextRequest): Promise<NextResponse> {
  const { username, password, isValidation } = await parseLogin(request);
  const user = validateLocalCredentials(username, password);

  // ---- 2. CAS link-validation contract (no browser session) -------------
  if (isValidation) {
    if (user) {
      return NextResponse.json({ success: true }, { status: 200 });
    }
    return NextResponse.json({ success: false }, { status: 401 });
  }

  // ---- 1. Browser form login (establish local session) ------------------
  if (!user) {
    // Re-render the form with an error (303 so the browser issues a GET).
    return NextResponse.redirect(
      new URL('/login?error=invalid_credentials', request.url),
      { status: 303 },
    );
  }

  // Establish the app's OWN local session — the SAME cookie CAS uses. We mint a
  // synthetic local id/email and a marker token so getCasSession() treats a
  // locally-authenticated user identically to an SSO user downstream.
  const casUser: CasUser = {
    id: `local:${user.id}`,
    username: user.username,
    email: `${user.username}@local`,
    roles: ['local-user'],
  };

  const cookieStore = await cookies();
  await setCasSession(cookieStore, casUser, `local-session:${user.id}`);

  return NextResponse.redirect(new URL(AFTER_LOGIN_URL, request.url), {
    status: 303,
  });
}
