# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [6.0.0-beta.8](https://github.com/userfrosting/framework/compare/6.0.0-beta.7...6.0.0-beta.8)
- No changes

## [6.0.0-beta.7](https://github.com/userfrosting/framework/compare/6.0.0-beta.6...6.0.0-beta.7)
- No changes

## [6.0.0-beta.6](https://github.com/userfrosting/framework/compare/6.0.0-beta.5...6.0.0-beta.6)
- No changes

## [6.0.0-beta.5](https://github.com/userfrosting/framework/compare/6.0.0-beta.4...6.0.0-beta.5)
- Fix Resource getBasePath: Fix edge case where the location path is the same as the locator base path (eg. the location is in the main sprinkle)
- Add new Resource methods to help find resource parent location : `getDirUri`, `getRelativeDirname`, `getAbsoluteDirname`, `getDirname` and `isDir`
- Improve Resource handling when the resource is a directory

## [6.0.0-beta.4](https://github.com/userfrosting/framework/compare/6.0.0-beta.3...6.0.0-beta.4)
- Remove unused Fortress JS files

## [6.0.0-beta.3](https://github.com/userfrosting/framework/compare/6.0.0-beta.2...6.0.0-beta.3)
- No changes

## [6.0.0-beta.2](https://github.com/userfrosting/framework/compare/6.0.0-beta.1...6.0.0-beta.2)
- No changes

## 6.0.0-beta.1
First beta release of UserFrosting 6

## [5.2.0](https://github.com/userfrosting/framework/compare/5.0.1...5.2.0)
- [Testing] `assertJsonEquals`/`assertJsonNotEquals` (and `assertResponse` by extension) now use `assertEquals` under the hood instead of `assertSame` to better reflect their name. Added `assertJsonSame`/`assertJsonNotSame` for the old behavior.

## [5.1.3](https://github.com/userfrosting/framework/compare/5.1.2...5.1.3)
- [Config] Fix issue with `getBool`, `getString`, `getInt` and `getArray` where a null value could be returned even if a default parameter was provided when the data did in fact return `null`, making the return value not type safe as it should be.

