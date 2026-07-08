import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

import { CasAuthService } from '@cas-system/angular-cas-client';
import { LocalSessionService } from '../services/local-session.service';

/**
 * Protected page — reachable only after sign-in because the route is guarded by
 * `CasAuthGuard` (see main.ts). The guard checks the app's session, which is
 * populated by EITHER a local login OR the CAS callback. If you hit it while
 * signed out the guard redirects you to CAS login.
 *
 * It displays the authenticated user the app stored (from local login or the
 * CAS server's validation).
 */
@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="card" *ngIf="auth.user$ | async as user">
      <h1>Your profile <span class="badge">authenticated</span></h1>
      <p class="subtitle">
        Signed in via <strong>{{ sessionKind() }}</strong>. For CAS these fields
        come from the CAS server via the backend's
        <code>/api/validate-token</code> call; for a local account they come
        from this app's SQLite user store.
      </p>

      <dl class="kv">
        <dt>ID</dt>
        <dd>{{ user.id }}</dd>
        <dt>Username</dt>
        <dd>{{ user.username }}</dd>
        <dt *ngIf="user.email">Email</dt>
        <dd *ngIf="user.email">{{ user.email }}</dd>
        <dt *ngIf="user.roles?.length">Roles</dt>
        <dd *ngIf="user.roles?.length">{{ user.roles?.join(', ') }}</dd>
      </dl>

      <button class="secondary" (click)="signOut()">Logout</button>
    </div>
  `,
})
export class ProfileComponent {
  constructor(
    public auth: CasAuthService,
    private readonly localSession: LocalSessionService,
    private readonly router: Router,
  ) {}

  /** Label the active session's origin for the UI. */
  sessionKind(): string {
    return this.localSession.isLocalSession() ? 'local account' : 'CAS SSO';
  }

  /** Session-aware logout (local: clear in place; CAS: bounce through CAS). */
  signOut(): void {
    if (this.localSession.isLocalSession()) {
      this.localSession.signOutLocal();
      this.router.navigateByUrl('/');
      return;
    }
    this.auth.logout('/');
  }
}
