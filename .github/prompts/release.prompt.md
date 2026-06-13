---
agent: agent
description: Guide through the UserFrosting 6 monorepo release process
---

# UserFrosting 6 Release Process

Execute a full release of the UserFrosting 6 monorepo autonomously. Follow each step in order. **Execute every step yourself — do not show commands and ask the user to run them.** If any step fails, abort immediately and report the full error output. Only stop to ask the user for input at Step 2 (version confirmation) and Step 9 (NPM login verification).

## Step 1 : Confirm branch and clean working directory

Verify the current branch is `6.0` by running `git branch --show-current`. If it is not `6.0`, abort immediately and report: 'Release must be run from the `6.0` branch. Currently on: BRANCH_NAME.'

## Step 2: Confirm the Version

Ask the user which version to release (e.g. `6.0.1`, `6.1.0-rc.1`).

Validate that the version matches the pattern `^6\.\d+\.\d+(-(alpha|beta|rc)\.\d+)?$`. If it does not match, reject it and re-prompt before continuing.

Also verify the tag does not already exist by running `git ls-remote --tags origin refs/tags/VERSION`. If the tag exists, reject the version and re-prompt with: "Tag VERSION already exists on the remote. Please provide a different version."

**Ask the user to confirm the version before continuing.**

## Step 3: Pre-Release Checks

Run all of the following. If any fails, stop immediately and show the full error.

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.php
vendor/bin/phpstan analyse -c phpstan.neon
vendor/bin/phpunit
npm run lint && npm run format && npm run typecheck && npm run build
```

## Step 4: Update Changelogs

For every `packages/*/CHANGELOG.md` and the root `CHANGELOG.md`, insert the new version section at the top. If the `Unreleased` section contains content, move it under the new version. If the `Unreleased` section is empty, add a placeholder change: '- No changes.'. Use the locale date in `YYYY-MM-DD` format for the release date.

Case A (has unreleased content):
BEFORE:
```
## [Unreleased]
- Fix foo

## [6.0.0] - 2024-01-01
```
AFTER:
```
## [Unreleased]

## [6.0.1] - 2024-06-01
- Fix foo

## [6.0.0] - 2024-01-01
```

Case B (no unreleased content):
BEFORE:
```
## [Unreleased]

## [6.0.0] - 2024-01-01
```
AFTER:
```
## [Unreleased]

## [6.0.1] - 2024-06-01
- No changes.

## [6.0.0] - 2024-01-01
```

Commit: `git commit -m "docs: update changelogs for VERSION"`

## Step 5: Merge Composer Configuration

```bash
vendor/bin/monorepo-builder merge
```

Check for changes with `git diff --exit-code composer.json`; if the exit code is non-zero, stage and commit: `git add composer.json && git commit -m "chore: merge composer.json changes"`

## Step 6: Run the Release

```bash
vendor/bin/monorepo-builder release VERSION
```

If this command fails after partially executing (e.g., some tags already pushed), do NOT retry automatically. Stop and report: "monorepo-builder release partially failed. Manually verify which tags were pushed with `git ls-remote --tags origin` before taking any further action."

## Step 7: GitHub Releases (Monorepo)

Create the GitHub release for the monorepo first, using the root `CHANGELOG.md` content. Write the extracted changelog section to `/tmp/uf_release_notes.md` using a heredoc or file-write tool call before running `gh release create`.

This will trigger the monorepo split action that syncs commits to package repos. The tag is already pushed in Step 6, so create the release directly. Use the `--prerelease` flag if the version contains `-alpha.`, `-beta.`, or `-rc.`.

```bash
gh release create VERSION --repo userfrosting/monorepo --title "VERSION" --notes /tmp/uf_release_notes.md [--prerelease]
```

## Step 8: GitHub Releases (Package Repos)

**Package repos:**

| Package directory             | GitHub repo                         | Changelog source                           |
| ----------------------------- | ----------------------------------- | ------------------------------------------ |
| `packages/framework`          | `userfrosting/framework`            | `packages/framework/CHANGELOG.md`          |
| `packages/sprinkle-core`      | `userfrosting/sprinkle-core`        | `packages/sprinkle-core/CHANGELOG.md`      |
| `packages/sprinkle-account`   | `userfrosting/sprinkle-account`     | `packages/sprinkle-account/CHANGELOG.md`   |
| `packages/sprinkle-admin`     | `userfrosting/sprinkle-admin`       | `packages/sprinkle-admin/CHANGELOG.md`     |
| `packages/theme-pink-cupcake` | `userfrosting/theme-pink-cupcake`   | `packages/theme-pink-cupcake/CHANGELOG.md` |
| `packages/skeleton`           | `userfrosting/UserFrosting`         | `packages/skeleton/CHANGELOG.md`           |

> **Critical:** The monorepo split syncs **commits** but does **not** push tags. Tags must be created manually on each package repo. Always get the `6.0` branch HEAD SHA from the **individual repo** — never assume it matches the monorepo SHA.

**1. Wait for the monorepo split to complete:**

Poll `gh run list` up to 10 times with a 60-second wait between checks (total ~10 minutes). If the workflow has not completed with `✓` after 10 attempts, stop and report: 'Monorepo split workflow has not completed after 10 minutes. Check https://github.com/userfrosting/monorepo/actions before proceeding manually.'

```bash
gh run list --repo userfrosting/monorepo --workflow=Monorepo.yml --limit 3
```

To confirm the correct run, match the workflow run's trigger SHA against the release tag SHA obtained from `gh api repos/userfrosting/monorepo/git/refs/tags/VERSION --jq ".object.sha"`. Only proceed if they match and the run status is `✓`. If the run with the matching SHA has a failed status, stop and report: 'Monorepo split workflow run for this release has failed. Check https://github.com/userfrosting/monorepo/actions before proceeding manually.'

**2. For each repo in the table above, sequentially, one after another without stopping between repos unless an error occurs:**

Get the correct SHA:
```bash
gh api repos/userfrosting/REPO/branches/6.0 --jq '.commit.sha'
```

Create the tag:
```bash
gh api --method POST repos/userfrosting/REPO/git/refs \
  -f ref="refs/tags/VERSION" -f sha="SHA"
```

Generate the release notes by extracting the new version section from the package's `CHANGELOG.md` (e.g. `packages/framework/CHANGELOG.md`) and write it to `/tmp/uf_release_notes.md`. You can use a heredoc or file-write tool call to create the file with the extracted content.

Create the release:
```bash
gh release create VERSION --repo userfrosting/REPO --title "VERSION" --notes /tmp/uf_release_notes.md [--prerelease] --target 6.0
```

## Step 9: NPM Publish

> **Requires manual login first.** `npm login` uses browser-based authentication and **cannot be completed by the agent**. Before running this step, ask the user to verify they are logged in with `npm whoami`. If not logged in, ask them to run `npm login` manually.

Once logged in, publish all packages using the `-workspaces` option. Select the publish command based on the version suffix: if the version contains `-alpha.`, use the Alpha command; if it contains `-beta.`, use the Beta command; if it contains `-rc.`, use the RC command; if there is no suffix, use the Stable command.

- **Stable**: `npm publish --access public --workspaces`
- **Alpha**: `npm publish --access public --workspaces --tag alpha`
- **Beta**: `npm publish --access public --workspaces --tag beta`
- **RC**: `npm publish --access public --workspaces --tag next`

> The `userfrosting` (skeleton) package is `private` and will be skipped — this is expected.

If `npm publish` fails with an 'over existing version' error for a specific workspace package, skip that package and continue publishing remaining packages. Report all skipped packages at the end.
