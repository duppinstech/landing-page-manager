# Meeting notes: department reviews

Every `/department-review` run counts as a meeting. Each one gets a timestamped file
here recording what each department found, what it recommended, and what was decided.
The file says which items went into PLAN.md, which went to Odell, and which were
declined. It records decisions only, not implementation progress
(that lives in PLAN.md and `.claude/memory/progress.md`).

## File naming
`YYYY-MM-DD-HHMM-<topic>.md`, using local time. Example: `2026-10-14-1951-baseline-review.md`.
An optional `…-appendix.md` keeps ready-to-use text that departments drafted (CHANGELOG entries, help text, legal wording), so approved PLAN items can be done without re-running the review.
Files are append-only history. Don't rewrite past meetings. When Odell decides a
forwarded item later, note the decision in the next meeting's file (or in
`.claude/memory/decisions.md`) and link back.

## Decision outcomes
| Outcome | Meaning |
|---|---|
| **Approved → PLAN** | A routine item within the existing rules. It was added to PLAN.md automatically and is **open to work right away**, without waiting for Odell (see the standing rule below). |
| **Forwarded → Odell** | It needs Odell's decision: pricing or packaging, spending, a legal position or attorney review, product direction or scope, anything published externally, deleting features or data, changing the updater, or waiving a High finding. It goes in PLAN.md under "Decisions needed". |
| **Already tracked** | It's already in PLAN.md. The item may have been reprioritized. |
| **Declined** | Not acted on. The file always gives a reason (for example, out of scope, a duplicate, or a false positive). |

## Standing rule: approved items are open to work
_Set by Odell on 2026-10-14. Applies to every meeting._
- Once a routine item is **Approved → PLAN**, work on it straight away, in priority order (P0 first), using the normal workflows. These cover branches, lint, the required `security-reviewer` passes, and a `/department-review` once the batch is done.
- Only **Forwarded → Odell** items wait for him. When he answers, his decision turns the item into approved work, and it's done the same way.
- The usual safety boundaries still apply. Ask before pushing to `main`, tagging or releasing, deleting files, or rewriting history. Releases still go through `/deploy`.

## Public-repo rule
This repo is public. Notes summarize; they don't publish exploits. An open
High security finding is recorded as "security issue in `<area>`, tracked privately"
without reproduction detail until it's fixed. Private financial numbers from
`CLAUDE.local.md` never appear here.

## Template
```markdown
# Department review: <topic>
**When:** YYYY-MM-DD HH:MM <TZ> · **Scope:** <branch / diff / release> · **Chair:** main session · **Owner:** Odell Duppins

## Attendance and verdicts
| Department | Verdict | Findings | Summary |
|---|---|---|---|

## Highlights
- 3–5 bullets: the things Odell most needs to know.

## Findings and recommendations by department
### <Department>
- <summary of the finding> → <recommendation>. **Outcome:** Approved → PLAN (P#) | Forwarded → Odell | Already tracked | Declined (reason)

## Decisions
- **Approved into PLAN.md:** list
- **Forwarded to Odell:** list, phrased as questions
- **Declined:** list with reasons
```
