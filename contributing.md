# Contributing to Shoebill

Thanks for helping! Shoebill merges [Pelican](https://github.com/pelican-dev/panel) and
[Pterodactyl](https://github.com/pterodactyl/panel); changes should keep every feature of both.

## Getting started
1. Fork the repository and clone your fork.
2. Set up a local panel as described in [README.md → Local development](README.md#local-development).
3. Create a branch: `git checkout -b feature/short-name`.

## Rules
- **Code style:** follow [code-standards.md](code-standards.md); run `vendor/bin/pint` before committing.
- **Tests:** add or update Pest tests for new logic and run `php -d memory_limit=2G vendor/bin/pest`.
- **Translations:** add UI strings to `lang/en` first, then `ja`, `ko`, `zh_CN` and `zh_TW`. Other languages fall back to English.
- **Features:** new Shoebill features get a case in `App\Enums\ShoebillFeature` and check `FeatureService::enabled()`, so admins can switch them off.
- **Wings:** anything that talks to Wings must work with both Pelican Wings and Pterodactyl Wings (see `WingsType`).
- **Licenses:** Shoebill is AGPL-3.0. Code copied from Pterodactyl (MIT) or elsewhere keeps its copyright notice; mention it in `NOTICE.md`.

## Pull requests
Open a pull request against `main` and fill in the template. Mark it as a draft while you are still working on it.
By contributing you agree that your contribution is licensed under the AGPL-3.0.

## Contact
- Questions and ideas: [GitHub Discussions](https://github.com/yamashi8040-ai/Shoebill/discussions)
- Bugs: [GitHub Issues](https://github.com/yamashi8040-ai/Shoebill/issues)
- Security: see [security.md](security.md)
- Email: <concat@windspace.dev>