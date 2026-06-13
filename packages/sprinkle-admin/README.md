# UserFrosting 6.0 - Admin Sprinkle

[![][UF6-AD-VER-I]][UF6-AD-VER-L]
[![][UF6-AD-NPM-I]][UF6-AD-NPM-L]
[![][UF6-LIS-I]][UF6-LIS-L]
[![][UF6-CHA-I]][UF6-CHA-L]
[![][UF6-COL-I]][UF6-COL-L]
[![][UF6-KOF-I]][UF6-KOF-L]

<!-- Links -->
[UF6-AD-VER-I]: https://img.shields.io/github/v/release/userfrosting/sprinkle-admin?include_prereleases
[UF6-AD-VER-L]: https://github.com/userfrosting/sprinkle-admin/releases
[UF6-AD-NPM-I]: https://img.shields.io/npm/v/%40userfrosting%2Fsprinkle-admin
[UF6-AD-NPM-L]: https://www.npmjs.com/package/@userfrosting/sprinkle-admin
[UF6-LIS-I]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[UF6-LIS-L]: LICENSE
[UF6-CHA-I]: https://img.shields.io/badge/Chat-UserFrosting-brightgreen?logo=Rocket.Chat
[UF6-CHA-L]: https://chat.userfrosting.com
[UF6-COL-I]: https://img.shields.io/badge/Open_Collective-Donate-blue?logo=Open%20Collective
[UF6-COL-L]: https://opencollective.com/userfrosting#backer
[UF6-KOF-I]: https://img.shields.io/badge/Ko--fi-Donate-blue?logo=ko-fi&logoColor=white
[UF6-KOF-L]: https://ko-fi.com/lcharette

> [!WARNING]
> Since 6.0, this is a **read-only** subtree split of the [UserFrosting Monorepo](https://github.com/userfrosting/monorepo). To contribute, all Issues and Pull Requests should be sent against the [monorepo](https://github.com/userfrosting/monorepo). Please see the [contributing guidelines](https://github.com/userfrosting/.github/blob/main/.github/CONTRIBUTING.md) for more information.

## By [Alex Weissman](https://alexanderweissman.com) and [Louis Charette](https://bbqsoftwares.com)

Copyright (c) 2013-2026, free to use in personal and commercial software as per the [MIT license](LICENSE.md).

UserFrosting is a secure, modern user management system written in PHP and built on top of the [Slim Microframework](http://www.slimframework.com/), [Twig](http://twig.sensiolabs.org/) templating engine, [Eloquent](https://laravel.com/docs/10.x/eloquent#introduction) ORM, [Vite](https://vitejs.dev/), [Vue](https://vuejs.org/), and [UiKit](https://getuikit.com/).

This **Admin sprinkle** contains the routes and controllers to implement the administrative user management interface, as well as the group, role, and permission management interfaces.

## Installation in your UserFrosting project
To use this sprinkle in your UserFrosting project, follow theses instructions (*N.B.: This sprinkle is enabled by default when using the base app template*).

1. Require in your [UserFrosting](https://github.com/userfrosting/UserFrosting) project : 
    ``` 
    composer require userfrosting/sprinkle-admin
    ```

2. Add the Sprinkle to your Sprinkle Recipe : 
    ```php
    public function getSprinkles(): array
    {
        return [
            \UserFrosting\Sprinkle\Admin\Admin::class,
        ];
    }
    ```

3. Bake
    ```bash
    php bakery bake
    ```

## Documentation
See main [UserFrosting Documentation](https://learn.userfrosting.com) for more information.

- [Changelog](CHANGELOG.md)
- [Issues](https://github.com/userfrosting/UserFrosting/issues)
- [License](LICENSE.md)
- [Style Guide](https://github.com/userfrosting/.github/blob/main/.github/STYLE-GUIDE.md)

## Contributing

This project exists thanks to all the people who contribute. If you're interested in contributing to the UserFrosting codebase, please see our [contributing guidelines](https://github.com/userfrosting/UserFrosting/blob/5.2/.github/CONTRIBUTING.md) as well as our [style guidelines](.github/STYLE-GUIDE.md).

[![](https://opencollective.com/userfrosting/contributors.svg?width=890&button=true)](https://github.com/userfrosting/sprinkle-core/graphs/contributors)
