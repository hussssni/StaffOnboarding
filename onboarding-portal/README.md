# Staff Onboarding Portal

A staff onboarding management system built with **PHP 8 + Laravel 11**, **Microsoft SQL Server**, and **Apache**.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x + Laravel 11 |
| Database | Microsoft SQL Server (sqlsrv PDO) |
| Web Server | Apache + mod_rewrite |
| Frontend | Blade templates + Bootstrap 5 |
| Auth | Laravel session-based auth + Gates |

---

## Prerequisites

Verify each before continuing:

```bash
php --version           # PHP 8.1+
composer --version      # Composer 2.x
sqlcmd -?               # SQL Server CLI (or use Docker below)
php -m | grep sqlsrv    # sqlsrv + pdo_sqlsrv extensions
apache2ctl -M | grep rewrite  # mod_rewrite enabled
```

### SQL Server via Docker (easiest)

```bash
docker run -e "ACCEPT_EULA=Y" -e "SA_PASSWORD=YourStrong@Passw0rd" \
  -p 1433:1433 --name onboarding-sql \
  -d mcr.microsoft.com/mssql/server:2022-latest
```

### Install PHP sqlsrv extensions (Ubuntu)

```bash
sudo pecl install sqlsrv pdo_sqlsrv
echo "extension=sqlsrv.so" | sudo tee -a /etc/php/8.x/apache2/php.ini
echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php/8.x/apache2/php.ini
sudo systemctl restart apache2
```

---

## Setup

### 1. Scaffold Laravel + install dependencies

```bash
composer create-project laravel/laravel onboarding-portal
cd onboarding-portal
```

Then copy all files from this repo into that directory (overlaying on top of the fresh Laravel install).

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your SQL Server credentials:

```
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=onboarding
DB_USERNAME=sa
DB_PASSWORD=YourStrong@Passw0rd
```

### 3. Create the database

```bash
sqlcmd -S 127.0.0.1 -U sa -P 'YourStrong@Passw0rd' -Q "CREATE DATABASE onboarding"
```

### 4. Run migrations and seed

```bash
php artisan migrate
php artisan db:seed
```

### 5. Configure Apache

```bash
sudo cp docs/vhost.conf /etc/apache2/sites-available/onboarding.conf
sudo a2ensite onboarding.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Add to `/etc/hosts`:

```
127.0.0.1 onboarding.local
```

### 6. Set storage permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Login Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@onboarding.local | password |
| Staff | staff@onboarding.local | password |

---

## Using the CSV Sync Tool

1. Log in as Admin
2. Navigate to **Sync** in the nav bar (`/admin/sync`)
3. Upload `storage/app/imports/sample_import.csv`
4. The tool will insert/update/skip each row and show a summary
5. All sync actions are logged to the `sync_log` table and `audit_log` table

**CSV format:**
```
full_name,email,department,start_date,status
Jane Smith,jane@example.com,Research,2026-04-01,Pending
```

---

## Key Design Decisions

- **Gates, not Policies or middleware roles** — access control is explicit and inline in each controller method. Easy to audit.
- **Audit log is append-only** — no update/delete routes exist on `audit_log`. Permanent paper trail.
- **SQL Server syntax throughout** — `NVARCHAR`, `DATETIME2`, `BIT`, `INT IDENTITY` — no MySQL-isms.
- **Session-based auth only** — no JWT, no Sanctum tokens. Standard Laravel `Auth`.

See `/docs/ARCHITECTURE.md` for full design notes.
