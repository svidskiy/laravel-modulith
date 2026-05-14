---
name: changelog
description: Maintain CHANGELOG.md for this composer package following Keep a Changelog 1.1.0 and Semantic Versioning 2.0.0. Use this whenever the user wants to add an entry to the changelog, document a change they just made, cut a release / bump the version, prepare release notes, yank a bad release, or asks "what should I put in the changelog". Trigger even when the user does not explicitly say "changelog" — phrases like "release 1.2.0", "bump version", "tag v0.3.0-beta1", "что в этом релизе", "release notes", "yanked release" should also activate this skill. Do not trigger for generic git log questions unrelated to the project's CHANGELOG.md file.
---

# Changelog Maintainer

Keep `CHANGELOG.md` at the repo root aligned with [Keep a Changelog 1.1.0](https://keepachangelog.com/en/1.1.0/) and [SemVer 2.0.0](https://semver.org/spec/v2.0.0.html). This is a Laravel/composer package — the changelog is the contract between you and downstream apps that depend on it, so it must describe what changed for *consumers*, not what changed internally.

## Guiding principles (Keep a Changelog 1.1.0)

These are the seven principles the rest of this skill enforces. When making a judgment call, fall back to these:

1. Changelogs are **for humans**, not machines. Curate; do not dump `git log`.
2. **Every released version** has an entry. No silent releases.
3. Same types of changes are grouped under the six section headings.
4. Versions and sections are **linkable** (compare links at the bottom).
5. **Latest version comes first** (reverse chronological).
6. **Release date is displayed** in ISO 8601 (YYYY-MM-DD).
7. The file states that the project follows Semantic Versioning.

## Workflow selection

Five distinct workflows. Figure out which one the user wants before doing anything:

1. **Append entry** — user finished a change, wants it documented under `[Unreleased]`.
2. **Cut release** — user is publishing a new version. Rename `[Unreleased]` to `[X.Y.Z] - YYYY-MM-DD`, suggest a SemVer bump, create a fresh empty `[Unreleased]`, update compare links.
3. **Yank a release** — a previously published version has a critical defect and was removed from Packagist. Mark its heading with `[YANKED]`.
4. **Pre-release** — user wants to cut `X.Y.Z-alpha.N`, `-beta.N`, or `-rc.N`. Same as cut-release with a pre-release suffix.
5. **Bootstrap** — `CHANGELOG.md` does not exist yet. Create it with the canonical header and an empty `[Unreleased]`.

If ambiguous, ask one clarifying question. Never silently guess between workflows — getting a version wrong is harder to reverse than asking.

**Released versions are immutable.** SemVer rule 3: "Once a versioned package has been released, the contents of that version MUST NOT be modified." That applies here too — do not retroactively edit a `[1.0.0]` section's bullets. If you find a wrong entry in a released section, add a correction note to `[Unreleased]` instead, or yank and re-release.

## File format

This is the canonical structure. Reproduce it exactly when bootstrapping:

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-05-14

### Added
- Public method `Module::guard()` for runtime module validation

### Changed
- **BREAKING:** `ModuleRepository::all()` now returns `Collection` instead of `array`

### Fixed
- Cache invalidation race condition when multiple commands ran in parallel

[Unreleased]: https://github.com/svidskiy/laravel-modulith/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/svidskiy/laravel-modulith/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/svidskiy/laravel-modulith/releases/tag/v1.1.0
```

Why this exact structure: Packagist, GitHub releases, and most Laravel-ecosystem tooling (Spatie, Filament, Livewire) parse this format. Deviating breaks downstream automation.

## The six categories

These are the only allowed section names. Pick the right one based on what the consumer of the package will experience:

| Section | Use when |
|---|---|
| `### Added` | New public API: classes, methods, facade methods, console commands, config keys, events |
| `### Changed` | Existing public API behavior changed (signature, return type, default config value, command flag) |
| `### Deprecated` | Public API is still functional but will be removed in a future major. Always say which version |
| `### Removed` | Public API no longer exists |
| `### Fixed` | Bug fix visible to package consumers |
| `### Security` | Vulnerability patch. Reference CVE if assigned |

Omit empty sections. A version with only bug fixes has only `### Fixed`.

## Commit type → category mapping (Spatie-style)

This project uses [Conventional Commits](https://www.conventionalcommits.org/) — the same set the `git-commit` skill produces (`feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`). When the user says "log this fix" or you scan recent commits to suggest entries, map like this:

| Commit type | Category | Notes |
| --- | --- | --- |
| `feat:` | Added | Or Changed if it modifies existing API |
| `feat!:` or `BREAKING CHANGE:` footer | Changed | Prepend `**BREAKING:**` to the bullet |
| `fix:` | Fixed | If the commit body/footer mentions CVE, advisory, or "security" → use Security instead |
| `fix!:` | Fixed + Changed | Bullet in Fixed; if API signature changed, also note in Changed with `**BREAKING:**` |
| `refactor:` | Excluded unless public API changed → then Changed | |
| `perf:` | Changed | Only if measurable consumer impact |
| `revert:` | Depends on what was reverted | Reverting a `feat` → Removed; reverting a `fix` → Fixed (the regression returned) |
| `chore:` `docs:` `test:` `ci:` `style:` `build:` | Excluded | Internal — package consumers do not care |

Note: Conventional Commits has no dedicated `security:` type. Security fixes ship as `fix:` commits with a CVE reference or "security" keyword in the body — the skill maps those to `### Security`, not `### Fixed`, because the consumer cares whether they need to upgrade urgently.

The skip rules are deliberate: `CHANGELOG.md` is consumer-facing. A reader of this file is deciding whether to upgrade — they need to know what might break and what they gain, not that the test suite got faster.

When in doubt about whether a `refactor:` is consumer-visible, ask: "does this change anything a user of the package would observe — class names, method signatures, return types, config schema, command output, exception types?" If no, skip.

## Workflow 1: Append entry to [Unreleased]

The user describes a change they made (or you can see it in recent git history). Steps:

1. Read `CHANGELOG.md`. If missing, run bootstrap workflow first.
2. Pick the category from the table above.
3. Write a **user-facing** bullet — describe the consumer impact, not the implementation.
   - Bad: `Refactored ModuleRepository::resolve to use spl_object_id for hashing`
   - Good: `Improved module resolution performance for projects with >100 modules`
4. If breaking, start the bullet with `**BREAKING:**` and explain the migration in one clause.
5. Insert the bullet under the right `###` heading inside `## [Unreleased]`. Create the heading if it doesn't exist.
6. Preserve the order of existing entries — append, don't reshuffle.

Do NOT touch the compare links at the bottom in this workflow — those only change at release time.

## Workflow 2: Cut a release

This is the riskier flow. Order matters.

### Step 1: Read state

Run these in parallel to figure out where the project is:

```bash
git tag --list --sort=-v:refname | head -5      # most recent tags
git log $(git describe --tags --abbrev=0)..HEAD --oneline   # commits since last tag
git remote get-url origin                        # for compare links
```

Also read `CHANGELOG.md` and look at what's under `[Unreleased]`.

If `[Unreleased]` is empty, stop and tell the user: there's nothing to release. Offer to scan commits since the last tag and propose entries.

### Step 2: Propose the version bump

Apply SemVer to what's in `[Unreleased]`:

| Content in [Unreleased] | Bump |
|---|---|
| Any `**BREAKING:**` marker, any `### Removed` entries | **major** (X.0.0) |
| Any `### Added` or non-breaking `### Changed` / `### Deprecated` | **minor** (X.Y.0) |
| Only `### Fixed` and/or `### Security` | **patch** (X.Y.Z) |

**Pre-1.0.0 convention**: SemVer rule 4 says explicitly "Major version zero (0.y.z) is for initial development. Anything MAY change at any time." The spec gives maintainers wide latitude. This project's *convention* (not the spec itself):
- Breaking change in 0.x → bump **minor** (0.1.x → 0.2.0)
- Non-breaking in 0.x → bump **patch** (0.1.0 → 0.1.1)

This matches what Spatie, Filament, and most modern PHP packages do during pre-1.0. The SemVer FAQ also recommends starting the very first release at `0.1.0`, not `0.0.1` — that's the bootstrap default.

Tell the user the proposed version and *why* (cite which entry triggered the bump level). Wait for confirmation before writing. Never auto-bump without explicit user approval — a release is a public action.

### Step 3: Rewrite the file

After the user confirms version `X.Y.Z`:

1. Rename `## [Unreleased]` heading to `## [X.Y.Z] - YYYY-MM-DD` (today's date, ISO format — get it with `date +%Y-%m-%d`).
2. Insert a fresh empty `## [Unreleased]` heading above it (with no entries — leave it blank, ready for the next cycle).
3. Update compare links at the bottom (see Workflow 3).

Don't run `git tag` yourself. Releasing is the user's call — they may want to review the file first or coordinate with CI.

## Workflow 3: Update compare links

The link block at the bottom of the file gives readers a one-click diff between versions. Structure:

```markdown
[Unreleased]: <repo-url>/compare/v<latest-tag>...HEAD
[<latest-version>]: <repo-url>/compare/v<previous-tag>...v<latest-tag>
[<previous-version>]: <repo-url>/compare/v<even-earlier>...v<previous-tag>
...
[<first-version>]: <repo-url>/releases/tag/v<first-version>
```

Repo URL detection (in priority order):
1. `composer.json` → `support.source` field
2. `composer.json` → `homepage` field
3. `git remote get-url origin` (strip `.git` suffix, convert SSH `git@github.com:owner/repo` → `https://github.com/owner/repo`)

When cutting release `X.Y.Z`:
1. Update `[Unreleased]` link to `compare/vX.Y.Z...HEAD`.
2. Insert a new `[X.Y.Z]: .../compare/v<previous>...vX.Y.Z` line right below `[Unreleased]`.
3. Leave older links untouched.

If this is the first release (no previous tag), the new line uses the `releases/tag/vX.Y.Z` form instead of `compare/...`.

## Workflow 4: Yank a release

Yanking means a published version was pulled from Packagist after release because of a critical defect — a broken install, a leaked secret, an accidentally-shipped breaking change. The version number is burned (SemVer rule 3: immutable) so you cannot reuse it. The next release just gets the next number, and the bad release stays in the file but with `[YANKED]` so anyone reading the changelog knows to skip it.

Steps:
1. Find the released section, e.g. `## [1.2.0] - 2026-05-14`.
2. Append `[YANKED]` to the heading: `## [1.2.0] - 2026-05-14 [YANKED]`.
3. Do not delete or edit the bullets underneath — they document what *would have* shipped and may help users diagnose if they installed it before the yank.
4. Optionally add a short note at the top of the section explaining why (one line, e.g. *"Yanked due to broken service-provider registration — install fails with composer 2.7+."*).
5. The compare-link block at the bottom stays as-is — the diff is still valid history.

The next release proceeds normally from where the yanked version left off. The yanked version still counts for SemVer bump purposes (consumers may have installed it).

## Workflow 5: Pre-release versions

Per SemVer rule 9, a pre-release is denoted by appending a hyphen + dot-separated identifiers: `1.0.0-alpha`, `1.0.0-alpha.1`, `1.0.0-beta.2`, `1.0.0-rc.1`. Useful when shipping a big change that warrants tester feedback before promoting to stable.

Precedence (rule 11): `1.0.0-alpha` < `1.0.0-alpha.1` < `1.0.0-beta` < `1.0.0-rc.1` < `1.0.0`.

Convention for this project:
- `alpha.N` — early, internal/unstable, API may still shift
- `beta.N` — feature-complete, API frozen, hunting bugs
- `rc.N` — release candidate, ship if no blockers found

Cut-release flow is the same as Workflow 2 with two differences:
1. The version heading uses the pre-release form: `## [1.0.0-rc.1] - 2026-05-14`.
2. Compare links use the same form: `[1.0.0-rc.1]: .../compare/v0.9.5...v1.0.0-rc.1`.

Pre-release entries do **not** auto-fold into the eventual stable section. When you finally cut `1.0.0`, that's a separate `## [1.0.0]` heading containing only what changed since the last rc (often "Promoted rc.N to stable"). Past rc sections stay where they are — that's the audit trail.

## Writing good entries

The single biggest mistake is writing entries from the developer's perspective instead of the consumer's. The reader of this file is someone deciding "should I upgrade to this version, and will it break my app?" Optimize for that.

**Example: Refactor to a new method signature**

Bad bullet (describes the diff):
> Changed `ModuleRepository::all` to return Collection instead of array

Good bullet (describes consumer impact):
> **BREAKING:** `ModuleRepository::all()` now returns `Illuminate\Support\Collection` instead of `array`. Cast with `->toArray()` if you relied on array access.

**Example: Performance improvement**

Bad:
> Optimized loader to use generator

Good:
> Reduced memory usage during module boot for projects with 50+ modules

**Example: Deprecation**

Bad:
> Deprecated old facade method

Good (always name the version):
> Deprecated `Modulith::resolve()`. Use `Modulith::module()->resolve()` instead. Removal planned for v2.0.

Each bullet should be one line. If you need a paragraph, you're probably trying to document two changes — split them.

## Project-specific notes

This skill ships inside the repo, so paths are relative to the repository root — never hardcode absolute paths. Other contributors will check out the project at a different location.

- Package: `svidskiy/laravel-modulith` (composer)
- Changelog file: `CHANGELOG.md` at the repo root
- Public API surface to watch (these changes belong in the changelog):
  - Classes under `src/` namespaced `Svidskiy\Modulith\*`
  - The `Modulith` facade and any aliases exported via the service provider
  - The `ModulithServiceProvider` registration / publishing behavior
  - All console commands under `src/Commands/`
  - The `modulith` config schema (added/removed keys, default value changes)
- Internal (skip for changelog): anything under `tests/`, `rector.php`, `pint.json`, `phpstan.neon.dist`, `.agents/`, `.claude/`, `skills-lock.json`, `docs/` content edits
- Commits use Conventional Commits (typically via the `git-commit` skill) — trust the type prefix and read the body for `BREAKING CHANGE:` footers or CVE references
