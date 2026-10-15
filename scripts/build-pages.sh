#!/usr/bin/env bash
# Assembles the standalone configurator into _site/ for GitHub Pages.
# The configurator loads ../assets/frontend/, so both keep their paths.
set -euo pipefail

cd "$(dirname "$0")/.."

npm run build

rm -rf _site
mkdir -p _site/configurator _site/assets/frontend
cp configurator/index.html _site/configurator/
cp -R configurator/build _site/configurator/
cp -R assets/frontend/js assets/frontend/css _site/assets/frontend/

cat > _site/index.html <<'HTML'
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>CookieFox Configurator</title>
	<meta http-equiv="refresh" content="0; url=configurator/">
	<script>location.replace("configurator/" + location.hash);</script>
</head>
<body>
	<a href="configurator/">Open the CookieFox configurator</a>
</body>
</html>
HTML

touch _site/.nojekyll
echo "Built _site/"
