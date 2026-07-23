# Online Library Management System

Web-based library management system built with PHP, MySQL, and Apache.

## Requirements

- PHP 7.4 or newer
- Composer
- MySQL 8+
- Apache with `mod_rewrite` enabled

## Local Setup

1. Clone the repository.
2. Copy your environment values into `.env`.
3. Install PHP dependencies with `composer install`.
4. Import `sql/olms.sql` into MySQL.
5. Update the database credentials in `config.php` if you are not using the default local setup.
6. Point your web server document root to the project root, or use the `public/` folder if you prefer a public web root.

## Docker Setup

1. Run `docker compose up --build`.
2. Open the app at `http://localhost:8080`.
3. Open phpMyAdmin at `http://localhost:8081`.

## Notes

- Keep `.env` out of version control.
- The project uses Composer autoloading and loads optional environment variables on startup.