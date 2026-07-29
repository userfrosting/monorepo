<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;

/*
 * Define version requirements and validators.
 */
class VersionsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // Minimum requirements
            'PHP_MIN_VERSION'              => '^8.3',
            'PHP_RECOMMENDED_VERSION'      => '^8.5',
            'NODE_MIN_VERSION'             => '>=20',
            'NPM_MIN_VERSION'              => '>=9',

            // Installed version
            'PHP_VERSION'                  => phpversion(),
            'NODE_VERSION'                 => function () {
                $v = exec('node -v');

                return $v !== false ? $v : '';
            },
            'NPM_VERSION'                  => function () {
                $v = exec('npm -v');

                return $v !== false ? $v : '';
            },

            // Version validators
            PhpVersionValidator::class     => function (ContainerInterface $c) {
                return new PhpVersionValidator($c->get('PHP_VERSION'), $c->get('PHP_MIN_VERSION'));
            },

            PhpDeprecationValidator::class => function (ContainerInterface $c) {
                return new PhpDeprecationValidator($c->get('PHP_VERSION'), $c->get('PHP_RECOMMENDED_VERSION'));
            },

            NodeVersionValidator::class    => function (ContainerInterface $c) {
                return new NodeVersionValidator($c->get('NODE_VERSION'), $c->get('NODE_MIN_VERSION'));
            },

            NpmVersionValidator::class     => function (ContainerInterface $c) {
                return new NpmVersionValidator($c->get('NPM_VERSION'), $c->get('NPM_MIN_VERSION'));
            },
        ];
    }
}
