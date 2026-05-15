---
mode: agent
description: Guide through the UserFrosting 6 monorepo release process
---

# UserFrosting 6 Release Process

Guide the user through a full release of the UserFrosting 6 monorepo. Follow each step in order. **After completing each step, summarize what was done and ask the user to confirm before moving on to the next step.** Abort and report clearly if any step fails — do not proceed until the current step passes and the user confirms.

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

**Ask the user to confirm the version before continuing.**

## Step 2: Pre-Release Checks

Run all of the following. If any command fails, stop immediately and show the full error output.

**PHP:**
```bash
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php
vendor/bin/phpstan analyse -c phpstan.neon
vendor/bin/phpunit
```

**TypeScript:**
```bash
npm run lint
npm run format
npm run typecheck
npm run build
```

**Ask the user to confirm all checks passed before continuing.**

## Step 3: Verify Changelogs

Check every `packages/*/CHANGELOG.md` and the root `CHANGELOG.md`:

- If a package has changes, ensure they are listed under `## [Unreleased]`.
- If a package has **no changes**, it must still have an `## [Unreleased]` section with a `- No changes.` entry. Add it if missing.

**Ask the user to confirm changelogs look correct before continuing.**

## Step 4: Merge Composer Configuration

```bash
vendor/bin/monorepo-builder merge
```

Commit any resulting changes to `composer.json` before proceeding.

**Ask the user to confirm before continuing.**

## Step 5: Run the Release

Before running the release command, **manually update each `packages/*/CHANGELOG.md`**: replace the `## [Unreleased]` heading with `## [VERSION] - YYYY-MM-DD`, using the version confirmed in Step 1 and today's date. Commit these changes.

**Ask the user to confirm all package changelogs have been updated before continuing.**

Then run:

```bash
vendor/bin/monorepo-builder release <VERSION>
```

Replace `<VERSION>` with the value confirmed in Step 1.

This command runs the following workers in order:
1. `UpdateReplaceReleaseWorker` — updates `replace` entries in `composer.json`
2. `SetCurrentMutualDependenciesReleaseWorker` — pins mutual package versions
3. `AddTagToChangelogReleaseWorker` — replaces `## [Unreleased]` with the version + date in the **root** `CHANGELOG.md` only
4. `NpmVersionWorker` — bumps all `package.json` versions via `npm version`
5. `TagVersionReleaseWorker` — creates the git tag
6. `PushTagReleaseWorker` — pushes the tag to origin
7. `SetNextMutualDependenciesReleaseWorker` — sets `dev-main` versions for continued development
8. `UpdateBranchAliasReleaseWorker` — updates the branch alias in `composer.json`

**Ask the user to confirm the release command completed successfully before continuing.**

## Step 6: Publish to NPM

For a **stable** release:
```bash
npm publish --access public --workspaces
```

For an **alpha** prerelease:
```bash
npm publish --access public --workspaces --tag alpha
```

For a **beta** prerelease:
```bash
npm publish --access public --workspaces --tag beta
```

For a **release candidate** prerelease:
```bash
npm publish --access public --workspaces --tag next
```

**Ask the user to confirm the publish succeeded before continuing.**

## Step 7: Create GitHub Releases

Create a GitHub release for each package repo using the `gh` CLI. The release tag and title must match the version from Step 1.

**Package repos** (from the subtree-splitter config):

| Package directory            | GitHub repo                            |
| ---------------------------- | -------------------------------------- |
| `packages/framework`         | `userfrosting/framework`               |
| `packages/sprinkle-core`     | `userfrosting/sprinkle-core`           |
| `packages/sprinkle-account`  | `userfrosting/sprinkle-account`        |
| `packages/sprinkle-admin`    | `userfrosting/sprinkle-admin`          |
| `packages/theme-pink-cupcake`| `userfrosting/theme-pink-cupcake`      |
| `packages/skeleton`          | `userfrosting/userfrosting`            |

For each package:

1. Extract the changelog entries for `VERSION` from `packages/*/CHANGELOG.md`.
2. Find the previous tag:
   ```bash
   git describe --tags --abbrev=0 VERSION^
   ```
3. Build the release notes in this format:
   ```markdown
   ## What's Changed

   - entry 1
   - entry 2

   **Full Changelog**: https://github.com/userfrosting/REPO/compare/PREV_VERSION...VERSION
   ```
4. Run:
   ```bash
   gh release create VERSION \
     --repo userfrosting/REPO \
     --title "VERSION" \
     --notes-file /tmp/release-notes-REPO.md
   ```
   Add `--prerelease` for alpha, beta, or RC versions.

**Ask the user to confirm all GitHub releases were created successfully before continuing.**

## Step 8: Post-Release Verification

Confirm the release succeeded:

```bash
git tag --list | grep <VERSION>
```

Report the tag name and let the user know the release is complete.
