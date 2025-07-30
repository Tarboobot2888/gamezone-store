# GameZone Marketplace

Modern Laravel 10 single page application powered by Inertia.js and Vue 3.

## Architecture Overview
- **Laravel 10** backend with role based access control using spatie/laravel-permission.
- **Inertia.js & Vue 3** front‑end served via Vite.
- **Stripe** used for payments with webhook handling.
- **Localization** support for English and Arabic.

## Development Workflow
1. Copy `.env.example` to `.env` and set your environment values.
2. Install PHP dependencies:
   ```bash
   composer install --ignore-platform-reqs
   ```
3. Install Node dependencies:
   ```bash
   npm install
   ```
4. Run the development server:
   ```bash
   php artisan serve
   npm run dev
   ```

## Testing
Run unit and browser tests:
```bash
php artisan test
php artisan dusk
```

## Deployment
The project includes a `Dockerfile` and `docker-compose.yml` for containerised deployments.
A GitHub Actions workflow runs tests on every push and can deploy to staging.
