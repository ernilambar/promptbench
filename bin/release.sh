#!/usr/bin/env bash
set -euo pipefail

TAG="${1:?Usage: bin/release.sh <tag>}"

# Zip name mirrors package.json "name", which is used as the build's output filename.
SLUG=$(node -p "require('./package.json').name")
ZIP_PATH="deploy/${SLUG}.zip"

pnpm run deploy

gh release view "$TAG" >/dev/null 2>&1 || gh release create "$TAG" --generate-notes
gh release upload "$TAG" "$ZIP_PATH" --clobber
