<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Represent a Markdown file.
 */
class MarkdownFile
{
    public readonly string $content;

    /** @var mixed[] */
    public readonly array $frontMatter;

    /**
     * @param string  $content
     * @param mixed[] $frontMatter
     */
    public function __construct(string $content, array $frontMatter = [])
    {
        $this->content = $content;
        $this->frontMatter = $frontMatter;
    }
}
