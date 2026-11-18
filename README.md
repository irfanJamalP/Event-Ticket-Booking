# Event Ticket Booking

PHP ticket-booking application backed by Neon PostgreSQL.

## Requirements

- PHP 8.2 or newer with `pdo_pgsql`
- PostgreSQL 15 or newer (Neon is recommended)
- Docker for the Render deployment

## Database setup

1. Create a Neon project in the Singapore region.
2. In the Neon SQL Editor, run [`database/db.sql`](database/db.sql). It creates the application tables and non-personal demo events/payment methods. It does not create an administrator or import customer/payment data.
3. Copy `.env.example` to `.env` and set `DATABASE_URL` to the Neon connection string. Keep the password private and do not commit `.env`.

## Run locally

```powershell
Copy-Item .env.example .env
# Set DATABASE_URL in .env to your Neon connection string.
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000`. Ensure the PHP runtime has `pdo_pgsql` enabled.

## Create an administrator

For a local installation, generate a password hash:

```bash
php -r "echo password_hash('use-a-unique-strong-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Insert an administrator through the Neon SQL Editor, replacing the username and hash:

```sql
INSERT INTO admin (username, password)
VALUES ('admin', '<password-hash>');
```

Do not use a default or shared administrator password.

On Render, `scripts/bootstrap.php` applies the schema and creates the first administrator from the `ADMIN_USERNAME` and `ADMIN_PASSWORD` environment variables. Set both as secrets in the Render dashboard.

## Deploy to Render

Connect the GitHub repository to Render as a Docker web service. Set `DATABASE_URL`, `ADMIN_USERNAME`, and `ADMIN_PASSWORD` in Render as secrets. Do not put database credentials or administrator passwords in Git, the Docker image, or `render.yaml`.

The application accepts uploaded payment proofs, but Render's container filesystem is ephemeral. Configure durable private object storage before relying on payment uploads in production.
