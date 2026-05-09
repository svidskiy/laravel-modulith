---
name: commit
description: Create one git commit from staged or working-tree changes. Use when the user asks to commit, finalize changes, or wrap up a logical unit of work. Pass an optional human co-author with --coauthor "Name <email>".
disable-model-invocation: true
argument-hint: [--coauthor "Name <email>"]
allowed-tools: Bash(git status:*), Bash(git diff:*), Bash(git log:*), Bash(git add:*), Bash(git commit:*)
---

One commit per logical change. Detect the repo's convention from history; mirror it; never invent.

## Detect convention

Run in parallel:

```bash
git status --short
git diff --cached --stat
git diff --stat
git log --no-merges --format='%s' -50
```

Classify each subject:

| Regex | Convention |
|---|---|
| `^(feat\|fix\|chore\|docs\|refactor\|perf\|test\|build\|ci\|style\|revert)(\([a-z0-9_./-]+\))?!?: ` | Conventional Commits |
| `^\[[^\]]+\] ` | Bracket-prefixed (release branch, subsystem, version) |
| `^(#\|[A-Z]+-)\d+[: ]` | Issue-keyed |
| Capitalized verb (`Add`, `Fix`, `Allow`, `Remove`, …) | Plain imperative |

**Threshold:** ≥70% of recent subjects → follow strictly. 40–70% → follow with plain imperative as fallback. <40% → default to plain imperative (the neutral that no convention rejects). Don't blend conventions.

When CC scopes are dominant, harvest actual scope tokens from the log and reuse them. Don't invent new scopes unless the diff genuinely covers a new area.

## Subject line — universal rules

- **Imperative mood**: "Add", "Fix", "Remove" — never "Added"/"Adds"/"Fixing". Source: git's own `SubmittingPatches`, Tim Pope.
- **Length**: target ≤50 chars, hard cap 72.
- **No trailing period.**
- **Capitalization**: sentence-case for plain imperative (`Add foo`); lowercase after the colon for CC (`feat: add foo`). Match what the repo does.

## Conventional Commits format

`<type>(<scope>)?[!]: <imperative subject>`

- Breaking change: append `!` before colon (`feat(api)!:`) **or** add a `BREAKING CHANGE: <text>` footer. The `!` form alone is sufficient.
- Spec mandates only `feat` and `fix`; the rest (`chore`, `docs`, `refactor`, `perf`, `test`, `build`, `ci`, `style`, `revert`) are conventional. Skip types the repo's history doesn't already use.

## Body

- One blank line between subject and body — required by `git rebase`, `git format-patch`, `git log --oneline`.
- Wrap at ~72 chars.
- **Why, not what.** The diff already shows what changed.
- Skip body for trivial single-purpose commits. A weak body is worse than none.

## Trailers

A trailing block of `Token: value` lines is parsed as trailers by `git interpret-trailers` when preceded by a blank line.

`Co-authored-by: Name <email>` format ([GitHub spec](https://docs.github.com/en/pull-requests/committing-changes-to-your-project/creating-and-editing-commits/creating-a-commit-with-multiple-authors)):

- Single space after the colon, name verbatim, email in angle brackets.
- **One blank line** between body and trailer block.
- Multiple co-authors: stack lines with **no blank line between them**.
- The GitHub no-reply form `ID+username@users.noreply.github.com` is valid; look up the numeric ID with `gh api users/<username> --jq .id`.

## Workflow

1. **Inspect.** Run the four commands above in parallel.
2. **Triage staging.** If unrelated changes are staged, ask which to keep before drafting.
3. **Draft.** Write the message; show it to the user; wait for confirmation.
4. **Stage explicitly by path.** Never `git add -A` or `git add .` — sweeps `.env*`, build artifacts, generated lockfiles, OS detritus (`.DS_Store`), IDE caches. Refuse paths matching `\.env*`, `*.pem`, `*.key`, `id_rsa*`, `credentials*`, `*.sqlite*` unless the user explicitly demands.
5. **Commit via stdin heredoc** — preserves blank lines, wrap, and trailers:
   ```bash
   git commit -F - <<'EOF'
   fix(parser): early-return on empty input

   The streaming parser allocated a buffer before checking input length,
   which surfaced as an OOM in tests with empty payloads.

   Closes #42
   EOF
   ```
   Single-quoted `<<'EOF'` is required. Unquoted heredocs run shell expansion on `$` and backticks in the body — a body containing `$HOME` gets silently rewritten.
6. **Verify.** `git status --short` and `git log -1 --format='%h %s'` after commit.

## Co-author handling

- **Never add `Co-Authored-By: Claude` or any AI-tool footer.** Tools don't sign their outputs; the human author owns the commit. OpenJDK treats AI co-author lines as a stricter-rules trigger; multiple projects have rolled back default AI footers under maintainer pressure. Add an AI footer only if the user explicitly asks during this turn.
- **Optional human co-author.** When the user passes `--coauthor "Name <email>"` (or says "with co-author X"), append a properly formatted `Co-authored-by:` trailer using the rules above. Multiple `--coauthor` arguments stack into multiple trailer lines.

Example with co-author:

```bash
git commit -F - <<'EOF'
feat: add streaming JSON decoder

Closes #51
Co-authored-by: Jane Doe <jane@example.com>
EOF
```

## After-commit recovery

- **Pre-commit hook failed.** Fix the issue, re-stage, run `git commit` again — **never** `--amend`. The failed commit didn't happen; amending would mutate the prior real commit, conflating its message and contents with the current changes.
- **Wrong message on last commit, not yet pushed.** `git commit --amend -F - <<'EOF' … EOF`.
- **Already pushed.** Don't amend — write a follow-up commit. Squash-merge collapses them on PR merge anyway.
- **Never `--no-verify`** unless the user explicitly authorizes it for a known-broken hook. Hooks fail for a reason — fix the root cause.
