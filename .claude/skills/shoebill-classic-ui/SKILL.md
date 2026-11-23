---
name: shoebill-classic-ui
description: Add or change a screen in Shoebill's Classic UI, the Pterodactyl React client (React 16, TypeScript, twin.macro, easy-peasy, SWR) served at /classic from panel/classic. Use when a feature must appear in the Classic UI or when fixing it. Backend work goes through shoebill-pelican-ui.
---

# Shoebill Classic UI (Pterodactyl UI)

Source: `panel/classic/resources/scripts` (from Pterodactyl, MIT — keep its license file `panel/classic/LICENSE.md`). Built with webpack into `panel/public/classic-assets/` and served by `ClassicUiController` at `/classic/*`, using the logged-in session of the main panel.

Build: `cd panel/classic && yarn install && yarn tsc && NODE_OPTIONS=--openssl-legacy-provider yarn build:production`.

## Adding a server screen
1. Backend first: a client API endpoint in `panel/routes/api-client.php` (see shoebill-pelican-ui). The Classic UI only talks to `/api/client/...`.
2. API call: `api/server/getX.ts` using `http` from `@/api/http`; return `data.attributes`. Copy `api/server/getServerStats.ts`.
3. Screen: `components/server/x/XContainer.tsx` wrapped in `ServerContentBlock`, boxes with `TitledGreyBox`, data via `useSWR`, uuid from `ServerContext.useStoreState((s) => s.server.data!.uuid)`. Copy `components/server/status/StatusContainer.tsx`.
4. Tab: add an entry to `server` in `routers/routes.ts` with a `permission` (`null` = everyone with access, else e.g. `'file.*'`).

## Rules
- Router basename is `/classic` (`components/history.ts`). Use react-router `Link`/`NavLink`; any raw `window.location`/`history.replaceState` path must start with `/classic`.
- Auth, 2FA and logout belong to the main panel: link to `/profile`, POST `/logout`, never re-add `/auth/*` calls.
- Pelican API differences already handled: SSH key delete is `DELETE /account/ssh-keys/{fingerprint}`; there is no `/account/two-factor`.
- Styling: tailwind via `tw\`...\`` (twin.macro), neutral-* palette, existing elements in `components/elements`.
- Strings in this UI are English (Pterodactyl never localized them); activity log text comes from the panel's `lang/*/activity.php` through `/locales/locale.json`.
- Keep the "Classic UI from Pterodactyl®" credit in `components/elements/PageContentBlock.tsx`; don't use Pterodactyl logos.

## Done means
`yarn tsc` clean, production build succeeds, screen checked at `http://localhost:8000/classic/server/<id>/<path>` with no console errors besides Wings connection errors on a dev box.
