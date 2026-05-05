---
name: pr
description: Create a pull request from the current branch. Use when the user asks to open a PR, create a pull request, or submit for review.
disable-model-invocation: true
---

Create one PR with title and body matched to the repo's actual style.

## Detect title style

```bash
gh pr list --state merged --limit 10 --json title --jq '.[].title'
```

Patterns:
- `[12.x] Allow X` → Laravel framework convention
- `Allow X` (imperative, no prefix) → Spatie / most apps
- `feat(scope): allow x` → Conventional Commits

Mirror the dominant one.

## Body — start minimal

Real merged PRs in laravel/framework, spatie/*, filamentphp/* are short:
- For most fixes: 2–6 sentences, plain prose, no headings.
- For features or non-obvious changes: lead paragraph (what + why) + optional code snippet.

**Add only when applicable:**
- `## Backwards compatibility` — when public API is touched (Laravel maintainers ask for this)
- A single fenced code block — when behavior change is hard to describe in prose
- `Closes #123` — when an issue is being resolved

**Skip:**
- `## Summary` heading — bare opening paragraph is the norm
- `## Test plan` with checkboxes — Meta/Anthropic transplant, not Laravel ecosystem norm. Mention tests inline ("Tests in `tests/Foo`, suite green") only if needed.
- AI-attribution footer — divisive in OSS Laravel. Add only if user asks.

## Workflow

1. Run in parallel:
   - `git status`
   - `gh repo view --json defaultBranchRef --jq .defaultBranchRef.name` — find base branch
   - `git log --oneline <base>..HEAD` — full PR commit history
   - `git diff <base>...HEAD` — full PR diff
   - `git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>/dev/null` — branch tracks a remote?
2. If branch is unpushed, **confirm before** `git push -u origin HEAD`.
3. Draft title + body covering ALL commits in the PR. Show; confirm.
4. Create with heredoc for body formatting:
   ```bash
   gh pr create --title "Allow mail driver to accept enums" --body "$(cat <<'EOF'
   Backport of #59866 to 12.x. MailManager now resolves BackedEnum default drivers via `->value`. Tests in tests/Mail/MailManagerTest.php cover enum + string. No public API change.
   EOF
   )"
   ```
5. Return the PR URL.

## Rules

- One PR = one concern.
- Title is for skim; body is for context — don't restate the title.
- Never push to `main`/`master`, never `--force`, never `--no-verify` without explicit instruction.
- Never auto-add `Closes #N` — confirm the issue with the user first.
