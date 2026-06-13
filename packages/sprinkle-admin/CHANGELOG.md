# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [6.0.0](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-rc.5...6.0.0) - 2026-06-12
- No changes.

## [6.0.0-rc.5](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-rc.4...6.0.0-rc.5) - 2026-06-03

### Changed
- Bump minimum Node.js engine requirement from `>= 18` to `>= 20`.

## [6.0.0-rc.4](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-rc.3...6.0.0-rc.4) - 2026-05-28
- No changes.

## [6.0.0-rc.3] - 2026-05-16
- No changes.

## [6.0.0-rc.2](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-rc.1...6.0.0-rc.2)
- No changes

## [6.0.0-rc.1](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.8...6.0.0-rc.1)
- No changes

## [6.0.0-beta.8](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.7...6.0.0-beta.8)
- Now ship built modules instead of source code
- Specify node engine version

## [6.0.0-beta.7](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.6...6.0.0-beta.7)
- Fix missing crumbs in Permission detail page
- Fix wrong permission in SystemInfoApiAction

## [6.0.0-beta.6](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.5...6.0.0-beta.6)
- Update Limax frontend dependency

## [6.0.0-beta.5](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.4...6.0.0-beta.5)
- Bump Vite and Axios versions 

## [6.0.0-beta.4](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.3...6.0.0-beta.4)
- No changes

## [6.0.0-beta.3](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.2...6.0.0-beta.3)
- Add YAML loader 

## [6.0.0-beta.2](https://github.com/userfrosting/sprinkle-admin/compare/6.0.0-beta.1...6.0.0-beta.2)
- Add schema s to the final npm build
- Update tests
- Add/fix type definition
- Cleanup `package.json` scripts & unused dev dependencies

## 6.0.0-beta.1
First beta release of UserFrosting 6

## [5.2.0](https://github.com/userfrosting/sprinkle-admin/compare/5.1.0...5.2.0)

## [5.1.3](https://github.com/userfrosting/sprinkle-admin/compare/5.1.2...5.1.3)
- [Fix] Locale is not displayed on the user page

## [5.1.2](https://github.com/userfrosting/sprinkle-admin/compare/5.1.1...5.1.2)
- Fix Unable to create a user without a group on MySQL (Fix [#1273](https://github.com/userfrosting/UserFrosting/issues/1273))

## [5.1.1](https://github.com/userfrosting/sprinkle-admin/compare/5.1.0...5.1.1)
- Fix issue when a Group Administrator without the `create_user_field` permission creates a new user, the new user SHOULD inherit the admin's group (Fix [#1256](https://github.com/userfrosting/UserFrosting/issues/1256))

## [5.1.0](https://github.com/userfrosting/sprinkle-admin/compare/5.0.1...5.1.0)
- Drop PHP 8.1 support, add PHP 8.3 support
- Update to Laravel 10
- Update to PHPUnit 10
- Test against MariaDB [#1238](https://github.com/userfrosting/UserFrosting/issues/1238)
- Update FontAwesome 6 references

## [5.0.2](https://github.com/userfrosting/sprinkle-admin/compare/5.0.1...5.0.2)
- Fix editing a role permissions erase all permissions - Fix [#1240](https://github.com/userfrosting/UserFrosting/issues/1240)

## [5.0.1](https://github.com/userfrosting/sprinkle-admin/compare/5.0.0...5.0.1)
- Update success message when admin resets password for a user - Fix [#852](https://github.com/userfrosting/UserFrosting/issues/852)

## [5.0.0-alpha3](https://github.com/userfrosting/sprinkle-admin/compare/5.0.0-alpha2...5.0.0-alpha3)
- Update cache clearing action.
  
## [5.0.0-alpha2](https://github.com/userfrosting/sprinkle-admin/compare/5.0.0-alpha1...5.0.0-alpha2)
- [Exceptions] Update exception inheritance.