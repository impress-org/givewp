# Pup Integration Plan

Plan for finishing the migration of GiveWP's packaging pipeline to [`stellarwp/pup`](https://github.com/stellarwp/pup) and unifying with the rest of the StellarWP `bot package` flow in [`stellarwp/jenkins-scripts`](https://github.com/stellarwp/jenkins-scripts).

## Current state (after this PR)

- `composer pup <command>` works locally — pinned to pup `1.3.9`, phar auto-downloads to `bin/pup.phar` on first use.
- `.puprc` is the canonical source for version detection and zip name. Jenkins-scripts already reads it for `bot package --plugin=givewp`.
- `composer pup zip` produces a working `give.<version>.zip` end-to-end (composer install → strauss → copy-fonts → npm build → i18n → zip).
- Strauss now produces an optimized classmap autoloader (`composer dump-autoload --optimize`) for production zips.
- Two latent PSR-4 violations in `src/` were fixed as a prerequisite for `--optimize`.
- Build-time speed flags applied: `--prefer-dist`, `--no-progress`, `--prefer-offline` (npm), `--no-fund` (npm).
- `composer.json` `process-timeout: 0` removes the 300s timeout so long pipelines don't false-fail.

### What still routes through the legacy path

`bot package --plugin=givewp` in Slack works today but goes through a hard-coded special case in `jenkins-scripts/includes/Commands/Package/ProductPlugin.php` that dispatches `.github/workflows/generate-zip.yml`. That workflow delegates to `impress-org/givewp-github-actions/.github/workflows/generate-zip.yml@master`, which runs a bespoke `composer install → npm build → rsync .distignore → zip` pipeline — pup is not in that picture.

`package.json` still carries `_zipname` and `_version_files` for backwards compat with the legacy workflow.

## Goal

Drive every zip — local, CI, Slack-triggered — through `pup zip`, and remove the GiveWP special case from `jenkins-scripts` so Give uses the same default path as other StellarWP plugins.

## Phase 1 — Pup-driven CI (the meaningful next change)

**Where the work happens:** `impress-org/givewp-github-actions` repo (the shared workflow), not this repo.

**What to change in `.github/workflows/generate-zip.yml@master`:**

Replace the bespoke build steps (composer install, npm build, version bump, i18n, rsync, zip) with a single pup invocation. Suggested shape:

```yaml
- name: Checkout
  uses: actions/checkout@v4
  with:
    ref: ${{ inputs.ref }}
    submodules: recursive

- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: '7.4'
    tools: composer

- name: Setup Node
  uses: actions/setup-node@v4
  with:
    node-version: 18
    cache: npm

- name: Build & zip via pup
  run: composer pup zip --no-clone
```

Pass `--no-clone` because the runner already checked out the correct ref — re-cloning inside pup is wasted network and disk.

Move the Slack notification, S3 upload, and artifact upload steps that the shared workflow currently performs to wrap around the pup step (they don't go inside pup).

**Acceptance:**
- Workflow run produces an identical zip to what the legacy pipeline produces (compare file lists with `unzip -l`).
- Version bump for non-production builds: handled by pup automatically (it appends git hash to dev versions when configured — verify with a non-`production: true` run).
- `.pot` generation still ships in the zip. Pup runs `wp i18n make-pot` during the i18n phase; ensure `wp-cli` is available in the runner.
- Slack/S3/artifact upload paths still fire.

**Risks:**
- The `.distignore` already exists and is pup-compatible, but pup's i18n step uses different exclusion semantics. Compare a generated zip against a legacy zip to confirm no files appear/disappear.
- Pup expects `composer pup` to be runnable on the cloned ref. The composer script (this PR) is now in `develop`, so any branch built must be merged or rebased on top of it. Document this in the workflow's release notes.
- If composer on the runner is older than 2.5, `--no-audit` will already be absent (it's not in our build commands). No action.

## Phase 2 — Strip GiveWP's hardcoded path from `jenkins-scripts`

**Where the work happens:** `stellarwp/jenkins-scripts`.

**What to change in `includes/Commands/Package/ProductPlugin.php`:**

Delete the `elseif ( $this->plugin === 'givewp' )` block. Give will then fall through to the generic `$has_mt_jenkins` path that runs `gh workflow run zip.yml --repo {$this->org}/{$this->plugin}` against any workflow named `zip.yml`.

To match the lookup, either:
- (a) Rename `.github/workflows/generate-zip.yml` to `.github/workflows/zip.yml` in this repo (the lookup tries `zip.yml`, then `generate-zip.yml`, then `zip-generator.yml` — but the dispatch path uses `zip.yml` literally), **or**
- (b) Update the generic path in `jenkins-scripts` to dispatch whichever workflow file was found during the lookup. (b) is more invasive but doesn't churn this repo's filename.

**Acceptance:**
- `bot package --plugin=givewp` from Slack triggers `generate-zip.yml` (or `zip.yml`) and posts results back to the channel exactly as before.
- The `elseif ( $this->plugin === 'givewp' )` block is gone.

**Risks:**
- Renaming the workflow file (option a) breaks any saved Slack shortcut, GitHub Action UI bookmark, or scheduled trigger that references `generate-zip.yml` by name. Check for references before renaming.
- The generic path passes a slightly different input set than the GiveWP special case. Diff the inputs (`ref`, `slack_channel`, `slack_thread`, `production`) against what the generic path provides (`ref`, `slack_channel`, `slack_thread`, `production` or `final` depending on workflow shape) — confirm `generate-zip.yml`'s input schema matches.

## Phase 3 — Cleanup

**Where the work happens:** this repo.

- Remove `package.json._zipname` and `package.json._version_files` (now redundant — `.puprc` is canonical and jenkins-scripts already prefers it).
- Remove the `composer pup` PATH-strip workaround once the pipeline no longer relies on running pup *via composer scripts* (CI invokes pup directly per Phase 1; local devs can be told to invoke `php bin/pup.phar` directly too). This is purely cosmetic.
- Consider deleting `bin/strauss-installar.sh` and `bin/strauss-version.txt` if you can replace strauss-via-bash with strauss-via-composer — out of scope but worth tracking.

**Acceptance:**
- `package.json` no longer has `_zipname` / `_version_files` keys.
- A clean checkout + `composer pup zip` still produces the right zip.
- A test run of `bot package --plugin=givewp --branch=develop` from Slack still succeeds.

## Out of scope

- Replacing strauss with a different vendor-prefixing tool.
- Migrating `release.yml` (the WP.org deploy workflow) to pup. It runs on tag publish, not branch zip, and uses `10up/action-wordpress-plugin-deploy` — keep separate.
- Changes to the `composer pup` script for local dev. It works; revisit only if devs hit issues.

## Decision log

- **Pinned to pup 1.3.9** (latest at integration time). Bumping is a single-line change in the `pup` composer script.
- **Kept `--optimize` for production zips.** Surfaces PSR-4 violations as build warnings — caught two latent bugs in `src/` already.
- **Did NOT use `--classmap-authoritative`.** WordPress plugin code can autoload classes via dynamic names; `-a` would break that.
- **Did NOT drop `--no-clone` to be the local default.** Locally it nukes dev deps from `vendor/`. Use it only in CI.
