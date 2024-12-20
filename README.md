# Oh Eweedy

The ultimate art platform for sharing your masterpieces. Create a collaborative image gallery.

This Laravel 11 app is here to retire my old plain PHP website.

### Future Features
- Upload your images and add them to **THE** gallery.
- Give your piece a name, turning it into a true work of art.
- Like other pictures to make someone’s day... or not.
- Comment to share your profound insights (or witty remarks) with the artist.

### Side Quests
- Basic user accounts (because everyone needs a profile, right?).
- Displaying the gallery in a stunning way.
- Image rotation and deletion (for those oops moments).

\
The [Laravel 11](https://laravel.com/docs/11.x/) app is [dockerbased](https://www.docker.com/) and based on [aschmelyun](https://github.com/aschmelyun/docker-compose-laravel)'s:
# docker-compose-laravel
A pretty simplified Docker Compose workflow that sets up a LEMP network of containers for local Laravel development. You can view the full article that inspired this repo [here](https://dev.to/aschmelyun/the-beauty-of-docker-for-local-laravel-development-13c0).

## Usage

To get started, make sure you have [Docker installed](https://docs.docker.com/docker-for-mac/install/) on your system, and then clone this repository.

Next, navigate in your terminal to the directory you cloned this, and spin up the containers for the web server by running `docker-compose up -d --build app`.

After that completes, follow the steps from the [src/README.md](src/README.md) file to get your Laravel project added in (or create a new blank one).

**Note**: Your MySQL database host name should be `mysql`, **not** `localhost`. The username and database should both be `homestead` with a password of `secret`. 

Bringing up the Docker Compose network with `app` instead of just using `up`, ensures that only our site's containers are brought up at the start, instead of all of the command containers as well. The following are built for our web server, with their exposed ports detailed:

- **nginx** - `:80`
- **mysql** - `:3306`
- **php** - `:9000`
- **redis** - `:6379`
- **mailhog** - `:8025` 

Three additional containers are included that handle Composer, NPM, and Artisan commands *without* having to have these platforms installed on your local computer. Use the following command examples from your project root, modifying them to fit your particular use case.

- `docker-compose run --rm composer update`
- `docker-compose run --rm artisan migrate`
- `docker-compose run --rm npm npm install` (important for first setup)
- `docker-compose run --rm npm run dev`


## Best Practice for development experience
This project includes a list of recommended VS Code extensions. This list can be found in the .vscode/extensions.json file within the repository. It includes extensions that enhance Laravel support, such as code completion for PHP, Blade templating, and Tailwind CSS, among others.

To ensure full compatibility with the recommended extensions, it is advised to have PHP 8.x installed in your  environment. This enables tools like Laravel Extra Intellisense to access PHP locally for providing advanced autocompletion and enhanced code suggestions, even when the app itself is containerized in Docker.


## Permissions Issues

If you encounter any issues with filesystem permissions while visiting your application or running a container command, try completing one of the sets of steps below.

**If you are using your server or local environment as the root user:**

- Bring any container(s) down with `docker-compose down`
- Replace any instance of `php.dockerfile` in the docker-compose.yml file with `php.root.dockerfile`
- Re-build the containers by running `docker-compose build --no-cache`

**If you are using your server or local environment as a user that is not root:**

- Bring any container(s) down with `docker-compose down`
- In your terminal, run `export UID=$(id -u)` and then `export GID=$(id -g)`
- If you see any errors about readonly variables from the above step, you can ignore them and continue
- Re-build the containers by running `docker-compose build --no-cache`

Then, either bring back up your container network or re-run the command you were trying before, and see if that fixes it.

## Persistent MySQL Storage

By default, whenever you bring down the Docker network, your MySQL data will be removed after the containers are destroyed. If you would like to have persistent data that remains after bringing containers down and back up, do the following:

1. Create a `mysql` folder in the project root, alongside the `nginx` and `src` folders.
2. Under the mysql service in your `docker-compose.yml` file, add the following lines:

```
volumes:
  - ./mysql:/var/lib/mysql
```

## Usage in Production

While I originally created this template for local development, it's robust enough to be used in basic Laravel application deployments. The biggest recommendation would be to ensure that HTTPS is enabled by making additions to the `nginx/default.conf` file and utilizing something like [Let's Encrypt](https://hub.docker.com/r/linuxserver/letsencrypt) to produce an SSL certificate.

## Compiling Assets

This configuration should be able to compile assets with both [laravel mix](https://laravel-mix.com/) and [vite](https://vitejs.dev/). In order to get started, you first need to add ` --host 0.0.0.0` after the end of your relevant dev command in `package.json`. So for example, with a Laravel project using Vite, you should see:

```json
"scripts": {
  "dev": "vite --host 0.0.0.0",
  "build": "vite build"
},
```

Then, run the following commands to install your dependencies and start the dev server:

- `docker-compose run --rm npm install`
- `docker-compose run --rm --service-ports npm run dev`

After that, you should be able to use `@vite` directives to enable hot-module reloading on your local Laravel application.

Want to build for production? Simply run `docker-compose run --rm npm run build`.

## MailHog

The current version of Laravel (9 as of today) uses MailHog as the default application for testing email sending and general SMTP work during local development. Using the provided Docker Hub image, getting an instance set up and ready is simple and straight-forward. The service is included in the `docker-compose.yml` file, and spins up alongside the webserver and database services.

To see the dashboard and view any emails coming through the system, visit [localhost:8025](http://localhost:8025) after running `docker-compose up -d mailhog`.


## Versions

Last Update: 10.11.24
- Laravel Framework 11.7.0
- php: 8.3.13
- composer: 2.8.2
- npm: 22.1.0
- vite: 5.4.10
- SQLite: 10.9.0

Check it with
```
docker-compose run --rm artisan --version

docker-compose run --rm php -v

docker-compose run --rm composer --version

docker-compose run --rm npm -v

docker-compose run --rm npm show vite version

docker-compose run --rm npm sqlite --version
```


# Usefull for development and first run

Add all the exisiting images from legacy-image directory as new posts. Also generate from them posts and image versions. Use the old exported databse for inserting the details like user and upload date:
```
docker-compose run --rm artisan app:migrate-legacy-images
```