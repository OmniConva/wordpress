# OmniConva AI Sales Agent Connector

WordPress plugin that drops the OmniConva AI Sales Agent chat widget onto a WordPress site. An administrator pastes a Site Key on a settings screen, and the plugin loads the widget bundle on the frontend and points it at the OmniConva AI Sales Agent backend.

## Status

Early stage. The settings screen and the frontend widget loading work end to end. Several scaffolded pieces are still placeholders:

- No REST routes are registered yet (`includes/Api.php` hooks `rest_api_init` with an empty callback).
- No database tables are created yet (`Installer::create_tables()` is an empty `dbDelta` stub).
- The shortcode `[omniconva_ai_sales_agent_connector]` renders a placeholder string.
- The template helper functions reference `OMNICONVA_AI_SALES_CONNECTOR_TEMPLATE_PATH`, which is not defined anywhere yet.
- The widget's API base is hardcoded to `http://localhost:4700` in `includes/Assets.php` and must be made configurable before any non-local deployment.

## Requirements

| Item | Version |
| --- | --- |
| WordPress | 4.0 or newer |
| PHP | 7.0 or newer |

## Installation

Clone into the plugins directory and install the Composer autoloader. The plugin will not load without `vendor/autoload.php`.

```bash
cd wp-content/plugins
git clone git@github.com:Biz-Cockpit/wp-plugin.git omniconva-ai-sales-agent-connector
cd omniconva-ai-sales-agent-connector
composer install
```

Then activate **OmniConva AI Sales Agent Connector** from the Plugins screen.

`vendor/` is git-ignored, so `composer install` is required on every fresh checkout and on every deploy target.

## Configuration

Go to **Settings → OmniConva AI Sales Agent**, or use the Settings link on the plugin's row on the Plugins screen. Enter the Site Key issued by the OmniConva AI Sales Agent backend and save.

The value is stored in the `omniconva_ai_sales_connector_settings` option under the `site_key` key. While the Site Key is empty, the widget script is registered but never enqueued, so nothing loads on the frontend.

Once a key is set, the plugin enqueues `assets/js/widget.min.js` in the footer and prints a config object ahead of it:

```js
window.OmniConvaChat = { siteKey: '…', apiBase: 'https://api.omniconva.com' };
```

`apiBase` defaults to `https://api.omniconva.com`. To point the widget at a self-hosted backend or a local development API, define the constant in `wp-config.php` before the plugin loads:

```php
define( 'OMNICONVA_API_BASE', 'http://localhost:4700' );
```

The bundle reads that object, fetches its configuration from `/api/widget/config/`, and talks to `/api/widget/` on the same host. It also tracks browsing history in `localStorage` under `omniconva_browse_history`.

## Architecture

The bootstrap file defines a singleton `Omniconva_ai_sales_connector` class, requires the Composer autoloader, defines the `OMNICONVA_AI_SALES_CONNECTOR_*` path and URL constants, and wires the WordPress lifecycle. Instantiated classes live in a private container exposed through magic getters, so `omniconva_ai_sales_connector()->assets` reaches the `Assets` instance.

Activation runs `Installer::run()`, which stores an install timestamp and the current version. On `plugins_loaded` the plugin instantiates `Admin` for admin requests and `Frontend` for frontend and AJAX requests, then on `init` instantiates `API` and `Assets` unconditionally and loads the text domain.

Classes are PSR-4 autoloaded from `includes/` under the `OmniConva\AiSalesAgentConnector\` namespace, and the directory layout mirrors the namespace one to one.

| Path | Responsibility |
| --- | --- |
| `omniconva-ai-sales-agent-connector.php` | Bootstrap, constants, lifecycle hooks, container |
| `includes/Admin.php` | Admin entry point, instantiates the menu |
| `includes/Admin/Menu.php` | Settings page, Settings API registration, sanitization |
| `includes/Frontend.php` | Frontend entry point, instantiates the shortcode |
| `includes/Frontend/Shortcode.php` | Shortcode handler (placeholder) |
| `includes/Api.php` | REST registration point (no routes yet) |
| `includes/Assets.php` | Script and style registration, widget enqueue |
| `includes/Installer.php` | Activation version tracking and table creation |
| `includes/Traits/Form_Error.php` | Reusable form error accessors |
| `includes/functions.php` | WooCommerce-style template loader helpers |
| `assets/js/widget.min.js` | Prebuilt widget bundle |
| `templates/` | Theme-overridable template files |

To add functionality, create a class under the matching namespace path in `includes/`, hook WordPress from its constructor, and instantiate it either from the main class for top-level request-gated services or from `Admin` or `Frontend` for surface-specific ones.

## Development

Install the dev tooling, then lint.

```bash
composer install
vendor/bin/phpcs          # WordPress Coding Standards + PHP 7.0 compatibility
vendor/bin/phpcbf         # auto-fix what is fixable
npx eslint assets/js      # ESLint, wordpress config
```

PHP uses tabs per `.editorconfig`, though the older files are inconsistently space-indented. JavaScript follows the ESLint `wordpress` config with local overrides in `.eslintrc.json`, and Prettier is configured for tabs, single quotes, and semicolons.

There is no `package.json` or JavaScript build pipeline in this repository. `assets/js/widget.min.js` is committed as a prebuilt artifact, has no source file here, and is excluded from linting through `.eslintignore`. It is built and maintained outside this repo.

PHPUnit is installed as a dev dependency, but there is no test suite yet. Running tests would first require a `phpunit.xml`, a `wp-tests-config.php`, and a `tests/bootstrap.php` scaffold.

## License

GPLv2 or later. Copyright Nurul Amin.
