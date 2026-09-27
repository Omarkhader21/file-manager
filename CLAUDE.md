# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project layout

This repo holds two independently-run apps, not a monorepo (no shared package manager, no root build):

- `file-manager-backend/` — Laravel 13 API (PHP 8.3+), Sanctum for auth, SQLite for dev/test.
- `file-manager-frontend/` — Vue 3 SPA (Vite, Pinia, Vue Router, Tailwind v4).

Not currently a git repository. Local dev uses `lerd` (a Herd-like Podman-based PHP dev environment, `.lerd.yaml`): the backend serves at `https://file-manager-backend.test`. `lerd`'s DNS wildcards the whole `.test` TLD to localhost, but `lerd`'s framework store is PHP-only — it can't proxy a standalone Vite/Node dev server, so the frontend is **not** a `lerd` site. It still runs via plain `npm run dev`, just reached at `https://app.file-manager-backend.test:5173` instead of `http://localhost:5173` — both the hostname **and** the HTTPS scheme matter here (see "Auth domain setup" below for why).

**Current state**: only the auth scaffold exists (Laravel Breeze-style controllers + a Vue login/register/dashboard skeleton). There is no file-manager domain code yet — no file/folder models, migrations, or controllers.

## Commands

### Backend (`file-manager-backend/`)

- `composer install` — install PHP deps
- `php artisan serve` or `composer dev` (runs `php artisan dev`) — run the app locally
- `php artisan test` or `composer test` — run the full PHPUnit suite
- `php artisan test tests/Feature/Auth/AuthenticationTest.php` — run one test file
- `php artisan test --filter=test_name` — run a single test by name
- `vendor/bin/pint` — format PHP (Laravel Pint)
- `php artisan migrate` — run migrations against `database/database.sqlite`

### Frontend (`file-manager-frontend/`)

- `npm install` — install JS deps
- `npm run dev` — Vite dev server
- `npm run build` — production build
- `npm run lint` — oxlint then eslint, both with `--fix`
- `npm run format` — Prettier over `src/`
- No test runner is configured yet (no Vitest/Jest/Cypress in `package.json`).

## Backend architecture

- Auth endpoints (`register`, `login`, `logout`, password reset, email verification) live in `routes/auth.php`, which is `require`d from `routes/web.php` — they run under the **web/session** middleware group, not under `/api` or the `auth.php` `guest`/`auth` API guards. They are session-cookie based (Laravel Breeze pattern: `Auth::login()`, `$request->session()->regenerate()`), and return `response()->noContent()` — no JSON body, no token.
- `routes/api.php` currently only has `GET /api/user`, gated by `auth:sanctum`, which relies on Sanctum's SPA (stateful cookie) mode — `EnsureFrontendRequestsAreStateful` is prepended to the `api` middleware group in `bootstrap/app.php`, and `config/sanctum.php` lists the stateful domains.
- `App\Models\User` uses `HasApiTokens` (Sanctum) but nothing in the current controllers issues an API token — the wiring so far is cookie/session auth end to end.

## Frontend architecture

