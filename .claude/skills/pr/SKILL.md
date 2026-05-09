---
name: pr
description: Create or maintain a GitHub pull request through review and merge. Use when opening a PR, editing title/body, addressing review comments, rebasing on the base branch, watching CI, or merging.
disable-model-invocation: true
argument-hint: [pr-number?]
allowed-tools: Bash(gh:*), Bash(git status:*), Bash(git diff:*), Bash(git log:*), Bash(git push:*), Bash(git fetch:*), Bash(git rebase:*), Bash(git rev-parse:*)
---

One PR for one concern. Detect the repo's conventions; mirror them. Don't impose templates the project doesn't use.

## Phase 1 — CREATE

### Detect title style

```bash
gh repo view --json defaultBranchRef --jq .defaultBranchRef.name
gh pr list --state merged --limit 30 --json title --jq '.[].title'
```

Classify (same regex set as `commit`); take the plurality.

- ≥60% match → follow strictly.
- 40–60% → follow with plain imperative as fallback.
- <40% → use plain imperative (the safest neutral).

### Detect body conventions

```bash
ls .github/PULL_REQUEST_TEMPLATE.md .github/pull_request_template.md .github/PULL_REQUEST_TEMPLATE/ 2>/dev/null
gh pr list --state merged --limit 5 --json body --jq '.[].body'
```

Three shapes recur:

1. **Template.** If a `PULL_REQUEST_TEMPLATE` exists (singular file or directory of variants), **fill it literally** — every header, every checklist box, every section. Do not omit. If the template has an AI-disclosure checkbox, fill it honestly.
2. **Prose lead, optional sections.** Default for projects without a template — a paragraph of *why*, an optional paragraph of *what*, optional fenced code or example, optional `Refs #N` / `Closes #N`.
3. **One-liner.** Trivial fix or typo — one sentence is enough.

**Skip by default:**
- `## Summary` / `## Test plan` headings — not a universal convention; only invent these if the project's history shows them.
- AI-attribution footer (`🤖 Generated with Claude Code`, `Co-Authored-By: Claude`). The OSS landscape has shifted firmly against unsolicited AI markers; some projects ban AI-generated PRs outright. The body says nothing about tooling unless a template asks.

**Add only when applicable:**
- `## Backwards compatibility` — when public API is touched and the project values that section.
- `Closes #N` / `Fixes #N` / `Resolves #N` — only when the issue is being resolved AND the PR targets the default branch (auto-close fires only there). Use `Refs #N` for backports / non-default targets.

### Workflow

1. **Inspect.** Run in parallel:
   ```bash
   gh repo view --json defaultBranchRef --jq .defaultBranchRef.name
   git log --oneline <base>..HEAD
   git diff <base>...HEAD --stat
   git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>/dev/null
   gh pr list --state merged --limit 30 --json title --jq '.[].title'
   ```
2. **Push if needed.** Confirm with the user before `git push -u origin HEAD` on a fresh branch.
3. **Draft title + body covering ALL commits in the range.** Show; wait for confirmation.
4. **Create with `--body-file -` over stdin** — the only safe path for multi-line bodies (no shell-quoting bugs from backticks, `$`, embedded quotes):
   ```bash
   gh pr create \
     --base main \
     --title "fix(parser): early-return on empty input" \
     --assignee @me \
     --body-file - <<'EOF'
   The streaming parser allocated before checking input length, surfacing
   as OOM in tests with empty payloads. Early-return when the buffer is
   length-zero.

   Closes #42
   EOF
   ```
   Use `--draft` only when commits are still queued; promote with `gh pr ready`. Some projects auto-close stale drafts.
5. **Return the PR URL.**

Avoid `--fill` — it concatenates commit subjects unpredictably. `--fill-verbose` is acceptable for clean one-commit branches.

## Phase 2 — ITERATE

### Edit metadata

```bash
gh pr edit <n> --title "new title"
gh pr edit <n> --body-file - <<'EOF'
…new body…
EOF
gh pr edit <n> --add-label bug --add-reviewer <login>
gh pr ready <n> # draft → ready
gh pr ready <n> --undo # ready → draft
gh pr comment <n> --body-file - <<'EOF'
Force-pushed after rebase on main.
EOF
```

### Address review — pick the flow that matches the merge strategy

Detect first:

```bash
gh repo view --json mergeCommitAllowed,squashMergeAllowed,rebaseMergeAllowed
```

- **Squash-only repo.** Push **plain follow-up commits** with descriptive messages. They collapse on merge — autosquashing is wasted effort.
- **Repo that preserves history** (rebase-merge, merge-commit, or curated history conventions). Use **fixups + autosquash** so each landed commit is meaningful:
  ```bash
  git commit --fixup=<sha>
  GIT_SEQUENCE_EDITOR=: git rebase -i --autosquash <base>
  git push --force-with-lease=origin/<branch>
  ```
- **Multiple strategies allowed.** Default to plain follow-up commits — the maintainer chooses the merge button.

### Rebase on a moved base

```bash
git fetch origin
git rebase origin/<base>
# resolve conflicts, run tests
git push --force-with-lease=origin/<branch>
```

`--force-with-lease` (never bare `--force`) is the universal default — it refuses to push when the remote has unseen commits, preventing accidental clobber of a collaborator's work. **Only on your own feature branch — never on default or release branches.**

Merging the base in (`git merge origin/<base>`) is acceptable in shops that prefer non-rewriting flows; pick what the project does.

### Re-request review

```bash
gh api -X POST "repos/{owner}/{repo}/pulls/<n>/requested_reviewers" \
  -f 'reviewers[]=<login>'
gh pr comment <n> --body-file - <<'EOF'
Addressed review: <one-line summary>. Ready for another look.
EOF
```

GitHub clears the review request once a reviewer responds; re-requesting bumps it back into their queue.

## Phase 3 — MERGE

### Wait for green

```bash
gh pr checks <n> --watch # polls every 10s; exits on terminal state
gh pr status # one-shot summary across your PRs
```

`gh pr checks --watch` is the canonical poll. Don't roll a `while sleep` loop — `--watch` already does this correctly, including `--fail-fast` for early exit.

### Merge

```bash
gh repo view --json mergeCommitAllowed,squashMergeAllowed,rebaseMergeAllowed
gh pr merge <n> --squash --delete-branch --auto
```

- `--auto` queues the merge to fire when required checks pass and approvals land. No-op without branch protection.
- Pass the strategy that matches the repo. If multiple are allowed, omit the strategy flag and let GitHub apply the repo default.
- `--delete-branch` cleans local + remote.
- For repos with merge queues (signaled by an error like *"this branch must use the merge queue"* or "Merge when ready" in the UI), omit the strategy flag and use `--auto` — the queue handles ordering.

## Cross-cutting rules

- **One PR = one concern.** Split when a refactor and a behavior change are mixed, or an unrelated drive-by fix sneaks in. ~400 lines is a common upper-bound heuristic for what one reviewer can hold in their head.
- **Never push to default or release branches.** Never `--force` (use `--force-with-lease` on your own feature branch only).
- **Never `--no-verify`.** Hooks fail for a reason — fix the root cause.
- **Never auto-add `Closes #N`** without confirming the issue with the user first.
- **Never add an AI-attribution footer** in title or body by default. If a project's template asks for AI disclosure, fill it honestly.
- **Verify auth cheaply.** `gh auth status`; on failure, surface the message verbatim and stop — don't try to work around it.
