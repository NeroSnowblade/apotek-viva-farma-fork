Docker-based setup and deployment
=================================

This project can be run locally using Docker and Docker Compose, and you can push images to a private registry to deploy elsewhere.

Local development (Docker Compose)

Prerequisites

- Docker and Docker Compose installed

Run locally

1. Copy `.env.example` to `.env` and tune DB credentials to match `docker-compose.yml` (DB host: `db`, port: `3306`).
2. Build and start containers:

   Use the development compose file (includes Vite dev server):

   docker compose -f docker-compose.dev.yml up -d --build

3. Enter the app container to run artisan commands:

   docker compose exec app bash

   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --class=UserSeeder --force
   php artisan storage:link

4. Open the app at: `http://localhost:8000`
5. Adminer GUI available at: `http://localhost:8080` (use credentials from `docker-compose.yml`)

Development notes (Vite + HMR)

- The `docker-compose.dev.yml` includes a `node` service that runs `npm run dev` and exposes the Vite server on port `5173`.

- The browser will load the built assets in production, but in development the Laravel Vite plugin will point to the dev server and provide HMR.

Notes about persistence

- `docker-compose.yml` mounts a named volume `db_data` for MySQL data so data persists between restarts.

Building and pushing images to a private registry



Production image notes

- The `Dockerfile` in this repo is a multi-stage build. During image build it runs `npm run build` in the Node stage and copies the compiled frontend into the final PHP image. This means:

- You DO NOT need `php artisan serve` in production. The runtime container uses Apache to serve the Laravel app.
- You DO NOT need `npm run dev` in production. Frontend assets should be built at image build-time (or CI) and served as static files from `public/`.

1. Build the image locally:

   docker build -t `your-registry-username`/apotek-viva-farma:latest .

2. Login to registry (Docker Hub example):

   docker login


3. Push the image:

   docker push `your-registry-username`/apotek-viva-farma:latest

4. On your deployment host (e.g., Railway, another provider) pull the image and run containers, or use a container orchestration platform (K8s) to deploy.

Private repo considerations

- If your GitHub repository is private but you want to deploy, build an image and push it to a private container registry (Docker Hub private repo, GitHub Packages / GHCR, or GitLab registry). Then configure the deployment environment to pull from that registry using credentials/secrets.


CI/CD: GitHub Actions (GHCR example)

I added a workflow at `.github/workflows/docker-publish.yml` that builds the image and pushes it to GitHub Container Registry (GHCR) on pushes to `main`.


How it works

- The workflow builds multi-arch images and pushes two tags: `latest` and the commit SHA.

- It uses the `GITHUB_TOKEN` to authenticate to GHCR (no additional secret required for publishing to your personal/org registry if repo permissions allow it).


Customizing for Docker Hub

- Replace the `docker/login-action` step to log in to Docker Hub using a `DOCKERHUB_USERNAME` and `DOCKERHUB_TOKEN` stored in repository secrets.

- Change the tags in the `build-push-action` to `yourusername/apotek-viva-farma:latest`.


If you want, I can also add a workflow variant that:

- Pushes to Docker Hub instead of GHCR

- Runs tests (phpunit/pest) before building the image

- Publishes only on creating a release or a specific tag
