# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A WordPress plugin (`omniconva-ai-sales-agent-connector`) that integrates a "OmniConva AI Sales Agent" into a SaaS site. It is early-stage scaffolding: most classes are stubs with empty/placeholder method bodies (no REST routes registered, no admin menu registered, no scripts/styles enqueued, no DB tables created yet).

## Commands

Install PHP dev dependencies (PHPCS/WPCS, PHPCompatibility, PHPUnit):
```
composer install
```

Lint PHP (WordPress Coding Standards, PHPCompatibility against PHP 7.0+, per `phpcs.xml`):
```
vendor/bin/phpcs
```
Auto-fix what's fixable:
```
vendor/bin/phpcbf
```

Lint JS (ESLint with the `wordpress` config, see `.eslintrc.json`):
```
npx eslint assets/js
```
Note: there is no `package.json`/build pipeline in this repo. `assets/js/widget.min.js` is a prebuilt/minified asset checked in directly (excluded from linting via `.eslintignore`); there is no JS source file for it in-repo, and it isn't currently registered in `Assets.php`.

PHPUnit is a dev dependency (`vendor/bin/phpunit`) but no test suite/config (`phpunit.xml`) exists yet — a WP test scaffold (`wp-tests-config.php`, `tests/bootstrap.php`) would need to be added before tests can run.

## Architecture

**Bootstrap**: `omniconva-ai-sales-agent-connector.php` defines the singleton `Omniconva_ai_sales_connector` class. It requires `vendor/autoload.php` (Composer PSR-4 autoloading, namespace `OmniConva\AiSalesAgentConnector\` → `includes/`), defines path/URL constants (`OMNICONVA_AI_SALES_CONNECTOR_*`), and wires WordPress lifecycle hooks:
- `register_activation_hook` → `Installer::run()` (version option + `create_tables()` placeholder using `dbDelta`)
- `plugins_loaded` → `init_plugin()`, which conditionally instantiates `Admin` (if `is_admin()`) or `Frontend` (if frontend/ajax request) via the `is_request()` request-type switch, then on `init` instantiates `API` and `Assets` unconditionally and sets up text-domain loading.

**Container pattern**: the main class holds instantiated singletons in `$this->container[...]` and exposes them via `__get`/`__isset` magic methods (e.g. `omniconva_ai_sales_connector()->api`).

**Namespace layout** (mirrors `includes/` directory structure 1:1, PSR-4):
- `Admin` (`includes/Admin.php`) → instantiates `Admin\Menu` (`includes/Admin/Menu.php`), which hooks `admin_menu`/`admin_enqueue_scripts` (currently empty callbacks).
- `Frontend` (`includes/Frontend.php`) → instantiates `Frontend\Shortcode` (`includes/Frontend/Shortcode.php`), which registers the `[omniconva_ai_sales_agent_connector]` shortcode.
- `API` (`includes/Api.php`) → hooks `rest_api_init` (no routes registered yet).
- `Assets` (`includes/Assets.php`) → registers scripts/styles from `get_scripts()`/`get_styles()` arrays (both currently empty); admin vs frontend enqueue hook chosen based on `is_admin()`.
- `Installer` (`includes/Installer.php`) → activation-time version tracking and DB table creation.
- `Traits\Form_Error` (`includes/Traits/Form_Error.php`) → reusable `has_error()`/`get_error()` for classes handling form validation.
- `includes/functions.php` (loaded via Composer's `autoload.files`, not PSR-4) → template-loading helpers (`omniconva_ai_sales_connector_get_template`, `omniconva_ai_sales_connector_get_template_part`, `omniconva_ai_sales_connector_locate_template`) following the WooCommerce-style theme-override pattern (checks theme dir, then falls back to `templates/`). Note: these reference a `OMNICONVA_AI_SALES_CONNECTOR_TEMPLATE_PATH` constant that is not currently defined anywhere — define it before relying on the template loader.

When adding new functionality, follow the existing pattern: create a class under the matching namespace path in `includes/`, hook into WordPress from its constructor, and instantiate it either from `Omniconva_ai_sales_connector::includes()`/`init_classes()` (top-level, request-gated) or from the relevant parent class (`Admin`, `Frontend`) if it's admin- or frontend-specific.

## Coding standards

- PHP: WordPress Coding Standards via PHPCS (`phpcs.xml`), targeting PHP 7.0+ compatibility (`phpcompatibility-wp`). Indentation is tabs (see `.editorconfig`), despite the existing PHP files being inconsistently space-indented.
- JS: ESLint `wordpress` config with local overrides in `.eslintrc.json` (tabs for indentation, permissive on `camelcase`/`no-undef`/etc.).
- Prettier config (`.prettierrc`) uses tabs, single quotes, semicolons.