- Auth uses Sanctum's cookie-based SPA flow, matching the backend as written (no bearer tokens, nothing in `localStorage`). `src/services/api.js` is an axios instance with `withCredentials: true` and `withXSRFToken: true` — axios reads the `XSRF-TOKEN` cookie and sends it back as `X-XSRF-TOKEN`, satisfying the `web`-group CSRF check on `/login`/`/register`/`/logout`. `baseURL` defaults to `https://file-manager-backend.test`; override with a frontend `VITE_API_URL` env var if you run the backend elsewhere (e.g. `php artisan serve` on `http://localhost:8000`).
- `src/stores/auth.js` (Pinia) holds only `user` (no token). `login`/`register` first call `csrf()` (`GET /sanctum/csrf-cookie`) to seed the cookie, then POST to the (bodyless) auth endpoint, then `fetchUser()` (`GET /api/user`) to populate `user` from the now-authenticated session. `isAuthenticated` is `!!user`.
- `src/main.js` calls `authStore.fetchUser()` once before mounting the router, so a page refresh restores auth state from the session cookie (there's nothing else client-side to restore it from). There's no global 401 interceptor — add one (scoped to skip the auth endpoints themselves) if/when session-expiry-mid-session handling is needed.
- `@` resolves to `src/` (see `vite.config.js`). Route guards in `src/router/index.js` use `meta.requiresAuth` / `meta.guestOnly` against `authStore.isAuthenticated`.

### Auth domain setup — why this isn't `http://localhost:5173`

Sanctum's SPA CSRF-cookie flow requires the frontend JS to read the `XSRF-TOKEN` cookie via `document.cookie`. Cookies are scoped by hostname, so a page on a completely different domain (`localhost`) can never read a cookie set by `file-manager-backend.test` — no CORS/axios setting changes that. Sanctum's own docs require the frontend and backend to share a top-level domain, differing only by subdomain, with the session cookie scoped via a leading-dot `domain`. **The scheme has to match too** (see the `mkcert`/HTTPS bullet below) — this is easy to miss because `curl`/Insomnia cookie-jar testing never exhibits it, only a real browser does.

Since `lerd` can't proxy the Vite dev server (see "Project layout"), this repo gets there without a `lerd` site for the frontend, relying on `lerd`'s DNS wildcarding the entire `.test` TLD to localhost:

- Frontend is opened at **`https://app.file-manager-backend.test:5173`** (still plain `npm run dev`, port 5173) instead of `http://localhost:5173`.
- `vite.config.js` sets `server.allowedHosts: ['app.file-manager-backend.test']` (Vite rejects unrecognized `Host` headers by default), `server.host: true` (binds all interfaces, since `.test` resolves to `::1`), and `server.https` pointed at a cert/key pair in `file-manager-frontend/.cert/` (gitignored, not committed).
- That cert is generated with `mkcert` — the same tool `lerd` uses for `file-manager-backend.test`, so it shares the same local CA and is trusted automatically (no "unsafe site" warning). Regenerate it with, from `file-manager-frontend/`: `mkcert -cert-file .cert/app.file-manager-backend.test.pem -key-file .cert/app.file-manager-backend.test-key.pem app.file-manager-backend.test` (`mkcert` lives at `~/.local/share/lerd/bin/mkcert` if it's not on `PATH`).
- **Why HTTPS on the frontend is required, not optional:** Chrome's "Schemeful Same-Site" policy treats `http://` and `https://` as different sites even on the exact same domain. Laravel's session/XSRF cookies default to `SameSite=Lax`, which browsers refuse to attach to a cross-site request — so with an `http://` frontend and `https://` backend, the browser silently drops the `laravel-session`/`XSRF-TOKEN` cookies from every API request, even with `withCredentials`/`credentials: 'include'`. The symptom is deceptive: `document.cookie` still shows the cookies fine (reading them locally isn't scheme-gated), and a manually-constructed `X-XSRF-TOKEN` header still decrypts without error — but since the session cookie itself never reached the server, Laravel silently starts a *new* session per request (visible as the `sessions` table row count going up on every attempt) whose stored token never matches the one in the header. Result: CSRF mismatch that looks identical to a missing-header problem but isn't. (An earlier, incorrect fix for this was `SESSION_SECURE_COOKIE=false`, which only solves the unrelated problem of an insecure page being unable to *read* a `Secure`-flagged cookie at all — necessary if you stay on `http://`, but insufficient, since the `SameSite`/scheme issue is a separate browser mechanism. Don't reintroduce it now that both sides are HTTPS.)
- Backend `.env`: `FRONTEND_URL=https://app.file-manager-backend.test:5173` (feeds `config/cors.php`'s exact-match `allowed_origins`), `SANCTUM_STATEFUL_DOMAINS=app.file-manager-backend.test:5173` (set explicitly, including the port — `EnsureFrontendRequestsAreStateful::fromFrontend()` matches the full `Origin`/`Referer` host *and port*, so the default host-only computed value in `config/sanctum.php` would never match a non-default port), and `SESSION_DOMAIN=.file-manager-backend.test` (leading dot shares the session cookie between `file-manager-backend.test` and `app.file-manager-backend.test`).
- After editing `.env`, config is cached (`bootstrap/cache/config.php` exists in this repo) — run `lerd console config:clear` (or `php artisan config:clear` if not going through `lerd`) for changes to take effect. Verify with `lerd console config:show cors|sanctum|session`.
- After editing `vite.config.js`'s `server` block, the dev server must be restarted (Vite doesn't hot-reload server-level config like `https`/`host`/`allowedHosts`).
- Testing with Insomnia/curl instead of the browser: enable the cookie jar, `GET https://file-manager-backend.test/sanctum/csrf-cookie` first (send `Origin: https://app.file-manager-backend.test:5173` if the client lets you set it), then the cookie jar carries the session/XSRF cookies into `POST /login`. Note this kind of testing bypasses browser cookie enforcement (`SameSite`, `Secure`) entirely, so it can pass even when the real browser flow would fail for scheme/site reasons — verify in an actual browser too, not just curl/Insomnia.

## Notes on other files

- `file-manager-backend/CLAUDE.md` and `AGENTS.md` are still Laravel Boost's default bootstrap stub (they instruct installing `laravel/boost` and re-reading `AGENTS.md`) — Boost has not actually been run in this repo, so those files don't yet describe real project conventions.
