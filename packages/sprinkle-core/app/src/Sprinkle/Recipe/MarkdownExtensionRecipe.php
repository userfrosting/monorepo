<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprinkle\Recipe;

/**
 * Sprinkle Markdown Extensions definition Interface.
 */
interface MarkdownExtensionRecipe
{
    /**
     * Return an array of all registered Markdown Extensions.
     *
     * @return class-string<\League\CommonMark\Extension\ExtensionInterface>[]
     */
    public function getMarkdownExtensions(): array;
}
