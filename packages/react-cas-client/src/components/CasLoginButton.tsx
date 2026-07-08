import type { ButtonHTMLAttributes, ReactNode } from 'react';
import { useCasAuth } from '../hooks/useCasAuth';

/**
 * Props for the {@link CasLoginButton} component.
 */
export interface CasLoginButtonProps
  extends Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'onClick'> {
  /**
   * URL to return to after CAS authentication.
   * If omitted, the current page URL is used.
   */
  returnUrl?: string;

  /**
   * Custom CSS class name(s) for the button element.
   */
  className?: string;

  /**
   * Custom button content. Defaults to `"Sign in"`.
   */
  children?: ReactNode;
}

/**
 * Pre-wired button that triggers CAS SSO login on click.
 *
 * Accepts all standard `<button>` HTML attributes (except `onClick`, which
 * is handled internally).
 *
 * @example
 * ```tsx
 * import { CasLoginButton } from '@cas-system/react-cas-client';
 *
 * // Default label
 * <CasLoginButton className="btn btn-primary" />
 *
 * // Custom label and return URL
 * <CasLoginButton returnUrl="/dashboard">
 *   🔑 Log in with SSO
 * </CasLoginButton>
 * ```
 */
export function CasLoginButton({
  returnUrl,
  className,
  children = 'Sign in',
  ...rest
}: CasLoginButtonProps) {
  const { login } = useCasAuth();

  return (
    <button
      type="button"
      className={className}
      onClick={() => login(returnUrl)}
      {...rest}
    >
      {children}
    </button>
  );
}
