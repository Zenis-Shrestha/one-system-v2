/**
 * Home page (public).
 *
 * Reads the app's session cookie SERVER-SIDE so it can show whether you are
 * currently signed in — via a LOCAL account OR via CAS SSO — who you are, and a
 * logout action. When signed out it links to both ways to sign in:
 *   - the local /login form (the app's own username/password accounts)
 *   - "Sign in with SSO" in the navbar (the existing CAS flow)
 */
import Link from 'next/link';
import { cookies } from 'next/headers';
import { getCasSession } from '@cas-system/nextjs-cas-client/server';
import { LogoutButton } from '@/components/LogoutButton';

export const dynamic = 'force-dynamic'; // auth state is per-request

export default async function HomePage() {
  const session = await getCasSession(await cookies());
  const isLocal = session?.user.id?.startsWith('local:') ?? false;

  return (
    <div className="card">
      <h1>One System CAS — Next.js sample</h1>

      {session ? (
        <>
          <p className="signed-in" role="status">
            Signed in as <strong>{session.user.username}</strong>{' '}
            <span className="muted">
              ({isLocal ? 'local account' : 'CAS SSO'})
            </span>
          </p>
          <p>
            <Link href="/dashboard" className="btn btn-primary">
              Go to dashboard →
            </Link>{' '}
            <LogoutButton />
          </p>
        </>
      ) : (
        <p className="muted" role="status">
          You are not signed in.
        </p>
      )}

      <p>
        End-to-end demo of <code>@cas-system/nextjs-cas-client</code> — now with
        the app&apos;s OWN local accounts too. Sign in EITHER with a local
        username/password or via CAS SSO.
      </p>

      <ol className="steps">
        <li>
          <strong>Local accounts:</strong> open{' '}
          <Link href="/login">/login</Link> and use a seeded demo account
          (<code>rajan / rajan123</code> or <code>demo / demo123</code>).
        </li>
        <li>
          <strong>CAS SSO:</strong> click <strong>Sign in with SSO</strong> (top
          right) or open the <Link href="/dashboard">Dashboard</Link> — both
          start the CAS flow, which redirects to{' '}
          <code>/api/cas/callback?token=…</code>, validates the single-use token{' '}
          <strong>server-to-server</strong>, and sets the session cookie.
        </li>
        <li>
          Either way you land on the{' '}
          <Link href="/dashboard">Dashboard</Link>, which reads your user from
          the SAME session cookie.
        </li>
      </ol>

      <p>
        <Link href="/login" className="btn">
          Local sign in
        </Link>{' '}
        <Link href="/dashboard" className="btn btn-primary">
          Go to protected dashboard →
        </Link>
      </p>
    </div>
  );
}
