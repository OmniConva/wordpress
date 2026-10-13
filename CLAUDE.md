# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

The WordPress.org plugin `omniconva-ai-sales-agent-connector`: a settings screen (Site Key, optional Identity Secret, opt-in credit link) and a footer script that loads the OmniConva chat widget against `https://api.omniconva.com` (overridable with `OMNICONVA_API_BASE` in `wp-config.php`). README.md describes the behaviour in full; keep it accurate when you change it.

## Commands

```
composer install          # dev tooling; also required for the plugin to load (vendor/autoload.php)
vendor/bin/phpcs          # WordPress Coding Standards + PHP 7.0 compatibility (phpcs.xml)
vendor/bin/phpcbf         # auto-fix
bin/build-release.sh      # the WordPress.org package, into dist/
```

There is no JS build here. `assets/js/widget.min.js` is built in the `web-chat-widget` repo and copied in; never edit it by hand. `bin/build-release.sh` fails unless it is byte-identical to the build of the widget source it ships.

## Architecture

- `omniconva-ai-sales-agent-connector.php`: singleton bootstrap. Requires `vendor/autoload.php` (PSR-4, `OmniConva\AiSalesAgentConnector\` → `includes/`), defines `OMNICONVA_AI_SALES_CONNECTOR_*` constants, runs `Installer` on activation, instantiates `Admin` on admin requests (`plugins_loaded`) and `Api` + `Assets` on `init`. Instances live in `$this->container`, exposed via `__get`.
- `includes/Admin/Menu.php`: the Settings → OmniConva AI Sales Agent page (Settings API, option `omniconva_ai_sales_connector_settings`, sanitization, plugin-row Settings link).
- `includes/Assets.php`: enqueues the widget only on the frontend and only with a Site Key, prints `window.OmniConvaChat`, and signs the logged-in user (`hash_hmac('sha256', user ID, identity_secret)`).
- `includes/Installer.php`: version/installed options. `uninstall.php` deletes all options.

## Releasing

Versions must match in the plugin header, the `VERSION` constant and the readme's `Stable tag`. Build with `bin/build-release.sh`, then publish through SVN (README.md → Releasing). The SVN commit needs the maintainer's WordPress.org credentials, so leave that step to them.

## Coding standards

- PHP: WordPress Coding Standards via PHPCS, PHP 7.0+. Tabs (see `.editorconfig`); older files are still space-indented.
- Anything added to `readme.txt` must stay accurate to what the plugin sends to OmniConva (the External services section is a WordPress.org requirement).
