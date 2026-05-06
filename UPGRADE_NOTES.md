# Upgrade Notes — Laravel 6 / PHP 7.2 → Laravel 12 / PHP 8.2

This is a **framework upgrade only**. We did **not** migrate the project to the
new Laravel 11/12 application skeleton. The legacy structure (`app/Http/Kernel.php`,
`app/Console/Kernel.php`, `app/Exceptions/Handler.php`, `app/Providers/RouteServiceProvider.php`,
`config/*.php`, the legacy `database/seeds/`) is preserved because Laravel 12 still
boots fine with it. A future, separate refactor can move to the new skeleton.

The Vue 2 frontend is intentionally **not** upgraded to Vue 3 — see the frontend
section below.

---

## Target versions

| Layer | From | To |
|---|---|---|
| PHP | ^7.2 | ^8.2 |
| Laravel | ^6.0 | ^12.0 |
| Laravel Passport | ^7.5 | ^12.0 |
| Spatie Permission | ^3.0 | ^6.10 |
| PHPUnit | ^8.0 | ^11.0 |
| Laravel Mix | ^4.0 | ^6.0 |
| Vue | ^2.5 | ^2.7.16 (kept on the Vue 2 line) |
| Node | unspecified | 20 LTS recommended |

---

## Backend package changes

### Added
- `laravel/framework: ^12.0`
- `laravel/passport: ^12.0`
- `laravel/tinker: ^2.10`
- `spatie/laravel-permission: ^6.10`
- `fakerphp/faker: ^1.23` (dev — replaces abandoned `fzaninotto/faker`)
- `spatie/laravel-ignition: ^2.4` (dev — replaces obsolete `facade/ignition` v1)
- `nunomaduro/collision: ^8.1` (dev)
- `mockery/mockery: ^1.6` (dev)
- `phpunit/phpunit: ^11.0` (dev)

### Removed
- `fideloper/proxy` — abandoned. Replaced by Laravel's built-in
  `Illuminate\Http\Middleware\TrustProxies`. Our middleware now extends the
  framework class and uses an explicit forwarded-headers bitmask
  (`Request::HEADER_X_FORWARDED_ALL` was removed in Symfony 5.2).
- `spatie/laravel-cors` — abandoned. Replaced by Laravel's built-in
  `Illuminate\Http\Middleware\HandleCors` and the standard `config/cors.php`
  schema. The `ALLOW_CORS` env contract is preserved.
- `smartins/passport-multiauth` — dead since Laravel 7. Replaced by native
  Passport multi-guard support. The `multiauth:admin` middleware was swapped
  for `auth:admin` in `routes/api.php`, the `multiauth` alias was dropped from
  `app/Http/Kernel.php`, and the `HasMultiAuthApiTokens` trait on
  `App\Models\Admin` was replaced with `Laravel\Passport\HasApiTokens`.
- `doctrine/dbal` — Laravel 11+ no longer needs it for `Schema::table()->...->change()`.
- `facade/ignition` (v1) — replaced with `spatie/laravel-ignition` v2.
- `fzaninotto/faker` — replaced with `fakerphp/faker`.

---

## Frontend package changes

### Vue 2 retained — **why**
- The constraint was explicit: keep Vue 2.
- The codebase uses Vue Router 3, Vuex 3, Vuetify 2, and Options API SFCs.
  Migrating to Vue 3 would require non-trivial rewrites of every component,
  the router, the store, and the UI library — out of scope for an upgrade that
  must "make the app run cleanly on Laravel 12."
- Vue 2 reached EOL in December 2023; security updates require an LTS / NES
  vendor agreement. This is documented as a remaining risk.

### `package.json`
- `vue: ^2.7.16` (final 2.x release)
- `vue-template-compiler: ^2.7.16` (must match `vue` exactly)
- `vue-loader: ^15.11.1` (Vue 2 compatible — Vue 3 uses 16+)
- `vue-router: ^3.6.5`, `vuex: ^3.6.2`, `vuetify: ^2.7.2` — final Vue 2 lines
- `laravel-mix: ^6.0.49` (Webpack 5 under the hood)
- `sass-loader: ^12.6.0`, `resolve-url-loader: ^5.0.0`, `cross-env: ^7.0.3`,
  `axios: ^1.7.7` (axios 0.x → 1.x bump; the Bearer-token contract is preserved)
- `postcss: ^8.4.41` — Mix 6 peer dep
- `engines.node: ">=18 <=20"` — pinned to Node 20 LTS first; Node 22 is **not**
  guaranteed to work with Mix 6 + Vue 2 + Webpack 5 today.
- `scripts` rewritten to use Mix's own CLI (`mix`, `mix watch`, `mix --production`).

### `webpack.mix.js`
- Added `const path = require('path')` (the legacy file referenced `path`
  without importing it — silently relied on globals removed in Webpack 5).
