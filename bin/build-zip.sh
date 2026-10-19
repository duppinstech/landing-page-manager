#!/usr/bin/env bash
# Build landing-page-manager.zip with top-level folder `landing-page-manager/`,
# excluding everything listed in .distignore. Usage: bin/build-zip.sh [out.zip]
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"
out="${1:-$root/landing-page-manager.zip}"
stage="$(mktemp -d)"
trap 'rm -rf "$stage"' EXIT

mkdir "$stage/landing-page-manager"
excludes=()
while IFS= read -r line; do
  line="${line%%#*}"; line="${line//[[:space:]]/}"
  [ -n "$line" ] && excludes+=(--exclude="./${line%/}")
done < "$root/.distignore"

(cd "$root" && tar -cf - "${excludes[@]}" .) | tar -xf - -C "$stage/landing-page-manager"
rm -f "$out"

if command -v zip >/dev/null 2>&1; then
  (cd "$stage" && zip -qr "$out" landing-page-manager)
else
  # Windows Git Bash has no zip; use Python's zipfile (set PYTHON to override).
  py="${PYTHON:-}"
  for c in python3 python py; do
    [ -z "$py" ] && "$c" -c 'import zipfile' >/dev/null 2>&1 && py="$c"
  done
  [ -n "$py" ] || { echo "Need zip or Python to build the archive." >&2; exit 1; }
  (cd "$stage" && "$py" -m zipfile -c "$out" landing-page-manager)
fi
echo "Built $out"
