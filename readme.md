# Shoebill

A game server panel that merges **Pelican** (backend, admin panel, plugins, 40+ languages) with
**Pterodactyl** features and design into one interface, roughly 60:40. Eggs in both formats (`PTDL_v1`, `PTDL_v2`,
`PLCN_v1`–`v3`) import directly.

## What Shoebill adds
- **Status page** for every server: today / 7-day / 30-day / all-time averages of CPU and memory, plus player
  counts and peaks for games that answer a query (Minecraft Java & Bedrock, Steam/Source A2S games). Other games
  show CPU and memory only. Pick the protocol per egg in *Admin → Eggs → Player query*, or leave it on auto.

Run the scheduler (`php artisan schedule:work` or the usual cron) so samples are recorded every minute.

## Credits and license
Shoebill is built on [Pelican Panel](https://github.com/pelican-dev/panel) (AGPL-3.0) and
[Pterodactyl Panel](https://github.com/pterodactyl/panel) (MIT). See [NOTICE.md](NOTICE.md).
Shoebill is licensed under the [GNU AGPL-3.0](license).
