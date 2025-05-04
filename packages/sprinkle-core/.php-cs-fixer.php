<?php

$header = 'UserFrosting Core Sprinkle (http://www.userfrosting.com)

@link      https://github.com/userfrosting/sprinkle-core
@copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
@license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)';

$rules = [
    'header_comment' => [
        'header'       => $header
    ]
];
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app/src',
        __DIR__ . '/app/tests',
    ]);
$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRiskyAllowed(true);
