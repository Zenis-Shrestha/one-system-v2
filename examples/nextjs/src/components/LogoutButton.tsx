'use client';

/**
 * Small client component used inside the (server-rendered) dashboard.
 * Calls the provider's logout() which POSTs /api/cas/logout, clears the
 * session cookie, and redirects home.
 */
import { useCasAuth } from '@cas-system/nextjs-cas-client';

export function LogoutButton() {
  const { logout } = useCasAuth();
  return (
    <button className="btn" onClick={() => logout()}>
      Sign out
    </button>
  );
}
