#!/usr/bin/env bash
# Render the WordPress.org visuals from the Imagen source and editable HTML.
set -euo pipefail
cd "$(dirname "$0")/.."

preview="$(mktemp --suffix=.png)"
trap 'rm -f "$preview"' EXIT

convert dev/assets-src/icon-generated.png -resize 256x256 .wordpress-org/icon-256x256.png
convert dev/assets-src/icon-generated.png -resize 128x128 .wordpress-org/icon-128x128.png

google-chrome --headless=new --no-sandbox --disable-gpu --hide-scrollbars \
	--allow-file-access-from-files --force-device-scale-factor=1 \
	--window-size=1544,500 --screenshot="$preview" \
	"file://$(pwd)/dev/assets-src/banner.html" >/dev/null 2>&1

convert "$preview" -sampling-factor 4:4:4 -quality 91 .wordpress-org/banner-1544x500.jpg
convert "$preview" -resize 772x250 -sampling-factor 4:4:4 -quality 91 .wordpress-org/banner-772x250.jpg
