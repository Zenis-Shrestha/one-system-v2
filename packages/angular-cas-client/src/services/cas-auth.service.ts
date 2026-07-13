import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, finalize, tap } from 'rxjs';

import { CasUser } from '../models/cas-config.model';
import { CasClientService } from './cas-client.service';

/**
 * High-level reactive authentication service built on top of
 * {@link CasClientService}.
 *
 * Exposes `Observable` streams for the current user, authentication status,
 * and loading state so Angular templates can use the `async` pipe directly.
 *
 * @example
 * ```html
 * <ng-container *ngIf="auth.isAuthenticated$ | async; else loginTpl">
 *   <p>Welcome, {{ (auth.user$ | async)?.username }}!</p>
 * </ng-container>
 * <ng-template #loginTpl>
 *   <button (click)="auth.login()">Login</button>
 * </ng-template>
 * ```
 */
@Injectable({ providedIn: 'root' })
export class CasAuthService {
  // ---------------------------------------------------------------------------
  //  Internal subjects
  // ---------------------------------------------------------------------------

  private readonly userSubject = new BehaviorSubject<CasUser | null>(null);
  private readonly loadingSubject = new BehaviorSubject<boolean>(false);

  // ---------------------------------------------------------------------------
  //  Public observables
  // ---------------------------------------------------------------------------

  /**
   * Reactive stream of the currently authenticated user.
   * Emits `null` when not logged in.
   */
  readonly user$: Observable<CasUser | null> = this.userSubject.asObservable();

  /**
   * Reactive boolean stream: `true` when a user session exists.
   */
  readonly isAuthenticated$: Observable<boolean> = new Observable<boolean>(
    (subscriber) => {
      this.userSubject.subscribe((user) => {
        subscriber.next(user !== null);
      });
    },
  );

  /**
   * Emits `true` while an asynchronous auth operation (callback handling,
   * token validation) is in progress.
   */
  readonly isLoading$: Observable<boolean> = this.loadingSubject.asObservable();

  constructor(private readonly casClient: CasClientService) {
    // Hydrate from sessionStorage on service init
    this.checkAuth();
  }

  // ---------------------------------------------------------------------------
  //  Public API
  // ---------------------------------------------------------------------------

  /**
   * Redirect the user to the CAS login page.
   *
   * @param returnUrl - URL to navigate to after authentication.
   */
  login(returnUrl?: string): void {
    this.casClient.login(returnUrl);
  }

  /**
   * Log the user out: clears local state and redirects to CAS logout.
   *
   * @param redirectUrl - URL to redirect to once logout is complete.
   */
  logout(redirectUrl?: string): Promise<void> {
    this.userSubject.next(null);
    return this.casClient.logout(redirectUrl);
  }

  /**
   * Re-hydrate the user from `sessionStorage`.
   *
   * Call this on application startup (or in an `APP_INITIALIZER`) to
   * restore a previous session without a network round-trip.
   */
  checkAuth(): void {
    const user = this.casClient.getUser();
    this.userSubject.next(user);
  }

  /**
   * Process the CAS callback: extract the token from the URL, validate it,
   * and update the reactive `user$` stream.
   *
   * @returns An `Observable` that emits the `CasUser` on success or `null`
   *   on failure.
   *
   * @example
   * ```typescript
   * this.auth.handleCallback().subscribe((user) => {
   *   if (user) {
   *     this.router.navigateByUrl('/dashboard');
   *   }
   * });
   * ```
   */
  handleCallback(): Observable<CasUser | null> {
    this.loadingSubject.next(true);

    return this.casClient.handleCallback().pipe(
      tap((user) => {
        this.userSubject.next(user);
      }),
      finalize(() => {
        this.loadingSubject.next(false);
      }),
    );
  }

  /**
   * Snapshot of the current user (non-reactive).
   *
   * @returns The `CasUser` or `null`.
   */
  get currentUser(): CasUser | null {
    return this.userSubject.getValue();
  }

  /**
   * Synchronous check of authentication status.
   *
   * @returns `true` when a user is currently stored.
   */
  get isAuthenticated(): boolean {
    return this.currentUser !== null;
  }
}
