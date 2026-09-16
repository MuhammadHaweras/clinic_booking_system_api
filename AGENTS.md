<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.
## Development Philosophy

This is a portfolio project intended to demonstrate real-world Laravel backend engineering ability.

Prioritize:

- Correct business logic over superficial CRUD.
- Clear and maintainable Laravel code.
- Proper validation and authorization.
- Testable business rules.
- Security.
- Database integrity.
- Realistic API behavior.
- Good error handling.
- Appropriate use of queues, caching, rate limiting, and events when the relevant feature is implemented.

Do not add technologies merely to make the project look more complex.

## API-Only Application

This project is API-only.

- Do not create Blade views or frontend UI unless explicitly requested.
- Use API routes and JSON responses.
- Test API behavior through Postman and automated tests.
- Follow Laravel API conventions already established in the project.
- Use API Resources where appropriate according to the existing Laravel Boost guidelines.

## Authentication and Roles

Authentication uses Laravel Sanctum.

The initial roles are:

- `admin`
- `provider`
- `customer`

Public registration must not allow a user to freely register as `admin`.

Normal public registration should create a customer account unless the application's explicitly defined onboarding flow says otherwise.

Provider accounts should be created through an appropriate provider onboarding/approval flow rather than allowing arbitrary public users to self-assign the provider role.

Admin accounts must be provisioned through a controlled process and must never be created by accepting `admin` as an unrestricted public registration value.

Role checks must be enforced server-side. Never trust a role supplied by the client for authorization.

## Authorization

Authentication answers:

> Who is this user?

Authorization answers:

> What is this user allowed to do?

Do not use authentication alone to protect role-specific operations.

Every protected business operation must verify the authenticated user's authorization.

Prefer Laravel's built-in authorization mechanisms, such as policies and gates, where they fit the existing application structure.

## Appointment Domain

The core business domain is appointment booking.

The system must eventually support:

- Provider working hours.
- Provider availability.
- Existing appointments.
- Appointment conflicts.
- Cancellation.
- Rescheduling.
- Unavailable dates.
- Timezone-aware scheduling.
- Payment state.
- Appointment state.

Do not treat appointment creation as simple CRUD.

Booking logic must protect against conflicting appointments, including situations where multiple requests attempt to book the same availability concurrently.

Business rules should be covered by automated tests.

## Payments

Stripe will be used for appointment payments.

The eventual payment flow is:

Customer booking request
→ payment process
→ Stripe webhook
→ verified payment state
→ appointment confirmation

Never consider a payment successful merely because the client says it succeeded.

Stripe webhook processing must be treated as an external event and must be safely repeatable/idempotent.

Do not store sensitive payment card data in the Laravel application.

## Redis and Queues

Redis will be introduced where it provides a real application benefit.

Planned uses include:

- Availability caching.
- API/login/booking rate limiting where appropriate.
- Queued notifications and background jobs.

Do not add Redis usage solely for demonstration purposes. Each use should have a clear reason and appropriate invalidation/error-handling behavior.

## Database and Data Integrity

Before creating or modifying database structures:

1. Inspect the existing schema.
2. Check related models and migrations.
3. Understand relationships and constraints.
4. Follow existing naming conventions.
5. Consider indexes and unique constraints for business-critical data.
6. Consider foreign-key behavior and deletion rules.

Do not create a database design based only on assumptions about the existing schema.

For booking-related data, database integrity is especially important because application-level checks alone may not be sufficient to prevent race-condition-related conflicts.

## Business Logic

Keep business rules out of controllers when the logic becomes substantial.

Controllers should primarily coordinate:

- Request validation.
- Authorization.
- Application/domain operations.
- Response formatting.

Do not create unnecessary service classes for trivial operations. Introduce dedicated application/domain classes when the business logic becomes complex enough to justify them.

## Validation

Validate all client-controlled input on the server.

Never rely on Postman, frontend validation, or client-side restrictions as a security boundary.

Validation should reflect actual business rules, not merely database column types.

## API Errors

API errors should be consistent and useful.

Do not expose:

- Stack traces.
- SQL queries.
- Internal implementation details.
- Secrets.
- Sensitive payment information.

Use appropriate HTTP status codes and clear JSON error responses.

Follow existing project conventions before introducing a new error-response format.

## Testing

Testing is a first-class part of this project.

Prefer feature tests for API behavior and business workflows.

Important scenarios should include:

- Registration.
- Authentication.
- Authorization.
- Role restrictions.
- Provider availability.
- Appointment creation.
- Appointment conflicts.
- Cancellation.
- Rescheduling.
- Payment/webhook behavior.
- Queue-related behavior where applicable.

For important business rules, test both successful and failure scenarios.

Do not rely only on manually testing endpoints in Postman.

## GitHub Actions

GitHub Actions is the CI/CD mechanism for this project.

The CI pipeline should eventually verify at minimum:

- Automated tests.
- PHPStan.
- Laravel Pint.

Additional checks may be added when they provide a clear benefit.

Do not introduce Docker-based CI unless explicitly requested.

## Dependency Discipline

Do not add packages simply because they are popular or convenient.

Before adding a dependency:

1. Check whether Laravel already provides the required capability.
2. Check the currently installed package versions.
3. Confirm that the dependency solves a real project requirement.
4. Get user approval before changing application dependencies, following the existing Laravel Boost rule.

## AI-Assisted Development

When implementing a feature, understand:

- What problem the feature solves.
- Why the chosen Laravel mechanism is appropriate.
- How the database relationships work.
- How authorization is enforced.
- How the code is tested.

When debugging, prefer understanding the root cause over repeatedly generating alternative code.



## Documentation

Keep project documentation concise and useful.

Document important non-obvious business decisions and setup requirements.

Do not create additional documentation files unless explicitly requested, consistent with the existing Laravel Boost guidelines.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.


=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
