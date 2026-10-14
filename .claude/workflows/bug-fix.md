# Workflow: bug fix

1. **Reproduce.** Capture the version, template, subdomain, Pro on or off, and the exact steps. Note the error or log output.
2. **Locate.** Trace from the entry point (hook, REST route, AJAX action, or template) to the root cause. Don't patch symptoms.
3. **Branch.** `fix/<name>` (or `security/<name>` for vulnerabilities; keep those out of public issues, see SECURITY.md).
4. **Regression test first.** Have the `test-writer` agent write a test that fails now. Without a harness, write down the manual repro.
5. **Fix.** Make the smallest change that addresses the root cause, PHP 7.4 compatible.
6. **Verify.** The test passes, lint is clean, and the original repro no longer reproduces. Check you haven't regressed subdomain AJAX or update installs.
7. **Review.** Run `/review`. Use the `security-reviewer` when the fix touches handlers, uploads, rewrites, or the updater.
8. **Ship.** Add a CHANGELOG `### Fixed` entry and open the PR. If the bug hurts live sites, propose a patch release through `/deploy`.
9. **Log.** Add a line to `progress.md`, and to `decisions.md` if the fix changed a design assumption.
