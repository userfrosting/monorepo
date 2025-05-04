<?php

$header = 'UserFrosting Framework (http://www.userfrosting.com)

@link      https://github.com/userfrosting/framework
@copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
@license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)';

$rules = [
    'header_comment' => [
        'header'       => $header,
    ]
];
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ]);
$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRiskyAllowed(true);
