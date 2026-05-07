# Doostyabi v2

A privacy-first, compatibility-based relationship/matching platform built with simple shared-hosting-friendly PHP and MySQL.

## Created file structure

```text
.gitignore
config.example.php
database/schema.sql
database/seeds.sql
public/assets/style.css
public/index.php
public/install/_common.php
public/install/check.php
public/install/verify_schema.php
src/Controllers/AdminController.php
src/Controllers/AuthController.php
src/Controllers/HomeController.php
src/Controllers/OnboardingController.php
src/Core/Auth.php
src/Core/Database.php
src/Core/Helpers.php
src/Core/Router.php
src/Core/SetupException.php
src/Core/View.php
src/Repositories/AdminSettingsRepository.php
src/Repositories/AnswerRepository.php
src/Repositories/AuditLogRepository.php
src/Repositories/DashboardRepository.php
src/Repositories/FormRepository.php
src/Repositories/GoalRepository.php
src/Repositories/LocationRepository.php
src/Repositories/UserRepository.php
src/Services/AdminCatalogService.php
src/Services/AdminSettingsService.php
src/Services/AuthService.php
src/Services/FormBuilderService.php
src/Services/OnboardingService.php
src/Services/SystemHealthService.php
src/bootstrap.php
storage/logs/.gitkeep
views/admin/catalogs.php
views/admin/dashboard.php
views/admin/form_builder.php
views/admin/health.php
views/admin/settings.php
views/admin/user_detail.php
views/admin/users.php
views/auth/login.php
views/auth/register.php
views/home.php
views/layouts/app.php
views/onboarding/form.php
views/setup.php
```

## Setup instructions

1. Create a MySQL database, for example `doostyabi`.
2. Copy `config.example.php` to `config.php` and update the database host, name, username, and password.
3. Import the schema and seeds:
   ```bash
   mysql -u YOUR_USER -p doostyabi < database/schema.sql
   mysql -u YOUR_USER -p doostyabi < database/seeds.sql
   ```
4. Point your web server document root to `public/`.
5. For local testing, run:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
6. Open `http://127.0.0.1:8000`.

## cPanel shared-hosting installation

1. In cPanel, create a MySQL/MariaDB database and database user, then grant the user privileges on that database.
2. Upload the project outside `public_html` when possible, for example to `/home/YOUR_CPANEL_USER/doostyabi`.
3. Point the domain or subdomain document root to the project's `public/` directory. If your host cannot point outside `public_html`, place the contents of `public/` in `public_html` temporarily (including `install/` for setup), keep `src/`, `database/`, `storage/`, `views/`, and `config.php` outside the web root, and delete or protect `public_html/install` immediately after setup.
4. Copy `config.example.php` to `config.php` in the project root. Set `db.host`, `db.name`, `db.user`, `db.pass`, and keep `app.env` as `production`.
5. Temporarily set `app.install_token` in `config.php` to a long random string, such as a 32+ character password-manager value. Do not reuse the database password.
6. Open phpMyAdmin, select the new database, use **Import**, and import `database/schema.sql` first.
7. In phpMyAdmin, import `database/seeds.sql` second.
8. Visit `/install/check.php?token=YOUR_TEMP_TOKEN` to verify PHP extensions, config loading, database connectivity, writable folders, and table existence.
9. Visit `/install/verify_schema.php?token=YOUR_TEMP_TOKEN` to verify required tables, the default admin account, default password hash verification, and core seed records.
10. Remove `app.install_token` or set it to `null`, then delete or password-protect the `public/install` directory.
11. Log in as `admin@example.com` / `admin123`, then immediately change the password or replace the seeded admin account.

### Running install checks from SSH/CLI

If your shared host provides SSH, you can run:

```bash
php public/install/check.php --token=YOUR_TEMP_TOKEN
php public/install/verify_schema.php --token=YOUR_TEMP_TOKEN
```

The install scripts do not print database passwords or password hashes. They are guarded and run only when `app.env` is `local`/`development` or a temporary `app.install_token`/`INSTALL_TOKEN` is configured.


## Recommended admin setup order

After importing schema/seeds and logging in as an admin:

1. Open **System → Settings** and set the site name, site status, registration availability, and default onboarding redirect.
2. Open **Catalogs** and review/create goals first; members select these during onboarding.
3. In **Catalogs**, review/create provinces and cities before adding city-based questions.
4. Open **Form Builder** and create form steps, such as Basics, Compatibility, and Lifestyle.
5. Add question groups inside each step to keep onboarding readable.
6. Add questions using the grouped builder sections: Basic Info, Answer Type, Privacy, Matching, Match Card, and Validation/UI.
7. For choice/select questions, add stable options with title, value, description, sort order, and active status. If an option already has answers, disable it instead of changing its meaning.
8. Preview onboarding as a member and verify previous answers remain selected when editing.
9. Use **Users** to review a member profile, selected goals, onboarding status, grouped answers, privacy level, and matchable status.
10. Use **System → Health** after changes to confirm the installation remains healthy.

