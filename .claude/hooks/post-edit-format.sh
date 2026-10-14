#!/usr/bin/env bash
# PostToolUse (Edit|Write|MultiEdit): auto-format PHP with phpcbf (WordPress standard)
# when it is installed. No-op otherwise — never blocks.
source "$(dirname "$0")/_lib.sh"
f="$(hook_file_path)"
case "$f" in *.php) ;; *) exit 0 ;; esac
case "$(hook_norm "$f")" in */vendor/*) exit 0 ;; esac

root="${CLAUDE_PROJECT_DIR:-.}"
for bin in "$root/vendor/bin/phpcbf" "$(command -v phpcbf 2>/dev/null)"; do
  if [ -n "$bin" ] && [ -x "$bin" ]; then
    "$bin" --standard=WordPress -q "$f" >/dev/null 2>&1
    break
  fi
done
exit 0
