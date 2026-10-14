#!/usr/bin/env bash
# PreToolUse (Edit|Write|MultiEdit): block writes to files Claude must never touch.
# Exit 0 = allow, exit 2 = block (stderr is shown to Claude).
source "$(dirname "$0")/_lib.sh"
f="$(hook_norm "$(hook_file_path)")"
[ -z "$f" ] && exit 0

case "$f" in
  */vendor/*)                    msg="vendor/ is managed by Composer — edit composer.json instead." ;;
  */.git/*)                      msg="Never write inside .git/." ;;
  */.env|*/.env.*|*.pem)         msg="Secrets files are off-limits." ;;
  */landing-page-manager.zip)    msg="The release zip is a build artifact — rebuild it, don't edit it." ;;
  */landing-page-manager-pro/*)  msg="Pro lives in a separate repo." ;;
  *) exit 0 ;;
esac
case "$f" in */.env.example) exit 0 ;; esac

echo "Blocked by block-sensitive-writes.sh: $f — $msg" >&2
exit 2
