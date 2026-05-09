# Compliance Checklist Management System

A full-stack web application for managing compliance workflows through structured checklists. Built with **Laravel 11**, **React** (via Inertia.js), and **Tailwind CSS**.

Admins create and manage checklist templates with typed questions. Auditors complete those checklists, save drafts, and submit final responses. The system exposes a REST API secured with Laravel Sanctum and provides a reporting interface with filtering and PDF export capabilities.

---

## Table of Contents

- [Installation](#installation)
- [Seeder Instructions](#seeder-instructions)
- [Test Credentials](#test-credentials)
- [Queue Setup](#queue-setup)
- [Docker Setup](#docker-setup)
- [Running Tests](#running-tests)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)

---

## Installation

### Prerequisites

- PHP 8.2+
- Composer 2
- Node.js 18+ and npm
- MySQL 8.0 (or SQLite for local development)

### Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/nganduntita1/ngandu-ntita-checklist-assessment.git
   cd ngandu-ntita-checklist-assessment
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**

   ```bash
   npm install
   ```

4. **Copy the environment file**

   ```bash
   cp .env.example .env
   ```

5. **Generate the application key**

   ```bash
   php artisan key:generate
   ```

6. **Configure your database**

   Open `.env` and update the database connection settings:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=compliance_db
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   ```

   For a quick local setup with SQLite, use:

   ```dotenv
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database/database.sqlite
   ```

   Then create the SQLite file:

   ```bash
   touch database/database.sqlite
   ```

7. **Run database migrations**

   ```bash
   php artisan migrate
   ```

8. **Build frontend assets**

   ```bash
   npm run build
   ```

9. **Start the development server**

   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`.

   For hot-reloading during development, run in a separate terminal:

   ```bash
   npm run dev
   ```

---

## Seeder Instructions

Seed the database with default users and sample checklist templates:

```bash
php artisan db:seed
```

Expected output:

```
   INFO  Seeding database.

  Database\Seeders\UserSeeder ............... RUNNING
  Database\Seeders\UserSeeder ............... DONE

  Database\Seeders\ChecklistTemplateSeeder .. RUNNING
  Database\Seeders\ChecklistTemplateSeeder .. DONE
```

This creates:
- 2 user accounts (admin and auditor — see [Test Credentials](#test-credentials))
- 5 sample checklist templates with 3–6 questions each

To reset and re-seed from scratch:

```bash
php artisan migrate:fresh --seed
```

---

## Test Credentials

| Role    | Email                 | Password   |
|---------|-----------------------|------------|
| Admin   | `admin@example.com`   | `password` |
| Auditor | `auditor@example.com` | `password` |

**Admin** can create, edit, and delete templates, and view reports.

**Auditor** can view active templates, start checklist instances, save drafts, and submit completed checklists.

---

## Queue Setup

PDF export is processed asynchronously via Laravel's queue system. The queue connection is configured in `.env`:

```dotenv
QUEUE_CONNECTION=database
```

### Starting the queue worker

```bash
php artisan queue:work --sleep=3 --tries=3
```

For production, use a process manager like Supervisor to keep the worker running. A minimal Supervisor config:

```ini
[program:compliance-queue]
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/compliance-queue.log
```

### Required environment variables for queue

| Variable           | Value      | Description                          |
|--------------------|------------|--------------------------------------|
| `QUEUE_CONNECTION` | `database` | Uses the `jobs` table for the queue  |
| `FILESYSTEM_DISK`  | `local`    | Where generated PDFs are stored      |

Failed jobs are stored in the `failed_jobs` table and can be retried with:

```bash
php artisan queue:retry all
```

---

## Docker Setup

The project includes a full Docker environment with PHP-FPM, Nginx, MySQL, and a queue worker.

### Starting the containers

```bash
docker-compose up -d
```

This starts four services:
- `app` — PHP 8.2-FPM application server
- `nginx` — Nginx reverse proxy (accessible at `http://localhost:8080`)
- `db` — MySQL 8.0 database
- `queue` — Laravel queue worker

### Running migrations and seeders inside Docker

Once the containers are running, exec into the `app` container:

```bash
docker-compose exec app bash
```

Then run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

Or combine them in one step:

```bash
php artisan migrate:fresh --seed
```

### Building frontend assets inside Docker

```bash
docker-compose exec app bash -c "npm install && npm run build"
```

### Stopping the containers

```bash
docker-compose down
```

To also remove the database volume (destroys all data):

```bash
docker-compose down -v
```

### Docker environment variables

The `docker-compose.yml` sets the following defaults:

| Variable        | Value          |
|-----------------|----------------|
| `DB_HOST`       | `db`           |
| `DB_DATABASE`   | `compliance_db`|
| `DB_USERNAME`   | `compliance`   |
| `DB_PASSWORD`   | `secret`       |
| `MYSQL_ROOT_PASSWORD` | `secret` |

---

## Running Tests

The test suite uses **Pest PHP** and runs against an in-memory SQLite database so no production data is affected.

### Run all tests

```bash
php artisan test
```

### Run with verbose output

```bash
php artisan test --verbose
```

### Run a specific test file

```bash
php artisan test tests/Feature/AuthTest.php
```

### Run a specific test by name

```bash
php artisan test --filter "valid login returns token"
```

### Expected output

```
   PASS  Tests\Feature\AuthTest
   PASS  Tests\Feature\TemplateCrudTest
   PASS  Tests\Feature\ChecklistFlowTest
   PASS  Tests\Feature\ReportTest
   PASS  Tests\Feature\PdfExportTest
   PASS  Tests\Unit\TemplateServiceTest
   PASS  Tests\Unit\ChecklistServiceTest
   PASS  Tests\Unit\ReportServiceTest
   PASS  Tests\Unit\TemplatePolicyTest
   PASS  Tests\Unit\InstancePolicyTest

  Tests:    XX passed
  Duration: X.XXs
```

### Test configuration

Tests are configured in `phpunit.xml` to use:

| Setting            | Value      |
|--------------------|------------|
| `DB_CONNECTION`    | `sqlite`   |
| `DB_DATABASE`      | `:memory:` |
| `QUEUE_CONNECTION` | `sync`     |
| `MAIL_MAILER`      | `array`    |

---

## Project Structure

```
compliance-checklist/
│
├── app/
│   ├── Actions/                    # Single-purpose action classes
│   │   └── SubmitChecklistAction.php
│   ├── Http/
│   │   ├── Controllers/            # Thin controllers delegating to services
│   │   │   ├── Web/                # Inertia/web controllers
│   │   │   ├── AuthController.php
│   │   │   ├── ChecklistController.php
│   │   │   ├── PdfExportController.php
│   │   │   ├── ReportController.php
│   │   │   └── TemplateController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureRole.php      # Role-based access middleware
│   │   │   └── HandleInertiaRequests.php
│   │   ├── Requests/               # Form request validation classes
│   │   │   ├── LoginRequest.php
│   │   │   ├── SaveDraftRequest.php
│   │   │   ├── StoreTemplateRequest.php
│   │   │   ├── UpdateTemplateRequest.php
│   │   │   └── ReportFilterRequest.php
│   │   └── Resources/              # API resource transformers
│   │       ├── AnswerResource.php
│   │       ├── InstanceResource.php
│   │       ├── QuestionResource.php
│   │       ├── ReportResource.php
│   │       ├── TemplateResource.php
│   │       └── UserResource.php
│   ├── Jobs/
│   │   └── GeneratePdfJob.php      # Async PDF generation queue job
│   ├── Models/                     # Eloquent models
│   │   ├── ChecklistAnswer.php
│   │   ├── ChecklistInstance.php
│   │   ├── ChecklistQuestion.php
│   │   ├── ChecklistTemplate.php
│   │   └── User.php
│   ├── Policies/                   # Laravel authorization policies
│   │   ├── InstancePolicy.php
│   │   ├── ReportPolicy.php
│   │   └── TemplatePolicy.php
│   ├── Repositories/               # Data access layer
│   │   ├── Contracts/              # Repository interfaces
│   │   ├── InstanceRepository.php
│   │   ├── ReportRepository.php
│   │   └── TemplateRepository.php
│   └── Services/                   # Business logic layer
│       ├── AuthService.php
│       ├── ChecklistService.php
│       ├── ReportService.php
│       └── TemplateService.php
│
├── database/
│   ├── factories/                  # Model factories for testing and seeding
│   ├── migrations/                 # Database schema migrations
│   └── seeders/                    # Database seeders
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       └── ChecklistTemplateSeeder.php
│
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── Questions/          # QuestionBuilder and AnswerInput components
│   │   │   └── UI/                 # Shared UI components (Badge, Modal, etc.)
│   │   ├── Layouts/                # AppLayout and GuestLayout
│   │   └── Pages/
│   │       ├── Auth/               # Login page
│   │       ├── Checklists/         # Auditor checklist pages
│   │       ├── Reports/            # Admin reports page
│   │       └── Templates/          # Admin template CRUD pages
│   └── views/
│       └── pdf/                    # Blade template for PDF export
│
├── routes/
│   ├── api.php                     # REST API routes (Sanctum-protected)
│   └── web.php                     # Inertia web routes
│
├── tests/
│   ├── Feature/                    # Full HTTP stack feature tests
│   │   ├── AuthTest.php
│   │   ├── ChecklistFlowTest.php
│   │   ├── PdfExportTest.php
│   │   ├── ReportTest.php
│   │   └── TemplateCrudTest.php
│   └── Unit/                       # Isolated unit tests
│       ├── ChecklistServiceTest.php
│       ├── InstancePolicyTest.php
│       ├── ReportServiceTest.php
│       ├── TemplatePolicyTest.php
│       └── TemplateServiceTest.php
│
├── docker/
│   ├── nginx/default.conf          # Nginx server configuration
│   └── php/Dockerfile              # PHP 8.2-FPM Docker image
│
├── docs/
│   └── postman_collection.json     # Postman API collection (v2.1)
│
├── docker-compose.yml              # Docker services definition
├── .env.example                    # Environment variable template
└── phpunit.xml                     # PHPUnit/Pest test configuration
```

---

## API Documentation

The full API is documented as a **Postman Collection v2.1** file located at:

```
docs/postman_collection.json
```

### Importing the Collection

1. Open Postman.
2. Click **File → Import** (or press `Ctrl+O` / `Cmd+O`).
3. Select `docs/postman_collection.json` from the project directory.
4. The collection **"Compliance Checklist Management API"** will appear in your sidebar.

### Setting Up Variables

The collection uses two variables:

| Variable  | Default                 | Description                            |
|-----------|-------------------------|----------------------------------------|
| `baseUrl` | `http://localhost:8080` | Base URL of the running server         |
| `token`   | *(empty)*               | Bearer token obtained after logging in |

After logging in via `POST /api/login`, copy the `token` from the response and paste it into the collection's `token` variable. All subsequent requests will include `Authorization: Bearer <token>` automatically.

### Endpoints Overview

| Method | Endpoint                          | Role          | Description                        |
|--------|-----------------------------------|---------------|------------------------------------|
| POST   | `/api/login`                      | Any           | Obtain Sanctum bearer token        |
| POST   | `/api/logout`                     | Any           | Revoke current token               |
| GET    | `/api/templates`                  | Admin/Auditor | List templates (role-filtered)     |
| POST   | `/api/templates`                  | Admin         | Create template with questions     |
| GET    | `/api/templates/{id}`             | Admin/Auditor | Get single template                |
| PUT    | `/api/templates/{id}`             | Admin         | Update template and questions      |
| DELETE | `/api/templates/{id}`             | Admin         | Delete template (cascades)         |
| GET    | `/api/checklists`                 | Auditor       | List own checklist instances       |
| POST   | `/api/checklists/start`           | Auditor       | Start a new checklist instance     |
| POST   | `/api/checklists/{id}/save-draft` | Auditor       | Save draft answers                 |
| POST   | `/api/checklists/{id}/submit`     | Auditor       | Submit completed checklist         |
| GET    | `/api/reports`                    | Admin         | Filtered, paginated instance report|
| POST   | `/api/checklists/{id}/export-pdf` | Admin/Auditor | Dispatch PDF generation job        |
| GET    | `/api/checklists/{id}/download-pdf`| Admin/Auditor| Download generated PDF             |

### Response Envelope

All API responses follow this consistent format:

```json
{
  "success": true,
  "message": "Human-readable status message",
  "data": {}
}
```

Error responses use `"success": false` and include field-level details in `data` for 422 validation errors.
