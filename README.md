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
public/install/smoke_matching.php
public/install/verify_schema.php
scripts/run_matching.php
src/Controllers/AdminController.php
src/Controllers/AuthController.php
src/Controllers/ChatController.php
src/Controllers/HomeController.php
src/Controllers/MatchController.php
src/Controllers/OnboardingController.php
src/Controllers/ReportController.php
src/Controllers/SafetyController.php
src/Core/Auth.php
src/Core/Database.php
src/Core/Helpers.php
src/Core/Router.php
src/Core/SetupException.php
src/Core/View.php
src/Repositories/AdminSettingsRepository.php
src/Repositories/AnswerRepository.php
src/Repositories/AuditLogRepository.php
src/Repositories/ChatRepository.php
src/Repositories/DashboardRepository.php
src/Repositories/FormRepository.php
src/Repositories/GoalRepository.php
src/Repositories/LocationRepository.php
src/Repositories/MatchRepository.php
src/Repositories/RevealRepository.php
src/Repositories/ReportRepository.php
src/Repositories/SafetyRepository.php
src/Repositories/UserRepository.php
src/Services/AdminCatalogService.php
src/Services/AdminSettingsService.php
src/Services/AuthService.php
src/Services/FormBuilderService.php
src/Services/MatchCardService.php
src/Services/MatchScoringService.php
src/Services/MatchService.php
src/Services/OnboardingService.php
src/Services/SystemHealthService.php
src/bootstrap.php
storage/logs/.gitkeep
views/admin/catalogs.php
views/admin/chat_detail.php
views/admin/chats.php
views/admin/dashboard.php
views/admin/form_builder.php
views/admin/health.php
views/admin/matches.php
views/admin/reveals.php
views/admin/moderation.php
views/admin/report_detail.php
views/admin/settings.php
views/admin/user_detail.php
views/admin/users.php
views/auth/login.php
views/auth/register.php
views/chats/index.php
views/chats/show.php
views/home.php
views/layouts/app.php
views/matches/index.php
views/onboarding/form.php
views/safety/index.php
views/setup.php
```


## Product UX overview

Doostyabi is designed to feel calm, private, and consent-led rather than like a public profile directory:

- The public landing page explains privacy-first compatibility introductions, anonymous match cards, mutual chat, and consent-based reveals.
- Registration and login pages reinforce that contact information and profile details are not exposed automatically.
- Onboarding uses progress indicators, question cards, clear empty states, and validation summaries to help members complete answers at their own pace.
- Match cards emphasize anonymous compatibility context with visual strengths, cautions, status badges, and clear but non-aggressive actions for Interested, Pass, Block, and Report.
- Chat screens use bubble-style messages, anonymous identity labels, separated reveal/safety panels, and accessible report controls.
- The Safety Center explains blocking, reporting, privacy, and reveal consent while listing active blocks.
- Admin screens remain practical and table-focused, with dashboard counts for moderation workload and consistent cards, filters, and pills.

### Manual visual QA checklist

Review these pages on desktop and mobile widths after deployment:

1. `/` — landing page hero, How it works, Privacy promise, Why it is different, and Start safely CTA.
2. `/login` and `/register` — welcoming copy, privacy reassurance, readable validation errors, and mobile layout.
3. `/onboarding` — progress indicator, goals area, question cards, validation summary, empty state when no steps exist, and save controls.
4. `/matches` — empty state, anonymous card styling, compatibility label, strengths/cautions, status badges, and Interested/Pass/Block/Report controls.
5. `/chats` and `/chats/{id}` — no-chat empty state, anonymous chat previews, bubble-style messages, reveal panel, report controls, and mobile readability.
6. `/safety` — reassuring safety copy, active blocks table, and unblock form.
7. `/admin` — consistent dashboard cards for users, matching, reports, flagged messages, and blocks.
8. `/admin/moderation`, `/admin/reveals`, `/admin/chats`, `/admin/matches` — practical filters, tables, badges/pills, and escaped dynamic content.

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
9. Visit `/install/verify_schema.php?token=YOUR_TEMP_TOKEN` to verify required tables, chat/reveal/report/moderation columns, the default admin account, default password hash verification, and core seed records.
10. Visit `/install/smoke_matching.php?token=YOUR_TEMP_TOKEN` after imports to create safe smoke users and verify the matching service can create a card.
11. Remove `app.install_token` or set it to `null`, then delete or password-protect the `public/install` directory.
12. Log in as `admin@example.com` / `admin123`, then immediately change the password or replace the seeded admin account.

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
- As admin, open `/admin/matches`, run matching for a user, and confirm scores/explanations appear.
- As a member, open `/matches` and confirm cards are anonymous and interested/pass actions save.
- Confirm mutual status is set only after both users choose Interested, and then `/chats` shows a new anonymous conversation for both users.
- Block a card and confirm it disappears, `blocks` has a row, the match status becomes `blocked`, and any related chat becomes read-only/closed.
- As admin, reset a match, clear actions, recalculate a match, review blocked pairs, review chat messages from `/admin/chats`, and close a chat with a reason.
- Confirm page output escapes user-provided text by entering characters such as `<script>` in text answers.


## Basic matching engine (Phase 3)

The Phase 3 matching engine is intentionally simple, explainable, and privacy-first:

- Candidates must be active member users, cannot be the viewer, and must share at least one active goal.
- Existing match pairs are skipped unless an admin chooses recalculation.
- Location fit uses city answers from `city_single` and `city_multi` questions when those answers exist.
- Matchable dynamic questions are controlled by admins with `is_matchable`, `match_weight`, and `match_rule`.
- Choice/select questions score by exact match or overlap depending on answer type.
- Boolean and single-choice/select questions use exact matching.
- Range questions use simple overlap compatibility.
- Scale/number questions use numeric closeness.
- `match_rule = hard_filter` rejects candidates when the comparable answer score is too low.
- Scores are saved into `match_scores` as `goal_fit`, `location_fit`, `answer_fit`, `boundary_fit`, `confidence`, and `penalty`.
- Human-readable reasons are saved in `match_explanations`.
- Anonymous `match_cards` are generated per viewer and do not expose names, email addresses, contact info, or private/admin-only answers.

### Admin matching workflow

1. In **Form Builder**, mark only safe compatibility questions as matchable.
2. Set `match_weight` higher for important soft-scored questions.
3. Use `match_rule = hard_filter` only for true boundaries that should reject incompatible candidates.
4. Ask users to complete onboarding with goals, cities, and matchable answers.
5. Open **Admin → Matches**, choose a user, and run matching.
6. Review saved score breakdowns and explanations before enabling broader use.


### Blocks, pass, and interested behavior

- Members can choose **Interested**, **Pass**, or **Block** from an anonymous card.
- A pass hides that card for the acting member and marks the match as passed unless an admin resets it.
- Interested is idempotent; clicking it again will not create duplicate action rows.
- A mutual status is set only after both users choose Interested. When this happens, the app automatically creates one chat and two `chat_participants` rows for the matched pair.
- Block creates a `blocks` row, stores a `match_actions` row with `block`, updates the match to `blocked`, hides cards for both directions, and closes/disables any related chat.
- Matching candidate selection excludes both directions of active blocks.
- Admins can reset a match, optionally clear actions, recalculate a pair, and review blocked pairs from **Admin → Matches**.


## Secure chat after mutual match (Phase 4)

Phase 4 adds privacy-preserving text chat for mutual matches only:

- `chats` stores one conversation per mutual match, including `open`/`closed` status and admin close reason fields.
- `chat_participants` limits each chat to the two matched members. User chat reads, sends, and flags all verify participant membership.
- `messages` stores text-only messages. Attachments are intentionally not implemented.
- `message_flags` stores member reports for moderation review. Flagged messages remain visible but are marked with `moderation_status = flagged`.
- Phase 5 consent-based reveal requests let mutual chat participants request safe mapped fields only after explicit approval.
- Chat creation is automatic when the second member chooses **Interested** and the match becomes `mutual`.
- `/chats` lists the signed-in member's anonymous conversations. `/chats/{id}` shows a conversation and a CSRF-protected text send form only while the chat is open and the match remains mutual.
- Blocking a match updates the match to `blocked` and closes/disables its chat so no further messages can be sent.
- `/admin/chats` lists chats with message/flag counts. `/admin/chats/{id}` lets admins review messages and close a chat with a required reason.

Privacy boundaries for this phase:

- Chat UI does not show email addresses, contact details, private profile data, public profiles, or automatic reveals.
- Members are shown as anonymous matches in user-facing chat screens.
- All output is escaped before rendering.

### Manual chat test checklist

1. Create or use two member accounts with anonymous match cards.
2. As member A, choose **Interested** on member B's card; verify no chat appears yet unless member B already chose Interested.
3. As member B, choose **Interested** on the reciprocal card; verify the match becomes `mutual` and `/chats` appears for both users.
4. Open `/chats/{id}` as each participant and send a text message; verify CSRF-protected forms save and output is escaped.
5. Try opening the chat as a third member; verify access is denied/not found.
6. Flag the other participant's message and verify `/admin/chats/{id}` shows the flag count/reason.
7. As admin, close the chat with a reason; verify members can still read but cannot send.
8. Create another mutual chat, then block the match; verify the chat becomes disabled/read-only.
9. Confirm there are no attachment controls, automatic reveal controls, public profile links, email addresses, or contact details in member chat screens.

### Known chat limitations

- No attachments, typing indicators, read receipts UI, realtime updates, automatic reveals, or public profiles.
- Moderation is review-oriented only: users can flag messages and admins can close chats, but there is no automated abuse classifier or message hiding workflow yet.
- Chat history is not encrypted at the application layer; rely on HTTPS in production and database/server access controls.


## Consent-based reveal requests (Phase 5)

Phase 5 adds limited, consent-based reveal steps inside open mutual-match chats:

- `reveal_types` is an admin-managed catalog with title, slug, description, safe `reveal_field_key`, privacy level, mutual-approval flag, active flag, and sort order.
- Seeded reveal fields are intentionally limited to `display_name`, `city`, and `selected_goals_summary`. Email, password data, contact information, public profiles, private answers, and admin-only answers are never revealed by these mappings.
- `reveal_requests` records requester, target, match, chat, reveal type, status, optional request/response notes, and expiry timestamps.
- Only participants in an open `mutual` chat can create reveal requests. Blocked or closed chats cannot create new reveal requests.
- Requesters cannot approve their own requests; only the target participant can approve or reject a pending incoming request.
- Approved values are copied into `match_visibility_snapshots` for the approved viewer only, so approval for one participant does not expose the same data to anyone else.
- Member chat pages show reveal request buttons, pending incoming/outgoing requests, approve/reject controls for incoming requests, and approved revealed info visible only to that approved viewer.
- Admins can manage reveal types and review/filter reveal requests from **Admin → Reveals**, but cannot force approval in this MVP.

### Manual reveal test checklist

1. Create a mutual match and verify an open chat exists.
2. As participant A, request `Display name`, `Home city`, or `Selected goals summary` from the chat page.
3. Verify participant A sees the request as outgoing pending and participant B sees it as incoming pending.
4. As participant B, approve one request and reject another; verify participant A sees only the approved mapped value and the rejection status/note.
5. Log in as a third member and verify they cannot open the chat or access reveal actions.
6. Close the chat as admin or block the match, then verify new reveal requests cannot be created.
7. In **Admin → Reveals**, create/edit reveal types and filter request oversight by status, type, and user.
8. Confirm no email, contact info, passwords, raw private answers, admin-only answers, public profiles, or automatic reveals appear in the member UI.

### Known reveal limitations

- Expired requests have schema/status support but no scheduled expiry job yet.
- Admin oversight is read-only for requests; forced admin approval/rejection is intentionally out of scope.
- Only three safe mapped fields are supported initially; additional fields require explicit code-level mapping before catalog use.
- Revealed snapshots are stored in the database for auditability and are not application-layer encrypted.


## Reports, moderation, and safety center (Phase 6)

Phase 6 adds safety workflows for members and admins without public profiles:

- `reports` stores reports about matches/cards, chats, messages, and users with reporter/reported users, related match/chat/message IDs, type, reason, description, status, priority, assignment, resolution note, and resolution timestamp.
- Members can submit CSRF-protected reports from match cards and chat pages. Message reports are participant-only and also flag the message for moderation review.
- Report validation checks match/chat/message access so non-participants cannot report inaccessible conversations or messages.
- **Safety Center** at `/safety` explains privacy, blocking, reporting, and reveal consent, lists active blocks, and lets members unblock when needed.
- **Admin → Moderation** lists reports with filters for status, type, and priority. Report detail shows related user/match/chat/message context, supports assignment to the current admin, priority/status updates, resolution/dismissal notes, and blocking the reported user for the reporter.
- Admin dashboard cards include open reports, flagged messages, and blocked pairs.
- If an optional `notifications` table exists with a compatible shape, the app attempts generic admin notifications for new reports and generic reporter notifications after resolution; notification failures are ignored safely.

### Manual moderation test checklist

1. As a member, report an anonymous match card from `/matches`; verify the report appears in **Admin → Moderation**.
2. As a chat participant, report a chat and report another participant's message; verify message reports mark the message as flagged.
3. Log in as a third member and verify they cannot report a chat/message they cannot access.
4. As admin, filter reports by status/type/priority, open a report detail page, assign it to yourself, change priority, and save a reviewing status.
5. Resolve or dismiss a report with a generic note; verify the status and resolved timestamp update.
6. From report detail, block the reported user for the reporter; verify the block appears in the reporter's Safety Center and related match/chat is disabled.
7. As the reporter, open `/safety`, review safety guidance and active blocks, then unblock if appropriate.
8. Confirm all member-facing report and safety screens avoid exposing email, contact info, private answers, admin-only answers, or public profiles.

### Known moderation limitations

- No public profiles are implemented.
- Admin notifications are best-effort only and require a compatible optional `notifications` table.
- Blocking from report detail blocks the reported member only for the reporter; broader account suspension is not implemented yet.
- There is no automated abuse classifier, evidence attachment upload, or moderation SLA workflow yet.

### Matching smoke test

After importing schema and seeds on shared hosting, run the guarded smoke test:

```text
/install/smoke_matching.php?token=YOUR_TEMP_TOKEN
```

The smoke test creates or reuses two `@example.invalid` test members, assigns a shared seeded goal, runs matching, and reports whether an anonymous card can be created. It does not expose secrets, passwords, hashes, or private answers. Delete or protect `public/install` after setup.

### Shared-hosting cron command

Run matching in small batches from cPanel Cron Jobs or SSH:

```bash
php /home/YOUR_CPANEL_USER/doostyabi/scripts/run_matching.php --limit=20
```

Use small limits on shared hosting. This script is not a daemon and exits after one batch.

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
