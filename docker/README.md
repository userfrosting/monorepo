# Monorepo Docker Development Environment

This Docker configuration is intended only for developing UserFrosting in the monorepo. It includes development services and is not suitable for production. For the Skeleton app's development and production options, see the [Skeleton Docker Guide](../packages/skeleton/docker/README.md).

> You can find the complete version of this guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

## Installation

### Prerequisites

Install [Docker Compose or Docker Desktop](https://docs.docker.com/compose/install/). Compose 2.20 or newer is required for the provider-specific `include` files.

### Initial Setup

Clone the UserFrosting Monorepo:

```bash
git clone https://github.com/userfrosting/monorepo.git UFMonorepo
```

Change to the new directory:

```bash
cd UFMonorepo
```

Copy the Docker environment example to the project root:

```bash
cp docker/.env.example .env
```

Display the Docker user and group IDs for your host account:

```bash
echo "DOCKER_UID=$(id -u)"
echo "DOCKER_GID=$(id -g)"
```

Copy the two displayed lines into `.env`. This lets the app run as `www-data` and the Vite frontend as `node` while keeping files created in bind mounts and the shared `node_modules` volume accessible to your host account. If the checkout or runtime directories are already owned by another user, fix their ownership before starting the containers.

> [!TIP]
> You can use a root `.env` file (copied from `docker/.env.example`) to define `COMPOSE_PROJECT_NAME`, which determines the names of containers, images, networks, and volumes. If it is unset, Compose uses the checkout directory name instead. You can set it to a unique value to avoid conflicts with other projects or branch checkouts.
>
> You can also define other environment variables here, such as database credentials or service ports. See [Advanced configuration](#advanced-configuration) for details.

### Build and Start Containers

Build and start all the Docker containers:

```bash
docker compose up -d --build
```

> [!NOTE]
> At this point, the Docker containers are built and running, but setup is not yet complete. You still need to install the Composer dependencies and run the Bakery commands to set up UserFrosting. Otherwise, the UI will not function correctly and may display a blank page.

### Install Composer Modules

Install all Composer dependencies used by UserFrosting:

```bash
docker compose exec app composer install
```

### Install UserFrosting

Set up UserFrosting by running the Bake command to apply database migrations and create an administrator account. You'll be prompted for the information needed to create the account.

```bash
docker compose exec app php bakery bake
```

Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

## Using the Docker Environment

### Start and stop containers

To stop the UserFrosting Docker containers, change to your UserFrosting directory and run:

```bash
docker compose stop
```

To start the containers again, change to your UserFrosting directory and run:

```bash
docker compose up -d
```

If you need to rebuild the containers without starting them, run:

```bash
docker compose build
```

### Running Bakery Commands

Prefix all Bakery commands with `docker compose` so they run in the containers rather than on your computer. For example:

```bash
docker compose exec app php bakery ...
```

### Updating frontend dependencies

The `app` and `vue-frontend` containers share a Docker-managed `node_modules` volume, separate from your host's `node_modules`. The Vue frontend waits for the dependencies to be installed by Bakery before starting Vite. On a fresh setup, `docker compose exec app php bakery bake` installs the locked dependencies as part of setup.

To install dependencies from the current lockfile, run:

```bash
docker compose exec app php bakery assets:install
```

To update dependencies and the lockfile, run:

```bash
docker compose exec app php bakery assets:update
```

After changing dependencies, restart the Vue dev server to ensure it reloads them:

```bash
docker compose restart vue-frontend
```

> [!NOTE]
> Vue Hot Module Replacement (HMR) is enabled and will automatically reload the frontend when changes are detected in the source files. No need to restart the Vue dev server manually when editing source files.

### Using Mailpit

Mailpit is used to capture outgoing emails from the application. You can access the Mailpit web interface at [http://localhost:8025](http://localhost:8025) to view and manage emails sent by UserFrosting.

### Running tests

To run tests in the app container with the testing environment:

```bash
docker compose exec app vendor/bin/phpunit
```

PHPUnit uses SQLite in memory by default. See [Advanced configuration](#advanced-configuration) to test against MySQL, MariaDB, or PostgreSQL.

## Advanced configuration

The default stack starts MySQL, Mailpit, the PHP app, nginx, and the Vue frontend. The optional project-root `.env` can enable additional services. Start by copying the example environment file to the project root:

```bash
cp docker/.env.example .env
```

After changing `.env`, apply the settings with `docker compose up -d`. An existing container does not receive changed environment variables until Compose recreates it. When disabling profiles, stop the old services explicitly or use `docker compose down --remove-orphans` before restarting. Avoid removing volumes unless you intend to delete data.

### Docker structure

The root [`compose.yaml`](../compose.yaml) defines the core services and includes the individual service files in the `docker/` directory:

| Service                                    | Purpose                                      | Default           |
| ------------------------------------------ | -------------------------------------------- | ----------------- |
| `app`                                      | PHP application and Bakery commands          | Started           |
| `nginx`                                    | Web server for the application               | Started           |
| `vue-frontend`                             | Vite development server                      | Started           |
| `mysql`                                    | Application database                         | Started           |
| `mailpit`                                  | Captures outgoing email                      | Started           |
| `mariadb`, `pgsql`                         | Alternative application databases            | Optional profiles |
| `mysql-test`, `mariadb-test`, `pgsql-test` | Isolated, ephemeral test databases           | Optional profiles |
| `memcached`, `redis`                       | Cache services for development and debugging | Optional profiles |

### Container Names

`COMPOSE_PROJECT_NAME` can be set to customize the names of containers, images, networks, and volumes.

### Service Profiles

Set `COMPOSE_PROFILES` to a comma-separated list of the following profiles:

- `mariadb`
- `pgsql`
- `mysql-test`
- `mariadb-test`
- `pgsql-test`
- `memcached`
- `redis`

Multiple profiles can be enabled together. Profiles start services; they do **not** change the app's database or cache configuration. The appropriate variable in the `.env` file must be set to select which service the app uses as defined below.

### Application databases

MySQL is always started, even if another provider is selected. To use MariaDB or PostgreSQL instead, enable its profile and select it for the app. For example, set these values in the root `.env`:

```dotenv
COMPOSE_PROFILES=pgsql
DOCKER_DB_CONNECTION=pgsql
```

For MariaDB use `mariadb` in both settings. Set `DOCKER_DB_CONNECTION=mysql` (or delete the variable) to return to the default. More than one provider may be running at the same time.

MySQL, MariaDB, and PostgreSQL use separate persistent volumes. Run `docker compose exec app php bakery bake` when setting up a new application database.

`DOCKER_DB_NAME`, `DOCKER_DB_USER`, and `DOCKER_DB_PASSWORD` configure the app database and are shared by all three providers. `DOCKER_DB_ROOT_PASSWORD` sets the MySQL and MariaDB administrator password. Default values will be used if these variables are not set. The database images apply these initialization settings only to an empty data directory. Changing credentials or database names in `.env` does not update an existing volume; update the database itself or deliberately provision a new volume after backing up its data.

The database ports are exposed on the host machine, allowing external connections if needed. These can be customized in the `.env` file. Check `docker/.env.example` for the default ports.

### Provider-backed test databases

By default, PHPUnit's tests use in-memory SQLite and require no specific service to run. For a server-backed test (e.g., MySQL, MariaDB, or PostgreSQL), enable the matching test profile and select the provider separately.

For example, to run tests with MySQL:

```dotenv
COMPOSE_PROFILES=mysql-test
DOCKER_DB_TEST_CONNECTION=mysql
```

For MariaDB:

```dotenv
COMPOSE_PROFILES=mariadb-test
DOCKER_DB_TEST_CONNECTION=mariadb
```

For PostgreSQL:

```dotenv
COMPOSE_PROFILES=pgsql-test
DOCKER_DB_TEST_CONNECTION=pgsql
```

> [!NOTE]
> The application and test database providers may differ.

As with the application database, the test database credentials can be customized with the optional `DOCKER_DB_TEST_NAME`, `DOCKER_DB_TEST_USER`, and `DOCKER_DB_TEST_PASSWORD` variables.

> [!WARNING]
> Server-backed tests may reset the configured test database. Never point them at an application database, and do not run concurrent suites against the same test database. Test data is stored in memory and is lost when its container stops or is recreated. These services are for testing, not persistent application data.

### Optional caches

Memcached and Redis are optional, independent services. Enable the service profile **and** set `DOCKER_CACHE_DRIVER` to use it from the app. For example:

```dotenv
COMPOSE_PROFILES=memcached
DOCKER_CACHE_DRIVER=memcached
```

Or:

```dotenv
COMPOSE_PROFILES=redis
DOCKER_CACHE_DRIVER=redis
```

Both cache services may be started for debugging without selecting either as the cache driver. If available, PHPUnit will use them. These tests write to and flush Memcached and Redis, which may affect existing cache data.

The app connects to `memcached` or `redis` over the backend network automatically; neither service publishes a host port. Cache data is not intended as durable storage and may be lost when the container stops. The testing configuration uses the array cache regardless of the application cache driver.

### Testing Docker Compose configuration

To check the resolved Docker Compose configuration, run the separate regression test **on the host** from the monorepo root:

```bash
vendor/bin/phpunit --no-coverage --no-logging docker/tests/ComposeConfigTest.php
```

### Reset the database and containers

To remove the containers while keeping the database and frontend dependency volumes, run:

```bash
docker compose down --remove-orphans
```

To also delete the persisted database and frontend dependency volumes and start with a clean slate, add `--volumes`:

```bash
docker compose down --remove-orphans --volumes
```

> [!WARNING]
> This permanently removes the Compose project's application database volumes and frontend dependency volume. Test database data is ephemeral and disappears when its container stops. Volumes from other projects and the legacy MySQL volume may remain. Source files and sprinkles are not deleted.
