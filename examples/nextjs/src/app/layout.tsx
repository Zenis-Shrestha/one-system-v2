/**
 * Root layout. Wraps the whole app in <CasProvider> so every client component
 * can read auth state via useCasAuth() / useCasUser().
 *
 * The provider builds the SSO login redirect URL from the NEXT_PUBLIC_* values
 * (server URL, client id, callback URL). These are NOT secret — the
 * client_secret stays on the server and is only used by the route handlers.
 */
import type { ReactNode } from 'react';
import { CasProvider } from '@cas-system/nextjs-cas-client';
import { Navbar } from '@/components/Navbar';
import './globals.css';

export const metadata = {
  title: 'One System CAS — Next.js sample',
  description: 'Minimal Next.js App Router app using @cas-system/nextjs-cas-client',
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en">
      <body>
        <CasProvider
          // Used by login()/<CasLoginButton> to build the CAS SSO redirect IN
          // THE BROWSER, so it must be the PUBLIC CAS url (the browser cannot
          // reach the internal back-channel host). Prefer NEXT_PUBLIC_CAS_PUBLIC_URL
          // (fed from CAS_PUBLIC_URL); fall back to NEXT_PUBLIC_CAS_SERVER_URL
          // so single-url local dev keeps working.
          casServerUrl={
            process.env.NEXT_PUBLIC_CAS_PUBLIC_URL ||
            process.env.NEXT_PUBLIC_CAS_SERVER_URL
          }
          casClientId={process.env.NEXT_PUBLIC_CAS_CLIENT_ID}
          casCallbackUrl={process.env.NEXT_PUBLIC_CAS_CALLBACK_URL}
        >
          <Navbar />
          <main className="container">{children}</main>
        </CasProvider>
      </body>
    </html>
  );
}
