<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class ComposeConfigTest extends TestCase
{
    public function testDefaultServicesAndSettings(): void
    {
        $services = $this->composeConfig()['services'];
        $this->assertEqualsCanonicalizing(['app', 'nginx', 'vue-frontend', 'mysql', 'mailpit'], array_keys($services));
        $this->assertSame('mysql', $services['app']['environment']['DB_CONNECTION']);
        $this->assertSame('mysql', $services['app']['environment']['DB_HOST']);
        $this->assertSame('memory', $services['app']['environment']['DB_TEST_CONNECTION']);
        $this->assertSame('file', $services['app']['environment']['CACHE_DRIVER']);
        $this->assertSame('service_healthy', $services['app']['depends_on']['mysql']['condition']);
        $this->assertFalse($services['app']['depends_on']['mysql']['required']);
    }

    #[DataProvider('optionalServices')]
    public function testOptionalServiceProfile(string $profile): void
    {
        $services = $this->composeConfig(['COMPOSE_PROFILES' => $profile])['services'];
        $this->assertEqualsCanonicalizing(
            ['app', 'nginx', 'vue-frontend', 'mysql', 'mailpit', $profile],
            array_keys($services),
        );
    }

    /** @return array<string, array{string}> */
    public static function optionalServices(): array
    {
        return [
            'MariaDB'       => ['mariadb'],
            'PostgreSQL'    => ['pgsql'],
            'MySQL test'    => ['mysql-test'],
            'MariaDB test'  => ['mariadb-test'],
            'Postgres test' => ['pgsql-test'],
            'Memcached'     => ['memcached'],
            'Redis'         => ['redis'],
        ];
    }

    #[DataProvider('databaseProviderNames')]
    public function testTestProviderDefaultsToItsOwnHost(string $provider): void
    {
        $services = $this->composeConfig([
            'COMPOSE_PROFILES'          => $provider . '-test',
            'DOCKER_DB_TEST_CONNECTION' => $provider,
        ])['services'];
        $this->assertSame($provider, $services['app']['environment']['DB_TEST_CONNECTION']);
        $this->assertSame($provider . '-test', $services['app']['environment']['DB_TEST_HOST']);
        $this->assertSame('service_healthy', $services['app']['depends_on'][$provider . '-test']['condition']);
        $this->assertFalse($services['app']['depends_on'][$provider . '-test']['required']);
    }

    /** @return array<string, array{string}> */
    public static function databaseProviderNames(): array
    {
        return [
            'MySQL'      => ['mysql'],
            'MariaDB'    => ['mariadb'],
            'PostgreSQL' => ['pgsql'],
        ];
    }

    public function testProvidersAndCacheAreSelectedIndependently(): void
    {
        $services = $this->composeConfig([
            'COMPOSE_PROFILES'           => 'pgsql,mariadb-test,redis,memcached',
            'DOCKER_DB_CONNECTION'       => 'pgsql',
            'DOCKER_DB_TEST_CONNECTION'  => 'mariadb',
            'DOCKER_CACHE_DRIVER'        => 'redis',
        ])['services'];
        $app = $services['app']['environment'];

        $this->assertArrayHasKey('mysql', $services);
        $this->assertArrayHasKey('pgsql', $services);
        $this->assertArrayHasKey('mariadb-test', $services);
        $this->assertArrayHasKey('redis', $services);
        $this->assertArrayHasKey('memcached', $services);
        $this->assertSame('pgsql', $app['DB_CONNECTION']);
        $this->assertSame('pgsql', $app['DB_HOST']);
        $this->assertSame('mariadb', $app['DB_TEST_CONNECTION']);
        $this->assertSame('mariadb-test', $app['DB_TEST_HOST']);
        $this->assertSame('redis', $app['CACHE_DRIVER']);
        $this->assertSame('redis', $app['REDIS_HOST']);
        $this->assertSame('memcached', $app['MEMCACHED_HOST']);
    }

    public function testTestServicesUseSeparateEphemeralDatabases(): void
    {
        $services = $this->composeConfig([
            'COMPOSE_PROFILES'        => 'mysql-test,mariadb-test,pgsql-test',
            'DOCKER_DB_TEST_NAME'     => 'custom_test',
            'DOCKER_DB_TEST_USER'     => 'test_user',
            'DOCKER_DB_TEST_PASSWORD' => 'test_password',
            'DOCKER_DB_TEST_HOST'     => 'custom-host',
        ])['services'];
        $app = $services['app']['environment'];

        $this->assertSame('custom-host', $app['DB_TEST_HOST']);
        $this->assertSame('custom_test', $app['DB_TEST_NAME']);
        $this->assertSame('test_user', $app['DB_TEST_USER']);
        $this->assertSame('test_password', $app['DB_TEST_PASSWORD']);

        foreach ([
            'mysql-test'   => ['MYSQL', '/var/lib/mysql'],
            'mariadb-test' => ['MARIADB', '/var/lib/mysql'],
            'pgsql-test'   => ['POSTGRES', '/var/lib/postgresql/data'],
        ] as $service => [$prefix, $directory]) {
            $environment = $services[$service]['environment'];
            $databaseKey = $prefix === 'POSTGRES' ? 'POSTGRES_DB' : $prefix . '_DATABASE';
            $this->assertSame('custom_test', $environment[$databaseKey]);
            $this->assertSame('test_user', $environment[$prefix . '_USER']);
            $this->assertSame('test_password', $environment[$prefix . '_PASSWORD']);
            $this->assertSame([$directory], $services[$service]['tmpfs']);
            $this->assertArrayNotHasKey('ports', $services[$service]);
            $this->assertArrayHasKey('healthcheck', $services[$service]);
        }
    }

    public function testProfileAndConnectionMismatchIsNotValidated(): void
    {
        $services = $this->composeConfig(['DOCKER_DB_CONNECTION' => 'pgsql'])['services'];
        $this->assertArrayNotHasKey('pgsql', $services);
        $this->assertSame('pgsql', $services['app']['environment']['DB_HOST']);
    }

    /** @param array<string, string> $environment
     * @return array<string, mixed>
     */
    private function composeConfig(array $environment = []): array
    {
        $root = dirname(__DIR__, 2);
        $defaults = [
            'COMPOSE_PROFILES'           => '',
            'DOCKER_DB_CONNECTION'       => 'mysql',
            'DOCKER_DB_TEST_CONNECTION'  => 'memory',
            'DOCKER_DB_TEST_HOST'        => false,
            'DOCKER_DB_TEST_NAME'        => 'userfrosting_test',
            'DOCKER_DB_TEST_USER'        => 'userfrosting_test',
            'DOCKER_DB_TEST_PASSWORD'    => 'testsecret',
            'DOCKER_CACHE_DRIVER'        => 'file',
        ];
        $process = new Process(
            ['docker', 'compose', '--file', $root . '/compose.yaml', '--env-file', '/dev/null', 'config', '--format', 'json'],
            $root,
            array_replace($defaults, $environment),
        );
        $process->run();
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        return json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
    }
}
