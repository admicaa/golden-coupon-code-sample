# Coupons & Stores Admin

A Laravel code sample from a real coupons and stores platform. The repo holds the backend API and the Vue 2 admin panel that ran on top of it. The public-facing site lived in a separate repo and consumed the JSON endpoints under `/api/front/*`.

The admin side manages stores, coupons, articles, countries, search filters, localized content, roles, and permissions.

Originally built on Laravel 6, PHP 7.2, and Vue 2. The backend was upgraded to Laravel 12 and PHP 8.2. Vue 2 and the legacy Laravel app structure were kept on purpose.

## What it includes

- Admin authentication with Passport personal access tokens
- Role and permission management with Spatie Permission
- Store, coupon, article, country, and filter management
- Localized content pages (default locales `GB` and `AR`)
- Search and facet filtering with a denormalized index table
- Image upload to a local disk plus a configurable shared "front" disk
- Vue 2 admin panel built with Laravel Mix 6
- Feature and unit tests covering auth, role updates, content flows, search, and translations

## Tech stack

Backend

- PHP 8.2
- Laravel 12
- Laravel Passport 12
- Spatie Permission 6
- MySQL 5.7+ or MariaDB 10.4+

Frontend

- Vue 2.7
- Vue Router 3
- Vuex 3
- Vuetify 2
- Laravel Mix 6
- Node 20 LTS recommended

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set the basics in `.env`:

```env
DB_DATABASE=goldencoupon
DB_USERNAME=root
DB_PASSWORD=
ALLOW_CORS=http://localhost:8080
FORNT_END_STORAGE_PATH=/tmp/golden-front-storage
```

`FORNT_END_STORAGE_PATH` keeps the spelling from the original project so existing deploys do not break. Renaming it is a follow-up.

Database and Passport:

```bash
php artisan migrate
php artisan db:seed
php artisan passport:install
```

Frontend:

```bash
nvm use 20
npm install
npm run development
```

Run the app:

```bash
php artisan serve
```

## Demo admin

```txt
email: admin@admin.com
password: 1234admin
```

These credentials are for local development only.

POST them to `POST /api/login/admin`. The response is `{ "user": {...}, "token": "<bearer>" }`. Send the token as `Authorization: Bearer <token>` for any `/api/*` route under the `auth:admin` guard.

## Useful commands

```bash
php artisan route:list
php artisan test
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## Reviewer notes

Good files to start with:

- `app/Http/Controllers/Backend/AuthController.php`
- `app/Http/Requests/Backend/AdminUpdateRequest.php`
- `app/Services/Search/SearchFacetService.php`
- `app/Services/Search/SearchQueryService.php`
- `app/Models/Concerns/ResolvesLocalizedRelations.php`
- `tests/Feature`
- `tests/Unit`

## Legacy notes

- The project started as Laravel 6 and was upgraded to Laravel 12.
- Vue 2 was kept on purpose. No Vue 3 migration in this sample.
- The legacy Laravel app structure (`Kernel.php`, `Handler.php`, `RouteServiceProvider.php`) was kept on purpose.
- Some naming and schema choices still reflect the original codebase.

Known limitations and follow-ups are in `KNOWN_LIMITATIONS.md`.
