'use client';

/**
 * @module @cas-system/nextjs-cas-client/components/CasLoginButton
 * @description A ready-to-use login button that redirects to the CAS SSO
 * login page when clicked.
 *
 * @example
 * ```tsx
 * import { CasLoginButton } from '@cas-system/nextjs-cas-client';
 *
 * export default function Header() {
 *   return (
 *     <nav>
 *       <CasLoginButton>Sign in with SSO</CasLoginButton>
 *     </nav>
 *   );
 * }
 * ```
 */

import React from 'react';
import { useCasAuthContext } from '../CasProvider';

/** Props for {@link CasLoginButton}. */
export interface CasLoginButtonProps
  extends Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, 'onClick'> {
  children?: React.ReactNode;

  /**
   * URL to return to after login.
   * Defaults to the current page.
   */
  returnUrl?: string;
}

/**
 * A simple button that triggers the CAS login flow.
 *
 * When the user is already authenticated the button renders nothing by
 * default.  Customise this via the {@link CasLoginButtonProps}.
 */
export function CasLoginButton({
  children = 'Sign in',
  returnUrl,
  ...buttonProps
}: CasLoginButtonProps) {
  const { isAuthenticated, isLoading, login } = useCasAuthContext();

  if (isLoading) {
    return (
      <button {...buttonProps} disabled>
        Loading…
      </button>
    );
  }

  if (isAuthenticated) {
    return null;
  }

  return (
    <button
      {...buttonProps}
      onClick={() =>
        login(
          returnUrl ??
            (typeof window !== 'undefined'
              ? window.location.pathname + window.location.search
              : undefined),
        )
      }
    >
      {children}
    </button>
  );
}
