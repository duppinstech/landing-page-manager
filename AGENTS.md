# AGENTS.md — team roster

Who does what. Read before delegating so work doesn't overlap.
Definitions live in `.claude/agents/`.

| Agent | Role | Tools | Invoke when |
|---|---|---|---|
| **main session** | Owns the task end to end: plans, edits, commits | all | always |
| `security-reviewer` | Read-only audit for WP security: nonces, caps, sanitize/escape, uploads, SSRF, updater | Read, Grep, Glob, Bash (read-only) | Any change to AJAX/REST/admin-post handlers, uploads, `Update_Checker`, rewrite rules, or anything touching `$_GET/$_POST/$_FILES` |
| `test-writer` | Writes PHPUnit / WP test cases and lint checks for a change | Read, Grep, Glob, Write, Edit, Bash | After a feature or fix is implemented and before PR |
| `research` | Investigates WP core APIs, Carbon Fields behavior, third-party APIs (Google Places, Brandfetch, GitHub releases) | Read, Grep, Glob, WebFetch, WebSearch | Unknown API behavior; never edits files |

## Handoff protocol
1. Main session states the goal, the files in scope, and the acceptance criteria.
2. Subagent returns **findings or a diff summary only** — no unrelated edits.
3. `security-reviewer` findings rated High must be fixed or explicitly waived by Odell before merge.
4. `test-writer` must not change production code; if code is untestable it reports why.
5. Main session records any lasting decision in `.claude/memory/decisions.md`.

## Boundaries
- Only the main session commits, pushes, tags, or edits `PLAN.md` status.
- No agent touches `vendor/`, the release zip, or the Pro repo.
