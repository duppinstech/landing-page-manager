#!/usr/bin/env bash
# PostToolUse (Edit|Write|MultiEdit): lint edited PHP and flag hardcoded secrets.
# Exit 2 feeds stderr back to Claude so it fixes the problem.
source "$(dirname "$0")/_lib.sh"
f="$(hook_file_path)"
[ -z "$f" ] || [ ! -f "$f" ] && exit 0

status=0
case "$f" in
  *.php)
    if command -v php >/dev/null 2>&1; then
      out="$(php -l "$f" 2>&1)" || { echo "PHP lint failed: $out" >&2; status=2; }
    fi
    ;;
esac

# Google API keys (AIza...) and common token shapes must never be hardcoded.
case "$f" in
  *.php|*.js|*.json|*.html)
    if grep -nE 'AIza[0-9A-Za-z_-]{35}|ghp_[0-9A-Za-z]{36}|sk-[A-Za-z0-9]{32,}' "$f" >/dev/null 2>&1; then
      echo "Possible hardcoded secret in $f — move it to plugin Settings." >&2
      status=2
    fi
    ;;
esac
exit $status
