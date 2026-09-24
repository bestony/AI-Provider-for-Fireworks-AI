#!/usr/bin/env bash
set -euo pipefail

part="patch"
if [ "$#" -gt 0 ]; then
  part="$1"
fi

case "$part" in
  major|minor|patch) ;;
  *) echo "usage: $0 major|minor|patch" >&2; exit 2 ;;
esac

plugin_file="ai-provider-for-fireworks-ai.php"
readme_file="readme.txt"
version=$(grep -m1 '^ \* Version:' "$plugin_file" | awk '{print $3}')
IFS=. read -r major minor patch <<< "$version"
case "$part" in
  major) major=$((major + 1)); minor=0; patch=0 ;;
  minor) minor=$((minor + 1)); patch=0 ;;
  patch) patch=$((patch + 1)) ;;
esac
next="$major.$minor.$patch"
perl -pi -e "s/^ \\* Version:.*/ * Version:           $next/" "$plugin_file"
perl -pi -e "s/^Stable tag:.*/Stable tag:        $next/" "$readme_file"
printf 'Updated plugin version to %s. Add a changelog entry before committing.\n' "$next"
