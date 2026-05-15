# UserFrosting 6 Monorepo - AI Coding Agent Instructions

## Architecture Overview

**UserFrosting** is a PHP/JavaScript user management framework built on Slim, Twig, Eloquent ORM, Vue 3, and Vite. Version 6 is a **monorepo** containing multiple interdependent packages.

### Sprinkle Architecture

The core architectural pattern is **Sprinkles** - composable modules that extend functionality through dependency injection:

- **Sprinkle Recipe**: Implements `SprinkleRecipe` interface with methods:
    - `getName()`: Human-readable name
    - `getPath()`: Absolute path to sprinkle directory
    - `getSprinkles()`: Array of dependent sprinkle class names (loaded recursively, dependencies first)
    - `getRoutes()`: Route definition classes
    - `getServices()`: PHP-DI service provider classes

- **Optional Recipe Interfaces**:
    - `BakeryRecipe`: Custom CLI commands via `getBakeryCommands()`
    - `MiddlewareRecipe`: PSR-15 middleware via `getMiddlewares()`
    - `EventListenerRecipe`: Event listeners via `getEventListeners()`
    - `TwigExtensionRecipe`: Twig extensions via `getTwigExtensions()`
    - `MigrationRecipe`: Database migrations via `getMigrations()`

**Example**: [MyApp.php](../packages/skeleton/app/src/MyApp.php) depends on Core → Account → Admin sprinkles. Dependencies are resolved automatically by `SprinkleManager`.

### Package Structure

```
packages/
  framework/         # Core abstractions (Sprinkle system, UniformResourceLocator, PHP-DI bridges)
  sprinkle-core/     # Essential services (config, DB, Twig, sessions, i18n, alerts, throttling)
  sprinkle-account/  # Authentication, users, roles, permissions
  sprinkle-admin/    # Admin UI (users, roles, permissions management)
  theme-pink-cupcake/# UIKit-based theme (optional)
  skeleton/          # Full application template (dev entry point in monorepo)
```

`skeleton` is the main app that ties everything together. While it is the main entry point for the monorepo, it should be used on it's own by the end user, with other sprinkles added as dependencies through Composer and NPM. Skeleton is meant as a starting point for new projects, and each file can be customized as needed.

## Dual Build System (PHP + TypeScript)

### PHP Side

