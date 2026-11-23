---
name: shoebill-pelican-ui
description: Add or change a feature in Shoebill's main panel (Pelican base: Laravel 13 + Filament 5 + Livewire). Use for new server pages, admin resources, widgets, settings, translations, scheduled jobs or client API endpoints in panel/.
---

# Shoebill main panel (Pelican UI)

Work in `panel/`. Toolchain: PHP 8.4+, Composer, Yarn. Tests: `php -d memory_limit=2G vendor/bin/pest <path>`. Format: `vendor/bin/pint <paths>`. Assets: `yarn build`.

## Where things go
| Feature | Location | Copy this |
|---|---|---|
| Server page (left nav under a server) | `app/Filament/Server/Pages/X.php` + `resources/views/filament/server/pages/x.blade.php` | `Status.php` (read-only), `Mounts.php` (form) |
| Server resource (list/create/edit) | `app/Filament/Server/Resources/` | `Backups/` |
| Admin resource | `app/Filament/Admin/Resources/` | `Mounts/` |
| Admin setting | `app/Filament/Admin/Pages/Settings.php` — add a `Tab::make()` + `private function xSettings()`; values are env keys written to `.env` | `languageSettings()` |
| Chart / stat widget | `app/Filament/Server/Widgets/` (`ChartWidget`) | `ServerStatusChart.php` |
| Client API | `routes/api-client.php` + `app/Http/Controllers/Api/Client/Servers/` | `ServerStatsController.php` |
| Scheduled job | `app/Console/Commands/...` + register in `app/Console/Kernel.php::schedule()` | `RecordServerStatsCommand.php` |
| Model + table | `app/Models/` + `database/migrations/YYYY_MM_DD_HHMMSS_*.php` | `ServerStatSample.php` |

## Rules
- **Permissions**: server pages gate with `canAccess()` using `SubuserPermission` against `Filament::getTenant()`; client API uses `GetServerRequest` (or a request class with a permission). Never skip this.
- **Translations**: never hardcode text. Add keys to `lang/en/...` first (English is the fallback and must be natural, concise UI English), then `ja`, `ko`, `zh_CN`, `zh_TW`. Missing keys fall back to English per key, so a partial locale file is fine.
- **Icons**: `App\Enums\TablerIcon::*`.
- **Live server data**: `$server->retrieveResources()` (cached 15 s, keys `cpu_absolute`, `memory_bytes`, `state`...). Player counts: `App\Services\GameQuery\GameQueryService` (protocol from `QueryProtocol::forEgg()`; never assume Minecraft).
- **Eggs**: importer accepts PTDL_v1/v2 and PLCN_v1–v3; `EggExporterService::toPterodactyl()` writes PTDL_v2. Keep both directions working.
- **Motion**: add `class="sb-stagger"` to a list container for a stagger reveal and `data-sb-count` to a leaf element holding a number for count-up. Styles live in `resources/css/shoebill-motion.css`; everything must sit inside `prefers-reduced-motion: no-preference`. Keep durations ≤ 320 ms.
- **Client API**: a user-facing server feature should also get a client API endpoint, as Pterodactyl does, so API users can reach it.
- **License**: Shoebill is AGPL-3.0. Code adapted from Pterodactyl keeps an MIT attribution comment. Update `NOTICE.md` when adding third-party code.

## Done means
Pest test for new logic (copy `tests/Integration/Services/Servers/ServerStatsServiceTest.php` or `tests/Integration/Api/Client/Server/ServerStatsControllerTest.php`), pint clean, strings in 5 languages, page checked in the browser (`php artisan serve`).
