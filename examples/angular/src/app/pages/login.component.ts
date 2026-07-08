import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';

import { CasAuthService, CasUser } from '@cas-system/angular-cas-client';
import { LocalSessionService } from '../services/local-session.service';

/**
 * Local username/password login screen (the app's OWN accounts).
 *
 * Submits the form to the backend's `POST /login`. On success the backend
 * returns `{ success, user }`; we hand that user to {@link LocalSessionService}
 * which establishes the app's OWN session (the same one CAS uses) and we
 * redirect to the home/dashboard. On failure we re-render with an error.
 *
 * This sits ALONGSIDE CAS SSO — the page also offers a "Login with CAS" button
 * so a user can sign in EITHER way.
 */
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `
    <div class="card" style="max-width: 380px; margin: 0 auto;">
      <h1>Sign in</h1>
      <p class="subtitle">
        Use a local account, or sign in with CAS single sign-on.
      </p>

      <form (ngSubmit)="onSubmit()" #f="ngForm" novalidate>
        <label class="field">
          <span>Username</span>
          <input
            name="username"
            type="text"
            autocomplete="username"
            [(ngModel)]="username"
            [disabled]="loading"
            required
            autofocus
          />
        </label>

        <label class="field">
          <span>Password</span>
          <input
            name="password"
            type="password"
            autocomplete="current-password"
            [(ngModel)]="password"
            [disabled]="loading"
            required
          />
        </label>

        <p *ngIf="error" class="error" role="alert">{{ error }}</p>

        <button type="submit" [disabled]="loading || !f.form.valid" style="width:100%">
          {{ loading ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>

      <div class="divider"><span>or</span></div>

      <button class="secondary" style="width:100%" (click)="loginWithCas()">
        Login with CAS
      </button>

      <p class="hint">
        Demo accounts: <code>rajan / rajan123</code> · <code>demo / demo123</code>
      </p>

      <p style="margin-top:16px;"><a routerLink="/">← Back to home</a></p>
    </div>
  `,
  styles: [
    `
      .field {
        display: block;
        margin-bottom: 14px;
      }
      .field span {
        display: block;
        font-size: 0.85rem;
        color: var(--muted);
        margin-bottom: 6px;
      }
      .field input {
        width: 100%;
        font: inherit;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
        color: var(--fg);
      }
      .field input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
      }
      .error {
        color: #b91c1c;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.88rem;
        margin: 0 0 14px;
      }
      .divider {
        text-align: center;
        color: var(--muted);
        font-size: 0.8rem;
        margin: 18px 0;
        position: relative;
      }
      .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border);
      }
      .divider span {
        position: relative;
        background: var(--bg-card);
        padding: 0 10px;
      }
      .hint {
        margin: 16px 0 0;
        font-size: 0.8rem;
        color: var(--muted);
      }
    `,
  ],
})
export class LoginComponent {
  username = '';
  password = '';
  error = '';
  loading = false;

  constructor(
    private readonly router: Router,
    private readonly auth: CasAuthService,
    private readonly localSession: LocalSessionService,
  ) {}

  /** Validate local credentials against the backend, then sign in. */
  async onSubmit(): Promise<void> {
    this.error = '';
    this.loading = true;

    try {
      const res = await fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          username: this.username,
          password: this.password,
        }),
      });

      const data = (await res.json().catch(() => ({}))) as {
        success?: boolean;
        user?: CasUser;
        error?: string;
      };

      if (!res.ok || !data.success || !data.user) {
        // Re-render the form with an error (no session created).
        this.error = data.error || 'Invalid username or password.';
        this.password = '';
        return;
      }

      // Establish the app's OWN session (shared with CAS) and go home.
      this.localSession.signInLocal(data.user);
      await this.router.navigateByUrl('/');
    } catch {
      this.error = 'Could not reach the server. Please try again.';
    } finally {
      this.loading = false;
    }
  }

  /** Kick off CAS SSO instead — same app, alternate sign-in path. */
  loginWithCas(): void {
    this.auth.login('/profile');
  }
}
