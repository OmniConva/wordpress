# OmniConva AI Sales Agent Connector

WordPress plugin that drops the OmniConva AI Sales Agent chat widget onto a WordPress site. An administrator pastes a Site Key on a settings screen, and the plugin loads the widget bundle on the frontend and points it at the OmniConva AI Sales Agent backend.

## Status

Version 1.0.0 is approved on WordPress.org as [`omniconva-ai-sales-agent-connector`](https://wordpress.org/plugins/omniconva-ai-sales-agent-connector/). The settings screen, the frontend widget and signed customer identity work end to end. `includes/Api.php` hooks `rest_api_init` with no routes yet.

## Requirements

| Item | Version |
| --- | --- |
| WordPress | 4.5 or newer (tested up to 7.1) |
| PHP | 7.0 or newer |

## Installation (development)

Clone into the plugins directory and install the Composer autoloader. The plugin will not load without `vendor/autoload.php`.

```bash
cd wp-content/plugins
git clone git@github.com:OmniConva/wordpress.git omniconva-ai-sales-agent-connector
cd omniconva-ai-sales-agent-connector
composer install
```

Then activate **OmniConva AI Sales Agent Connector** from the Plugins screen.

`vendor/` is git-ignored, so `composer install` is required on every fresh checkout. Never ship a copy of the git tree as a release: build it with `bin/build-release.sh` (see **Releasing**).

## Configuration

### Connect button

**Connect to OmniConva** fills the keys in without copying them. It is an authorization-code grant with PKCE (`includes/Admin/Connect.php`, API side in `api/src/wordpress-connect/`):

1. `admin-post.php?action=omniconva_connect_start` (nonce, `manage_options`) makes a verifier and state, keeps them in a per-user transient for 30 minutes, `POST`s `{siteUrl, returnUrl, state, codeChallenge, siteName}` to `{apiBase}/wordpress/connect/requests`, and redirects to the `connectUrl` it gets back (the dashboard's `/connect/wordpress?req=…`).
2. The admin signs in or signs up there and a store **owner** approves. The dashboard redirects to `admin-post.php?action=omniconva_connect_callback&code=…&state=…` (or `error=access_denied`).
3. The callback checks the state, then `POST`s `{code, codeVerifier}` to `{apiBase}/wordpress/connect/exchange` and saves the returned `siteKey`, `identitySecret` and `storeName`. The exchange also adds the site's hostname to the store's allowed widget domains, so no domain has to be typed in the dashboard.
4. WooCommerce, optional. With WooCommerce active and `manage_woocommerce`, the connect forms show **Also sync WooCommerce products and orders** (ticked by default). Ticked, step 1 also sends `{woocommerce: true, wooReturnUrl}` (the settings page with `omniconva_notice=woo_return`), and the choice is kept in the transient. The exchange then starts the API's ordinary WooCommerce connect for the store and returns `woocommerce: {authorizeUrl}`, and the callback redirects there: `{site}/wc-auth/v1/authorize`, WooCommerce's own approval screen. It only redirects if the admin asked at step 1, and only to that path on this site's host. WooCommerce posts the key straight to the API's `/product-sources/woocommerce/callback`, so the plugin never sees it, then returns with `success=1` or `0`, which picks the notice. The API skips a store that already has a working connection: `{skipped: 'already_connected'}` for this site, `'connected_elsewhere'` for another site or platform, and `'unavailable'` when it can't start, for example on a private address. Each of these still connects the widget and shows its own notice.

Disconnect (`omniconva_disconnect`) only clears the local keys. A WooCommerce sync stays until it is disconnected in the OmniConva dashboard. The result comes back as `?omniconva_notice=` on the settings page.

### Chat widget settings

Once connected, the settings page also edits the widget's look: agent name, status text, avatar shape (`circle`/`square`), header color, text color and greeting (`includes/Admin/Appearance.php`, API side in `api/src/wordpress-connect/wordpress-widget-settings.*`). Nothing is stored in WordPress. These are the store's `store_widget_settings` in OmniConva, the same values the dashboard's **Custom Chat** tab and the mobile app edit, and what the widget fetches from `/api/widget/config/`.

- Opening the page `GET`s `{apiBase}/wordpress/widget-settings`. Saving (`admin-post.php?action=omniconva_save_appearance`, nonce, `manage_options`) `PATCH`es the same URL with `{agentName, statusText, avatarShape, primaryColor, primaryTextColor, greetingMessage}`. An empty `statusText` clears it, and the widget then shows "We reply right away".
- Each request carries `X-OmniConva-Site-Key`, `X-OmniConva-Timestamp` (Unix seconds) and `X-OmniConva-Signature`: `hash_hmac('sha256', "omniconva-wp-v1\n{timestamp}\n{METHOD}\n/wordpress/widget-settings\n{body}", identity_secret)` over the exact body bytes (empty for the `GET`). The API allows 5 minutes of clock skew and accepts each write signature once.
- The section needs both keys. Without an Identity Secret it says to Reconnect. A `401` (usually a rotated secret) says the same.
- Embed domains and the pre-chat form can't be changed this way. They stay dashboard-only.

### Inbox

**OmniConva AI Sales Agent → Inbox** (`includes/Admin/Inbox.php`) frames the dashboard's chat inbox: `{dashboard}/embed/wordpress?siteKey=…`. The dashboard defaults to `https://dashboard.omniconva.com`; override it with `define( 'OMNICONVA_DASHBOARD_URL', 'http://localhost:3000' );` in `wp-config.php`. The frame's URL carries only the public Site Key, and WordPress signs nobody in: a WordPress admin account alone never opens the store's conversations.

- **Who sees it.** The **Inbox access** setting (`inbox_access`): `administrators` (the default, `manage_options`) or, with WooCommerce active, `shop_managers` (`manage_woocommerce`, which administrators have too). A shop manager then gets the top-level menu with only Inbox under it; the settings page stays `manage_options`. A saved value the site no longer offers, such as `shop_managers` after WooCommerce is deactivated, falls back to administrators only.
- **Framing.** The dashboard sends `frame-ancestors` with the store's own site domains (`web-dashboard/proxy.ts`, from the API's `GET /wordpress/embed/frame-ancestors?siteKey=`), the same domains the widget is allowed on. wp-admin must be served from one of them, over HTTPS in production. Any other site gets `frame-ancestors 'none'`.
- **Signing in.** The dashboard's own session cookies aren't sent to a cross-site frame, so the frame starts signed out and shows **Sign in to OmniConva**. That opens `{dashboard}/connect/wordpress/sign-in?siteKey=…` in a popup, a first-party window where the usual sign-in works. Once signed in, the popup asks `POST /wordpress/embed/handoff` for a 60-second single-use code (members of the Site Key's store only), posts it to the frame with `postMessage` targeted at the dashboard's own origin, and closes. The frame redeems it at `POST /wordpress/embed/session` for an access cookie partitioned under this site (`SameSite=None; Partitioned`).
- **Lifetime.** That cookie has no refresh cookie, so it ends after an hour and the frame shows Sign in again. The popup is still signed in by then, so it takes one click.
- **Wrong account.** If the account signed in isn't a member of the store, the frame and the popup say so, and the popup offers **Use another account**.
- **Local development.** Partitioned cookies are only set when the API runs in production mode. In development the embedded cookie falls back to `SameSite=Lax`, which a cross-site frame doesn't send, so the signed-in inbox only loads when wp-admin and the dashboard are same-site.

### Manual setup

The widget only loads on the domain saved for the store in the OmniConva dashboard (**Settings → Custom Chat → Site domain**). The API refuses its CORS requests from any other origin, so a site with the right Site Key but no domain set shows nothing. `www.` is folded; other subdomains have to match.

Then go to **OmniConva AI Sales Agent**, or use the Settings link on the plugin's row on the Plugins screen:

- **Site Key** (`site_key`): the store's widget key from the dashboard. While it is empty, nothing loads on the frontend.
- **Identity Secret** (`identity_secret`): optional. When set, a logged-in user is passed to the widget as `customer` (ID, display name, email, avatar, WooCommerce billing phone and address) with `hash = hash_hmac('sha256', user ID, secret)`. The secret itself never reaches the browser.
- **Inbox access** (`inbox_access`): who sees the Inbox screen. See **Inbox**.
- **Credit link** (`show_powered_by`): optional, off by default.
- **Store name** (`store_name`): set by the Connect button for the "Connected to …" line; cleared when the Site Key changes by hand.

All of these live in the `omniconva_ai_sales_connector_settings` option. Once a key is set, the plugin enqueues `assets/js/widget.min.js` in the footer and prints a config object ahead of it:

```js
window.OmniConvaChat = { siteKey: '…', apiBase: 'https://api.omniconva.com', showPoweredBy: false };
```

`apiBase` defaults to `https://api.omniconva.com`. To point the widget at a self-hosted backend or a local development API, define the constant in `wp-config.php` before the plugin loads:

```php
define( 'OMNICONVA_API_BASE', 'http://localhost:4700' );
```

The bundle reads that object, fetches its configuration from `/api/widget/config/`, and talks to `/api/widget/` on the same host. It also tracks browsing history in `localStorage` under `omniconva_browse_history`. Deleting the plugin removes its options (`uninstall.php`).

## Architecture

The bootstrap file defines a singleton `Omniconva_ai_sales_connector` class, requires the Composer autoloader, defines the `OMNICONVA_AI_SALES_CONNECTOR_*` path and URL constants, and wires the WordPress lifecycle. Instantiated classes live in a private container exposed through magic getters, so `omniconva_ai_sales_connector()->assets` reaches the `Assets` instance.

Activation runs `Installer::run()`, which stores an install timestamp and the current version. On `plugins_loaded` the plugin instantiates `Admin` for admin requests, then on `init` instantiates `Api` and `Assets` unconditionally.

Classes are PSR-4 autoloaded from `includes/` under the `OmniConva\AiSalesAgentConnector\` namespace, and the directory layout mirrors the namespace one to one.

| Path | Responsibility |
| --- | --- |
| `omniconva-ai-sales-agent-connector.php` | Bootstrap, constants, lifecycle hooks, container |
| `includes/Admin.php` | Admin entry point, instantiates the menu and the connect handlers |
| `includes/Admin/Menu.php` | Settings page, Settings API registration, sanitization, Settings link |
| `includes/Admin/Connect.php` | Connect / Disconnect buttons: the PKCE handshake with the OmniConva API |
| `includes/Admin/Appearance.php` | Chat widget settings section, read from and saved to the OmniConva API with signed requests |
| `includes/Admin/Inbox.php` | Inbox submenu: the dashboard's chat inbox in an iframe |
| `includes/Api.php` | REST registration point (no routes yet) |
| `includes/Assets.php` | Widget enqueue, config object, signed customer identity, API and dashboard base URLs |
| `includes/Installer.php` | Activation version tracking |
| `uninstall.php` | Removes the plugin's options when it is deleted |
| `assets/js/widget.min.js` | The widget bundle, built from the `web-chat-widget` repo |
| `bin/build-release.sh` | Builds the WordPress.org release (see **Releasing**) |

## Development

Install the dev tooling, then lint.

```bash
composer install
vendor/bin/phpcs          # WordPress Coding Standards + PHP 7.0 compatibility
vendor/bin/phpcbf         # auto-fix what is fixable
```

PHP uses tabs per `.editorconfig`, though the older files are inconsistently space-indented.

`assets/js/widget.min.js` is not edited here. Its source is the [`web-chat-widget`](https://github.com/OmniConva/web-chat-widget) repo: build it there (`pnpm build`), copy `dist/widget.min.js` to `assets/js/`, and commit. The release build checks that the committed bundle is exactly the build of the widget source it ships.

PHPUnit is installed as a dev dependency, but there is no test suite yet.

## Releasing

WordPress.org serves the plugin from SVN (`https://plugins.svn.wordpress.org/omniconva-ai-sales-agent-connector`), not from this repo.

1. Bump the version in three places: the `Version:` header and `VERSION` constant in `omniconva-ai-sales-agent-connector.php`, and `Stable tag:` in `readme.txt`. Add a changelog entry. Commit.
2. Build:

   ```bash
   bin/build-release.sh                     # widget source from web-chat-widget origin/main
   bin/build-release.sh --widget-ref <sha>  # if the bundle came from another commit
   ```

   It writes `dist/omniconva-ai-sales-agent-connector/` and a zip. It refuses to build if the three versions disagree, if `widget.min.js` isn't the build of the widget source, or if the package has PHP syntax errors or hidden files. The package adds `vendor/` (no dev dependencies) and the widget's TypeScript source under `src/widget/`, and leaves out dev files.
3. Publish with SVN (needs your WordPress.org account):

   ```bash
   svn co https://plugins.svn.wordpress.org/omniconva-ai-sales-agent-connector svn
   rsync -a --delete dist/omniconva-ai-sales-agent-connector/ svn/trunk/
   cd svn && svn add --force trunk && svn status | awk '/^!/ {print $2}' | xargs -r svn rm
   svn cp trunk tags/<version>
   svn ci -m "Release <version>"
   ```

   Plugin page images (icon, banner, screenshots) go in SVN `assets/`, not in the plugin.

## License

GPLv2 or later. Copyright Nurul Amin.
