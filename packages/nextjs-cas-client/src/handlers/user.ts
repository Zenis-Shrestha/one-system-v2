/**
 * @module @cas-system/nextjs-cas-client/handlers/user
 * @description API route handler that returns the current CAS user from
 * the session cookie.  This endpoint is called by the client-side
 * `CasProvider` to hydrate the auth state.
 *
 * @example
 * ```ts
 * // app/api/cas/user/route.ts
 * import { createUserHandler } from '@cas-system/nextjs-cas-client/handlers';
 *
 * const handler = createUserHandler();
 * export { handler as GET };
 * ```
 */

import { NextResponse } from 'next/server';
import { cookies } from 'next/headers';
import { getCasSession } from '../server/auth';

/**
 * Create a GET handler for `/api/cas/user`.
 *
 * Returns `{ user: CasUser }` when authenticated, or `401` otherwise.
 * The response includes `Cache-Control: no-store` to prevent caching of
 * auth state.
 *
 * @returns A Next.js App Router GET handler.
 */
export function createUserHandler() {
  return async function GET(): Promise<NextResponse> {
    const cookieStore = await cookies();
    const session = await getCasSession(cookieStore);

    if (!session) {
      return NextResponse.json(
        { user: null, error: 'Not authenticated' },
        {
          status: 401,
          headers: { 'Cache-Control': 'no-store' },
        },
      );
    }

    return NextResponse.json(
      { user: session.user },
      {
        status: 200,
        headers: { 'Cache-Control': 'no-store' },
      },
    );
  };
}
