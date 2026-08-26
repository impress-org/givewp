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

## Notes

* The release tooling lives in `bin/` and is built on [stellarwp/pup](https://github.com/stellarwp/pup) and [@stellarwp/changelogger](https://github.com/stellarwp/changelogger). Its own tests can be run with `composer run release:test`.
* Patch releases follow the same process — the only difference is scope and testing focus (regressions around the fix).
