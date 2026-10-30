# UserFrosting 6
[![][UF6-VER-I]][UF6-VER-L]
[![][UF6-LIS-I]][UF6-LIS-L]
[![][UF6-VIT-I]][UF6-VIT-L]
[![][UF6-BUI-I]][UF6-BUI-L]
[![][UF6-COV-I]][UF6-COV-L]
[![][UF6-STY-I]][UF6-STY-L]
[![][UF6-STA-I]][UF6-STA-L]
[![][UF6-CHA-I]][UF6-CHA-L]
[![][UF6-COL-I]][UF6-COL-L]
[![][UF6-KOF-I]][UF6-KOF-L]

<!-- Links - Monorepo -->
[UF6]: https://github.com/userfrosting/monorepo
[UF6-VER-I]: https://img.shields.io/github/v/release/userfrosting/monorepo?include_prereleases
[UF6-VER-L]: https://github.com/userfrosting/monorepo/releases
[UF6-LIS-I]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[UF6-LIS-L]: LICENSE
[UF6-BUI-I]: https://img.shields.io/github/actions/workflow/status/userfrosting/monorepo/PHPUnit.yml?branch=6.0&logo=github&label=PHPUnit
[UF6-BUI-L]: https://github.com/userfrosting/monorepo/actions?query=workflow%3ABuild
[UF6-COV-I]: https://codecov.io/gh/userfrosting/monorepo/branch/6.0/graph/badge.svg
[UF6-COV-L]: https://app.codecov.io/gh/userfrosting/monorepo/branch/6.0
[UF6-STY-I]: https://github.styleci.io/repos/900493101/shield?branch=6.0&style=flat
[UF6-STY-L]: https://github.styleci.io/repos/900493101
[UF6-STA-I]: https://img.shields.io/github/actions/workflow/status/userfrosting/monorepo/PHPStan.yml?branch=6.0&label=PHPStan
[UF6-STA-L]: https://github.com/userfrosting/monorepo/actions/workflows/PHPStan.yml
[UF6-VIT-I]: https://img.shields.io/github/actions/workflow/status/userfrosting/monorepo/Frontend.yml?branch=6.0&logo=vitest&label=Vitest
[UF6-VIT-L]: https://github.com/userfrosting/monorepo/actions/workflows/Frontend.yml
[UF6-CHA-I]: https://img.shields.io/badge/Chat-UserFrosting-brightgreen?logo=Rocket.Chat
[UF6-CHA-L]: https://chat.userfrosting.com
[UF6-COL-I]: https://img.shields.io/badge/Open_Collective-Donate-blue?logo=Open%20Collective
[UF6-COL-L]: https://opencollective.com/userfrosting#backer
[UF6-KOF-I]: https://img.shields.io/badge/Ko--fi-Donate-blue?logo=ko-fi&logoColor=white
[UF6-KOF-L]: https://ko-fi.com/lcharette

## Setup

1. Clone this repository:
   ```
   git clone https://github.com/userfrosting/monorepo.git
   ```
2. Install the Composer dependencies:
   ```
   composer install
   ```
3. Run Bake to set up the application:
   ```
   php bakery bake
   ```
4. Start the PHP and Vite servers in two separate terminals:
   ```
   php bakery serve
   ```
   ```
   php bakery assets:vite
   ```

   Alternatively, use the preconfigured VSCode `==> Serve` task.

The app will be available at [http://localhost:8080](http://localhost:8080).

## Docker

[See this page](./docker/)

## Composer

The Composer side of the monorepo is managed by [Monorepo Builder](https://github.com/symplify/monorepo-builder).
Always edit the individual packages' `composer.json` files, not the root file. After each update, run:

```
vendor/bin/monorepo-builder merge
```

## Release
   
To release a new version, such as `6.0`, run:

```
vendor/bin/monorepo-builder release 6.0
```

This updates the Composer and npm package versions and creates a Git tag.

Or:

```
vendor/bin/monorepo-builder release patch
```

You can also use `minor` or `major`.


To publish the packages to the npm registry:

```
npm publish --access public --workspaces
```
