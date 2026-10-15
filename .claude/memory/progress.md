# Progress log

Newest first. One line per meaningful step; reference PLAN.md items.

- **2026-10-14:** Ran the first department review: a baseline of v1.0.6 plus the WIP, with all eight departments. Every verdict was fix-before-merge. Added 36 approved items to PLAN.md (10 P0) and forwarded 13 decisions to Odell. Minutes are in `meeting-notes/2026-10-14-1951-baseline-review.md`.
- **2026-10-14:** Released v1.0.6 through the Release workflow. The asset is `landing-page-manager.zip`, and both the old and new repo API URLs return it. Not yet tested on a live WP site.
- **2026-10-14:** PR #1 (template upload security) and PR #2 (release hygiene, uninstall, repo move) merged to main. WIP committed on `feature/quicktow-template-ux` as PR #3.
- **2026-10-14:** P0/P1 work. Branch `security/template-upload`: template upload secured and fixed (reviewed twice, verdict ok). Branch `chore/p0-release-hygiene`: zip untracked, Release and CI workflows, `bin/build-zip.sh`, Elementor handle pattern, Composer pin, LICENSE copyright. Open: WIP triage (needs Odell), dev-deps decision, uploaded-template storage.
- **2026-10-14:** Added SDLC and Claude Code scaffolding. Seeded PLAN.md from a repo audit (P0: secure template upload, triage WIP, untrack release zip, de-hardcode Elementor handles).
- **2026-03-09:** Released v1.0.5 (subdomain AJAX fix, normalized update folder, WhiteRail AI UX overhaul).
- **2026-03-09:** Released v1.0.1–v1.0.4 (GitHub updater, update-source toggle, manual update check).
