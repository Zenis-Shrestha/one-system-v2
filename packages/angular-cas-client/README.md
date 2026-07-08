# @cas-system/angular-cas-client

Angular SDK for **CAS (Central Authentication System)** SSO integration — guards, interceptors, reactive services.

## Features

- 🔐 **SSO Login / Logout** — one-call redirects to the CAS server
- 🔄 **Token Handling** — automatic extraction from callback URLs
- 🛡️ **Backend Validation** — POST tokens to your server for secure validation
- 🚦 **Route Guard** — protect routes with `CasAuthGuard`, including role-based access
- 📡 **HTTP Interceptor** — auto-attach `Authorization: Bearer` headers
- 📦 **Reactive State** — `user$`, `isAuthenticated$`, `isLoading$` observables
- 🧩 **Standalone + NgModule** — works with both Angular patterns
- 💾 **Session Storage** — persists user data across page navigations

> **⚠️ Important:** Never validate tokens in the browser. This SDK sends tokens to your backend for server-side validation.

---

## Installation

```bash
npm install @cas-system/angular-cas-client
```

### Peer dependencies

| Package           | Version |
| ----------------- | ------- |
| `@angular/core`   | ≥ 18    |
| `@angular/common` | ≥ 18    |
| `@angular/router` | ≥ 18    |
| `rxjs`            | ≥ 7     |

---

## Quick Start

### 1. Import the module

```typescript
// app.module.ts
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { CasModule } from '@cas-system/angular-cas-client';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

@NgModule({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    HttpClientModule,
    AppRoutingModule,

    // ─── CAS SSO ───────────────────────────────────────────────
    CasModule.forRoot({
      serverUrl: 'https://cas.example.com',
      clientId: 'my-app-client-id',
      callbackUrl: 'https://my-app.com/cas/callback',    // optional
      backendValidateUrl: '/api/auth/validate',           // optional
    }),
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
```

### 2. Add the callback route

```typescript
// app-routing.module.ts
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import {
  CasCallbackComponent,
  CasAuthGuard,
} from '@cas-system/angular-cas-client';

import { DashboardComponent } from './dashboard/dashboard.component';
import { LoginComponent } from './login/login.component';

const routes: Routes = [
  // CAS callback — handles the redirect from the CAS server
  { path: 'cas/callback', component: CasCallbackComponent },

  // Protected route
  {
    path: 'dashboard',
    component: DashboardComponent,
    canActivate: [CasAuthGuard],
  },

  // Public
  { path: 'login', component: LoginComponent },
  { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
```

### 3. Use the auth service in components

```typescript
import { Component } from '@angular/core';
import { CasAuthService } from '@cas-system/angular-cas-client';

@Component({
  selector: 'app-navbar',
  template: `
    <nav>
      <ng-container *ngIf="auth.isAuthenticated$ | async; else loggedOut">
        <span>Hello, {{ (auth.user$ | async)?.username }}</span>
        <button (click)="auth.logout('/')">Logout</button>
      </ng-container>
      <ng-template #loggedOut>
        <button (click)="auth.login()">Login with CAS</button>
      </ng-template>
    </nav>
  `,
})
export class NavbarComponent {
  constructor(public auth: CasAuthService) {}
}
```

---

## Auth Flow

```
┌───────────┐       ┌──────────────┐       ┌──────────────┐
│  Angular   │──1──▶│  CAS Server  │──2──▶│  Angular App  │
│   App      │      │  /sso/login  │      │ /cas/callback │
│ (login())  │      │              │      │  ?token=JWT   │
└───────────┘       └──────────────┘       └──────┬───────┘
                                                   │
                                             3. Extract token
                                             4. POST to backend
                                                   │
                                                   ▼
                                           ┌──────────────┐
                                           │  Your Backend │
                                           │ /api/auth/    │
                                           │  validate     │
                                           └──────┬───────┘
                                                   │
                                             5. Returns CasUser
                                             6. Store in session
                                             7. Navigate to returnUrl
```

1. `CasClientService.login()` redirects the browser to the CAS server.
2. The CAS server authenticates the user and redirects to `callbackUrl?token=JWT`.
3. `CasCallbackComponent` (or `handleCallback()`) extracts the token from the URL.
4. The token is sent to your backend's validation endpoint.
5. The backend validates the token with the CAS server and returns a `CasUser`.
6. The user object and token are stored in `sessionStorage`.
7. The app navigates to the originally-requested URL.

---

## Guards

### Basic protection

```typescript
{
  path: 'profile',
  component: ProfileComponent,
  canActivate: [CasAuthGuard],
}
```

If the user is not authenticated the guard calls `casClient.login(currentUrl)` so that after login the user returns to the page they originally requested.

### Role-based protection

Add `data.roles` to require specific roles:

```typescript
{
  path: 'admin',
  component: AdminPanelComponent,
  canActivate: [CasAuthGuard],
  data: { roles: ['admin'] },
}
```

```typescript
{
  path: 'reports',
  component: ReportsComponent,
  canActivate: [CasAuthGuard],
  data: { roles: ['admin', 'analyst'] },   // any of these roles
}
```

---

## Interceptor

The `CasTokenInterceptor` is automatically registered by `CasModule.forRoot()`. It attaches `Authorization: Bearer <token>` to every outgoing `HttpClient` request.

### Restrict to specific URLs

Pass a second argument to `forRoot()` with URL prefixes to intercept:

