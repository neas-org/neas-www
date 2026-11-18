#!/usr/bin/env bash
# Builds the zips to upload in WordPress (Plugins/Temas → Adicionar novo → Enviar).
# The folder name inside each zip is the slug WordPress uses to identify it.
set -euo pipefail

cd "$(dirname "$0")"
rm -rf dist
mkdir -p dist

for module in modules/*/; do
	name="$(basename "$module")"
	(cd modules && zip -qr "../dist/$name.zip" "$name" -x '*.DS_Store')
	echo "dist/$name.zip"
done
