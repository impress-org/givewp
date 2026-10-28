# Releasing GiveWP

This document outlines the process for releasing new versions of GiveWP.

## During development

Releases are prepared continuously as features and fixes are merged:

* **Changelog entries** — every PR adds an entry with `composer run changelog:add`, which drops a YAML file into `./changelog`. These are compiled into `readme.txt` and `changelog.txt` at release time.  WordPress [recommends](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/#file-size) keeping the latest version's changelog in `readme.txt` and the full history in `changelog.txt`. For Give, this means we reset the `readme.txt` changelog during major version releases.
* **Docblocks** — new or changed code uses `@since TBD`, which is replaced with the real version number at release time.

## Preparing a release

Normal releases follow a [gitflow](https://nvie.com/posts/a-successful-git-branching-model/) pattern: features and fixes land in `develop`, and releases are cut from there.

1. Make sure everything intended for the release is merged into `develop` and CI is green.
2. Create a release branch off `develop` (`release/x.y.z`).
3. Run the release prep command with the new version number:

   ```bash
   composer run release:prep 4.16.0
   ```

   This runs the full version-bump pipeline:
   1. Updates all version strings — `GIVE_VERSION` and the `Version:` plugin header in `give.php`, and `Stable tag:` in `readme.txt`
   2. Replaces all `@since TBD` / `@deprecated TBD` placeholders with the new version
   3. Compiles the pending entries in `./changelog` into the `readme.txt` and `changelog.txt` changelog

   Options: `--date <date>` sets the changelog date (defaults to today); `--dry-run` previews without writing.

4. Check the plugin requirements — `release:prep` does **not** update these:
   * `Requires at least:` and `Requires PHP:` in the `give.php` plugin header
   * `Requires at least:`, `Tested up to:`, and `Requires PHP:` in `readme.txt` (especially `Tested up to:` when a new WordPress version has shipped)
   * The `= Minimum Requirements =` section in `readme.txt`
5. Review the diff carefully — version strings, replaced TBD tags, and the new changelog entry.
6. Validate `readme.txt` with the [WordPress readme validator](https://wordpress.org/plugins/developers/readme-validator/) and preview it on [WPReadme.com](https://wpreadme.com/).
7. Run the test suite (`composer test`) and let CI pass on the release branch.
8. Build a release candidate ZIP for QA by running the [Generate Plugin Zip](.github/workflows/generate-zip.yml) GitHub Action against the release branch. Attach the ZIP to the release ticket and wait for QA approval.
9. Open a PR for the release branch against `master` and get it reviewed.

## Preparing a release with the Release Prep workflow

Step 3 above can also run in CI, through the [Release Prep](.github/workflows/release-prep.yml) GitHub Action. It runs the same three steps as `composer run release:prep`, with the same `.puprc`, changelog entries and changelogger configuration, through the shared [release-prep action](https://github.com/stellarwp/plugin-toolbox/tree/main/.github/actions/release-prep) in stellarwp/plugin-toolbox. It then commits the result to the release branch as one commit, `Prepare release/x.y.z (x.y.z)`. Nothing needs to be installed locally.

The NX1 release dashboard uses this workflow for a release's prepare step. To run it by hand once the release branch exists:

```bash
gh workflow run release-prep.yml -f branch=release/4.16.0 -f version=4.16.0
```

* `-f date=YYYY-MM-DD` sets the changelog date; it defaults to today, in UTC.
* The version must be three or four numbers (`4.16.0`, `4.16.0.1`). A pre-release such as `4.16.0-beta.1` is refused before anything changes.
* Running it again for the same version changes nothing and commits nothing.
* The preparation commit does not trigger CI on its own: GitHub does not run workflows for commits made with a workflow's `GITHUB_TOKEN`. Opening the pull request, or any push after it, runs every check on the whole branch, so the missing run only matters when the preparation is the last commit on the branch: the pull request then keeps the checks of the commit before it. A push after it, even an empty commit, runs them.
* `branch` is the release branch to prepare, `release/*` or `hotfix/*`. It has to exist already (step 2 above). Its version has to be lower than or equal to the one you pass: preparing `4.16.0` on a branch already at `4.17.0` is refused.

After it runs, continue from step 4 above. It works the same for a `hotfix/x.y.z` branch.

## Publishing

1. Merge the release branch into `master`.
2. Manually merge the release branch back into `develop` — this keeps `develop` in sync with the version bumps, compiled changelog, and replaced TBD tags from the release. If there are merge conflicts, resolve them and send a fresh release candidate back through QA.
3. Draft a new GitHub release using the version as the tag and title, with the target branch set to `master`. Generate release notes, double-check everything, and publish.
4. Monitor the release GitHub Action and the Slack notifications — publishing the release kicks off the deploy to WordPress.org and pushes the pot file to the [translations server](https://translations.stellarwp.com/).

## Hotfixes

For urgent fixes that can't wait for the normal release cycle, branch off `master` instead of `develop` (`hotfix/x.y.z`). The rest of the process is the same — run `composer run release:prep`, review, merge to `master`, and publish. Afterwards, merge `master` back into `develop` so the fix isn't lost in the next release.

## Post-release verification

* Verify the new version is available on WordPress.org and installs cleanly.
* Confirm automatic updates work from the previous version.
* Monitor the [support forums](https://wordpress.org/support/plugin/give) and error tracking for new issues over the next 24–48 hours.

## Adopting this flow in an add-on

An add-on gets the same preparation once it has the same three files as core and its changelog
history is in the same format. Work on a branch of the add-on, from an add-on checkout next to
this repository, because the conversion command is in this repository. impress-org/give-recurring#1253
is an example to follow.

1. A `.puprc` with `paths.versions` (plugin header, version constant, `Stable tag` in
   `readme.txt`) and `checks.tbd.dirs`. The shape is in the
   [release-prep action's README](https://github.com/stellarwp/plugin-toolbox/tree/main/.github/actions/release-prep#what-the-repository-needs).
2. A `changelogger` section in `package.json`: copy core's, with `files` pointing at the
   add-on's `readme.txt` and `"strategy": "stellarwp-readme"`, and a `changelog/` directory
   with a `.gitkeep`. If `readme.txt` already lists the changes of an unreleased version under
   a `= x.y.z: TBD =` header, move each line to a YAML file in `changelog/`
   (`npx @stellarwp/changelogger add`) and delete the block: the workflow writes those entries
   at release time.
3. `.github/workflows/release-prep.yml`, copied from core as it is.
4. The changelog history converted. The changelogger writes entries as
   `= [2.20.3] 2026-10-14 =` and `* Fix - …`; an add-on's history is in Give's earlier form,
   `= 2.20.3: October 14th, 2026 =` and `* Fix: …`, so convert it once, or the file carries both
   formats from the next release on. From the add-on's root:

   ```bash
   node ../give/bin/convert-changelog-format.js readme.txt
   ```

   It rewrites the changelog section, checks the result against the original before writing,
   and prints both counts, for example
   `116 header(s) and 369 bullet(s) converted; verified against the original: 126 version(s) and 372 entries match`.
   When something does not verify, nothing is written and the problems are listed. Commit the
   conversion on its own, so the reviewer can skim it.
5. The same check, with the original taken from git, to quote in the pull request:

   ```bash
   git show HEAD~1:readme.txt > /tmp/readme-before.txt
   node ../give/bin/convert-changelog-format.js --verify /tmp/readme-before.txt readme.txt
   ```

6. The workflow can only be dispatched once it is on the add-on's default branch. After the
   merge, run it once on a throwaway branch
   (`gh workflow run release-prep.yml -f branch=<branch> -f version=<next>`) and check the
   `Prepare <branch> (<version>)` commit.

## Notes

* The release tooling lives in `bin/` and is built on [stellarwp/pup](https://github.com/stellarwp/pup) and [@stellarwp/changelogger](https://github.com/stellarwp/changelogger). Its own tests can be run with `composer run release:test`.
* Patch releases follow the same process — the only difference is scope and testing focus (regressions around the fix).
