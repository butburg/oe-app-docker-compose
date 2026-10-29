# Oh Eweedy

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/O4O111DFT3)

The ultimate art platform for sharing your masterpieces. Create a collaborative image gallery.

This Laravel 11 app is here to retire my old plain PHP website.

## Support This Project

If this project helps you, you can support ongoing development here:

[Buy me an ice cream 🍦](https://ko-fi.com/butburg)

### Features
- Upload your images and add them to **THE** gallery.
- Give your piece a name, turning it into a true work of art.
- Comment other posts to share your profound insights (or witty remarks) with the artist.

If you are a nerd (with love), or you just want the full history of this cool project, jump into the [CHANGELOG.md](docs/CHANGELOG.md).
You will find all the details there: zoom, pagination, and other gloriously boring stuff like that.

\
The [Laravel 11](https://laravel.com/docs/11.x/) app is [dockerbased](https://www.docker.com/) and based on [aschmelyun](https://github.com/aschmelyun/docker-compose-laravel)'s:
# docker-compose-laravel
A pretty simplified Docker Compose workflow that sets up a LEMP network of containers for local Laravel development. You can view the full article that inspired this repo [here](https://dev.to/aschmelyun/the-beauty-of-docker-for-local-laravel-development-13c0).

## Usage

To get started, make sure you have [Docker installed](https://docs.docker.com/docker-for-mac/install/) on your system, and then clone this repository.

Next, navigate in your terminal to the directory you cloned this, and spin up the containers for the web server by running `docker-compose up -d --build app`.

After that completes, follow the steps from the [src/README.md](src/README.md) file to get your Laravel project added in (or create a new blank one).

**Note**: Local development uses SQLite (`DB_CONNECTION=sqlite`, file `src/database/database.sqlite`, see `src/.env.example`). Production runs on MySQL, configured only via the `.env` deployed from the GitHub secret `ENV_VARIABLES`; no database container is needed locally.
There is no implicit fallback: without `DB_CONNECTION` the app fails with `Database connection [] not configured.`, and the SQLite file is never created automatically. For a fresh local setup create it once with `touch src/database/database.sqlite`, then run `docker-compose run --rm artisan migrate --seed`.

Bringing up the Docker Compose network with `app` instead of just using `up`, ensures that only our site's containers are brought up at the start, instead of all of the command containers as well. The following are built for our web server, with their exposed ports detailed:

- **nginx** - `:80`
- **php** - `:9000`
- **redis** - `:6379`
- **mailhog** - `:8025`
- **queue** - sends the queued mails (`queue:work --tries=3`), restarts with the stack. After code changes run `docker-compose restart queue`, the worker keeps the old code in memory. Failed mails: `docker-compose run --rm artisan queue:failed`. On the server a lima-city cron job does this instead, see [docs/server-cronjobs.md](docs/server-cronjobs.md).

Three additional containers are included that handle Composer, NPM, and Artisan commands *without* having to have these platforms installed on your local computer. Use the following command examples from your project root, modifying them to fit your particular use case.

- `docker-compose run --rm composer update`
- `docker-compose run --rm artisan migrate`
- `docker-compose run --rm artisan test` (tests run only in the container, on an in-memory SQLite DB)
- `docker-compose run --rm npm npm install` (important for first setup)
- `docker-compose run --rm npm run dev`
- `docker-compose run --rm artisan impressum:image --contact "..."` (see [Impressum images](#impressum-images))


## Impressum images

The contact and mail data on the impressum are shown as images (`public/images/impressum-contact-protected.png` and `impressum-mail-protected.png`). The text is drawn with shifted, rotated letters on a noisy background, so simple scrapers can't read it while people still can. Regenerate them with the `impressum:image` Artisan command whenever the data changes.

```bash
# address image, each quoted argument becomes one line
docker-compose run --rm artisan impressum:image --contact "Jane Doe" "Example Street 1" "12345 Berlin"

# mail image
docker-compose run --rm artisan impressum:image --mail "someone@example.com"
```

- Pass exactly one of `--contact` or `--mail`. It decides which file is written and the existing one is overwritten.
- For a line break, start a new quoted argument. `\n` inside the text is not interpreted.
- Add `--seed=7` (any number) to get the same image every run; without it every run looks different.
- `docker-compose run --rm artisan impressum:image --help` lists all options.

Pass the real data only on the command line and never write it into a file in the repository, because the repository is public. Commit only the generated PNGs.

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

## Usage in Production

While I originally created this template for local development, it's robust enough to be used in basic Laravel application deployments. The biggest recommendation would be to ensure that HTTPS is enabled by making additions to the `nginx/default.conf` file and utilizing something like [Let's Encrypt](https://hub.docker.com/r/linuxserver/letsencrypt) to produce an SSL certificate.

## Compiling Assets

This configuration should be able to compile its assets with both [laravel mix](https://laravel-mix.com/) and [vite](https://vitejs.dev/). In order to get started, you first need to add ` --host 0.0.0.0` after the end of your relevant dev command in `package.json`. So for example, with a Laravel project using Vite, you should see:

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
- php: 8.3 (image pinned to `php:8.3-fpm-alpine`, phpredis 5.3.4 does not build on newer PHP)
- composer: 2.8.2
- npm: 22.1.0
- vite: 5.4.10
- Database: SQLite (dev), MySQL (prod)

Check it with
```
docker-compose run --rm artisan --version

docker-compose run --rm php -v

docker-compose run --rm composer --version

docker-compose run --rm npm -v

docker-compose run --rm npm show vite version

docker-compose run --rm npm sqlite --version
```


# Demo data for development

`artisan db:seed` fills the database with static demo data from `src/database/seeders/data/`: 31 CC0 images from Wikimedia Commons in mixed aspect ratios and sizes, drafts, comments and profile images. Sources and licenses are listed in `posts.json` and `profiles.json` there.

| User | Login | Content |
|---|---|---|
| Test-Admin User | `admin@user.com` / `admin` | 3 published posts |
| Test-User ohne Bilder | `user-ohne-bilder@user.com` / `password` | portrait profile image, no posts |
| Test-User mit ganz vielen Bildern | `user-viele-bilder@user.com` / `password` | landscape profile image, 14 published posts, 14 drafts |

On the local login page the two developer login buttons sign in as the user with many images or as the admin.

To start over: `docker-compose run --rm artisan migrate:fresh --seed`. This wipes the local database, and the generated image files in `src/storage/app/public/files/posts` and `profiles` get overwritten by id.