```typescript
CasModule.forRoot(
  {
    serverUrl: 'https://cas.example.com',
    clientId: 'my-app',
  },
  ['/api/', 'https://backend.example.com'],   // only these URLs get the header
),
```

Or provide the token manually:

```typescript
import { CAS_INTERCEPT_URLS } from '@cas-system/angular-cas-client';

providers: [
  { provide: CAS_INTERCEPT_URLS, useValue: ['/api/'] },
],
```

---

## Service API Reference

### CasClientService

Low-level service for direct CAS interactions.

| Method                            | Returns                     | Description                                              |
| --------------------------------- | --------------------------- | -------------------------------------------------------- |
| `getLoginUrl(returnUrl?)`         | `string`                    | Build the full CAS SSO login URL                         |
| `login(returnUrl?)`               | `void`                      | Redirect the browser to the CAS login page               |
| `extractTokenFromUrl()`           | `string \| null`            | Extract `?token=…` from the current URL                  |
| `validateTokenViaBackend(token)`  | `Observable<CasUser\|null>` | POST token to backend for validation                     |
| `handleCallback()`               | `Observable<CasUser\|null>` | Extract + validate in one step                           |
| `getToken()`                      | `string \| null`            | Retrieve the stored JWT token                            |
| `getUser()`                       | `CasUser \| null`           | Retrieve the stored user object                          |
| `isAuthenticated()`               | `boolean`                   | Check if a session exists                                |
| `logout(redirectUrl?)`            | `void`                      | Clear session and redirect to CAS logout                 |
| `userHasRole(role)`               | `boolean`                   | Check a single role                                      |
| `userHasAnyRole(roles)`           | `boolean`                   | Check if user has any of the listed roles                |
| `userHasAllRoles(roles)`          | `boolean`                   | Check if user has all listed roles                       |

### CasAuthService

High-level reactive wrapper with `Observable` streams.

| Property / Method    | Type                          | Description                                     |
| -------------------- | ----------------------------- | ----------------------------------------------- |
| `user$`              | `Observable<CasUser \| null>` | Reactive stream of the current user              |
| `isAuthenticated$`   | `Observable<boolean>`         | `true` when a user session exists               |
| `isLoading$`         | `Observable<boolean>`         | `true` during async auth operations             |
| `currentUser`        | `CasUser \| null`             | Synchronous snapshot of the current user         |
| `isAuthenticated`    | `boolean`                     | Synchronous auth check                          |
| `login(returnUrl?)`  | `void`                        | Redirect to CAS login                           |
| `logout(redirectUrl?)` | `void`                      | Clear session and redirect                      |
| `checkAuth()`        | `void`                        | Re-hydrate user from sessionStorage             |
| `handleCallback()`   | `Observable<CasUser \| null>` | Process callback and update reactive state      |

---

## Models

### CasConfig

```typescript
interface CasConfig {
  serverUrl: string;            // CAS server base URL
  clientId: string;             // OAuth client identifier
  callbackUrl?: string;         // redirect URI (default: origin + '/cas/callback')
  backendValidateUrl?: string;  // your backend validation endpoint
}
```

### CasUser

```typescript
interface CasUser {
  id: string;
  username: string;
  email: string;
  roles?: string[];
}
```

---

## Standalone Usage (without NgModule)

If you prefer Angular's standalone component pattern you can provide the config directly:

```typescript
// main.ts
import { bootstrapApplication } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptorsFromDi, HTTP_INTERCEPTORS } from '@angular/common/http';
import {
  CAS_CONFIG,
  CasTokenInterceptor,
  CasCallbackComponent,
  CasAuthGuard,
} from '@cas-system/angular-cas-client';

import { AppComponent } from './app/app.component';

bootstrapApplication(AppComponent, {
  providers: [
    provideHttpClient(withInterceptorsFromDi()),
    provideRouter([
      { path: 'cas/callback', component: CasCallbackComponent },
      {
        path: 'dashboard',
        loadComponent: () => import('./app/dashboard.component').then(m => m.DashboardComponent),
        canActivate: [CasAuthGuard],
      },
    ]),
    {
      provide: CAS_CONFIG,
      useValue: {
        serverUrl: 'https://cas.example.com',
        clientId: 'my-app',
        callbackUrl: 'https://my-app.com/cas/callback',
        backendValidateUrl: '/api/auth/validate',
      },
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: CasTokenInterceptor,
      multi: true,
    },
  ],
});
```

---

## Role-Based UI Examples

### Show / hide elements based on roles

```typescript
import { Component } from '@angular/core';
import { CasClientService } from '@cas-system/angular-cas-client';

@Component({
  selector: 'app-sidebar',
  template: `
    <nav>
      <a routerLink="/dashboard">Dashboard</a>
      <a routerLink="/admin" *ngIf="isAdmin">Admin Panel</a>
      <a routerLink="/reports" *ngIf="canViewReports">Reports</a>
    </nav>
  `,
})
export class SidebarComponent {
  get isAdmin(): boolean {
    return this.cas.userHasRole('admin');
  }

  get canViewReports(): boolean {
    return this.cas.userHasAnyRole(['admin', 'analyst']);
  }

  constructor(private cas: CasClientService) {}
}
```

### Guard multiple role combinations

```typescript
const routes: Routes = [
  {
    path: 'admin',
    canActivate: [CasAuthGuard],
    data: { roles: ['admin', 'superadmin'] },
    children: [
      { path: '', component: AdminDashboardComponent },
      { path: 'users', component: UserManagementComponent },
    ],
  },
];
```

---

## License

MIT
