#!/usr/bin/env bash
# Ship a WordPress.org release from this git working copy.
#
# Manual first: set the version in cookiefox.php, COOKIEFOX_VERSION,
# readme.txt (Stable tag + changelog), and package.json.
#
#   npm run release:wporg          # build, sync trunk, commit, tag
#   npm run release:wporg:dry      # same, without svn commit
#   ./scripts/release-wporg.sh --yes
#
# Override paths with COOKIEFOX_SVN, COOKIEFOX_PHP, COOKIEFOX_COMPOSER.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SVN_ROOT="${COOKIEFOX_SVN:-$HOME/Development/svn/cookiefox/cookiefox}"
TRUNK="$SVN_ROOT/trunk"
DRY_RUN=0
ASSUME_YES=0

usage() {
	sed -n '2,12p' "$0" | sed 's/^# \{0,1\}//'
	exit "${1:-0}"
}

while [[ $# -gt 0 ]]; do
	case "$1" in
		--dry-run) DRY_RUN=1 ;;
		--yes|-y) ASSUME_YES=1 ;;
		--help|-h) usage 0 ;;
		*) echo "Unknown argument: $1" >&2; usage 1 ;;
	esac
	shift
done

header_version() {
	sed -n 's/^ \* Version:[[:space:]]*//p' "$ROOT/cookiefox.php" | head -n 1
}

stable_tag() {
	sed -n 's/^Stable tag:[[:space:]]*//p' "$ROOT/readme.txt" | head -n 1
}

php_constant_version() {
	sed -n "s/.*define('COOKIEFOX_VERSION', '\\([^']*\\)').*/\\1/p" "$ROOT/includes/class-main.php" | head -n 1
}

package_version() {
	sed -n 's/^[[:space:]]*"version":[[:space:]]*"\([^"]*\)".*/\1/p' "$ROOT/package.json" | head -n 1
}

find_php() {
	if [[ -n "${COOKIEFOX_PHP:-}" ]]; then
		echo "$COOKIEFOX_PHP"
		return
	fi
	local candidate
	for candidate in \
		/Applications/MAMP/bin/php/php8.2.30/bin/php \
		/Applications/MAMP/bin/php/php8.3.30/bin/php \
		/Applications/MAMP/bin/php/php8.4.17/bin/php \
		/Applications/MAMP/bin/php/php8.5.2/bin/php
	do
		if [[ -x "$candidate" ]]; then
			echo "$candidate"
			return
		fi
	done
	command -v php
}

find_composer() {
	if [[ -n "${COOKIEFOX_COMPOSER:-}" ]]; then
		echo "$COOKIEFOX_COMPOSER"
		return
	fi
	if [[ -f /Applications/MAMP/bin/php/composer ]]; then
		echo /Applications/MAMP/bin/php/composer
		return
	fi
	command -v composer
}

status_path() {
	printf '%s\n' "$1" | awk '{ $1=""; sub(/^ +/, ""); print }'
}

is_scheduled_add() {
	svn info "$1" 2>/dev/null | grep -q 'Schedule: add'
}

# Drop missing files from SVN. Uncommitted adds are reverted; versioned
# files are svn rm'd. Nested paths are skipped when a parent is listed.
sync_svn_deletes() {
	local missing tops path
	missing="$(mktemp)"
	tops="$(mktemp)"

	svn status trunk | awk '/^[!D]/ { $1=""; sub(/^ +/, ""); print }' > "$missing"

	sort "$missing" | awk '
		{
			skip = 0
			for (i = 1; i <= n; i++) {
				if (index($0, kept[i] "/") == 1) {
					skip = 1
					break
				}
			}
			if (!skip) {
				n++
				kept[n] = $0
				print
			}
		}
	' > "$tops"

	while IFS= read -r path || [[ -n "${path:-}" ]]; do
		[[ -z "$path" ]] && continue
		if is_scheduled_add "$path"; then
			svn revert --depth=infinity "$path" >/dev/null
		else
			svn rm --force "$path" >/dev/null
		fi
	done < "$tops"

	rm -f "$missing" "$tops"
}

log() {
	printf '==> %s\n' "$*"
}

VERSION="$(header_version)"
README_VERSION="$(stable_tag)"
CONST_VERSION="$(php_constant_version)"
PKG_VERSION="$(package_version)"

if [[ -z "$VERSION" ]]; then
	echo "Could not read Version from cookiefox.php" >&2
	exit 1
fi

