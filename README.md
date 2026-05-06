# Doostyabi v2

A privacy-first, compatibility-based relationship/matching platform built with simple shared-hosting-friendly PHP and MySQL.

## Created file structure

```text
config.example.php
database/schema.sql
database/seeds.sql
public/index.php
public/assets/style.css
src/bootstrap.php
src/Core/Auth.php
src/Core/Database.php
src/Core/Helpers.php
src/Core/Router.php
src/Core/View.php
src/Controllers/AdminController.php
src/Controllers/AuthController.php
src/Controllers/HomeController.php
src/Controllers/OnboardingController.php
src/Repositories/AnswerRepository.php
src/Repositories/AuditLogRepository.php
src/Repositories/FormRepository.php
src/Repositories/GoalRepository.php
src/Repositories/LocationRepository.php
src/Repositories/UserRepository.php
src/Services/AuthService.php
src/Services/FormBuilderService.php
src/Services/OnboardingService.php
views/layouts/app.php
views/home.php
views/admin/dashboard.php
views/admin/form_builder.php
views/admin/user_detail.php
views/admin/users.php
views/auth/login.php
views/auth/register.php
views/onboarding/form.php
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

## Default admin credentials

- Email: `admin@example.com`
- Password: `admin123`

Change this password immediately after first login in a real deployment.

## Manual test checklist

- Visit `/` and confirm the public landing page loads.
- Login at `/login` with the default admin credentials.
- Open `/admin` and confirm dashboard counts appear.
- Open `/admin/forms` and add a form step.
- Add a question group under that step.
- Add text, choice/select, city, boolean, date, number, scale, and range questions.
- Confirm important question saves are recorded in `audit_logs`.
- Register a member account at `/register`.
- Complete `/onboarding`, selecting goals, database-backed question options, and database-backed cities.
- Confirm answers are saved into `user_answers`, `user_answer_options`, and `user_answer_cities` based on answer type.
- Confirm `user_onboarding_progress` marks onboarding complete.
- As admin, open `/admin/users`, then a user detail page, and confirm profile, goals, and readable dynamic answers render correctly.
- Confirm page output escapes user-provided text by entering characters such as `<script>` in text answers.
