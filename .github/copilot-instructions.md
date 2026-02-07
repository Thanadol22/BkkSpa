# Copilot instructions — BangkokSpa (short guide)

Purpose
- Help AI coding agents make safe, focused edits in this PHP MVC-style app.

Big picture
- Entrypoint: [public/index.php](public/index.php) is the front controller and primary router — most actions are implemented inline via `$action = $_GET['action']` and a `switch` statement.
- Lightweight MVC: models live in `app/models/` (often accept a PDO `$pdo`), views in `app/views/` and layouts in `app/views/layouts/` (see [app/views/layouts/main_layout.php](app/views/layouts/main_layout.php)).
- DB: MySQL via singleton `app/config/database.php` (class `Database::getInstance()->getConnection()` returns PDO).

What to edit and where
- Add new user-facing routes: prefer adding the logic to `public/index.php` (consistent with existing app behavior) or create a controller in `app/controllers/` and require it from `index.php` — but do not remove or refactor `index.php` routing without coordination.
- Data access: add methods to model classes in `app/models/`. Models commonly receive `$pdo` in constructor (see `app/models/Course.php`). When possible reuse prepared statements and existing transaction patterns.
- Views & layout: capture page content via `ob_start()` in `index.php` and inject into layout `$content`. Edit view fragments in `app/views/`.

Patterns and conventions (examples)
- Routing: `index.php` reads `$action = $_GET['action'] ?? $_GET['url'] ?? 'home'` and uses `switch($action)` — follow this pattern for new actions.
- Auth: session-based. Session keys set in `index.php`: `user_id`, `username`, `role_id`, `full_name`. Role mapping used elsewhere: `1 = Admin`, `2 = Staff`, `3 = Member`.
- Database: Prefer prepared statements with `?` placeholders and `execute([$a,$b])`. Example model method: `Course::updateScheduleCapacity($schedule_id, $delta)` uses this style (see `app/models/Course.php`).
- File uploads: uploaded slips stored under `public/assets/uploads/slips/` (create dirs with `mkdir(..., 0777, true)` if absent).
- Transactions: booking/enrollment code uses `$pdo->beginTransaction()` / `commit()` / `rollback()` — follow this approach when multi-statement consistency is required.

Debug / run notes (how devs test changes)
- This project runs on a standard LAMP/XAMPP stack. Serve `public/` as the document root. Start Apache + MySQL in XAMPP and visit `http://localhost/<project>/index.php` or map the site to `public/`.
- DB credentials live in `app/config/database.php` (defaults to `root`/empty). Use the existing `dbbkkspa` DB name unless instructed otherwise.
- Logs: PHP errors appear via `error_log()` and (if enabled) in the server logs; check `storage/logs/` for any app logs.

Safe-edit rules for AI
- Do not remove or radically refactor `public/index.php` routing without opening a PR — many controllers are empty and the app expects procedural routes.
- When changing DB schema assumptions, update only model methods first and ensure callers in `index.php` or controllers are updated together.
- Preserve session-based auth behavior and role IDs — many redirects and access checks depend on them.
- Use existing patterns: prepared statements, `try/catch` around DB calls, set `$_SESSION['error']` or `$_SESSION['success']` for UI messages.

Quick pointers to inspect
- Front controller: [public/index.php](public/index.php)
- DB singleton: [app/config/database.php](app/config/database.php)
- Example model: [app/models/Course.php](app/models/Course.php)
- Layouts: [app/views/layouts/main_layout.php](app/views/layouts/main_layout.php)

If unsure, ask
- If you need to change routing style (centralize to controllers, add PSR routing, etc.), ask for approval — the app currently relies on `index.php`'s behavior and many empty controller files suggest partial migration work.

---
Please review areas you want me to expand (examples: adding a new REST API endpoint, migrating an inline route to a controller, or creating a unit-test strategy). I can iterate on this file.
