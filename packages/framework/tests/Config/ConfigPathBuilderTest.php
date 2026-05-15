<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

use PHPUnit\Framework\TestCase;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\UniformResourceLocator\Normalizer;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;

class ConfigPathBuilderTest extends TestCase
{
    protected string $basePath;

    protected ResourceLocator $locator;

    public function setUp(): void
    {
        $this->basePath = Normalizer::normalizePath(__DIR__ . '/data');
        $this->locator = new ResourceLocator($this->basePath);

        // Add them as locations to simulate how they are added in SprinkleManager
        $this->locator->addLocation(new ResourceLocation('core'))
                      ->addLocation(new ResourceLocation('account'))
                      ->addLocation(new ResourceLocation('admin'))
                      ->addStream(new ResourceStream('config'));
    }

    public function testDefault(): void
    {
        // Arrange
        $builder = new ConfigPathBuilder($this->locator, 'config://');

        // Act
        $paths = $builder->buildPaths();

        $this->assertEquals([
            $this->basePath . 'core/config/default.php',
            $this->basePath . 'account/config/default.php',
            $this->basePath . 'admin/config/default.php',
        ], $paths);
    }

    public function testEnvironmentMode(): void
    {
        // Arrange
        $builder = new ConfigPathBuilder($this->locator, 'config://');

        // Act
        $paths = $builder->buildPaths('production');

        $this->assertEquals([
            $this->basePath . 'core/config/default.php',
            $this->basePath . 'core/config/production.php',
            $this->basePath . 'account/config/default.php',
            $this->basePath . 'account/config/production.php',
            $this->basePath . 'admin/config/default.php',
            $this->basePath . 'admin/config/production.php',
        ], $paths);
    }
}
