import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterOutlet, RouterLink } from '@angular/router';

import { CasAuthService } from '@cas-system/angular-cas-client';
import { LocalSessionService } from './services/local-session.service';

/**
 * Root shell. Shows a nav bar driven entirely by the SDK's reactive streams:
 *   - `auth.isAuthenticated$` toggles the Login / Logout buttons
 *   - `auth.user$` shows the signed-in username
 *
 * Sign-in can happen EITHER way and both land in the same reactive streams:
 *   - LOCAL: the `/login` page validates a username/password against the
 *     backend's SQLite store and establishes the app's own session.
 *   - CAS:   `auth.login()` redirects to {CAS_BASE}/sso/login.
 *
 * Logout is session-aware: a local session is cleared in place, while a CAS
 * session bounces through the CAS server's logout endpoint.
 */
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  template: `
    <div class="container">
      <nav style="margin-bottom: 24px; display:flex; align-items:center; gap:8px;">
        <a routerLink="/">Home</a>
        <a routerLink="/profile">Profile (protected)</a>
        <span style="flex:1"></span>

        <ng-container *ngIf="auth.isAuthenticated$ | async; else loggedOut">
          <span class="badge">{{ (auth.user$ | async)?.username }}</span>
          <button class="secondary" (click)="signOut()">Logout</button>
        </ng-container>

        <ng-template #loggedOut>
          <a routerLink="/login"><button>Sign in</button></a>
        </ng-template>
      </nav>

      <!-- Active route renders here (Home, Login, Profile, or the SDK callback). -->
      <router-outlet></router-outlet>
    </div>
  `,
})
export class AppComponent {
  // `public` so the template can bind directly to the SDK service.
  constructor(
    public auth: CasAuthService,
    private readonly localSession: LocalSessionService,
    private readonly router: Router,
  ) {}

  /**
   * Logout that works for BOTH sign-in paths:
   *   - local session  → clear it in place and return home (no CAS redirect).
   *   - CAS session     → use the SDK's logout (bounces through CAS logout).
   */
  signOut(): void {
    if (this.localSession.isLocalSession()) {
      this.localSession.signOutLocal();
      this.router.navigateByUrl('/');
      return;
    }
    this.auth.logout('/');
  }
}
