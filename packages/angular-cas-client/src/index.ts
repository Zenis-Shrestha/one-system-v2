/**
 * @cas-system/angular-cas-client — Public API Surface
 *
 * @packageDocumentation
 */

// ── Models & Tokens ─────────────────────────────────────────────────────────
export {
  CasConfig,
  CasUser,
  CasValidateResponse,
  CAS_CONFIG,
} from './models/cas-config.model';

// ── Services ────────────────────────────────────────────────────────────────
export { CasClientService } from './services/cas-client.service';
export { CasAuthService } from './services/cas-auth.service';

// ── Guards ──────────────────────────────────────────────────────────────────
export { CasAuthGuard } from './guards/cas-auth.guard';

// ── Interceptors & Tokens ───────────────────────────────────────────────────
export {
  CasTokenInterceptor,
  CAS_INTERCEPT_URLS,
} from './interceptors/cas-token.interceptor';

// ── Components ──────────────────────────────────────────────────────────────
export { CasCallbackComponent } from './components/cas-callback.component';

// ── Module ──────────────────────────────────────────────────────────────────
export { CasModule } from './cas.module';
