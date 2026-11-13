<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## EA Store API

The Laravel application provides the API used by `ea-store-frontend/forex-ea-discovery-shop`.

### Local setup

1. Copy `.env.example` to `.env`, configure a database, then run `php artisan key:generate`.
2. Set `FRONTEND_URL` to the frontend origin (the storefront's Vite configuration defaults to `http://localhost:8080`; if Vite selects another port, set it accordingly).
3. Configure `STRIPE_SECRET` and `STRIPE_WEBHOOK_SECRET` in `.env`. Never expose these secrets in the frontend.
4. Run `php artisan migrate --seed`.
5. Add each real EA file to `storage/app/private/ea-files/` and set that product's `file_path` to its path relative to the private storage root. Seed products have no downloadable files and cannot be purchased until this is configured.
6. In the frontend project, copy `.env.example` to `.env`, set `VITE_API_URL=http://localhost:9000`, and restart Vite.

For local Stripe testing, run `stripe listen --forward-to localhost:9000/api/stripe/webhook` and copy the displayed signing secret to `STRIPE_WEBHOOK_SECRET`. Subscribe the endpoint to `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, and `checkout.session.expired`.

### Business flow

- Public catalog endpoints: `GET /api/products`, `GET /api/products/{slug}`, and `GET /api/categories`.
- Customers register or sign in at `/account`. The API returns a Sanctum bearer token, held by the storefront for the current browser session.
- Authenticated `POST /api/checkout` accepts product slugs and quantities. The server verifies product availability and delivery files, recalculates sale prices from the database, creates a pending order, then creates a Stripe Checkout session.
- The Stripe webhook verifies its signature and amount before marking an order paid. It issues one license per purchased unit; duplicate webhook deliveries do not issue duplicate licenses.
- Authenticated customers can view orders and license keys at `/account` and download only files attached to their own paid licenses.

The product catalog seeder mirrors the current frontend sample products. Its ratings, growth, and drawdown values are demo content, not verified trading results; replace them with substantiated data before production. Product administration, customer reviews, and trading-performance verification are not part of the current frontend/API flow.

In local development, Laravel permits browser requests from `localhost` and `127.0.0.1` on any port. In production, set `FRONTEND_URL` to the exact deployed frontend origin.

Run backend tests with `php artisan test`.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
