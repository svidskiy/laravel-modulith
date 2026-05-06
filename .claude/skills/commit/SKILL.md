---
name: commit
description: Create a git commit after analyzing staged changes. Use when the user asks to commit, create a commit, or finalize a logical unit of work.
disable-model-invocation: true
---

Create one git commit for one logical change.

## Detect convention first

```bash
git log --oneline -20
```

Three patterns to recognize:
- `feat(scope): …` / `fix: …` → **Conventional Commits**
- `[12.x] Allow …` → **Laravel framework style**
- `Add …` / `Fix …` (no prefix) → **freeform imperative** (Spatie, most apps)

Mirror the dominant pattern. If no signal, default to Conventional Commits.

## Conventional Commits format

`<type>(<scope>)?[!]: <imperative subject>`

- subject: imperative, lowercase, no trailing period, ≤72 chars
- body (optional, separate paragraph, wrap ~72): explain *why*
- breaking: `!` before colon (the `BREAKING CHANGE:` footer is spec-valid but virtually unused)

**Types in active ecosystem use:** `feat`, `fix`, `chore`, `refactor`, `perf`, `test`, `docs`, `ci`. Skip `style`, `build`, `revert` — vanishingly rare.

**Scope:** single lowercase token, package-domain (`auth`, `actions`, module name). Optional. Skip if it would be redundant with the type.

## Workflow

1. Run in parallel: `git status`, `git diff --staged` (or `git diff` if nothing staged), `git log --oneline -10`.
2. If unrelated changes are staged, ask which to keep.
3. Draft the message following the detected convention; show it; confirm.
4. Stage by explicit filename (never `git add -A` — leaks secrets).
5. Commit via heredoc to preserve formatting:
   ```bash
   git commit -m "$(cat <<'EOF'
   fix(actions): early-return ExportCsv handle() on cancelled batch

   Workers were swallowing the cancellation flag and continuing the export.

   Co-authored-by: Claude <noreply@anthropic.com>
   EOF
   )"
   ```
6. Verify with `git status`.

## Examples by convention

- CC: `fix(actions): early-return ExportCsv handle() on cancelled batch`
- Laravel: `[12.x] Allow mail driver to accept enums`
- Freeform: `Cache module discovery results between requests`
