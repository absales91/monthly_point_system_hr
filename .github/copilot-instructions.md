**Purpose**
- **Goal:** Help AI coding agents be immediately productive in this Laravel project.

**Project Overview**
- **Framework:** Laravel 12 skeleton with Livewire (see `composer.json`).
- **High-level flow:** HTTP routes in [routes/web.php](routes/web.php) → Controllers in [app/Http/Controllers](app/Http/Controllers) → Domain logic in [app/Services](app/Services) → Persistence via Eloquent models in [app/Models](app/Models) and migrations in [database/migrations](database/migrations).

**Key Patterns & Conventions**
- **Service layer:** Domain logic lives in `app/Services` and uses static methods (e.g. [RewardEngineService::run](app/Services/RewardEngineService.php) and [EmployeeOfMonthService::generate](app/Services/EmployeeOfMonthService.php)). Prefer adding new domain rules in a Service rather than inflating controllers.
- **DB transactions & safety checks:** Services wrap multi-row writes with `DB::transaction()` and guard against duplicate runs by checking existence then throwing exceptions. Follow this pattern when implementing scheduled or bulk operations.
- **Eloquent usage:** Use relationships and query helpers (`with`, `whereHas`, `orderByDesc`) as shown in [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php) and services.
- **Wallet / reward logic:** Reward allocation is rule-driven: read [app/Services/RewardEngineService.php](app/Services/RewardEngineService.php) for examples of thresholding, limiting `max_per_month`, and `RewardWallet` updates.

**Developer Workflows (commands)**
- Install & setup: `composer run setup` (runs composer install, env setup, migrate, npm build).
- Local dev: `composer run dev` to start `php artisan serve`, queue listener and Vite; or run `php artisan serve` and `npm run dev` separately for simpler sessions.
- Tests: `composer run test` (invokes `php artisan test`). Project uses Pest — prefer `php artisan test` or `vendor/bin/pest`.
- Database: use `php artisan migrate` and `php artisan db:seed` as needed. The repository contains migration files under [database/migrations](database/migrations).

**Files to inspect when working on features**
- Domain logic and bulk operations: [app/Services/RewardEngineService.php](app/Services/RewardEngineService.php)
- Employee-of-month rules: [app/Services/EmployeeOfMonthService.php](app/Services/EmployeeOfMonthService.php)
- Controllers that prepare view data: [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php)
- Routes entrypoints: [routes/web.php](routes/web.php)
- Models and relationships: [app/Models](app/Models)

**What to change vs. what to preserve**
- Preserve existing transactional guards and existence checks for recurring batch jobs. Services intentionally throw exceptions to indicate precondition failures — callers rely on that behavior.
- Avoid renaming migrations or changing migration timestamps; altering historical migration files can break CI/dev environments.

**Examples of good edits**
- Adding a new reward rule: create a `RewardRule` record and a corresponding admin UI route/controller. Follow the patterns in `RewardRuleController` routes in [routes/web.php](routes/web.php).
- Implementing a new bulk job: add a Service method, wrap writes in `DB::transaction()`, and add idempotency checks before creating records (see `RewardEngineService`).

**Behavioral guidance for the agent**
- Be conservative with schema changes — suggest migrations but flag potential migration-timestamp issues.
- When modifying services that affect points/wallets, include unit tests and a short migration rollback plan.
- Reference the exact files above when proposing code edits; include a one-line rationale for each change.

**If unsure, ask the human**
- Whether a change must be backwards-compatible with production data (e.g., wallet adjustments).
- Expected admin roles and permissions for routes affecting rewards or points.

---
If you want, I can iterate on this draft, add explicit code examples, or merge any existing agent docs if you have them. What would you like changed? 