if [[ "$VERSION" != "$README_VERSION" ]]; then
	echo "Version mismatch: cookiefox.php is $VERSION, readme.txt Stable tag is $README_VERSION" >&2
	exit 1
fi

if [[ "$VERSION" != "$CONST_VERSION" ]]; then
	echo "Version mismatch: cookiefox.php is $VERSION, COOKIEFOX_VERSION is $CONST_VERSION" >&2
	exit 1
fi

if [[ "$VERSION" != "$PKG_VERSION" ]]; then
	echo "Version mismatch: cookiefox.php is $VERSION, package.json is $PKG_VERSION" >&2
	exit 1
fi

if ! command -v svn >/dev/null 2>&1; then
	echo "svn is not on PATH. Install it (xcode-select --install or brew install svn) and retry." >&2
	exit 1
fi

if [[ ! -d "$SVN_ROOT/.svn" ]]; then
	echo "SVN working copy not found at $SVN_ROOT" >&2
	echo "Set COOKIEFOX_SVN if the checkout lives somewhere else." >&2
	exit 1
fi

PHP_BIN="$(find_php)"
COMPOSER_BIN="$(find_composer)"

if [[ -z "$PHP_BIN" || ! -x "$PHP_BIN" ]]; then
	echo "PHP binary not found. Set COOKIEFOX_PHP." >&2
	exit 1
fi

if [[ -z "$COMPOSER_BIN" ]]; then
	echo "Composer not found. Set COOKIEFOX_COMPOSER." >&2
	exit 1
fi

log "Releasing CookieFox $VERSION"
log "Plugin: $ROOT"
log "SVN:    $SVN_ROOT"
log "PHP:    $PHP_BIN"

cd "$ROOT"

log "Building frontend"
npm run build

log "Installing PHP dependencies (no-dev)"
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --prefer-dist --no-interaction

if [[ ! -f "$ROOT/assets/frontend/js/main.js" ]]; then
	echo "Build did not produce assets/frontend/js/main.js" >&2
	exit 1
fi

if [[ ! -f "$ROOT/vendor/autoload.php" ]]; then
	echo "Composer install did not produce vendor/autoload.php" >&2
	exit 1
fi

log "Updating SVN working copy"
svn update "$SVN_ROOT"

if [[ -d "$SVN_ROOT/tags/$VERSION" ]]; then
	echo "Tag tags/$VERSION already exists in the SVN checkout." >&2
	exit 1
fi

log "Syncing trunk"
mkdir -p "$TRUNK"
rsync -a --delete --delete-excluded \
	--exclude='.DS_Store' \
	--exclude='.git/' \
	--exclude='.gitignore' \
	--exclude='.nova/' \
	--exclude='.svn/' \
	--exclude='.agents/' \
	--exclude='.beads/' \
	--exclude='.claude/' \
	--exclude='.codex/' \
	--exclude='node_modules/' \
	--exclude='src/' \
	--exclude='scripts/' \
	--exclude='release/' \
	--exclude='package.json' \
	--exclude='package-lock.json' \
	--exclude='rollup.config.js' \
	--exclude='composer.json' \
	--exclude='composer.lock' \
	--exclude='playwright.config.js' \
	--exclude='.jshintrc' \
	--exclude='.phpcs.xml.dist' \
	--exclude='.wp-env-tests.json' \
	--exclude='AGENTS.md' \
	--exclude='CLAUDE.md' \
	--exclude='Claude.md' \
	--exclude='AUDIT.md' \
	--exclude='README.md' \
	--exclude='vendor/bin/' \
	--exclude='vendor/cmb2/cmb2/tools/' \
	"$ROOT/" "$TRUNK/"

cd "$SVN_ROOT"

log "Staging SVN changes"
svn cleanup "$SVN_ROOT" >/dev/null
svn add --force --parents --depth infinity trunk >/dev/null
sync_svn_deletes
svn status trunk

if [[ "$DRY_RUN" -eq 1 ]]; then
	log "Dry run: skipped svn commit and tag"
	exit 0
fi

if [[ "$ASSUME_YES" -ne 1 ]]; then
	printf 'Commit trunk and tag %s to WordPress.org? [y/N] ' "$VERSION"
	read -r answer
	case "$answer" in
		y|Y|yes|YES) ;;
		*) echo "Aborted."; exit 1 ;;
	esac
fi

log "Committing trunk"
svn commit trunk -m "$VERSION"

log "Tagging $VERSION"
svn copy "trunk" "tags/$VERSION"
svn commit "tags/$VERSION" -m "tagging version $VERSION"

log "Released $VERSION"
