# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [6.0.0] - 2026-06-12
- No changes.

## [6.0.0-rc.5] - 2026-06-03

### Changed
- Bump minimum Node.js engine requirement from `>= 18` to `>= 20`.

## [6.0.0-rc.4] - 2026-05-28
- No changes.

## [6.0.0-rc.3] - 2026-05-16

### Changed
- `NavBar`: replaced `uk-sticky` JS-driven wrapper with CSS `position: sticky` on the `<header>` element, eliminating the FOUC/layout reflow caused by UIkit's deferred placeholder insertion.

## [6.0.0-rc.2](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-rc.1...6.0.0-rc.2)
- No changes

## [6.0.0-rc.1](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.8...6.0.0-rc.1)
- No changes

## [6.0.0-beta.8](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.7...6.0.0-beta.8)
- Now ship built modules instead of source code
- Specify node engine version

## [6.0.0-beta.7](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.6...6.0.0-beta.7)
- Add scroll-padding-top to prevent content from being hidden behind fixed headers when using anchor links
- Minor UI tweaks to sidebar components
- Renamed sidebar class
- CSS: Add webkit-font-smoothing
- Improved footer structure
- Add mobile navigation
- Renamed components : FooterContent -> UFFooter
- Added `SideBarUserCard` component

## [6.0.0-beta.6](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.5...6.0.0-beta.6)
- Fix fontsource resources not being found when importing the main Less file in skeleton
- Change body font to Mulish
- Improved sidebar theming, enabling a light variant with a single variable
- Fix icon not all having `fixed-width` in sidebar

## [6.0.0-beta.5](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.4...6.0.0-beta.5)
- Bump Vite and Axios versions 

## [6.0.0-beta.4](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.3...6.0.0-beta.4)
- Add missing components from exports of Account and Admin pages

## [6.0.0-beta.3](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.1...6.0.0-beta.2)
- Add missing index files references in `package.json`
- Add/fix type definition
- Cleanup `package.json` scripts & unused dev dependencies

## [6.0.0-beta.2](https://github.com/userfrosting/theme-pink-cupcake/compare/6.0.0-beta.1...6.0.0-beta.2)
- Update dependencies to version 6.0.0-beta across all packages

## 6.0.0-beta.1
First beta release of UserFrosting 6