## Default admin credentials

- Email: `admin@example.com`
- Password: `admin123`

Change this password immediately after first login in a real deployment.

## Manual test checklist

- Visit `/` and confirm the public landing page loads.
- Login at `/login` with the default admin credentials.
- Open `/admin` and confirm dashboard stat cards, quick links, and sidebar navigation appear.
- Open `/admin/settings`, update settings, and confirm they persist in `admin_settings`.
- Open `/admin/catalogs` and add/update goals, provinces, and cities.
- Open `/admin/forms` and add a form step.
- Add a question group under that step.
- Add text, choice/select, city, boolean, date, number, scale, and range questions.
- Edit a choice/select question and confirm option IDs are preserved while removed options are soft-disabled.
- Confirm important admin saves are recorded in `audit_logs`.
- Register a member account at `/register`.
- Complete `/onboarding`, selecting goals, database-backed question options, and database-backed cities.
- Re-open `/onboarding` and confirm previous answers are visually preserved and progress is shown.
- Confirm answers are saved into `user_answers`, `user_answer_options`, and `user_answer_cities` based on answer type.
- Confirm `user_onboarding_progress` marks onboarding complete.
- As admin, open `/admin/users`, then a user detail page, and confirm profile summary, goals, onboarding status, grouped answers, privacy level, and matchable status render correctly.
- Confirm page output escapes user-provided text by entering characters such as `<script>` in text answers.

## Production deployment checklist for shared hosting

- Set the document root to `public/` so `src/`, `database/`, `storage/`, and `config.php` are not web-accessible.
- Copy `config.example.php` to `config.php`, fill in real database credentials, and keep `config.php` out of Git.
- Use a strong unique database password and a database user with only the required application privileges.
- Import `database/schema.sql` first, then `database/seeds.sql`; both are designed to be safe to re-run where practical.
- Change the seeded admin password immediately after first login, or create a fresh admin manually if seeds are not imported.
- Confirm PHP has the `pdo_mysql` extension enabled.
- Confirm HTTPS is enabled before collecting real user data.
- Confirm `storage/logs` is writable by the PHP user and not publicly exposed.
- Visit `/admin/health` after login to verify PHP, PDO MySQL, database connectivity, writable logs, session status, environment, and expected tables.

## File and folder permissions

- `public/`: readable by the web server.
- `config.php`: readable by the PHP process only; recommended `640` or stricter depending on host ownership.
- `storage/logs/`: writable by the PHP process; commonly `750`/`770` depending on host ownership.
- `database/`, `src/`, and `views/`: readable by PHP but should not be directly served by the web server.

## Creating the first admin without seeds

If you cannot import `database/seeds.sql`, create the roles and admin manually after importing the schema:

```sql
INSERT INTO roles (name, label) VALUES ('admin', 'Administrator'), ('user', 'Member')
ON DUPLICATE KEY UPDATE label=VALUES(label);

INSERT INTO permissions (name, label) VALUES
('admin.access', 'Access admin dashboard'),
('forms.manage', 'Manage dynamic form builder'),
('users.view', 'View users'),
('onboarding.complete', 'Complete onboarding')
ON DUPLICATE KEY UPDATE label=VALUES(label);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.name='admin'
ON DUPLICATE KEY UPDATE role_id=role_id;
```

Generate a password hash on your host:

```bash
php -r "echo password_hash('REPLACE_WITH_TEMP_PASSWORD', PASSWORD_BCRYPT), PHP_EOL;"
```

Then insert the admin user with that hash:

```sql
INSERT INTO users (role_id, email, password_hash, first_name, is_active)
SELECT id, 'admin@example.com', 'PASTE_HASH_HERE', 'Admin', 1 FROM roles WHERE name='admin';
```

## Troubleshooting common errors

- **Setup required page:** `config.php` is missing. Copy `config.example.php` to `config.php` and update credentials.
- **Database connection failed:** verify DB host/name/user/password, confirm the DB exists, and confirm the hosting firewall allows local MySQL connections.
- **PDO MySQL missing:** enable the `pdo_mysql` PHP extension in hosting control panel or ask the host to enable it.
- **Tables missing on System Health:** import `database/schema.sql`, then `database/seeds.sql`.
- **Invalid CSRF token:** refresh the form page and resubmit; also verify PHP sessions are working and cookies are enabled.
- **Cannot write logs:** update permissions or ownership for `storage/logs` so the PHP process can write there.
