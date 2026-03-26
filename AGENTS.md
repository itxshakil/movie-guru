<laravel-boost-guidelines>
=== .ai/00-forbidden rules ===

- Do not put business logic inside Controllers or Blade templates.
- Avoid Fat Controllers or massive "God" Services.
- Do not abuse static helpers for business logic.
- Do not use raw `request()` usage; use Form Requests and `$request->validated()`.
- Do not return raw Eloquent models from APIs; use API Resources.
- Avoid unpaginated large dataset queries.
- Do not allow unvalidated input or mass assignment vulnerabilities.
- Avoid global state mutation or hidden side effects.
- Do not use `env()` outside of configuration files.
- Never commit debug code (`dd()`, `dump()`, `ray()`, `console.log()`).
- Remove commented-out production code and unused imports.
- Never trust external input without validation and authorization.
- Avoid excessive use of Facades within core domain logic.
- Do not use outdated helper patterns or deprecated Laravel features.
- Avoid inconsistent or non-deterministic code generation.
- Generate strictly typed PHP with `declare(strict_types=1);`.
- Follow PSR-12 and Laravel idiomatic structures.
- Assume Rector, Larastan, and Pint are part of the pipeline.
- Produce testable, modular architecture by default.
- Ensure all code is statically analyzable and minimal.

=== .ai/01-coding-standards rules ===

- Prioritize readability over cleverness and consistency over creativity.
- Use RESTful controllers only (index, store, show, update, destroy).
- Move complex logic from controllers to Actions or Services.
- Follow PSR-12 coding style.
- Limit line length to 120 characters.
- Use `snake_case` for database columns, `camelCase` for variables and methods, and `PascalCase` for classes.
- Prefix methods with a verb (e.g., `updatePassword`).
- Prefix boolean methods with `is` or `has` (e.g., `isActive`).
- Use FormRequests for validation, Services for business logic, and Models for persistence.
- Format API responses as `{"success": bool, "message": string, "data": {}}` with standard HTTP codes.
- Use API Resources for all API responses; never return Eloquent models directly.
- Use the `Storage` facade for file management and organize by entity (e.g., `users/{id}/avatar.jpg`).
- Keep controllers under 30 lines per method and 8 methods per class.
- Access validated data only via `$request->validated()`; never trust `request()` directly.
- Store secrets in `.env` only and access them via `config()`.
- Use Conventional Commits (`feat:`, `fix:`, `refactor:`, `test:`, `chore:`).

=== .ai/02-architecture rules ===

- Use skinny controllers and fat services; controllers must remain thin and never perform heavy loops.
- Limit the controller role to validation, authorization, and delegation.
- Use the service layer for multi-model logic and 3rd-party integrations; keep services stateless.
- Use single-purpose Actions for atomic units of work (e.g., `CreateOrder`).
- Use idempotent Jobs for asynchronous or heavy tasks.
- Use Models as data containers; keep logic within Scopes, Accessors, or Mutators without side effects.
- Use strict PHP: include `declare(strict_types=1);`, and use mandatory typed properties and return types.
- Avoid mixed types, dynamic properties, and magic array structures.
- Use constructor injection instead of global helpers for dependency management.
- Use constructor property promotion and readonly properties where applicable.
- Use ViewModels for complex Inertia or Blade data shaping.
- Use DTOs for passing immutable data across different layers.
- Use Policies for all resource authorization logic.
- Map folders as follows:
    - `app/Actions/`: Single-purpose executors.
    - `app/Services/`: Complex business logic.
    - `app/DTOs/`: Data transfer objects.
    - `app/Enums/`: Enumerations.
    - `app/Jobs/`: Background tasks.
    - `app/Models/`: Eloquent models.
    - `app/Policies/`: Authorization logic.
    - `app/Http/Requests/`: Form validation.
    - `app/Http/Resources/`: API resources.

=== .ai/03-database rules ===

- Use plural `snake_case` for tables and `snake_case` for columns.
- Use singular `model_id` for foreign keys.
- Order pivot table names alphabetically (e.g., `post_tag`).
- Ensure each migration has a single responsibility.
- Use `constrained()->cascadeOnDelete()` for foreign key constraints.
- Index all columns used in `where`, `order by`, or as foreign keys.
- Define `fillable` or `guarded` attributes in models to prevent mass assignment.
- Declare relationships explicitly in model classes.
- Use the `casts()` method for defining attribute types (e.g., bool, array, enum).
- Use Query Scopes for shared database filters.
- Always use eager loading (`with()` or `load()`) to prevent N+1 query problems.
- Use `paginate()` by default; avoid `all()` on large datasets.
- Use Database Transactions for multi-step write operations.
- Use `chunk()` or `lazy()` when processing large result sets.
- Use `fake()` in factories and create environment-specific seeders.
- Avoid raw SQL unless explicitly justified.

=== .ai/04-testing rules ===

- Test the behavior of the application rather than its implementation.
- Use Feature Tests for Controllers, HTTP requests, database interactions, and response flows.
- Use Unit Tests for Services and isolated pure logic without database or I/O access.
- Use Policy tests for all authorization logic.
- Use sentence-style names for test methods (e.g., `it_sends_email_after_order_placed`).
- Follow the AAA (Arrange, Act, Assert) pattern in every test.
- Use Fakes for external services (e.g., `Bus::fake()`, `Mail::fake()`, `Http::fake()`).
- Use Factories for all test data; do not seed the database manually.
- Use standard assertions like `assertStatus()`, `assertJson()`, and `assertDatabaseHas()`.
- Ensure `pint`, `larastan`, and `rector` pass before finishing.
- Maintain a minimum of 70% test coverage.

=== .ai/05-security rules ===

- Use Sanctum or Passport for API authentication.
- Use Blade `{{ }}` for output escaping; avoid `{!! !!}` unless data is pre-sanitized.
- Use `@csrf` in all HTML forms.
- Never log sensitive information such as passwords, tokens, or personal identifiable information.
- Run `composer audit` regularly to check for package vulnerabilities.

=== .ai/06-performance rules ===

- Cache configurations, routes, and views in production environments.
- Use `Cache::remember()` for frequently queried data.
- Select only the necessary columns in database queries; avoid `select(*)`.
- Build frontend assets using `npm run build` and lazy-load images and JavaScript.
- Use tools like Laravel Debugbar (local) or Sentry (production) to measure performance.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines
should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an
expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/breeze (BREEZE) - v2
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- tightenco/ziggy (ZIGGY) - v2
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/vue3 (INERTIA_VUE) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that
domain—don't wait until you're stuck.

- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being
  written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions,
  converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write
  something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser
  testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets,
  mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all
  Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `inertia-vue-development` — Develops Inertia.js v2 Vue client-side applications. Activates when creating Vue pages,
  forms, or navigation; using <Link>, <Form>, useForm, or router; working with deferred props, prefetching, or polling;
  or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for:
  building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with
  sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms,
  inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case:
  writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database
  queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla
  CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling
  files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature
  tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`,
  `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`,
  `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the
  correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel
  or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the
  remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an
  array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example:
  `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`,
  not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->

```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally
  complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the
  `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests
  to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a
  specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side
  patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for
  server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list
  available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the
  correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries
  or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing
  them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other
  things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you
  should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both
  validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config
  files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be
  used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to
  use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit`
  to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run
  `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to
  ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting
  issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.

- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
