# @cas-system/nextjs-cas-client

Next.js CAS (Central Authentication System) Client SDK for the **One System** CAS server.
Supports the App Router, Server Components, Route Handlers, Middleware, and client-side hooks.

## Install

```bash
npm install @cas-system/nextjs-cas-client
# peer deps: next >= 14, react >= 18, react-dom >= 18
# runtime: Node.js >= 18.17 (App Router). Verified on Node 22.
```

## How the SSO flow works

1. The browser is redirected to `GET {CAS_SERVER_URL}/sso/login?client_id={CLIENT_ID}`.
2. After authenticating, the CAS server 302-redirects to your registered callback URL
   with the token appended: `{callbackUrl}?token={JWT}`.
3. Your callback route handler reads the `token` query parameter and validates it
   **server-to-server** via `POST {CAS_SERVER_URL}/api/validate-token`
   (body: `{ token, client_id, client_secret }`). The token is **single-use**.
4. On success the handler establishes an `HttpOnly`, signed session cookie.

## Configuration

Server-side configuration (`CasServerConfig`):

| Field          | Env var (suggested)   | Description                                  |
| -------------- | --------------------- | -------------------------------------------- |
| `serverUrl`    | `CAS_SERVER_URL`      | Base URL of the CAS server (no trailing `/`) |
| `clientId`     | `CAS_CLIENT_ID`       | Registered client identifier                 |
| `clientSecret` | `CAS_CLIENT_SECRET`   | Secret for server-to-server token validation |
| `callbackUrl`  | `CAS_CALLBACK_URL`    | Optional; defaults to `/api/cas/callback`    |

The session cookie is signed with `CAS_COOKIE_SECRET` (falls back to `CAS_CLIENT_SECRET`).

## Route handlers (App Router)

```ts
// app/api/cas/callback/route.ts
import { createCallbackHandler } from '@cas-system/nextjs-cas-client/handlers';

export const GET = createCallbackHandler({
  cas: {
    serverUrl: process.env.CAS_SERVER_URL!,
    clientId: process.env.CAS_CLIENT_ID!,
    clientSecret: process.env.CAS_CLIENT_SECRET!,
  },
  afterLoginUrl: '/dashboard',
});
```

```ts
// app/api/cas/logout/route.ts
import { createLogoutHandler } from '@cas-system/nextjs-cas-client/handlers';

export const POST = createLogoutHandler({
  cas: {
    serverUrl: process.env.CAS_SERVER_URL!,
    clientId: process.env.CAS_CLIENT_ID!,
    clientSecret: process.env.CAS_CLIENT_SECRET!,
  },
  afterLogoutUrl: '/',
});
```

```ts
// app/api/cas/user/route.ts
import { createUserHandler } from '@cas-system/nextjs-cas-client/handlers';

export const GET = createUserHandler();
```

## Middleware

```ts
// middleware.ts
import { createCasMiddleware } from '@cas-system/nextjs-cas-client/middleware';

export default createCasMiddleware({
  protectedPaths: ['/dashboard', '/admin'],
  publicPaths: ['/api/health'],
  loginPath: '/login', // optional
});

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
```

The middleware reads `CAS_SERVER_URL`, `CAS_CLIENT_ID`, and `CAS_CALLBACK_URL`
from the environment when `loginPath` is not configured.

## Client provider + hooks

```tsx
// app/layout.tsx
import { CasProvider } from '@cas-system/nextjs-cas-client';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html>
      <body>
        {/* casServerUrl / casClientId / casCallbackUrl let login() build the
            CAS SSO redirect. They are NEXT_PUBLIC_* (no secret). */}
        <CasProvider
          casServerUrl={process.env.NEXT_PUBLIC_CAS_SERVER_URL}
          casClientId={process.env.NEXT_PUBLIC_CAS_CLIENT_ID}
          casCallbackUrl={process.env.NEXT_PUBLIC_CAS_CALLBACK_URL}
        >
          {children}
        </CasProvider>
      </body>
    </html>
  );
}
```

```tsx
'use client';
import { useCasAuth, useCasUser, CasLoginButton, CasProtectedRoute } from '@cas-system/nextjs-cas-client';

export function Navbar() {
  const { user, isAuthenticated, login, logout } = useCasAuth();
  return isAuthenticated
    ? <button onClick={logout}>Sign out ({user?.username})</button>
    : <CasLoginButton>Sign in with SSO</CasLoginButton>;
}
```

## Server-side session helpers

```ts
import { cookies } from 'next/headers';
import { getCasSession, withCasAuth, CasClient } from '@cas-system/nextjs-cas-client/server';

// In a Server Component / Route Handler
const session = await getCasSession(await cookies());

// Protect a route handler
export const GET = withCasAuth(async (req, ctx, user) => {
  return Response.json({ message: `Hello ${user.username}` });
});
```

## License

MIT
