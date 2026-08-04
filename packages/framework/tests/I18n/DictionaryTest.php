<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\I18n;

use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\I18n\Dictionary;
use UserFrosting\I18n\Locale;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceStream;

class DictionaryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected string $basePath;

    protected ResourceLocator $locator;

    public function setUp(): void
    {
        $this->basePath = __DIR__ . '/data/dictionary';
        $this->locator = new ResourceLocator($this->basePath);

        $this->locator->addStream(new ResourceStream('locale', shared: true));
    }

    public function testGetLocale(): void
    {
        $locale = Mockery::mock(Locale::class);
        $locator = Mockery::mock(ResourceLocator::class);
        $dictionary = new Dictionary($locale, $locator); //<-- Test no fileLoader too

        $this->assertSame($locale, $dictionary->getLocale());
    }

    public function testGetDictionary_withNoDependentLocaleNoData(): void
    {
        // Prepare mocked locale - aa_bb
        /** @var Locale */
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare mock Locator - Return no file
        /** @var Mockery\MockInterface&ResourceLocator */
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->with('locale://aa_bb', true, false)->andReturn([])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        /** @var ArrayFileLoader */
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldNotReceive('setPaths')
            ->shouldNotReceive('load')
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals([], $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withNoDependentLocaleNoData
     */
    public function testSetUri(): void
    {
        // Prepare mocked locale - aa_bb
        /** @var Mockery\MockInterface&Locale */
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare mock Locator - Return no file
        /** @var Mockery\MockInterface&ResourceLocator */
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->with('foo://aa_bb', true, false)->andReturn([])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        /** @var Mockery\MockInterface&ArrayFileLoader */
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldNotReceive('setPaths')
            ->shouldNotReceive('load')
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $dictionary->setUri('foo://');
        $this->assertEquals([], $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withNoDependentLocaleNoData
     */
    public function testGetDictionary_withNoDependentLocaleWithData(): void
    {
        // Set expectations
        $expectedResult = ['Foo' => 'Bar'];

        // Prepare mocked locale - aa_bb
        /** @var Mockery\MockInterface&Locale */
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare mock Resource - File `Foo/Bar/File1.php`
        /** @var Mockery\MockInterface&\UserFrosting\UniformResourceLocator\Resource */
        $file = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->andReturn('php')
            ->shouldReceive('__toString')->andReturn('Foo/Bar/File1.php')
            ->getMock();

        // Prepare mock Locator - Return the file
        /** @var Mockery\MockInterface&ResourceLocator */
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->with('locale://aa_bb', true, false)->andReturn([$file])
            ->getMock();

        // Prepare mock FileLoader - Will return the mock file, with a mock data
        /** @var ArrayFileLoader */
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('setPaths')->with(['Foo/Bar/File1.php'])
            ->shouldReceive('load')->andReturn($expectedResult)
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withNoDependentLocaleWithData
     */
    public function testGetDictionary_withNoDependentLocaleWithManyFiles(): void
    {
        // Set expectations
        $expectedResult = [
            'Foo'  => 'Bar',
            'Bar'  => 'Foo',
            'test' => [
                'Bar' => 'Rab',
                'Foo' => 'Oof',
            ],
        ];

        // Prepare mocked locale - aa_bb
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->once()->andReturn([])
            ->shouldReceive('getIdentifier')->twice()->andReturn('aa_bb')
            ->getMock();

        // Prepare first mock Resource - File `Foo/Bar/File1.php`
        $file1 = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->once()->andReturn('php')
            ->shouldReceive('__toString')->times(2)->andReturn('Foo/Bar/File1.php')
            ->getMock();

        // Prepare second mock Resource - File `Bar/Foo/File2.php`
        $file2 = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->once()->andReturn('php')
            ->shouldReceive('__toString')->times(2)->andReturn('Bar/Foo/File2.php')
            ->getMock();

        // Prepare Third mock Resource - non `.php` file
        $file3 = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->once()->andReturn('txt')
            ->shouldNotReceive('__toString')
            ->getMock();

        // Prepare mock Locator - Return the file
        /** @var Mockery\MockInterface&ResourceLocator */
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->with('locale://aa_bb', true, false)->once()->andReturn([$file1, $file2, $file3])
            ->getMock();

        // Prepare mock FileLoader - Will return the mock file, with a mock data
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('setPaths')->once()
            ->shouldReceive('load')->once()->andReturn($expectedResult)
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withNoDependentLocaleNoData
     */
    public function testGetDictionary_withDependentLocaleNoDataOnBoth(): void
    {
        // Prepare dependent mocked locale - ff_FF
        $localeDependent = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('ff_FF')
            ->getMock();

        // Prepare mocked locale - aa_bb
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([$localeDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['ff_FF'])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare mock Locator - Return no file
        /** @var ResourceLocator */
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->once()->with('locale://aa_bb', true, false)->andReturn([])
            ->shouldReceive('listResources')->once()->with('locale://ff_FF', true, false)->andReturn([])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldNotReceive('setPaths')
            ->shouldNotReceive('load')
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals([], $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withDependentLocaleNoDataOnBoth
     */
    public function testGetDictionary_withDependentLocaleAndDataOnAA(): void
    {
        // Set expectations
        $expectedResult = [
            'Foo'  => 'Bar',
            'test' => [
                'aaa' => 'AAA',
                'ccc' => '',
            ],
        ];

        // Prepare dependent mocked locale - ff_FF
        $localeDependent = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('ff_FF')
            ->getMock();

        // Prepare mocked locale - aa_bb
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([$localeDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['ff_FF'])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare first mock Resource - File `Foo/Bar/File1.php`
        $file1 = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->andReturn('php')
            ->shouldReceive('__toString')->andReturn('Foo/Bar/File1.php')
            ->getMock();

        // Prepare mock Locator - Return no file on ff_FF
        /** @var ResourceLocator */
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->once()->with('locale://aa_bb', true, false)->andReturn([$file1])
            ->shouldReceive('listResources')->once()->with('locale://ff_FF', true, false)->andReturn([])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('setPaths')->with(['Foo/Bar/File1.php'])
            ->shouldReceive('load')->andReturn($expectedResult)
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withDependentLocaleAndDataOnAA
     */
    public function testGetDictionary_withDependentLocaleAndDataOnFF(): void
    {
        // Set expectations
        $expectedResult = [
            'Bar'  => 'Foo',
            'test' => [
                'bbb' => 'BBB',
                'ccc' => 'CCC',
            ],
        ];

        // Prepare dependent mocked locale - ff_FF
        $localeDependent = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('ff_FF')
            ->getMock();

        // Prepare mocked locale - aa_bb
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([$localeDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['ff_FF'])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare first mock Resource - File `Foo/Bar/File1.php`
        $file1 = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->andReturn('php')
            ->shouldReceive('__toString')->andReturn('Bar/Foo/File2.php')
            ->getMock();

        // Prepare mock Locator - Return no file on ff_FF
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->once()->with('locale://aa_bb', true, false)->andReturn([])
            ->shouldReceive('listResources')->once()->with('locale://ff_FF', true, false)->andReturn([$file1])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('setPaths')->with(['Bar/Foo/File2.php'])
            ->shouldReceive('load')->andReturn($expectedResult)
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withDependentLocaleAndDataOnFF
     */
    public function testGetDictionary_withDependentLocaleDataOnBoth(): void
    {
        // Set expectations
        $fr_FR_FILE = [
            'Foo'  => 'Bar',
            'test' => [
                'aaa' => 'AAA',
                'ccc' => '', // Overwrites "CCC"
                'ddd' => 'DDD', // Overwrites ""
            ],
        ];
        $en_US_FILE = [
            'Bar'  => 'Foo',
            'test' => [
                'bbb' => 'BBB',
                'ccc' => 'CCC', // Overwritten by ""
                'ddd' => '', //Overwritten by "DDD"
            ],
        ];

        // NOTE : FF is a parent of AA. So FF should be loaded first
        $expectedResult = [
            'Foo'  => 'Bar',
            'test' => [
                'aaa' => 'AAA',
                'ccc' => '',
                'ddd' => 'DDD',
                'bbb' => 'BBB',
            ],
            'Bar'  => 'Foo',
        ];

        // Prepare dependent mocked locale - en_US
        $localeDependent = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('en_US')
            ->getMock();

        // Prepare mocked locale - fr_FR
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([$localeDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['en_US'])
            ->shouldReceive('getIdentifier')->andReturn('fr_FR')
            ->getMock();

        // Prepare first mock Resource - File `Foo/Bar/File1.php`
        $file_FR = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->andReturn('php')
            ->shouldReceive('__toString')->andReturn('Locale/fr_FR/file.php')
            ->getMock();

        // Prepare first mock Resource - File `Foo/Bar/File1.php`
        $file_EN = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->andReturn('php')
            ->shouldReceive('__toString')->andReturn('Locale/en_US/file.php')
            ->getMock();

        // Prepare mock Locator - Return no file on ff_FF
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->once()->with('locale://fr_FR', true, false)->andReturn([$file_FR])
            ->shouldReceive('listResources')->once()->with('locale://en_US', true, false)->andReturn([$file_EN])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('setPaths')->once()->with(['Locale/fr_FR/file.php'])
            ->shouldReceive('load')->once()->andReturn($fr_FR_FILE)
            ->getMock();

        $fileLoader->shouldReceive('setPaths')->once()->with(['Locale/en_US/file.php'])
            ->shouldReceive('load')->once()->andReturn($en_US_FILE)
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withDependentLocaleNoDataOnBoth
     */
    public function testGetDictionary_withManyDependentLocale(): void
    {
        // Prepare dependent mocked locale - ee_EE
        $localeSubDependent = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn([])
            ->shouldReceive('getIdentifier')->andReturn('ee_EE')
            ->getMock();

        // Prepare dependent mocked locale - ff_FF
        $localeDependent = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([$localeSubDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['ee_EE'])
            ->shouldReceive('getIdentifier')->andReturn('ff_FF')
            ->getMock();

        // Prepare mocked locale - aa_bb
        $locale = Mockery::mock(Locale::class)
            ->shouldReceive('getDependentLocales')->andReturn([$localeDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['ff_FF'])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare mock Locator - Return no file on ff_FF
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->once()->with('locale://aa_bb', true, false)->andReturn([])
            ->shouldReceive('listResources')->once()->with('locale://ff_FF', true, false)->andReturn([])
            ->shouldReceive('listResources')->once()->with('locale://ee_EE', true, false)->andReturn([])
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldNotReceive('setPaths')
            ->shouldNotReceive('load')
            ->getMock();

        // Perform assertions
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->assertEquals([], $dictionary->getDictionary());
    }

    /**
     * @depends testGetDictionary_withManyDependentLocale
     */
    public function testGetDictionary_withRecursiveDependentLocale(): void
    {
        // Set expectations
        $aa_AA_FILE = [
            'Foo'  => 'Bar',
            'test' => [
                'aaa' => 'AAA',
                'ccc' => '', // Overwrites "CCC"
                'ddd' => 'DDD', // Overwrites ""
            ],
        ];

        // Prepare dependent mocked locale - ff_FF && aa_bb
        $localeDependent = Mockery::mock(Locale::class);
        $locale = Mockery::mock(Locale::class);

        $localeDependent->shouldReceive('getDependentLocales')->andReturn([$locale])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['aa_bb'])
            ->shouldReceive('getIdentifier')->andReturn('ff_FF')
            ->getMock();

        $locale->shouldReceive('getDependentLocales')->andReturn([$localeDependent])
            ->shouldReceive('getDependentLocalesIdentifier')->andReturn(['ff_FF'])
            ->shouldReceive('getIdentifier')->andReturn('aa_bb')
            ->getMock();

        // Prepare first mock Resource - File `Foo/Bar/File1.php`
        $file1 = Mockery::mock(Resource::class)
            ->shouldReceive('getExtension')->andReturn('php')
            ->shouldReceive('__toString')->andReturn('Foo/Bar/File1.php')
            ->getMock();

        // Prepare mock Locator - Return no file on ff_FF
        $locator = Mockery::mock(ResourceLocator::class)
            ->shouldReceive('listResources')->once()->with('locale://aa_bb', true, false)->andReturn([$file1])
            ->shouldReceive('listResources')->never()->with('locale://ff_FF', true, false)
            ->getMock();

        // Prepare mock FileLoader - No files, so loader shouldn't load anything
        $fileLoader = Mockery::mock(ArrayFileLoader::class)
            ->shouldReceive('setPaths')->once()->with(['Foo/Bar/File1.php'])
            ->shouldReceive('load')->once()->andReturn($aa_AA_FILE)
            ->getMock();

        // Expect exception
        $dictionary = new Dictionary($locale, $locator, $fileLoader);
        $this->expectException(LogicException::class);
        $dictionary->getDictionary();
    }

    /**
     * Integration test with default.
     */
    public function testGetDictionary_withRealLocale(): void
    {
        $locale = new Locale('es_ES');
        $dictionary = new Dictionary($locale, $this->locator);

        $expectedResult = [
            'FOO' => 'BAR',  // bar/bar.php file will be loaded first
            'CAR' => 'Coche',
            'BAR' => 'Bar', // ...but zzz/bar.php will be loaded LAST because of alphabetical order !
        ];

        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    public function testGetDictionary_withRealLocale_withDependentLocaleDataOnBoth(): void
    {
        // Set expectations
        // fr_FR depends on en_US. So FR data will be loaded over EN data
        // Replicate testGetDictionary_withDependentLocaleDataOnBoth result
        $expectedResult = [
            'Foo'  => 'Bar',
            'test' => [
                'aaa'          => 'AAA',
                'ccc'          => '',
                'ddd'          => 'DDD',
                'bbb'          => 'BBB',
                '@TRANSLATION' => 'Test',
            ],
            'Bar'  => 'Foo',
        ];

        $locale = new Locale('fr_FR');
        $dictionary = new Dictionary($locale, $this->locator);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }

    public function testGetDictionary_forFlat(): void
    {
        // Set expectations
        // fr_FR depends on en_US. So FR data will be loaded over EN data
        // Replicate testGetDictionary_withDependentLocaleDataOnBoth result
        $expectedResult = [
            'Foo'               => 'Bar',
            'test.@TRANSLATION' => 'Test',
            'test.aaa'          => 'AAA',
            'test.ccc'          => '',
            'test.ddd'          => 'DDD',
            'test.bbb'          => 'BBB',
            'Bar'               => 'Foo',
        ];

        $locale = new Locale('fr_FR');
        $dictionary = new Dictionary($locale, $this->locator);
        $this->assertEquals($expectedResult, $dictionary->getFlattenDictionary());
    }

    public function testGetDictionary_withRealLocale_withThirdDependentLocale(): void
    {
        // Set expectations
        // fr_CA depends on fr_FR which depends on en_US.
        // So CA data will be loaded over FR data will be loaded over EN data
        // 'foo' key will be different
        $expectedResult = [
            'Foo'  => 'Tabarnak',
            'test' => [
                'aaa'          => 'AAA',
                'ccc'          => '',
                'ddd'          => 'DDD',
                'bbb'          => 'BBB',
                '@TRANSLATION' => 'Test',
            ],
            'Bar'  => 'Foo',
        ];

        $locale = new Locale('fr_CA');
        $dictionary = new Dictionary($locale, $this->locator);
        $this->assertEquals($expectedResult, $dictionary->getDictionary());
    }
}
