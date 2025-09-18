<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\UniformResourceLocator;

use PHPUnit\Framework\TestCase;
use UserFrosting\UniformResourceLocator\Normalizer;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceLocationInterface;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;
use UserFrosting\UniformResourceLocator\ResourceStreamInterface;

/**
 * Tests for the example code in the docs/Readme.md.
 */
class DocTest extends TestCase
{
    /**
     * Setup the shared locator.
     */
    public function testDocExample(): void
    {
        // Create Locator
        $locator = new ResourceLocator(__DIR__ . '/app/');

        // Register Locations
        $locator->addLocation(new ResourceLocation('Floor1', 'floors/Floor1/'));
        $locator->addLocation(new ResourceLocation('Floor2', 'floors/Floor2/'));

        // Register Streams
        $locator->addStream(new ResourceStream('config'));
        $locator->addStream(new ResourceStream('upload', 'uploads/', true));

        // Finding Files
        $defaultResource = $locator->getResource('config://default.json');
        $this->assertEquals($this->getBasePath() . 'app/floors/Floor2/config/default.json', $defaultResource);
        $this->assertInstanceOf(ResourceInterface::class, $defaultResource);
        $this->assertSame($this->getBasePath() . 'app/floors/Floor2/config/default.json', $defaultResource->getAbsolutePath());
        $this->assertSame('floors/Floor2/config/default.json', $defaultResource->getPath());
        $this->assertSame('default.json', $defaultResource->getBasePath());
        $this->assertSame('default.json', $defaultResource->getBasename());
        $this->assertSame('json', $defaultResource->getExtension());
        $this->assertSame('default', $defaultResource->getFilename());
        $this->assertSame('config://default.json', $defaultResource->getUri());

        // GetLocation
        $defaultResourceLocation = $defaultResource->getLocation();
        $this->assertInstanceOf(ResourceLocationInterface::class, $defaultResourceLocation);
        $this->assertSame('Floor2', $defaultResourceLocation->getName());
        $this->assertSame('floors/Floor2/', $defaultResourceLocation->getPath());

        // GetStream
        $defaultResourceStream = $defaultResource->getStream();
        $this->assertInstanceOf(ResourceStreamInterface::class, $defaultResourceStream); // @phpstan-ignore-line
        $this->assertSame('config/', $defaultResourceStream->getPath());
        $this->assertSame('config', $defaultResourceStream->getScheme());
        $this->assertSame(false, $defaultResourceStream->isShared());
        $this->assertSame(false, $defaultResourceStream->isReadonly());

        // GetResources
        $defaults = $locator->getResources('config://default.json');
        $this->assertContainsOnlyInstancesOf(ResourceInterface::class, $defaults);
        $this->assertSame([
            $this->getBasePath() . 'app/floors/Floor2/config/default.json',
            $this->getBasePath() . 'app/floors/Floor1/config/default.json',
        ], array_map('strval', $defaults)); // N.B.: array_map is not required if `assertEquals` is used. But we do as the doc.

        // Finding Files - upload://profile
        // GetResource
        $uploadResource = $locator->getResource('upload://profile/');
        $this->assertInstanceOf(ResourceInterface::class, $uploadResource);
        $this->assertEquals($this->getBasePath() . 'app/uploads/profile', $uploadResource);
        $this->assertSame($this->getBasePath() . 'app/uploads/profile', $uploadResource->getAbsolutePath());
        $this->assertSame('uploads/profile', $uploadResource->getPath());
        $this->assertSame('profile', $uploadResource->getBasePath());
        $this->assertSame('', $uploadResource->getBasename());
        $this->assertSame('', $uploadResource->getExtension());
        $this->assertSame('', $uploadResource->getFilename());
        $this->assertSame('upload://profile', $uploadResource->getUri());

        // Side note, `getPath` doesn't add a `/` at the end, because NormalizePath normalize this behavior.
        // In any case, `upload://profile` and `upload://profile/` are equivalent.
        $this->assertSame('uploads/profile', $locator->getResource('upload://profile/')?->getPath());

        // GetResources
        $defaults = $locator->getResources('upload://profile');
        $this->assertSame([
            $this->getBasePath() . 'app/uploads/profile',
        ], array_map('strval', $defaults)); // N.B.: array_map is not required if `assertEquals` is used. But we do as the doc.

        // ListResources
        $list = $locator->listResources('config://');
        $this->assertEquals([
            $this->getBasePath() . 'app/floors/Floor1/config/debug.json',
            $this->getBasePath() . 'app/floors/Floor2/config/default.json',
            $this->getBasePath() . 'app/floors/Floor2/config/foo/bar.json',
            $this->getBasePath() . 'app/floors/Floor2/config/production.json',
        ], $list);

        // ListResources - All
        $list = $locator->listResources('config://', all: true);
        $this->assertEquals([
            $this->getBasePath() . 'app/floors/Floor1/config/debug.json',
            $this->getBasePath() . 'app/floors/Floor1/config/default.json',
            $this->getBasePath() . 'app/floors/Floor2/config/default.json',
            $this->getBasePath() . 'app/floors/Floor2/config/foo/bar.json',
            $this->getBasePath() . 'app/floors/Floor2/config/production.json',
        ], $list);

        // ListResources - Sort
        $list = $locator->listResources('config://', sort: false);
        $this->assertEquals([
            $this->getBasePath() . 'app/floors/Floor2/config/default.json',
            $this->getBasePath() . 'app/floors/Floor2/config/foo/bar.json',
            $this->getBasePath() . 'app/floors/Floor2/config/production.json',
            $this->getBasePath() . 'app/floors/Floor1/config/debug.json',
        ], $list);

        // ListResources - Folder
        $list = $locator->listResources('config://foo/', all: true);
        $this->assertEquals([
            $this->getBasePath() . 'app/floors/Floor2/config/foo/bar.json',
        ], $list);

        // GetStreams
        $streams = $locator->getStreams();
        $this->assertCount(2, $streams);
        $this->assertContainsOnlyInstancesOf(ResourceStreamInterface::class, $streams['config']);
        $this->assertContainsOnlyInstancesOf(ResourceStreamInterface::class, $streams['upload']);

        // listSchemes
        $streams = $locator->listSchemes();
        $this->assertSame([
            'config',
            'upload',
        ], $streams);

        // getStream
        $streams = $locator->getStream('upload');
        $this->assertContainsOnlyInstancesOf(ResourceStreamInterface::class, $streams);
        $this->assertCount(1, $streams);
        $this->assertSame('uploads/', $streams[0]->getPath());

        // GetLocations
        $locations = $locator->getLocations();
        $this->assertContainsOnlyInstancesOf(ResourceLocationInterface::class, $locations);
        $this->assertCount(2, $locations);
        $this->assertSame('floors/Floor2/', $locations['Floor2']->getPath());

        // listLocations
        $locations = $locator->listLocations();
        $this->assertSame([
            'Floor2',
            'Floor1',
        ], $locations);

        // GetLocation
        $location = $locator->getLocation('Floor1');
        $this->assertSame('floors/Floor1/', $location->getPath());
    }

    protected function getBasePath(): string
    {
        return Normalizer::normalizePath(__DIR__);
    }
}
