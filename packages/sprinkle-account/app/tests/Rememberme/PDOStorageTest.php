<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Rememberme;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use mober\Rememberme\Storage\AbstractStorage;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Rememberme\PDOStorage;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class PDOStorageTest extends AccountTestCase
{
    use RefreshDatabase;

    /**
     * @var PDOStorage
     */
    protected PDOStorage $storage;

    /**
     * @var UserInterface
     */
    protected UserInterface $testUser;

    // Test tokens
    protected string $validToken = '78b1e6d775cec5260001af137a79dbd5';
    protected string $validPersistentToken = '0e0530c1430da76495955eb06eb99d95';
    protected string $invalidToken = '7ae7c7caa0c7b880cb247bb281d527de';

    // SHA256 hashes of the tokens
    protected string $validDBToken = 'b1d2a86fa0da79e800fb7da591cf1fb69e833ee3b6a545bf18959d2f2460310b';
    protected string $validDBPersistentToken = '874df7491a474a04d132691d0af6f64a2d546a4933818b0255d0ce346e0cd712';
    protected string $invalidDBToken = '546cfd5dd78d826ad623b2a169a56b14c8de2194900bd101ae55f89d2ef25316';

    // Expiration date for the test tokens
    protected string $expire;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->refreshDatabase();

        // Set dynamic date (can't be done in property declaration)
        $this->expire = Carbon::now()->addYear();

        // Create a test user
        /** @var User&UserInterface */
        $user = User::factory()->create();
        $this->testUser = $user;

        /** @var Capsule */
        $capsule = $this->getService(Capsule::class);
        $this->storage = new PDOStorage($capsule);
    }

    public function testFindTripletReturnsFoundIfDataMatches(): void
    {
        $this->insertTestData();
        $this->assertSame(1, Persistence::count());
        $result = $this->storage->findTriplet($this->testUser->id, $this->validToken, $this->validPersistentToken);
        $this->assertSame(AbstractStorage::TRIPLET_FOUND, $result);
    }

    public function testFindTripletReturnsNotFoundIfNoDataMatches(): void
    {
        $result = $this->storage->findTriplet($this->testUser->id, $this->validToken, $this->validPersistentToken);
        $this->assertSame(AbstractStorage::TRIPLET_NOT_FOUND, $result);
    }

    public function testFindTripletReturnsInvalidTokenIfTokenIsInvalid(): void
    {
        $this->insertTestData();
        $result = $this->storage->findTriplet($this->testUser->id, $this->invalidToken, $this->validPersistentToken);
        $this->assertSame(AbstractStorage::TRIPLET_INVALID, $result);
    }

    public function testStoreTripletSavesValuesIntoDatabase(): void
    {
        $this->storage->storeTriplet($this->testUser->id, $this->validToken, $this->validPersistentToken, strtotime($this->expire)); // @phpstan-ignore-line
        $row = Persistence::select(['user_id', 'token', 'persistent_token', 'expires_at'])->first()?->toArray(); // @phpstan-ignore-line
        $this->assertSame([$this->testUser->id, $this->validDBToken, $this->validDBPersistentToken, $this->expire], array_values($row));
    }

    public function testCleanTripletRemovesEntryFromDatabase(): void
    {
        $this->insertTestData();
        $this->storage->cleanTriplet($this->testUser->id, $this->validPersistentToken);
        $this->assertSame(0, Persistence::count());
    }

    public function testCleanAllTripletsRemovesAllEntriesWithMatchingCredentialsFromDatabase(): void
    {
        $this->insertTestData();
        $persistence = new Persistence([
            'user_id'          => $this->testUser->id,
            'token'            => 'dummy',
            'persistent_token' => 'dummy',
            'expires_at'       => null,
        ]);
        $persistence->save();
        $this->storage->cleanAllTriplets($this->testUser->id);
        $this->assertSame(0, Persistence::count());
    }

    public function testReplaceTripletRemovesAndSavesEntryFromDatabase(): void
    {
        $this->insertTestData();
        $this->storage->replaceTriplet($this->testUser->id, $this->invalidToken, $this->validPersistentToken, strtotime($this->expire)); // @phpstan-ignore-line
        $this->assertSame(1, Persistence::count());
        $row = Persistence::select(['user_id', 'token', 'persistent_token', 'expires_at'])->first()->toArray(); // @phpstan-ignore-line
        $this->assertSame([$this->testUser->id, $this->invalidDBToken, $this->validDBPersistentToken, $this->expire], array_values($row));
    }

    public function testCleanExpiredTokens(): void
    {
        $this->insertTestData();
        $persistence = new Persistence([
            'user_id'          => $this->testUser->id,
            'token'            => 'dummy',
            'persistent_token' => 'dummy',
            'expires_at'       => Carbon::now()->subHour(),
        ]);
        $persistence->save();
        $this->assertSame(2, Persistence::count());
        $this->storage->cleanExpiredTokens((int) Carbon::now()->timestamp);
        $this->assertSame(1, Persistence::count());
    }

    /**
     * Insert test dataset
     * @return Persistence
     */
    protected function insertTestData(): Persistence
    {
        $persistence = new Persistence([
            'user_id'          => $this->testUser->id,
            'token'            => $this->validDBToken,
            'persistent_token' => $this->validDBPersistentToken,
            'expires_at'       => $this->expire,
        ]);
        $persistence->save();

        return $persistence;
    }
}
