# Shoebill — notices and credits

Shoebill is a game server management panel that combines two open-source projects.
It is not affiliated with or endorsed by either project.

| Part | Source | Copyright | License |
|------|--------|-----------|---------|
| Backend, admin and server panels | [Pelican Panel](https://github.com/pelican-dev/panel) (commit `45bfb80`) | © Pelican contributors | AGPL-3.0 (`license`) |
| Features and design taken from Pterodactyl | [Pterodactyl Panel](https://github.com/pterodactyl/panel) (1.x `60a1001`, 2.0 `7f676fc`) | © Dane Everitt and contributors | MIT (`licenses/PTERODACTYL-MIT.md`) |
| Shoebill changes | this repository | © Shoebill contributors | AGPL-3.0 |

Because Pelican is AGPL-3.0, Shoebill as a whole is distributed under the **GNU Affero General Public License v3.0**.
The MIT notice for code taken from Pterodactyl is kept, as that license requires.

**If you run a modified Shoebill for other people over a network, AGPL-3.0 section 13 requires you to offer them
the source code of your version.** Set `SHOEBILL_SOURCE_URL` in `.env` to where your source is published;
it is linked from every page footer and from `/credits`.

"Pterodactyl" is a registered trademark of its owner and "Pelican" is the name of the Pelican project.
Shoebill uses these names only to credit where its code comes from and does not use their logos.

Third-party dependencies keep their own licenses (see `composer.lock`, `yarn.lock`).
