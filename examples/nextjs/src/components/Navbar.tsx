'use client';

/**
 * Top navigation bar — demonstrates the client-side hooks & components:
 *   - useCasAuth():   { user, isAuthenticated, isLoading, login, logout, ... }
 *   - <CasLoginButton>: renders a "Sign in" button that triggers the SSO flow
 *
 * When signed out it shows a CAS login button. When signed in it shows the
 * username and a logout button (logout() POSTs /api/cas/logout, clears the
 * session cookie, then redirects home).
 */
import { useCasAuth, CasLoginButton } from '@cas-system/nextjs-cas-client';
import Link from 'next/link';

export function Navbar() {
  const { user, isAuthenticated, isLoading, logout } = useCasAuth();

  return (
    <nav className="navbar">
      <Link href="/" className="brand">
        One System · Next.js sample
      </Link>

      <div className="nav-actions">
        <Link href="/dashboard">Dashboard</Link>

        {isLoading ? (
          <span className="muted">Loading…</span>
        ) : isAuthenticated ? (
          <>
            <span className="muted">
              {user?.username} ({user?.email})
            </span>
            <button className="btn" onClick={() => logout()}>
              Sign out
            </button>
          </>
        ) : (
          <>
            {/* Local username/password accounts (the app's own login form). */}
            <Link href="/login" className="btn">
              Local sign in
            </Link>
            {/* CasLoginButton redirects the browser to the CAS SSO login page. */}
            <CasLoginButton className="btn btn-primary">
              Sign in with SSO
            </CasLoginButton>
          </>
        )}
      </div>
    </nav>
  );
}
