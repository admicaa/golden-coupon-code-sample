# Coupons & Stores Admin (API)

A Laravel code sample from a real coupons and stores platform. This repo is the backend API only. The Vue 2 admin panel and the public-facing site live in separate repos and consume these JSON endpoints.

The API manages stores, coupons, articles, countries, search filters, localized content, roles, and permissions.

Originally built on Laravel 6 and PHP 7.2. The backend was upgraded to Laravel 12 and PHP 8.2. The legacy Laravel app structure was kept on purpose.

## What it includes

- Admin authentication with Passport personal access tokens
- Role and permission management with Spatie Permission
- Store, coupon, article, country, and filter management
- Localized content pages (default locales `GB` and `AR`)
- Search and facet filtering with a denormalized index table
- Image upload to a local disk plus a configurable shared "front" disk
- Translation files endpoints consumed by the SPA repo
- Feature and unit tests covering auth, role updates, content flows, search, and translations

## Tech stack

- PHP 8.2
- Laravel 12
- Laravel Passport 12
- Spatie Permission 6
- MySQL 5.7+ or MariaDB 10.4+

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
- The legacy Laravel app structure (`Kernel.php`, `Handler.php`, `RouteServiceProvider.php`) was kept on purpose.
- Some naming and schema choices still reflect the original codebase.