- Added `mix.vue({ version: 2 })` so Mix 6 selects the Vue 2 toolchain
  explicitly.
- Added `mix.version()` in production builds so cache busting works under
  Webpack 5.
- Public output paths (`public/js`, `public/css`, `public/js/{prod|dev}/chunks/`)
  are unchanged so deployed asset paths stay the same.

---

## Backend code changes

### `composer.json`
- PHP requirement: `^8.2`
- Added PSR-4 autoload entries for `Database\Seeders\` and `Database\Factories\`
- Removed `database/factories` from classmap (now PSR-4); kept `database/seeds`
  in classmap so the legacy seeders keep autoloading
- `minimum-stability: stable` (was `dev`)

### `app/Http/Kernel.php`
- Replaced `\Spatie\Cors\Cors::class` in the global stack with
  `\Illuminate\Http\Middleware\HandleCors::class`
- Renamed `$routeMiddleware` → `$middlewareAliases` (Laravel 10+ preferred name;
  the old name still works but the new one avoids future deprecations)
- Removed `'multiauth'` alias (no longer needed)

### `app/Http/Middleware/TrustProxies.php`
- Now extends `\Illuminate\Http\Middleware\TrustProxies` instead of
  `Fideloper\Proxy\TrustProxies`
- Replaced `Request::HEADER_X_FORWARDED_ALL` with an explicit bitmask
- Trusted-proxy list still loads from `config('app.trusted')` (env: `TRUSTED_IP_ADDRESS`)

### `app/Http/Middleware/CheckForMaintenanceMode.php`
- Now extends `\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance`
  (the parent class was renamed in Laravel 8). The class name is kept as
  `CheckForMaintenanceMode` so the Kernel registration is unchanged.

### `app/Http/Middleware/Authenticate.php`
- `redirectTo()` now has the L12-compatible signature (`?string` return,
  typed `Request $request` param). Behaviour for JSON requests is unchanged.

### `app/Exceptions/Handler.php`
- `report()` and `render()` accept `\Throwable` instead of `\Exception`
  (parent signature widened in Laravel 7).

### `app/Providers/RouteServiceProvider.php`
- Modernized to use `$this->routes(fn () => …)` inside `boot()` instead of the
  legacy `map()` callback. The legacy `protected $namespace = 'App\Http\Controllers'`
  is **kept** so existing route files (which reference some controllers as
  bare strings in places) keep resolving without rewrites.

### `app/Providers/AuthServiceProvider.php`
- Removed `Passport::routes()` — Passport ≥ 11 auto-registers its routes via
  its service provider; calling the helper is a runtime error.
- Token lifetimes (`tokensExpireIn`, `refreshTokensExpireIn`,
  `personalAccessTokensExpireIn`) preserved unchanged.

### `app/Models/Admin.php`
- Replaced `SMartins\PassportMultiauth\HasMultiAuthApiTokens` with
  `Laravel\Passport\HasApiTokens`. `$guard_name = 'admin'` preserved so
  Spatie Permission keeps scoping roles/permissions to the `admin` guard.

### `app/Http/Controllers/Backend/AuthController.php`
- Login no longer uses the `admin-api` session guard as a credential validator
  (a workaround for `passport-multiauth`). Credentials are now validated via
  `Auth::createUserProvider('admins')` directly. **Response shape is identical**:
  `{ user, token }`.

### `config/auth.php`
- Removed the unused `admin-api` session guard. The `admin` (Passport) guard is
  the single guard used by the API.
- Added `'throttle' => 60` to `passwords.admins` to match L12 schema (cosmetic).

### `config/cors.php`
- Rewritten to match `Illuminate\Http\Middleware\HandleCors`'s expected schema.
  `ALLOW_CORS` env behaviour preserved. `paths` defaults to `['api/*', 'oauth/*']`.

### `config/app.php`
- `'trusted'` now uses `(string) env(...)` and filters empty values to avoid
  the PHP 8.1+ `explode(',', null)` deprecation.

### `routes/api.php`
- `multiauth:admin` → `auth:admin`. All other routes, URLs, and parameters are
  unchanged.

### `routes/web.php`
- Added explicit `use Illuminate\Support\Facades\Route;` (defensive — the
  `aliases` array in `config/app.php` still loads it, but the explicit import
  insulates the file from future skeleton refactors).

### `database/seeders/DatabaseSeeder.php` (new)
- Tiny shim under `Database\Seeders` namespace that forwards to the legacy
  global `DatabaseSeeder` class so `php artisan db:seed` (which defaults to
  `Database\Seeders\DatabaseSeeder` in L12) works without `--class=…`.

### Tests
- `phpunit.xml` rewritten to PHPUnit 11 schema:
  - removed `convertErrorsToExceptions`, `convertNoticesToExceptions`,
    `convertWarningsToExceptions`, `backupStaticAttributes`
  - replaced `<filter><whitelist>` with `<source><include>`
  - removed the `<extensions><extension class="Tests\Bootstrap"/>` block
  - swapped `<server>` env entries for `<env>` and added `APP_KEY`,
    `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`
- `tests/Bootstrap.php` deleted — its `config:cache`/`event:cache` warm-up was a
  fragile perf trick built on PHPUnit 8's removed `BeforeFirstTestHook`/`AfterLastTestHook`
  interfaces. Existing tests run fine without it.
- `tests/CreatesApplication.php` — added typed return.

---

## Manual commands you should run later

> Do **not** run these against a production database; they are listed in safe
> order for a fresh local environment. Production-data migration of the
> Passport `oauth_*` tables is a separate, deliberate operation — see the
> "Remaining risks" section.

```bash
# 1. Backend dependencies
cd backend
rm -f composer.lock                                 # only if you want a fresh resolve
composer install

