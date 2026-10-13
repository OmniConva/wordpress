#!/usr/bin/env bash
#
# Builds the WordPress.org release of the plugin into dist/:
#
#   dist/omniconva-ai-sales-agent-connector/            what goes in SVN trunk/
#   dist/omniconva-ai-sales-agent-connector-<ver>.zip   the same, zipped
#
# The git tree alone isn't installable: the plugin requires vendor/autoload.php
# (Composer, gitignored) and the readme promises the widget's TypeScript source
# under src/widget/, which lives in the web-chat-widget repo. This puts both in,
# leaves the dev files out, and checks that assets/js/widget.min.js really is
# the build of the source it ships.
#
# Usage:
#   bin/build-release.sh [--widget-repo PATH] [--widget-ref REF] [--skip-verify]
#
#   --widget-repo  web-chat-widget checkout (default: ../web-chat-widget, its
#                  place in the OmniConva workspace). Its node_modules is used
#                  to rebuild the bundle for the check.
#   --widget-ref   commit the bundle was built from (default: origin/main).
#   --skip-verify  don't rebuild the widget to compare it with the bundle.

set -euo pipefail

SLUG="omniconva-ai-sales-agent-connector"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WIDGET_REPO="$ROOT/../web-chat-widget"
WIDGET_REF="origin/main"
VERIFY=1

while [ $# -gt 0 ]; do
	case "$1" in
		--widget-repo) WIDGET_REPO="$2"; shift 2 ;;
		--widget-ref) WIDGET_REF="$2"; shift 2 ;;
		--skip-verify) VERIFY=0; shift ;;
		*) echo "Unknown option: $1" >&2; exit 1 ;;
	esac
done

fail() { echo "error: $*" >&2; exit 1; }

command -v composer >/dev/null || fail "composer is not installed"
command -v zip >/dev/null || fail "zip is not installed"
git -C "$WIDGET_REPO" rev-parse --is-inside-work-tree >/dev/null 2>&1 || fail "no web-chat-widget checkout at $WIDGET_REPO (pass --widget-repo)"

if [ -n "$(git -C "$ROOT" status --porcelain)" ]; then
	echo "warning: uncommitted changes are NOT included; the build uses HEAD" >&2
fi

VERSION="$(sed -n 's/^ \* Version: *//p' "$ROOT/$SLUG.php" | tr -d '[:space:]')"
STABLE="$(sed -n 's/^Stable tag: *//p' "$ROOT/readme.txt" | tr -d '[:space:]')"
CONST="$(sed -n "s/.*const VERSION = '\([^']*\)'.*/\1/p" "$ROOT/$SLUG.php")"
[ -n "$VERSION" ] || fail "no Version: header in $SLUG.php"
[ "$VERSION" = "$STABLE" ] || fail "Version $VERSION != readme Stable tag $STABLE"
[ "$VERSION" = "$CONST" ] || fail "Version $VERSION != VERSION constant $CONST"

WIDGET_SHA="$(git -C "$WIDGET_REPO" rev-parse --verify "$WIDGET_REF^{commit}")" || fail "unknown widget ref $WIDGET_REF"

DIST="$ROOT/dist"
OUT="$DIST/$SLUG"
rm -rf "$DIST"
mkdir -p "$OUT"

echo "Plugin $VERSION from $(git -C "$ROOT" rev-parse --short HEAD), widget source from $(git -C "$WIDGET_REPO" rev-parse --short "$WIDGET_SHA")"

# 1. The plugin, as committed, minus what only matters for development.
git -C "$ROOT" archive HEAD | tar -x -C "$OUT"
rm -rf "$OUT/bin" "$OUT/CLAUDE.md" "$OUT/README.md" "$OUT/composer.lock" \
	"$OUT/phpcs.xml" "$OUT"/.[!.]*

# 2. Composer's autoloader, without dev dependencies.
(cd "$OUT" && composer install --no-dev --optimize-autoloader --no-interaction --quiet)
rm -f "$OUT/composer.lock"

# 3. The widget's source, which assets/js/widget.min.js is built from.
mkdir -p "$OUT/src/widget"
git -C "$WIDGET_REPO" archive "$WIDGET_SHA" | tar -x -C "$OUT/src/widget"
rm -rf "$OUT/src/widget/AGENTS.md" "$OUT/src/widget/CLAUDE.md" \
	"$OUT/src/widget/pnpm-lock.yaml" "$OUT/src/widget"/.[!.]*

# 4. The bundle must be the build of that source.
if [ "$VERIFY" = 1 ]; then
	[ -d "$WIDGET_REPO/node_modules" ] || fail "run pnpm install in $WIDGET_REPO first (or pass --skip-verify)"
	TMP="$(mktemp -d)"
	trap 'rm -rf "$TMP"' EXIT
	git -C "$WIDGET_REPO" archive "$WIDGET_SHA" | tar -x -C "$TMP"
	ln -s "$(cd "$WIDGET_REPO" && pwd)/node_modules" "$TMP/node_modules"
	(cd "$TMP" && node esbuild.config.mjs >/dev/null)
	if ! cmp -s "$TMP/dist/widget.min.js" "$OUT/assets/js/widget.min.js"; then
		fail "assets/js/widget.min.js is not the build of web-chat-widget $(git -C "$WIDGET_REPO" rev-parse --short "$WIDGET_SHA"); copy its dist/widget.min.js in and commit it, or pass the right --widget-ref"
	fi
	echo "Widget bundle matches its source."
fi

# 5. Sanity checks WordPress.org would also catch.
while IFS= read -r -d '' file; do
	php -l "$file" >/dev/null || fail "PHP syntax error in ${file#"$OUT"/}"
done < <(find "$OUT" -name '*.php' -print0)
HIDDEN="$(find "$OUT" -mindepth 1 -name '.*' -print)"
[ -z "$HIDDEN" ] || fail "hidden files in the package: $HIDDEN"
[ -f "$OUT/vendor/autoload.php" ] || fail "vendor/autoload.php missing"

(cd "$DIST" && zip -qr "$SLUG-$VERSION.zip" "$SLUG")

echo "Built $DIST/$SLUG-$VERSION.zip ($(find "$OUT" -type f | wc -l | tr -d ' ') files)"
