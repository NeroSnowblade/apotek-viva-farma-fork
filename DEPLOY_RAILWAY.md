Deploying Apotek Viva Farma to Railway
======================================

This guide explains how to deploy this Laravel app to Railway (https://railway.app) and provision a database.

Prerequisites
- Railway account (https://railway.app)
- GitHub account (to connect the repo) or Railway CLI installed locally
- Composer & PHP locally (optional for local build/tests)

Overview
1. Connect your repository to Railway (or use Railway CLI).
2. Add a Database plugin (Postgres or MySQL) in Railway.
3. Configure environment variables in Railway.
4. Deploy and run migrations + seeds.

Step-by-step (Railway web UI)
1. Create project
   - Go to Railway, click "New Project" → "Deploy from GitHub".
   - Authorize and select this repository (or use Railway CLI: `railway init` then `railway link`).

2. Add a Managed Database
   - Click "Plugins" → "Add Plugin" → choose Postgres or MySQL.
   - After the database is ready, click into it and copy credentials (host, port, database, user, password).

3. Configure environment variables
   - In Project Settings → Variables, add the env vars below (replace values from plugin):

     APP_NAME="Apotek Viva Farma"
     APP_ENV=production
     APP_KEY=
     APP_DEBUG=false
     APP_URL=https://<your-railway-domain>
     DB_CONNECTION=mysql    # or pgsql
     DB_HOST=<host>
     DB_PORT=<port>
     DB_DATABASE=<database>
     DB_USERNAME=<username>
     DB_PASSWORD=<password>
     BROADCAST_DRIVER=log
     CACHE_DRIVER=file
     SESSION_DRIVER=file
     QUEUE_CONNECTION=sync

   - Optional: mail settings (MAIL_MAILER, MAIL_HOST, etc.) if email is needed.

4. Procfile
   - This repo contains a `Procfile` with the line:

     web: vendor/bin/heroku-php-apache2 public/

   - Railway will detect PHP and use a Heroku-compatible PHP buildpack.

5. Deploy
   - Trigger a deploy from Railway UI (it will run Composer and set up the app).

Post-deploy commands (Railway console or CLI)
- Generate app key (if APP_KEY not set):
  php artisan key:generate --force

- Run database migrations:
  php artisan migrate --force

- Seed initial data (if needed):
  php artisan db:seed --class=UserSeeder --force

- Create storage symlink (if using public disk):
  php artisan storage:link

Railway CLI (quick)
- Install: https://railway.app/docs/cli
- Example workflow:

  railway init
  railway link
  railway up

- Set variables:
  railway variables set APP_KEY="base64:..."

- Run remote commands:
  railway run "php artisan migrate --force"

Database & backup notes
- Railway's free tiers can be ephemeral. For production, enable backups or upgrade your plan.
- To export your DB, use `mysqldump` or `pg_dump` from the Railway shell or from local with the connection string.

Security checklist
- Never commit `.env`.
- Set `APP_DEBUG=false` in production.
- Use secure credentials for DB and mail.

Troubleshooting
- Composer memory issues: check build logs and add composer flags or tweak memory limits.
- Missing vendor: ensure Composer installed dependencies during the build; check Railway build logs for errors.
- Storage 403: ensure `php artisan storage:link` executed and storage permissions are correct.

Optional follow-ups I can implement
- Add a `deploy.md` with exact, copyable Railway CLI commands.
- Add a GitHub Action to trigger Railway deployments automatically or run tests before deploy.
- Add a small `railway` deployment script.

If you'd like, I can also append a short note to `README.md` linking to this file; let me know and I will try updating `README.md` (I ran into an intermittent write error earlier).