1. **Composer monorepo** is managed by [symplify/monorepo-builder](https://github.com/symplify/monorepo-builder)
2. **Critical**: Edit individual `packages/*/composer.json`, NOT root `composer.json`
3. After edits, run: `vendor/bin/monorepo-builder merge` to sync root composer.json
4. Composer type for packages: `userfrosting-sprinkle`

### TypeScript/Frontend Side

1. **NPM workspaces**: All sprinkles are npm packages with frontend assets
2. **Monorepo dev mode**: Uses `userfrosting:monorepo` export condition in package.json
    - Points to source `.ts` files for HMR (Hot Module Reload)
    - Vite resolves imports to source code, not dist builds
    - Root [vite.config.ts](../vite.config.ts) sets `conditions: ['userfrosting:monorepo', 'import']`
3. **Production build**: Each sprinkle builds to `dist/` with conditional exports:
    ```json
    "exports": {
      ".": {
        "userfrosting:monorepo": "./app/assets/index.ts",  // Dev: source
        "import": "./dist/index.js",                        // Prod: built
        "types": "./dist/index.d.ts"
      }
    }
    ```
4. **Key frontend pattern**: Sprinkles expose composables, stores, routes, views via subpath exports
    - Example: `import { useAuthStore } from '@userfrosting/sprinkle-account/stores'`
    - See [sprinkle-core/package.json](../packages/sprinkle-core/package.json) exports

## Critical Workflows

### Development Servers

**Option 1 - VS Code Task** (recommended):

```bash
# Run "==> Serve" task from Command Palette (Cmd/Ctrl+Shift+P)
# Starts both PHP + Vite in parallel
```

**Option 2 - Manual**:

```bash
# Terminal 1: PHP dev server
php bakery serve

# Terminal 2: Vite dev server with HMR
npm run vite:dev
```

App: http://localhost:8080 | Vite: http://localhost:5173

### Building Packages

**Build all sprinkle TypeScript packages**:

```bash
npm run build
# Compiles each workspace to dist/ with type definitions
```

**Build specific sprinkle**:

```bash
npm run build:core      # @userfrosting/sprinkle-core
npm run build:account   # @userfrosting/sprinkle-account
npm run build:admin     # @userfrosting/sprinkle-admin
npm run build:theme     # @userfrosting/theme-pink-cupcake
```

**IMPORTANT**: The monorepo dev mode (`userfrosting:monorepo` condition) means Vite HMR works WITHOUT building. Only build when:

- Testing production builds
- Publishing to NPM
- Verifying type definitions

### Testing

**PHP tests** (PHPUnit):

```bash
# Run all tests
vendor/bin/phpunit

# Run specific suite
vendor/bin/phpunit --testsuite="Core Sprinkle"
vendor/bin/phpunit --testsuite="Framework"

# Or use VS Code task "PHPUnit"
```

Running all tests can be time consuming. During development, run tests for specific files or suites as needed first, then run the full suite before committing.

```bash
vendor/bin/phpunit tests/src/SomeClassTest.php
```

**Frontend tests** (Vitest):

```bash
npm run test           # Watch mode
npm run coverage       # With coverage report
```

- Root [vitest.config.ts](../vitest.config.ts) runs tests from all workspace packages
- Uses `happy-dom` environment
- Coverage:
    - PHP : `_meta/coverage/`
    - JS : `_meta/_coverage/`

All changes must be covered by tests. New features require new tests and 100% code coverage for all new changes is required. Legacy code coverage is not mandatory.

### Database Setup

```bash
php bakery bake        # Run migrations + seeders
php bakery migrate     # Migrations only
```

## Project Conventions

### File Organization

| Type            | Path                           |
| --------------- | ------------------------------ |
| PHP source      | `packages/*/app/src/`          |
| PHP tests       | `packages/*/app/tests/`        |
| Frontend source | `packages/*/app/assets/`       |
| Frontend tests  | `packages/*/app/assets/tests/` |
| Templates       | `packages/*/app/templates/`    |
| Config          | `packages/*/app/config/`       |
| Routes          | `packages/*/app/src/Routes/`   |

### Naming Patterns

| Type              | Pattern          | Example                               |
| ----------------- | ---------------- | ------------------------------------- |
| Sprinkle class    | `{SprinkleName}` | `Core`, `Account`, `Admin`            |
| Service providers | `*Service.php`   | `DatabaseService`, `TwigService`      |
| Routes            | `*Routes.php`    | implements `RouteDefinitionInterface` |
| Bakery commands   | `*Command.php`   | extends Symfony Console Command       |
| PHP tests         | `*Test.php`      | —                                     |
| TS tests          | `*.spec.ts`      | —                                     |

### Dependency Injection

1. Uses **PHP-DI** with autowiring enabled
2. Constructor injection preferred
3. Attributes supported (`#[Inject]`)
4. Service definitions in classes implementing `ServicesProviderInterface`
5. Example: [TwigService.php](../packages/sprinkle-core/app/src/ServicesProvider/TwigService.php)

### Frontend State Management

- **Pinia stores** for global state (persisted with `pinia-plugin-persistedstate`)
- Key stores: `useAuthStore`, `useConfigStore`, `useTranslator`, `useAlertsStore`
- Import pattern: `from '@userfrosting/sprinkle-*/stores'`

### Vue Router Integration

Routes defined per-sprinkle, merged in skeleton:

```typescript
// packages/skeleton/app/assets/router/index.ts
import AccountRoutes from '@userfrosting/sprinkle-account/routes'
import AdminRoutes from '@userfrosting/sprinkle-admin/routes'

const router = createRouter({
    routes: [...ErrorRoutes, ...AccountRoutes, ...AdminRoutes]
})
```

## Linting and Code Style

These **must** be run before committing code, from the root of the monorepo:

1. [ ] PHP code style: `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php`
2. [ ] PHPStan static analysis: `vendor/bin/phpstan analyse -c phpstan.neon`
3. [ ] TypeScript lint: `npm run lint`
4. [ ] TypeScript format: `npm run format`
5. [ ] TypeScript type check: `npm run typecheck`

## Changelog Guidelines

**Every change must include a changelog update** — do this automatically, without waiting to be asked:

1. Update the relevant `packages/*/CHANGELOG.md` file(s) for affected packages
2. Add entries under the `## [Unreleased]` section
3. Follow the existing format (Added, Changed, Deprecated, Removed, Fixed, Security)
4. If the change affects multiple packages, update all relevant changelogs
5. For significant changes, also update the root `CHANGELOG.md`

**Before committing changes** (in order):

1. [ ] Run PHP CS Fixer: `vendor/bin/php-cs-fixer fix`
2. [ ] Run PHPStan: `vendor/bin/phpstan analyse`
3. [ ] Run ESLint + Prettier: `npm run lint && npm run format`
4. [ ] Ensure all tests pass: `vendor/bin/phpunit` and `npm run test`
5. [ ] Write clear, descriptive commit messages

## Release Process

**Before Releasing**:

1. Ensure all tests pass (PHPUnit + Vitest)
2. Build all packages: `npm run build`
3. Merge composer.json changes: `vendor/bin/monorepo-builder merge`
4. Make sure all changelogs are updated in `packages/*/CHANGELOG.md` + root `CHANGELOG.md`

If any command fails, warn the user and abort the release process until all issues are resolved.

**Creating a new version** (handled by monorepo-builder):

```bash
vendor/bin/monorepo-builder release 6.0.1   # Specific version
vendor/bin/monorepo-builder release patch   # Auto-increment patch
vendor/bin/monorepo-builder release minor   # Auto-increment minor
```

This updates:

- All `packages/*/composer.json` versions
- All `packages/*/package.json` versions (via custom [NpmVersionWorker](../utils/NpmVersionWorker.php))
- Creates git tag
- Root composer.json

**Publish to NPM**:

```bash
npm publish --access public --workspaces
```

## Common Pitfalls

1. **Editing root composer.json directly**: Always edit package-level files, then merge
2. **Missing builds**: In prod, sprinkles need `dist/` built; in dev, monorepo mode bypasses this and MUST use source TS files
3. **Sprinkle order matters**: Dependencies are loaded first, then main sprinkle
4. **Vite HMR not working**: Check `userfrosting:monorepo` condition is set in root vite.config.ts
5. **PHP-DI not resolving**: Verify service provider is registered in sprinkle's `getServices()`

## Key Files to Reference

- [SprinkleManager.php](../packages/framework/src/Sprinkle/SprinkleManager.php) - Sprinkle loading logic
- [Cupcake.php](../packages/framework/src/Cupcake.php) - Base application class
- [MyApp.php](../packages/skeleton/app/src/MyApp.php) - Example sprinkle implementation
- [vite.config.ts](../vite.config.ts) - Monorepo Vite configuration
- [monorepo-builder.php](../monorepo-builder.php) - Composer monorepo config

## Resources

- [Official Docs](https://learn6.userfrosting.com)
- [Chat](https://chat.userfrosting.com)
