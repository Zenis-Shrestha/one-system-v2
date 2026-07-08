import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';

import { CasAuthService } from '@cas-system/angular-cas-client';
import { LocalSessionService } from '../services/local-session.service';

/**
 * Public landing page. Shows whether you are signed in (LOCALLY or via CAS),
 * who you are, and offers logout. Anyone can view this — no guard.
 */
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, RouterLink],
  template: `
    <div class="card">
      <h1>One System CAS — Angular Sample</h1>
      <p class="subtitle">
        End-to-end demo of <code>&#64;one-system/angular-cas-client</code> with
        the app's OWN local accounts alongside CAS single sign-on.
      </p>

      <ng-container *ngIf="auth.isAuthenticated$ | async; else anon">
        <p>
          You are signed in as
          <strong>{{ (auth.user$ | async)?.username }}</strong>
          <span class="badge">{{ sessionKind() }}</span>.
          Open the <strong>Profile</strong> page to see your details, or sign
          out below.
        </p>
        <button class="secondary" (click)="signOut()">Logout</button>
      </ng-container>

      <ng-template #anon>
        <p>
          Sign in with a <strong>local account</strong> (username + password,
          validated against this app's SQLite store) or with
          <strong>CAS single sign-on</strong>. Either way the app establishes
          the same session, and the guarded <code>/profile</code> page becomes
          reachable.
        </p>
        <a routerLink="/login"><button>Sign in</button></a>
      </ng-template>
    </div>
  `,
})
export class HomeComponent {
  constructor(
    public auth: CasAuthService,
    private readonly localSession: LocalSessionService,
    private readonly router: Router,
  ) {}

  /** Label the active session's origin for the UI. */
  sessionKind(): string {
    return this.localSession.isLocalSession() ? 'local' : 'CAS';
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
