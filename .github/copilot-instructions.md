# Copilot Instructions (admin9-backend-laravel)

Concise, project-specific guidance for AI coding agents. Reflect existing patterns; never introduce speculative abstractions.

**Critical: Follow Laravel best practices in all implementations.** Use framework conventions (Eloquent relationships, resource controllers, form requests when appropriate, service containers, events/listeners). Leverage Laravel's built-in features rather than reinventing solutions.

## Core Architecture

Two auth principals: `Admin` (auto-increment id) & `User` (Snowflake id). JWT multi-guard (`admin`, `user`) configured in `config/auth.php`. Use helpers `admin()` / `user()` (see `app/Support/helpers.php`) instead of `auth()->user()`. Routes separated: `routes/admin.php` (admin-only), `routes/api.php` (public + user auth). Controllers currently lean but may delegate to services in `app/Services/{Domain}/` when logic grows. Enums in `app/Enums/` (e.g. `Role`) drive role/logic; never compare raw strings, call `$model->role() === Role::ADMIN`.

## IDs & JWT

`User` uses Snowflake via `mitoop/laravel-snowflake` (`$table->snowflake('id')`); `Admin` uses default increment. JWT subject is hashid-encoded for integer IDs via `HasJWT` trait (see `app/Models/Traits/HasJWT.php`) with fixed issuer `admin9`. Always refresh/invalidate tokens on the correct guard (`auth('user')->refresh()` vs `auth('admin')->invalidate(true)`). Do not mix guards; mismatches silently fail.

## Request Flows

Unified user login endpoint: `POST /api/auth/login` with `channel` = `password|sms|email` (extend channel logic, never add new endpoints). Password reset: `POST /api/auth/password/resets` then `PUT /api/auth/password/resets/{token}`. Verification codes: single endpoint `POST /api/auth/verification-codes` (throttled `throttle:10,1`). Admin auth: `POST /admin/login`, `/admin/logout` kept minimal. OAuth: `GET /api/auth/oauth/{provider}` redirects via Socialite; callback creates/links `UserProvider` record and issues JWT in cookie. Provider config relies on Laravel Socialite; credentials in `.env` (e.g., `GITHUB_CLIENT_ID`).

## Responses & Errors

Use base `Controller` helpers from `mitoop/laravel-api-response`: `$this->success($data)`, `$this->deny($msg)`, `$this->unauthorized($msg)`. Format service-layer caught exceptions with `format_exception($e)` for compact trace; never craft ad-hoc JSON arrays. Maintain a uniform envelope.

## Services & Validation

Controllers use `ValidatesRequests` trait for inline validation (see `app/Http/Controllers/Controller.php`). For complex logic, create services under `app/Services/{Domain}/` (directory doesn't exist yet—add when needed). Base `Controller` extends `RespondsWithJson` trait from `mitoop/laravel-api-response` providing `$this->success()`, `$this->deny()`, `$this->unauthorized()` methods.

## Testing & Tooling

Tests optional. Run with `composer test` (script resets config, runs PHPUnit). Prefer enum assertions over string roles. Generate IDE stubs: `composer ide-helper`. Code style: `composer pint`. Dev loop (server + queue + Vite + logs): `composer dev`.

## Data Layer & Migrations

Snowflake column helper: `$table->snowflake('id')` for users; admins use default increments. Models implement `RoleAware` interface (see `app/Models/Contracts/RoleAware.php`) requiring `role(): Role` method. Keep model `$fillable` minimal—add new attributes deliberately. Use factories in `database/factories/` instead of manual `new` for test entities.

## Extensibility Patterns

Add new enums instead of constants. Persist enum backed values directly. Extend login channels within existing service logic; NEVER create `login-sms` style routes. Use helper `argon()` for hashing if needed (check existing patterns). Reuse throttle middleware signatures like `throttle:10,1`.

## Localization (i18n)

`SetLocale` middleware exists (`app/Http/Middleware/SetLocale.php`) checking `Accept-Language` header. Language files under `lang/{locale}` (e.g. `zh_CN`, `en`). Structured keys in grouped PHP files (`enums.php`, `messages.php`, `validation.php`); full-sentence UI strings can go into `{locale}.json`. Enum display labels resolve via `__("enums.role.$enumValue")`. Keep snake locale codes (`zh_CN` not `zh-CN`). Avoid hardcoded text in controllers/services—translate at the edge.

## External Packages

JWT: `php-open-source-saver/jwt-auth`; ID encoding: `vinkla/hashids`; Snowflake IDs: `mitoop/laravel-snowflake`; Enums: `archtechx/enums`; API responses: `mitoop/laravel-api-response`. Prefer these abstractions—do not duplicate features.

## Logging & Debugging

`composer dev` streams logs (pail) + queue worker; avoid redundant `dump()`/`var_dump()`. Use `logger()->info()` with structured arrays. Telescope present but disabled; do not enable in production. For noisy failures use `format_exception($e)` and return a denial with user-safe message.

## Common Pitfalls

1. Mixing guards when refreshing/invalidation.
2. Hardcoding role strings instead of enum comparison.
3. Adding extra auth endpoints instead of extending service logic.
4. Handcrafting JSON responses instead of controller helpers.
5. Skipping Pint—causes noisy diffs.
6. **Ignoring Laravel conventions**: Don't bypass Eloquent, avoid raw queries when ORM suffices, use built-in validation over manual checks.

## Laravel Best Practices to Follow

-   **Eloquent over Query Builder**: Use models & relationships unless performance demands raw SQL
-   **Route Model Binding**: Leverage implicit/explicit binding instead of manual `findOrFail()`
-   **Form Requests**: For complex validation, extract to dedicated FormRequest classes
-   **Single Responsibility**: Keep controllers thin (delegate to services), services focused (one concern per class)
-   **Dependency Injection**: Type-hint dependencies in constructors; let service container resolve
-   **Collections**: Use Laravel collections methods (`map`, `filter`, `pluck`) over raw loops
-   **Middleware**: Extract cross-cutting concerns (auth checks, logging) into middleware, not controller methods
-   **Eager Loading**: Always `with()` relationships to avoid N+1 queries
-   **Job Queues**: Offload slow tasks (emails, API calls) to queued jobs when needed
-   **Cache Wisely**: Use `Cache::remember()` for expensive queries; tag caches for easy invalidation

Reference [Laravel Docs](https://laravel.com/docs) when implementing features. Prefer framework patterns over custom solutions.

## Quick Start

1. `composer install && npm install`
2. Copy `.env.example` if missing; run `php artisan key:generate`
3. `php artisan migrate`
4. `composer dev`

## Feature Checklist

1. (Optional) Enum addition in `app/Enums/`
2. Service under `app/Services/{Domain}` if logic is complex
3. Controller method with inline validation via `$this->validate()`
4. Route added to proper file with `auth:{guard}` if protected
5. (Optional) Feature test if complex
6. Run `composer pint` (+ `composer test` if added tests)

Refer to existing route & controller patterns before introducing new abstractions. Keep changes incremental and consistent.
