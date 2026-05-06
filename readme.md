# Coupons & Stores Admin API

A Laravel 12 / PHP 8.2 backend for a coupons-and-stores platform. It powers a Vue 2 admin panel and a public-facing site that consume its JSON endpoints.

The API manages stores, coupons, articles, countries, search filters, localized content, roles, and permissions. It ships with Passport auth, Spatie Permission, full-text search with faceted filtering, and a comprehensive feature/unit test suite.

## Highlights

- **Auth** — Passport personal access tokens against a dedicated `admins` guard
- **Authorization** — policies + form-request `authorize()` checks for every admin action
- **Roles & permissions** — Spatie Permission, with safe escalation guards
- **Localized content** — first-class GB/AR pages on stores, coupons, articles, and countries
- **Search & facets** — MySQL full-text index with country, type, and filter facets
- **Media** — local disk + a configurable shared `front` disk for the public site
- **Translation editor** — admins can edit language files through the API; public consumers read `/api/js/lang/{lang}.js`
- **Tests** — Feature and unit tests covering auth, roles, content flows, image actions, search results and facets

## Tech stack

|          |                            |
| -------- | -------------------------- |
| PHP      | 8.2                        |
| Laravel  | 12                         |
| Auth     | Laravel Passport 12        |
| RBAC     | Spatie Permission 6        |
| Database | MySQL 5.7+ / MariaDB 10.4+ |

## Project layout

```
app/
├── Http/Controllers/       # Backend (admin) and Front controllers
├── Http/Requests/          # FormRequests with rules + authorize()
├── Models/                 # Eloquent models (incl. localized accessors)
├── Policies/Backend/       # One policy per admin resource
├── Queries/                # Index/list query objects (filter + paginate)
├── Services/
│   ├── Admin/              # Admin/role/permission orchestration
│   ├── Catalog/            # Store/coupon/article/country/search-option services
│   ├── Content/            # Meta-tag service
│   ├── Media/              # Image storage
│   ├── Navigation/         # Header/link tree
│   ├── Search/             # Search query + facet services
│   └── Translations/       # Translation file editor
└── Traits/                 # Cross-cutting helpers
routes/
├── api.php                 # Public auth + composition entry point
├── api_admin.php           # Authenticated admin API
└── api_front.php           # Public front API
```

## Getting started

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
FRONT_END_STORAGE_PATH=/tmp/golden-front-storage
```

`FRONT_END_STORAGE_PATH` is the local path used by the `front` filesystem disk to share media with the public-facing site. It is optional; it defaults to `storage/app/front`.

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

## Recommended reading order

If you want to skim the most representative files first:

- [routes/api.php](routes/api.php), [routes/api_admin.php](routes/api_admin.php), [routes/api_front.php](routes/api_front.php) — composition + admin/front split
- [app/Http/Controllers/Backend/AuthController.php](app/Http/Controllers/Backend/AuthController.php) — token issuance
- [app/Http/Requests/Backend/AdminUpdateRequest.php](app/Http/Requests/Backend/AdminUpdateRequest.php) — privilege-aware role assignment
- [app/Queries/StoreIndexQuery.php](app/Queries/StoreIndexQuery.php), [app/Queries/CouponIndexQuery.php](app/Queries/CouponIndexQuery.php) — admin list filters
- [app/Services/Search/SearchQueryService.php](app/Services/Search/SearchQueryService.php), [app/Services/Search/SearchFacetService.php](app/Services/Search/SearchFacetService.php)
- [app/Models/Concerns/ResolvesLocalizedRelations.php](app/Models/Concerns/ResolvesLocalizedRelations.php) — localized page accessors
- [tests/Feature](tests/Feature), [tests/Unit](tests/Unit)
