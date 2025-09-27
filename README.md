# UserFrosting 6

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build](https://img.shields.io/github/actions/workflow/status/userfrosting/monorepo/Frontend.yml?branch=6.0&logo=vitest&label=Vitest)](https://github.com/userfrosting/monorepo/actions/workflows/Frontend.yml)
[![PHPUnit](https://img.shields.io/github/actions/workflow/status/userfrosting/monorepo/PHPUnit.yml?branch=6.0&logo=github&label=PHPUnit)](https://github.com/userfrosting/monorepo/actions/workflows/PHPUnit.yml)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/userfrosting/monorepo/PHPStan.yml?branch=6.0&logo=github&label=PHPStan)](https://github.com/userfrosting/monorepo/actions/workflows/PHPStan.yml)
[![Codecov](https://codecov.io/gh/userfrosting/monorepo/branch/6.0/graph/badge.svg)](https://app.codecov.io/gh/userfrosting/monorepo/branch/6.0)
[![StyleCI](https://github.styleci.io/repos/900493101/shield?branch=6.0&style=flat)](https://github.styleci.io/repos/900493101)
[![Join the chat](https://img.shields.io/badge/Chat-UserFrosting-brightgreen?logo=Rocket.Chat)](https://chat.userfrosting.com)
[![Donate](https://img.shields.io/badge/Open_Collective-Donate-blue?logo=Open%20Collective)](https://opencollective.com/userfrosting#backer)
[![Donate](https://img.shields.io/badge/Ko--fi-Donate-blue?logo=ko-fi&logoColor=white)](https://ko-fi.com/lcharette)

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
