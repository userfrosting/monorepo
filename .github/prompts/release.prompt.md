---
agent: agent
description: Guide through the UserFrosting 6 monorepo release process
---

# UserFrosting 6 Release Process

Execute a full release of the UserFrosting 6 monorepo autonomously. Follow each step in order. **Execute every step yourself — do not show commands and ask the user to run them.** If any step fails, abort immediately and report the full error output. Only stop to ask the user for input at Step 1 (version confirmation).

## Step 1: Confirm the Version

Ask the user which version to release. Accept either:
- A specific version string (e.g., `6.0.1`, `6.1.0`)
- A bump keyword: `patch`, `minor`, or `major`

**Prerelease versions** must follow these formats:
- Alpha: `6.x.x-alpha.N` (e.g., `6.1.0-alpha.1`)
- Beta: `6.x.x-beta.N` (e.g., `6.1.0-beta.1`)
- Release candidate: `6.x.x-rc.N` (e.g., `6.1.0-rc.1`)

If the provided version matches a prerelease format, note it as a prerelease and remind the user that:
- Prerelease versions are **not** intended for production use.
- The NPM publish step (Step 6) must use the `--tag` flag to avoid promoting the prerelease to `latest`:
  - Alpha: `--tag alpha`
  - Beta: `--tag beta`
  - RC: `--tag next`

**Ask the user to confirm the version before continuing.** All remaining steps will be executed automatically.

## Step 2: Pre-Release Checks

Run all of the following. If any command fails, stop immediately and show the full error output.

**PHP:**
- Run `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php`
- Run `vendor/bin/phpstan analyse -c phpstan.neon`
- Run `vendor/bin/phpunit`

**TypeScript:**
- Run `npm run lint`
- Run `npm run format`
- Run `npm run typecheck`
- Run `npm run build`

## Step 3: Update Changelogs

> **Critical:** The `AddTagToChangelogReleaseWorker` (Step 5) only matches `## Unreleased` (no brackets) and is a **no-op** for this project, which uses the `## [Unreleased]` format. All changelog updates must be done manually here.

For every `packages/*/CHANGELOG.md` and the root `CHANGELOG.md`:

- If the file has a `## [Unreleased]` section: replace it with `## [VERSION] - YYYY-MM-DD` using today's date.
- If the file has **no** `## [Unreleased]` section (e.g. a package with no changes since last release): insert a new `## [VERSION] - YYYY-MM-DD\n- No changes.\n` block immediately above the previous release heading.

Commit all changelog changes: `docs: update changelogs for VERSION`.

## Step 4: Merge Composer Configuration

Run `vendor/bin/monorepo-builder merge`. If `composer.json` changed, commit the result before proceeding.

## Step 5: Run the Release

Run `vendor/bin/monorepo-builder release VERSION`.

This command runs the following workers in order:
1. `UpdateReplaceReleaseWorker` — updates `replace` entries in `composer.json`
2. `SetCurrentMutualDependenciesReleaseWorker` — pins mutual package versions
3. `AddTagToChangelogReleaseWorker` — **no-op** for this project (changelogs already updated in Step 3)
4. `NpmVersionWorker` — bumps all `package.json` versions
5. `TagVersionReleaseWorker` — creates the git tag locally
6. `PushTagReleaseWorker` — pushes the tag and commits to `origin`
7. `SetNextMutualDependenciesReleaseWorker` — sets `dev-main` versions
8. `UpdateBranchAliasReleaseWorker` — updates the branch alias in `composer.json`

## Step 6: Publish to NPM

First run `npm login`. If it prompts for browser authentication, open the URL and complete the login flow before continuing.

Then publish:
- **Stable**: `npm publish --access public --workspaces`
- **Alpha**: `npm publish --access public --workspaces --tag alpha`
- **Beta**: `npm publish --access public --workspaces --tag beta`
- **RC**: `npm publish --access public --workspaces --tag next`

> **Note:** The `userfrosting` (skeleton) package is marked `private` and will be skipped with a warning — this is expected.

## Step 7: Create GitHub Releases

**Package repos and changelog sources:**

| Package directory             | GitHub repo                       | Changelog source                          |
| ----------------------------- | --------------------------------- | ----------------------------------------- |
| *(monorepo root)*             | `userfrosting/monorepo`           | root `CHANGELOG.md`                       |
| `packages/framework`          | `userfrosting/framework`          | `packages/framework/CHANGELOG.md`         |
| `packages/sprinkle-core`      | `userfrosting/sprinkle-core`      | `packages/sprinkle-core/CHANGELOG.md`     |
| `packages/sprinkle-account`   | `userfrosting/sprinkle-account`   | `packages/sprinkle-account/CHANGELOG.md`  |
| `packages/sprinkle-admin`     | `userfrosting/sprinkle-admin`     | `packages/sprinkle-admin/CHANGELOG.md`    |
| `packages/theme-pink-cupcake` | `userfrosting/theme-pink-cupcake` | `packages/theme-pink-cupcake/CHANGELOG.md`|
| `packages/skeleton`           | `userfrosting/userfrosting`       | `packages/skeleton/CHANGELOG.md`          |

### 7a. Monorepo release

The monorepo tag was already pushed in Step 5. Create the release directly — no tag creation needed:

```bash
gh release create VERSION --repo userfrosting/monorepo --title "VERSION" \
  --notes-file /tmp/release-notes-monorepo.md [--prerelease]
```

### 7b. Package repo releases

> **Critical:** The monorepo split workflow (`.github/workflows/Monorepo.yml`) syncs **commits** to individual package repos but does **not** push tags. Tags must be created manually. The split also runs **asynchronously** — if you create tags before it finishes, they will point to stale commits.

**1. Wait for the monorepo split to complete:**

Run `gh run list --repo userfrosting/monorepo --workflow=Monorepo.yml --limit 3` and confirm the most recent run triggered by the "prepare release" commit on the `6.0` branch shows `✓`.

**2. For each package repo:**

Get the `6.0` HEAD SHA (after the split has completed):
```bash
gh api repos/userfrosting/REPO/git/refs/heads/6.0 --jq '.object.sha'
```

Create the tag on that SHA:
```bash
gh api repos/userfrosting/REPO/git/refs -X POST \
  -f ref="refs/tags/VERSION" -f sha="SHA"
```

Find the previous version (for the Full Changelog URL):
```bash
git describe --tags --abbrev=0 VERSION^
```

Build release notes:
```markdown
## What's Changed

<entries from package CHANGELOG.md for VERSION>

**Full Changelog**: https://github.com/userfrosting/REPO/compare/PREV_VERSION...VERSION
```

Create the release:
```bash
gh release create VERSION --repo userfrosting/REPO --title "VERSION" \
  --notes-file /tmp/release-notes-REPO.md [--prerelease]
```

## Step 8: Post-Release Verification

Verify:
1. Local tag exists: `git tag --list | grep VERSION`
2. Each package repo tag points to the correct commit (the `6.0` branch HEAD, not a stale commit):
   ```bash
   gh api repos/userfrosting/REPO/git/refs/tags/VERSION --jq '.object.sha'
   gh api repos/userfrosting/REPO/git/refs/heads/6.0 --jq '.object.sha'
   ```
   Both SHAs must match for every repo.
3. GitHub releases exist for all 7 repos (monorepo + 6 packages).
4. NPM packages are published: `npm info @userfrosting/sprinkle-core version`

Report a summary of all steps completed and confirm the release is done.

