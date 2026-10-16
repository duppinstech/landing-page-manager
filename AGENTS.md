# AGENTS.md — team roster

Who does what. Read before delegating so work doesn't overlap.
Definitions live in `.claude/agents/`.

| Agent | Role | Tools | Invoke when |
|---|---|---|---|
| **main session** | Owns the task end to end: plans, edits, commits | all | always |
| `security-reviewer` | Read-only audit for WP security: nonces, caps, sanitize/escape, uploads, SSRF, updater | Read, Grep, Glob, Bash (read-only) | Any change to AJAX/REST/admin-post handlers, uploads, `Update_Checker`, rewrite rules, or anything touching `$_GET/$_POST/$_FILES` |
| `test-writer` | Writes PHPUnit / WP test cases and lint checks for a change | Read, Grep, Glob, Write, Edit, Bash | After a feature or fix is implemented and before PR |
| `research` | Investigates WP core APIs, Carbon Fields behavior, third-party APIs (Google Places, Brandfetch, GitHub releases) | Read, Grep, Glob, WebFetch, WebSearch | Unknown API behavior; never edits files |

### Department reviewers
All are read-only and end with `VERDICT: block | fix-before-merge | ok | n/a`. `/department-review` runs them all in parallel **after development is done** and merges their results into one sign-off table.

| Agent | Department | Tools | Looks at |
|---|---|---|---|
| `security-reviewer` | Security | Read, Grep, Glob, Bash | See above |
| `legal-reviewer` | Legal and licensing | Read, Grep, Glob, Bash, WebFetch, WebSearch | Dependency and asset licenses, attribution, copyright and ownership lines, third-party API terms, privacy and data collection, client confidentiality, LICENSE/readme/disclaimer text, WP.org guideline fit |
| `finance-reviewer` | Finance and pricing | Read, Grep, Glob, Bash, WebFetch, WebSearch | Paid API costs, who pays, free-vs-Pro value, pricing recommendation, billable client work, revenue risk |
| `product-reviewer` | Product | Read, Grep, Glob, Bash | INTENT fit, acceptance criteria, scope, core vs Pro, operator and visitor experience, PLAN.md updates |
| `marketing-reviewer` | Marketing | Read, Grep, Glob, Bash | Customer story, release-note draft, naming and copy, unsupported claims, screenshots, listing readiness |
| `support-docs-reviewer` | Support and docs | Read, Grep, Glob, Bash | CHANGELOG, README/readme.txt drift, upgrade notes, admin messages and i18n, predicted tickets and FAQ |
| `release-ops-reviewer` | Operations and release | Read, Grep, Glob, Bash | PHP 7.4/WP 5.9 compatibility, migrations, rewrite flushes, uninstall, `.distignore`/zip contents, failure modes, rollback, smoke test |
| `ux-reviewer` | UX, accessibility, performance | Read, Grep, Glob, Bash | WCAG 2.1 AA, mobile, Core Web Vitals risk, conversion UX, admin UI |

Finance, marketing, legal and product read `.claude/memory/business-context.md` (public-safe). Private numbers live in the gitignored `CLAUDE.local.md`.

## Handoff protocol
1. Main session states the goal, the files in scope, and the acceptance criteria.
2. Subagent returns **findings or a diff summary only** — no unrelated edits.
3. `security-reviewer` findings rated High must be fixed or explicitly waived by Odell before merge.
4. `test-writer` must not change production code; if code is untestable it reports why.
5. Main session records any lasting decision in `.claude/memory/decisions.md`.
6. Department reviewers return findings only, each tagged `[auto]` or `[needs Odell]` with a priority. The main session adds `[auto]` items to PLAN.md as approved and **starts working on them right away** (Odell's standing rule). It lists `[needs Odell]` items under "Decisions needed (Odell)", where they wait for his answer. Every run is minuted in `meeting-notes/`. A `block` verdict from any department stops the merge. Only Odell can waive High security or legal findings, and each waiver goes in `decisions.md`.
7. "Attorney review" (legal) and "revisit pricing" (finance) are decisions for Odell, not automatic blockers.

## Boundaries
- Only the main session commits, pushes, tags, or edits `PLAN.md` status.
- No agent touches `vendor/`, the release zip, or the Pro repo.
