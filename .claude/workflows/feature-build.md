# Workflow: feature build

1. **Intent check.** Confirm the feature serves a goal in INTENT.md and decide core vs. Pro. Add or locate the PLAN.md item and mark it `[~]`.
2. **Research (optional).** Use the `research` agent for unfamiliar APIs.
3. **Branch.** `git checkout -b feature/<name>` from an up-to-date `main`.
4. **Scaffold.** Run `/scaffold module …` or `/scaffold template …` if the feature needs new files.
5. **Implement.** Follow `.claude/rules/`. Hooks lint every PHP edit and block vendor and secret writes.
6. **Tests.** Have the `test-writer` agent add coverage, or record manual smoke steps per `testing-standard.md`.
7. **Review.** Run `/review`. The `security-reviewer` runs automatically for sensitive areas. Fix every High finding.
8. **Docs.** Add a CHANGELOG [Unreleased] entry, update README if behavior changed, and update `decisions.md` if architecture changed.
9. **PR.** Draft with the `pr-description` skill, then open it once Odell approves.
10. **Close out.** Mark PLAN.md `[x]` and add a line to `progress.md`.