# 2. Publish updated package config (only if you customise these)
php artisan vendor:publish --tag=permission-config --force
php artisan vendor:publish --tag=permission-migrations
php artisan vendor:publish --tag=passport-migrations

# 3. App key (only needed if .env doesn't already have APP_KEY)
php artisan key:generate

# 4. Database — local/dev only. NEVER run on production without a backup
#    and a tested data-migration plan for the oauth_* tables.
php artisan migrate
php artisan db:seed

# 5. Passport client setup (creates personal access + password grant clients)
php artisan passport:install

# 6. Frontend
cd backend
nvm install 20 && nvm use 20    # or any Node 20.x
rm -rf node_modules yarn.lock package-lock.json
npm install
npm run development             # or `npm run production`

# 7. Quick smoke test
php artisan route:list
php artisan test
```

---

## Remaining risks

1. **Passport oauth_* schema migration.** Passport 11+ changed
   `oauth_clients`, `oauth_personal_access_clients`, `oauth_access_tokens`, and
   `oauth_refresh_tokens` to use ULID/UUID identifiers and added new columns.
   On a production database with existing tokens, you cannot just run
   `php artisan migrate` — you need to publish the new Passport migrations,
   write a data-migration plan that maps old integer IDs to the new format,
   and decide whether to invalidate existing sessions or migrate them. This
   was intentionally **not** automated — it's a deployment-time decision that
   needs DBA review.
2. **Vue 2 EOL.** Vue 2 reached end-of-life in December 2023. We retained it
   per the explicit constraint, but new CVEs will not be patched upstream.
   Plan a Vue 3 migration as a follow-up.
3. **Laravel Mix 6 is in maintenance mode.** It still works for Vue 2 + Webpack 5
   builds today, but new Webpack 5 / Node 22+ ecosystem changes may eventually
   break it. The eventual migration path is Vite — but Vite + Vue 2 needs a
   community plugin and is not "drop-in" the way Mix is.
4. **Spatie Permission v3 → v6** changed several internal cache keys and the
   way teams/guards interact. The seed/permission code in `database/seeds/`
   was reviewed and uses the still-supported public API
   (`Permission::firstOrCreate(['name' => …, 'guard_name' => 'admin'])`,
   `Role::firstOrCreate(...)`, `$role->syncPermissions(...)`,
   `$user->getAllPermissions()`), but you should still re-run a smoke test of
   admin role updates and permission inheritance after deploying.
5. **No factories on disk.** `database/factories/` is empty in this branch.
   Tests construct admins via `Admin::create([...])`. If you add factories
   later, place them under the `Database\Factories\` namespace per the new
   PSR-4 entry in `composer.json`.
6. **`config:cache` warm-up was removed from tests.** If your CI relied on it
   for speed, re-add it as a per-CI-job step (`php artisan config:cache &&
   php artisan event:cache && php artisan test && php artisan config:clear`).
7. **`admin-api` session guard removed.** It was only referenced from
   `AuthController::login`, which now validates credentials via the user
   provider directly. Grep your deployment scripts / queue workers / Horizon
   config for any leftover `Auth::guard('admin-api')` references before going live.

---

## Follow-up cleanup tasks (not blocking the upgrade)

- Move `database/seeds/` → `database/seeders/` (rename namespaces, drop the
  classmap entry, drop the shim) once you're ready to do a small refactor.
- Move legacy `App\Models\Model` callers to extend `Illuminate\Database\Eloquent\Model`
  directly; the wrapper only adds `$guarded = []` and a hidden timestamps list.
- Replace the explicit `providers` and `aliases` arrays in `config/app.php`
  with the modern auto-discovery + `app/aliases.php` once the rest of the app
  is stable on L12.
- Plan a Vue 3 migration (or Vue 2 → Vue 2.7 → Vue 3) to retire the EOL frontend.
- Plan a Vite migration as a separate task once Vue is on a non-EOL line.
