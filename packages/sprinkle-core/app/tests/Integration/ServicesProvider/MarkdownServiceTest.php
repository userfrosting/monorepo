<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use League\CommonMark\ConverterInterface;
use UserFrosting\Sprinkle\Core\Markdown\MarkdownRepositoryInterface;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test MarkdownService implementation works.
 */
class MarkdownServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(ConverterInterface::class, $this->getService(ConverterInterface::class)); // @phpstan-ignore-line
        $this->assertInstanceOf(MarkdownRepositoryInterface::class, $this->getService(MarkdownRepositoryInterface::class)); // @phpstan-ignore-line
    }

    public function testMarkdownConversion(): void
    {
        /** @var ConverterInterface */
        $converter = $this->getService(ConverterInterface::class);

        $markdown = '# Hello World';
        $result = $converter->convert($markdown);
        $html = (string) $result;

        $this->assertStringContainsString('<h1>Hello World</h1>', $html);
    }

    public function testGithubFlavoredMarkdown(): void
    {
        /** @var ConverterInterface */
        $converter = $this->getService(ConverterInterface::class);

        // Test strikethrough (GFM feature)
        $markdown = '~~strikethrough~~';
        $result = $converter->convert($markdown);
        $html = (string) $result;

        $this->assertStringContainsString('<del>strikethrough</del>', $html);
    }

    public function testFrontMatter(): void
    {
        /** @var ConverterInterface */
        $converter = $this->getService(ConverterInterface::class);

        $markdown = <<<'MD'
---
title: Test Page
author: John Doe
---

# Content

This is the content.
MD;

        $result = $converter->convert($markdown);
        $html = (string) $result;

        // Front matter should be parsed and not displayed in HTML
        $this->assertStringNotContainsString('---', $html);
        $this->assertStringContainsString('<h1>Content</h1>', $html);
        $this->assertStringContainsString('This is the content.', $html);
    }
}
