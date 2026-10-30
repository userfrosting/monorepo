# Monorepo Docker Development Environment

This Docker configuration is for developing UserFrosting in the monorepo only. It includes development services and is not a production deployment configuration. For the Skeleton app's development and production options, see the [Skeleton Docker guide](../packages/skeleton/docker/README.md).

> You can find the complete version of this guide at [UserFrosting Learn](https://learn.userfrosting.com/installation/environment/docker).

## Installation

### Prerequisites

First, install [Docker Compose or Docker Desktop](https://docs.docker.com/compose/install/).

### Initial Setup
Second, initialize a new UserFrosting project:

```bash
git clone https://github.com/userfrosting/monorepo.git UFMonorepo
```

Change to the new directory: 

```bash
cd UFMonorepo
```

Copy the default env file:

```bash
cp docker/.env.example .env
cp packages/skeleton/app/.env.docker packages/skeleton/app/.env
```

> [!TIP]
> The `.env` file (copied from `docker/.env.example`) can define `COMPOSE_PROJECT_NAME`, which defines the containers, images, networks, and volumes names. If it is unset, Compose uses the checkout directory name instead. You can set it to a unique value to avoid conflicts with other projects or branch checkouts.
> 
> The same can be done for other environment variables such as database credentials or service ports.

> [!NOTE]
> Compose passes the database settings from `MYSQL_*` environment variables in this file to the PHP app, keeping its connection credentials synchronized with MySQL. No need to manually configure the database connection in the app, especially in the app `.env` file.

Set some directory permissions (you may have to enter your root password):

```bash
sudo chown -R $USER: .
sudo chmod 777 packages/skeleton/app/{logs,cache,sessions}
```

### Build and Start Containers

Build all the Docker containers, and start them:
```bash
docker compose up -d --build
```

> [!NOTE]
> At this point, the Docker containers are built and running, but setup is not yet complete. You still need to install Composer modules and run the Bakery commands to set up UserFrosting. Otherwise the UI will not function correctly, including showing a white page.

### Install Composer Modules

Install all Composer modules used in UserFrosting:

```bash
docker compose exec app composer install
```

### Install UserFrosting

Install UserFrosting (database migrations, creation of admin user, ...) using the Bake command. You'll need to provide info to create the admin user.

```bash
docker compose exec app php bakery bake
```

Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

## Using the Docker Environment
### Start / stop containers

If you need to stop the UserFrosting docker containers, just change to your userfrosting directory and run:

```bash
docker compose stop
```

To start containers again, change to your userfrosting directory and run:

```bash
docker compose up -d
```

### Running Bakery Commands

All calls to Bakery commands need to be prefixed by `docker compose`,  since they need to run on the containers, not your computer. For example:

```bash
docker compose exec app php bakery ...
```

### Updating Frontend Dependencies

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
