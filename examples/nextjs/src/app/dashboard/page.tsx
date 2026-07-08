/**
 * Protected dashboard (Server Component).
 *
 * Two layers of protection are demonstrated:
 *   1. middleware.ts already redirects unauthenticated users to CAS before
 *      this page renders.
 *   2. As a belt-and-braces server-side check, we ALSO read the session here
 *      with getCasSession() and redirect to login if it is somehow missing.
 *
 * The user object shown here comes from the signed session cookie that the
 * callback handler created after validating the token server-to-server.
 */
import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';
import { getCasSession } from '@cas-system/nextjs-cas-client/server';
import { LogoutButton } from '@/components/LogoutButton';

export const dynamic = 'force-dynamic'; // never cache — auth state is per-request

export default async function DashboardPage() {
  const session = await getCasSession(await cookies());

  // Defensive: middleware should already guarantee a session here.
  if (!session) {
    redirect('/');
  }

  const { user } = session;
  const isLocal = user.id?.startsWith('local:') ?? false;

  return (
    <div className="card">
      <h1>Dashboard</h1>
      <p className="muted">
        You are authenticated. This page rendered on the server using the
        session cookie.
      </p>

      <dl className="user-grid">
        <dt>Signed in via</dt>
        <dd>{isLocal ? 'Local account' : 'CAS SSO'}</dd>
        <dt>ID</dt>
        <dd>
          <code>{user.id}</code>
        </dd>
        <dt>Username</dt>
        <dd>{user.username}</dd>
        <dt>Email</dt>
        <dd>{user.email}</dd>
        {user.roles && user.roles.length > 0 ? (
          <>
            <dt>Roles</dt>
            <dd>{user.roles.join(', ')}</dd>
          </>
        ) : null}
      </dl>

      <LogoutButton />
    </div>
  );
}
