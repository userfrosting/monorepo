# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [6.0.0-rc.5] - 2026-06-03

### Changed
- Bump minimum Node.js engine requirement from `>= 18` to `>= 20`.

## [6.0.0-rc.4] - 2026-05-28
- No changes.

## [6.0.0-rc.3] - 2026-05-16
- No changes.

## [6.0.0-rc.2](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-rc.1...6.0.0-rc.2)
- No changes

## [6.0.0-rc.1](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.8...6.0.0-rc.1)
- No changes

## [6.0.0-beta.8](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.7...6.0.0-beta.8)
- Now ship built modules instead of source code
- Specify node engine version

## [6.0.0-beta.7](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.6...6.0.0-beta.7)
- No changes

## [6.0.0-beta.6](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.5...6.0.0-beta.6)
- No changes

## [6.0.0-beta.5](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.4...6.0.0-beta.5)
- Bump Vite and Axios versions 

## [6.0.0-beta.4](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.3...6.0.0-beta.4)
- Replace default groups since the icons are no longer available

## [6.0.0-beta.3](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.2...6.0.0-beta.3)
- Add YAML loader 
- Add/fix type definition
- Cleanup `package.json` scripts & unused dev dependencies

## [6.0.0-beta.2](https://github.com/userfrosting/sprinkle-account/compare/6.0.0-beta.1...6.0.0-beta.2)
- Update dependencies to version 6.0.0-beta across all packages
- Add schema s to the final npm build
- Update tests

## 6.0.0-beta.1
First beta release of UserFrosting 6

## [5.2.0](https://github.com/userfrosting/sprinkle-account/compare/5.1.0...5.2.0)

## [5.1.6](https://github.com/userfrosting/sprinkle-account/compare/5.1.5...5.1.6)
- Add PHP 8.4 support & tests

## [5.1.5](https://github.com/userfrosting/sprinkle-account/compare/5.1.4...5.1.5)
- `Authenticator::checkAccess` wouldn't use the logged in user if it wasn't loaded before

## [5.1.4](https://github.com/userfrosting/sprinkle-account/compare/5.1.3...5.1.4)
- Update SiteLocale for sprinkle-core 5.1.3
- User field `deleted_at` can be null if the user has not been deleted

## [5.1.3](https://github.com/userfrosting/sprinkle-account/compare/5.1.2...5.1.3)
- Fix another SQL issue when working with extending the User model with an auxiliary table.

## [5.1.2](https://github.com/userfrosting/sprinkle-account/compare/5.1.1...5.1.2)
- [Fix #1252](https://github.com/userfrosting/UserFrosting/issues/1252) - For Permission & Role

## [5.1.1](https://github.com/userfrosting/sprinkle-account/compare/5.1.0...5.1.1)
- [Fix #1252](https://github.com/userfrosting/UserFrosting/issues/1252) - Column not found when extending the User (or Group) model 

## [5.1.0](https://github.com/userfrosting/sprinkle-account/compare/5.0.1...5.1.0)
- Drop PHP 8.1 support, add PHP 8.3 support
- Update to Laravel 10
- Update to PHPUnit 10
- Test against MariaDB [#1238](https://github.com/userfrosting/UserFrosting/issues/1238)
- Add missing roles to DefaultPermissions seed
- `UserActivityLogger` now implements `UserActivityLoggerInterface` + constants in `UserActivityLogger` moved to `UserActivityTypes` enum
- Fix link in password reset email

## [5.0.3](https://github.com/userfrosting/sprinkle-account/compare/5.0.2...5.0.3)
- Fix exception thrown when empty user is serialized ([userfrosting/sprinkle-account#15](https://github.com/userfrosting/sprinkle-account/pull/15))

## [5.0.2](https://github.com/userfrosting/sprinkle-account/compare/5.0.1...5.0.2)
- Fix issue with `has_role` access condition

## [5.0.1](https://github.com/userfrosting/sprinkle-account/compare/5.0.0...5.0.1)
- Add deliberate warning when Mail exception occurs during registration - Fix [#1229](https://github.com/userfrosting/UserFrosting/issues/1229)

## [5.0.0-alpha4](https://github.com/userfrosting/sprinkle-account/compare/5.0.0-alpha3...5.0.0-alpha4)
- Update PHP-DI

## [5.0.0-alpha3](https://github.com/userfrosting/sprinkle-account/compare/5.0.0-alpha2...5.0.0-alpha3)
- [Exceptions] Account exception extend UserFacingException (corresponding handler haven been removed)

## [5.0.0-alpha2](https://github.com/userfrosting/sprinkle-account/compare/5.0.0-alpha1...5.0.0-alpha2)
- [Migration] Fix dependencies in AddingForeignKeys
- [Testing] Add WithTestUser Trait
- [CI] Add PHP 8.2 to test suite
