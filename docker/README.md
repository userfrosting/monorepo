# Monorepo Docker Development Environment

This Docker configuration is intended only for developing UserFrosting in the monorepo. It includes development services and is not suitable for production. For the Skeleton app's development and production options, see the [Skeleton Docker guide](../packages/skeleton/docker/README.md).

> You can find the complete version of this guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

## Installation

### Prerequisites

Install [Docker Compose or Docker Desktop](https://docs.docker.com/compose/install/).

### Initial Setup

Initialize a new UserFrosting project:

```bash
git clone https://github.com/userfrosting/monorepo.git UFMonorepo
```

Change to the new directory:

```bash
cd UFMonorepo
```

Copy the default environment file:

```bash
cp docker/.env.example .env
cp packages/skeleton/app/.env.docker packages/skeleton/app/.env
```

> [!TIP]
> The `.env` file (copied from `docker/.env.example`) can define `COMPOSE_PROJECT_NAME`, which determines the names of containers, images, networks, and volumes. If it is unset, Compose uses the checkout directory name instead. You can set it to a unique value to avoid conflicts with other projects or branch checkouts.
>
> You can also define other environment variables here, such as database credentials or service ports.

> [!NOTE]
> Compose passes the database settings from `MYSQL_*` environment variables in this file to the PHP app, keeping its connection credentials synchronized with MySQL. You don't need to configure the database connection manually in the app, including in its `.env` file.

Set the directory permissions (you may need to enter your root password):

```bash
sudo chown -R $USER: .
sudo chmod 777 packages/skeleton/app/{logs,cache,sessions}
```

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

### Start/stop containers

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
> This permanently removes the database data volume. Source files and sprinkles are not deleted.
