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

1. Clone this repo
   ```
   git clone https://github.com/userfrosting/monorepo.git
   ```
2. Run Composer
   ```
   composer install
   ```
3. Run Bake
   ```
   php bakery serve
   ```
4. Run at the same time the PHP server and the Vite server is **two terminals**:
   ```
   php bakery assets:vite
   ```
   ```
   php bakery serve
   ```

   ...or use the VSCode `==> Serve` pre-configured task.

The app will be available at [http://localhost:8080](http://localhost:8080).

## Docker

[See this page](./docker/)

## Composer

The monorepo is managed by [Monorepo-builder](https://github.com/symplify/monorepo-builder) on the Composer side.   
It's important to edit the individual package `composer.json`, not the root one. After each update, run this command.

```
vendor/bin/monorepo-builder merge
```

## Release
   
To release a new version, for example `6.0`, run this command. Composer will be updated, as well as NPM and a git tag will be pushed using the local user :

```
vendor/bin/monorepo-builder release 6.0
```

Or:

```
vendor/bin/monorepo-builder release patch
```

You can use `minor` and `major` too.


To publish the packages to NPM repo : 

```
npm publish --access public --workspaces
```
