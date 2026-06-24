# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- [Core] Update Google Analytics tracking code to GA4 format in `analytics.html.twig`. Note: This is a breaking change for users who have customized this template with the old Universal Analytics code. You will need to update your custom template to use the new GA4 code format.
- [Core] Removed `pages/partials/config.js.twig` and `pages/partials/page.js.twig` templates, as they were not being used anymore. This also removes the `site` JavaScript global variable, which is not used by Vue. If you were using `site` in your custom JavaScript, you will need to update your code to use a different method of passing configuration data to the frontend (e.g. implement back in your custom Twig templates).
- [Core] Update Bake command title ASCII art.
- [Account] Fix user not appearing in the user list if no activity has been recorded for the user yet (see [sprinkle-account#25](https://github.com/userfrosting/sprinkle-account/pull/25)).
- [Core] Fix [#33](https://github.com/userfrosting/monorepo/issues/33): UF's language codes do not always map to valid HTML lang codes.
- [Core + Pink-Cupcake] Add CSV download functionality to the Sprunje tables UI.
- [Account] Make `create:user` retry interactive input after validation errors (including password mismatch) instead of aborting immediately.
- [Core + Pink-Cupcake] Allow Sprunjer table to set the default page size to "all" to display all rows in a single page.
- [Pink-Cupcake + Admin] Refactor role and user management modals to utilize Sprunje component for improved performance and maintainability.
- [Pink Cupcake] Improve Sprunje table overflow handling for better UI responsiveness.

## 6.0.0 - 2026-06-12
- No changes.

## 6.0.0-rc.5 - 2026-06-03

### Changed
- Bump minimum Node.js requirement from 18 to 20 across all packages (Vitest 4 requires `^20 || ^22 || >=24`).
- Bump `@vitest/coverage-v8` from `^3.1.1` to `^4.1.0` to align with Vitest 4.
- Update Frontend CI matrix: replace Node 18 with Node 20.

## 6.0.0-rc.4 - 2026-05-28

### Security
- Force `js-yaml` to `4.1.1` via npm `overrides` to fix prototype pollution vulnerability in merge (`<<`) operator ([GHSA-mh29-5h37-fv8m](https://github.com/advisories/GHSA-mh29-5h37-fv8m)) introduced transitively via `@modyfi/vite-plugin-yaml`. Temp fix until `@modyfi/vite-plugin-yaml` is updated to use `js-yaml` 4.1.1 or later.

## 6.0.0-rc.3 - 2026-05-16

### Fixed
- [Core] `Markdown::getFilePath()` now performs a case-insensitive fallback lookup when the exact-match locator call returns nothing, fixing 404s on case-sensitive filesystems when URL casing differs from the filename (e.g. `tos` → `Tos.md`).
- [Framework] `DotenvEditor::save()` now preserves existing file permissions and defaults to `0644` for new files, preventing the web server from being locked out of a `.env` file created via `php bakery setup:env`.
- [Core] `debug:version` now warns when `.env` exists but cannot be read, helping diagnose deployment permission issues.
- [Docker] Fix `docker/mysql/Dockerfile` not applying `mysql.conf` — `innodb_use_native_aio=0` was silently ignored because the file was never copied into the image.
- [Docker] Fix duplicate `memory_limit` directive in `docker/app/php/custom.ini` (64M was immediately overridden by 512M).
- [Skeleton][Docker] Fix `.env.docker` `SMTP_PORT` from 2025 to 1025 (Mailpit's actual SMTP port).
- [Skeleton][Docker] Remove unused `DB_ROOT_PASSWORD` and `ROOT_PASSWORD` variables from `.env.docker`.
- [Skeleton][Docker] Set `UF_MODE=debug` (was empty) in `.env.docker`.

### Changed
- [Core] `FilePermissionMiddleware` now caches a successful permission check for a configurable TTL (`cache.file_permission.ttl`), skipping redundant `is_writable()` calls on subsequent requests.
- [Core] `NODE_VERSION` and `NPM_VERSION` container entries in `VersionsService` are now lazily evaluated, avoiding `exec()` calls on every web request.
- [Core] Bump Composer dependency `userfrosting/vite-php-twig` from `^1.0.2` to `^1.2.0`.

### Added
- [Core] New config keys `cache.file_permission.key` and `cache.file_permission.ttl` to control permission-check caching. Production default is 3600 s.
- [Core/Skeleton] Add `vite_css_preload()` Twig function via `userfrosting/vite-php-twig` 1.2.0, emitting `<link rel="preload" as="style">` hints for CSS files. `stylesheets_site.html.twig` now calls it before `vite_css()`.
- [Skeleton + Monorepo][Docker] Upgrade Node.js from 22 to 24 in all Docker configurations.

## 6.0.0-rc.2 - 2026-05-12
- [Skeleton] Fix package dependency issue.

## 6.0.0-rc.1 - 2026-05-12
- [Skeleton] Remove `composer.lock` from `.gitignore`
- [Skeleton] Load `.env` file in Vite config, including the `VITE_PORT` variable
- [Skeleton] Add Vitest config
- [Skeleton] Add Frontend Github Action template to Skeleton
- [Skeleton] Add `format-dry-run` npm script
- [Core] Remove `site.debug.ajax` config (legacy jQuery flag)
- [Core] Change `PHP_RECOMMENDED_VERSION` to PHP 8.5
- [Framework] Replace `jackiedo/dotenv-editor` with our own implementation to avoid dependency on an unmaintained package.
- [Monorepo] Renamed `dev` npm script to `vite:dev` for consistency with Skeleton + Added `vite:build`
- Update all READMEs
- Update dependencies with Dependabot

## 6.0.0-beta.8 - 2026-01-13
- Packages now ship built modules instead of source code. All `package.json`, Vite configs and scripts have been updated accordingly.
- Specify node engine version in each packages
- [Skeleton] Updated `vite.config.ts` : Other packages removed from `optimizeDeps`
- [Monorepo] Add `.github/copilot-instructions.md` 
- [Monorepo] Update Workflows and Tasks
- [Skeleton] Convert GitHub Actions workflow files to reusable templates
- [Skeleton] Docker - Suppress PHP warnings in custom PHP ini

## 6.0.0-beta.7 - 2025-12-30
- [Core] Add markdown parser extension system via MarkdownExtensionRecipe. Sprinkles can now register custom markdown extensions through the new MarkdownRepositoryInterface.
- [Pink-Cupcake] Add scroll-padding-top to prevent content from being hidden behind fixed headers when using anchor links
- [Pink-Cupcake] Minor UI tweaks to sidebar components
- [Pink-Cupcake] CSS: Add webkit-font-smoothing
- [Core] MarkdownService now uses configuration from the config service to customize markdown parser behavior. New `markdown` config section added with `html_input`, `allow_unsafe_links`, and `max_nesting_level` options.
- [Skeleton + Monorepo][Docker] Update tp PHP 8.4
- [Core] Add `SERVE_PORT` env variable for the built-in PHP Server.
- [Admin] Fix missing crumbs in Permission detail page
- [Pink-Cupcake] Improved footer structure
- [Skeleton + Pink-Cupcake] Add mobile navigation
- [Skeleton] Restructure Navbar/Sidebar/Footer Content files
- [Pink-Cupcake] Renamed components : FooterContent -> UFFooter
- [Pink-Cupcake] Added `SideBarUserCard` component

## 6.0.0-beta.6 - 2025-11-23
- Update Docker Readme
- Add tests for PHP 8.5
- [Admin] Update Limax frontend dependency + [Skeleton] Remove Limax from optimizeDeps (4.2.0 is now an ESModule)
- [Core] Fix deprecation with `thephpleague/csv`
- [Core] Update assets config to be more robust in edge cases in production
- [Skeleton] Add example file on how to overwrite the Less/CSS theme
- [Pink-Cupcake] Fix fontsource resources not being found when importing the main Less file in skeleton
- [Pink-Cupcake] Change body font to Mulish
- [Pink-Cupcake] Improved sidebar theming, enabling a light variant with a single variable
- [Pink-Cupcake] Fix icon not all having `fixed-width` in sidebar

## 6.0.0-beta.5 - 2025-09-28
- Bump Vite and Axios versions 
- Add Docker config for Monorepo
- [Skeleton] Update Docker setup (@ssnukala)
- [Core] Run `vite:build` in production mode when `assets:build` is used
- [Framework] Fix Resource getBasePath: Fix edge case where the location path is the same as the locator base path (eg. the location is in the main sprinkle)
- [Framework] Add new Resource methods to help find resource parent location : `getDirUri`, `getRelativeDirname`, `getAbsoluteDirname`, `getDirname` and `isDir`
- [Framework] Improve Resource handling when the resource is a directory
- [Core + Skeleon] Go back to Vite default port (`5173`) + allows Vite port to be in env variable

## 6.0.0-beta.4 - 2025-09-08
- [Core] Fix missing Composer dependency
- [Account] Replace default groups since the icons are no longer available
- [Skeleton] Add exclude list for optimizeDeps in Vite config
- [Skeleton] Add Admin Sprinkle to `main.ts`
- [Pink-Cupcake] Add missing components from exports of Account and Admin pages
- [Framework] Remove unused Fortress JS files

## 6.0.0-beta.3 - 2025-09-07
- Add 'limax' to optimizeDeps (Prevents `importing binding name 'default' cannot be resolved by star export entries`)
- Add YAML loader and replace `?raw` to load yaml files as string
- Update Eslint config
- [Pink Cupcake] Add missing index files references in `package.json`
- [Skeleton] [Fix : user can be null per Typescript](https://github.com/userfrosting/monorepo/commit/dd6a9e15b0d43856745f641e4738b0527b44e76c)
- Cleanup `package.json` scripts & unused dev dependencies
- [Monorepo + Skeleton] Update VSCode tasks
- [Monorepo] Add Eslint & Typescript to CI Action
- Add/fix type definition
- Fix various tests

## 6.0.0-beta.2 - 2025-09-03
- Fix frontend package version reference
- Fix schemas not available in npm packages
- Revert previous comment preventing assets to be served in production mode

## 6.0.0-beta.1 - 2025-09-02
- Add [Regle](https://reglejs.dev) for frontend form validation
- Replace deprecated birke/rememberme 2.0 with mober/rememberme 5.1
- Add Terms of Service and Privacy Policy functionality
- Rename Sprunje "item" to "row"
- Fix modal issue when the element has a dot in the slug
- Log username and slug in edit forms
- Update support for Slim 4.15

## 6.0.0-alpha.6 - 2025-07-07
- Add AlertsStore + Notification Plugin
- Add Axios interceptor for global error handling
- Add Axios interceptor for unsetting the user on 401 errors
- Add username suggestion feature to registration form
- Fix user-created mail template
- Rename files for consistency & convention
- Refactor frontend error management
- Update dependencies

## 6.0.0-alpha.5 - 2025-05-23
- Implement CSRF protection
- Add remember me checkbox
- New user email verification
- Refactor account-related views and tests
- Split User Enabled vs Email verified in admin views
- Refactor password reset functionality
- Update Vite version
- Cleanup Monorepo

This version include new migrations:
- DropPasswordResetsTable : Drop the PasswordResets table
- DropVerificationsTable : Drop the Verifications table
- UpdateUsersTable : Add `password_last_set` column to the User table
- UserVerificationTable : Add new common table for all user verifications 

## 6.0.0-alpha.4 - 2025-03-31
- Add frontend permission check.
- Permission's `conditions` field is **DEPRECATED**. This field cannot be verified on the frontend and is prone to confusion. A better permission system is planned for UF6.1 which will make this field obsolete anyway.
- All built in permissions are updated to `always()`, plus :
  - `create_user_field` : Removed. Will be replaced by group permissions management in 6.1;
  - `delete_user` : Conditions changed to `always()`. Master ID is manually enforced in the controller. Role isn't enforced anymore;
  - `update_user_field`: Conditions changed to `always()`. Was giving access to all field anyway. More granular permissions should be added in 6.1 (for enable, password, etc.);
  - `update_user_field_group` : Removed. The ability to edit users in your own group is removed. Granular group permissions management will be added in 6.1;
  - `update_user_field_role` : Renamed the slug to `update_user_role`. 
  - `update_role_field` : Conditions changed to `always()`. Was giving access to all field anyway. Ability to change permissions could be added in 6.1;
  - New `uri_group_own` slug to view the group page of your own group.
  - Permission with `uri_user` slug which allowed to view the user page of any user in your group, except the master user and Site and Group Administrators: Renamed to `uri_user_in_group` and allow to view the user page of any user in your group period.
  - Permission with `view_group_field` slug which allowed to view certain properties of your own group : Changed the slug to `view_group_field_own` and allow to view properties of your own group.
  - `view_role_field`: Conditions changed to `always()`. Was giving access to all field anyway. Ability to change permissions could be added in 6.1;
  - `view_user_field`: Conditions changed to `always()`.
  - `view_user_field_group` : Removed. The condition was too complex.
- New permissions with slugs `view_user_activities` & `view_user_roles`
- Move System Info to new Config Page and add Cache clearing UI
- Login redirect to the previous page
- Fix type error present in alpha.3

## 6.0.0-alpha.3 - 2025-03-01
- Implement Frontend Translations
- Replace Moment with Luxon
- Remove old locales & cleanup French & English dictionaries 

## 6.0.0-alpha.2 - 2025-01-22
- First real Alpha release of UserFrosting 6
