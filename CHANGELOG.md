# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased
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