## [5.1.2](https://github.com/userfrosting/framework/compare/5.1.1...5.1.2)
- Add Slim [type hinting](https://github.com/slimphp/Slim/releases/tag/4.14.0) & bump minimum slim version

## [5.1.1](https://github.com/userfrosting/framework/compare/5.1.0...5.1.1)
- Fix InputArray in Fortress (See https://github.com/userfrosting/UserFrosting/issues/1251)
- Update PHPStan config
- Add VSCode config

## [5.1.0](https://github.com/userfrosting/framework/compare/5.0.0...5.1.0)
- Removed Assets
- Drop PHP 8.1 support, add PHP 8.3 support
- Update to Laravel 10
- Update to PHPUnit 10
- SprinkleManager is a bit more strict on argument types. Recipe classed must be a `class-string`. The actual instance of the class will now be rejected (it wasn't a documented feature anyway).

### Fortress
Complete refactoring of Fortress. Mostly enforcing strict types, updating PHPDocs, simplifying code logic and making uses of new PHP features and method. Most classes have been deprecated and replaced by new classes with updated implementation. In general, instead of passing the *schema* in the constructor of Adapters, Transformers and Validators class, you pass it directly to theses class methods. This makes it easier to inject the classes as services and reuse the same instance with different schemas. Checkout the documentation for more information on new class usage. 

- `UserFrosting\Fortress\RequestSchema` constructor first argument now accept the schema data as an array, as well as a string representing a path to the schema json or yaml file. The argument can still be omitted to create an empty schema. This change makes `UserFrosting\Fortress\RequestSchema\RequestSchemaRepository` obsolete and and such been ***deprecated***. For example:
  ```php
  // Before
  $schemaFromFile = new \UserFrosting\Fortress\RequestSchema('path/to/schema.json');
  $schemaFromArray = new \UserFrosting\Fortress\RequestSchema\RequestSchemaRepository([
    // ...
  ]);

  // After
  $schemaFromFile = new \UserFrosting\Fortress\RequestSchema('path/to/schema.json');
  $schemaFromArray = new \UserFrosting\Fortress\RequestSchema([
    // ...
  ]);
  ```

- `UserFrosting\Fortress\RequestSchema\RequestSchemaInterface` now extends `\Illuminate\Contracts\Config\Repository`. The interface itself is otherwise unchanged.

- `UserFrosting\Fortress\RequestDataTransformer` is ***deprecated*** and replaced by `\UserFrosting\Fortress\Transformer\RequestDataTransformer` (*notice the difference in the namespace!*). `\UserFrosting\Fortress\RequestDataTransformerInterface` is also ***deprecated*** and replaced by `\UserFrosting\Fortress\Transformer\RequestDataTransformerInterface`. When using the new class, instead of passing the schema in the constructor, you pass it directly to `transform()` or `transformField()`. For example : 
  ```php
  // Before
  $transformer = new \UserFrosting\Fortress\RequestDataTransformer($schema);
  $result = $transformer->transform($data, 'skip');

  // After
  $transformer = new \UserFrosting\Fortress\Transformer\RequestDataTransformer();
  $result = $transformer->transform($schema, $data, 'skip');
  ```

- `\UserFrosting\Fortress\ServerSideValidator` is ***deprecated*** and replaced by `\UserFrosting\Fortress\Validator\ServerSideValidator` (*notice the difference in the namespace!*). `\UserFrosting\Fortress\ServerSideValidatorInterface` is also ***deprecated*** and replaced by `\UserFrosting\Fortress\Validator\ServerSideValidatorInterface`. When using the new class, instead of passing the schema in the constructor, you pass it directly to `validate()`. For example : 
  ```php
  // Before
  $validator = new \UserFrosting\Fortress\ServerSideValidator($schema, $this->translator);
  $result = $validator->validate($data);

  // After
  $adapter = new \UserFrosting\Fortress\Validator\ServerSideValidator($this->translator);
  $result = $validator->validate($schema, $data);
  ```
  
- `UserFrosting\Fortress\Adapter\FormValidationAdapter` is ***deprecated***. 
  Instead of defining the format in the `rules` method, you simply use of the appropriate class for the associated format.
  | `rules(...)`                               | Replacement class                                          |
  |--------------------------------------------|------------------------------------------------------------|
  | `$format = json` & `$stringEncode = true`  | `UserFrosting\Fortress\Adapter\FormValidationJsonAdapter`  |
  | `$format = json` & `$stringEncode = false` | `UserFrosting\Fortress\Adapter\FormValidationArrayAdapter` |
  | `$format = html5`                          | `UserFrosting\Fortress\Adapter\FormValidationHtml5Adapter` |

  `UserFrosting\Fortress\Adapter\JqueryValidationAdapter` is ***deprecated***. 
  Instead of defining the format in the `rules` method, you simply use of the appropriate class for the associated format.
  | `rules(...)`                               | Replacement class                                            |
  |--------------------------------------------|--------------------------------------------------------------|
  | `$format = json` & `$stringEncode = true`  | `UserFrosting\Fortress\Adapter\JqueryValidationJsonAdapter`  |
  | `$format = json` & `$stringEncode = false` | `UserFrosting\Fortress\Adapter\JqueryValidationArrayAdapter` |

  All adapters above now implements `UserFrosting\Fortress\Adapter\ValidationAdapterInterface` for easier type-hinting. 
  
  Finally, instead of passing the schema in the constructor, you now pass it directly to `rules()`. 
  
  For example : 
  ```php
  // Before
  $adapter = new FormValidationAdapter($schema, $this->translator);
  $result = $adapter->rules('json', false);

  // After
  $adapter = new FormValidationArrayAdapter($this->translator);
  $result = $adapter->rules($schema);
  ```

- `ClientSideValidationAdapter` abstract class replaced with `FromSchemaTrait` trait + `ValidationAdapterInterface` interface.

- `FormValidationHtml5Adapter` Will now throw an exception on missing field param, instead of returning null.

- In `FormValidationHtml5Adapter`, when using `identical` rule, the validation used to be applied to the "confirmation" field. It will now be applied to the source field, making it consistent with array|json format. For example, if `password` requires to be identical to `passwordc`, the validation was added to the `passwordc` field. Now it's applied to `password`.

### Config
- Methods `getBool`, `getString`, `getInt` & `getArray` now return `null` if key doesn't exist, to make it on par with parent `get` method.

### Alert
- Messages are now translated at read time ([#1156](https://github.com/userfrosting/UserFrosting/pull/1156), [#811](https://github.com/userfrosting/UserFrosting/issues/811)). Messages will be translated when using `messages` and `getAndClearMessages`. `addMessage` now accept the optional placeholders, which will be stored with the alert message. `addMessageTranslated` is **deprecated**. 
- Translator is not optional anymore. `setTranslator` method has been removed.
- `addValidationErrors` is deprecated (N.B.: It can't accept the new `\UserFrosting\Fortress\Validator\ServerSideValidatorInterface`)

### UniformResourceLocator
- Two locations cannot have the same name anymore. An `InvalidArgumentException` will be thrown otherwise. (Ref [userfrosting/UserFrosting#1243](https://github.com/userfrosting/UserFrosting/issues/1243)).
- [*DEPRECATION*] Location's `getSlug` is deprecated (redundant with the name and not really used).

## [5.0.0](https://github.com/userfrosting/framework/compare/4.6.1...5.0.0)
With version 5, this repo can be used as a bare bone Slim & Symfony Console application. It include the necessary routing class, [PHP-DI](https://php-di.org) as the Dependency Injection Container, a PSR EventDispatcher, etc. SprinkleManager has also been moved from Core/System Sprinkle and completely rewritten. 

It's necessary for the SprinkleMAnager, Slim and Symfony (Bakery) to be outside of the Core Sprinkle so it can be properly managed. All extra feature (template, database, config, etc.) are left for the Core Sprinkle. The old `sprinkle.json` has been replace with `SprinkleRecipe` interface.

Version 5 also requires PHP 8.0 and up. With that in mind, most of the code has been updated to support PHP 8 and make use of it's new features. Code quality has also been stepped up, with PHPStan analysis added to the build process.

### Global
#### Added
- Moved Alert into Framework from Core Sprinkle
- Added Bakery / Symfony Console app : `UserFrosting\Bakery\Bakery`
- UserFrosting / Slim 4 Web app : `UserFrosting\UserFrosting`
- Moved `SprinkleManager` from main repo.
- Added custom Event Dispatcher and Listeners : `UserFrosting\Event\EventDispatcher` & `UserFrosting\Event\SprinkleListenerProvider`

#### Dependencies
- Drop PHP 7.3 & 7.4 support
- Updated `twig/twig` to `^3.3`

#### Code Quality
- Updated PHPStan config and added Github Action for automatic code analysis for UniformResourceLocator & Config (with no issues on max level)
- Updated PHP-CS-Fixer & StyleCI config

### Assets
**Assets module is now deprecated and will be removed in UserFrosting 5.1 !**
#### Removed
- `UserFrosting\Assets\ServeAsset\SlimServeAsset` has been removed. Code has been moved into Core Sprinkle

### Bakery
#### Added
- `UserFrosting\Bakery\WithSymfonyStyle` moved to this repo from Core. 

### Testing
- Added helper class to test Bakery command, Container, CustomAssertionsTrait, HttpTester, TestCase, etc.

### Support
#### Removed
- These HTTP exceptions have been removed and replace with new system in Core Sprinkle :
  - `BadRequestException`
  - `ForbiddenException`
  - `HttpException`
  - `NotFoundException`
- `UserFrosting\Support\Util\Util::normalizePath` has been removed. Use `UserFrosting\UniformResourceLocator\Normalizer::normalizePath` instead.

### UniformResourceLocator
#### Changes
- Remove dependency on `rockettheme/toolbox` by integrating our own `StreamWrapper\Stream` and `StreamBuilder`
- `findResource` and `getResource` now return `null` if a resource is not found instead of `false`

#### Added
- Added `readonly` option for streams. Files accessed using a readonly scheme will be protected against destructive action at the streamwrapper level.

#### Deprecated
- `findResource` is deprecated. Use `getResource` instead
- `findResources` is deprecated. Use `getResources` instead
- `registerStream` and `registerSharedStream` are deprecated. Use `addStream` instead
- `registerLocation` is deprecated. Use `addLocation` instead

#### Removed
- Scheme Prefix has been removed
- Resource : `setLocation`, `setPath`, `setLocatorBasePath` and `setStream` methods have been removed
- ResourceLocation : `setName` and `setPath` methods have been removed
- ResourceStream : `setScheme`, `setPath` and `setShared` methods have been removed
- Deprecated `ResourceLocator::addPath` method removed
- `ResourceLocator::setBasePath` method removed

### Fortress

#### Fix
- Fix [userfrosting/UserFrosting#1216](https://github.com/userfrosting/UserFrosting/issues/1216) - Throw error when RequestSchema path doesn't exist

#### Removed
- Removed deprecated method `getSchema` in `RequestSchema`

## [4.6.1](https://github.com/userfrosting/framework/compare/4.6.0...4.6.1)
 - Fix issue with location outside of the main path not returning  the correct relative path.
 - Update php-cs-fixer to V3

## 4.6.0
 - Drop PHP 7.2 and add PHP 8.0 support.
 - Upgrade all Laravel packages to ^8.x from ^5.8.
 - Upgrade `vlucas/phpdotenv`to ^5.3 from ^3.4.
 - Upgrade `symfony/console` to ^5.1 from ^4.3.
 - Upgrade `phpunit/phpunit` to ^9.5
 - [Cache] Fix tagged file issue on Windows

## For versions before 4.6.0, see :
 - [Assets](https://github.com/userfrosting/assets/blob/master/CHANGELOG.md)
 - [Cache](https://github.com/userfrosting/cache/blob/master/CHANGELOG.md)
 - [Config](https://github.com/userfrosting/config/blob/master/CHANGELOG.md)
 - [Fortress](https://github.com/userfrosting/fortress/blob/master/CHANGELOG.md)
 - [i81n](https://github.com/userfrosting/i18n/blob/master/CHANGELOG.md)
 - [Session](https://github.com/userfrosting/session/blob/master/CHANGELOG.md)
 - [Support](https://github.com/userfrosting/support/blob/master/CHANGELOG.md)
 - [UniformResourceLocator](https://github.com/userfrosting/UniformResourceLocator/blob/master/CHANGELOG.md)