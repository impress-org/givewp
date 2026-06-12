# Releasing GiveWP

This document outlines the process for releasing new versions of GiveWP.

## During development

Releases are prepared continuously as features and fixes are merged:

* **Changelog entries** — every PR adds an entry with `composer run changelog:add`, which drops a YAML file into `./changelog`. These are compiled into `readme.txt` at release time.
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
   3. Compiles the pending entries in `./changelog` into the `readme.txt` changelog

   Options: `--date <date>` sets the changelog date (defaults to today); `--dry-run` previews without writing.

4. Review the diff carefully — version strings, replaced TBD tags, and the new changelog entry.
5. Validate `readme.txt` with the [WordPress readme validator](https://wordpress.org/plugins/developers/readme-validator/) and preview it on [WPReadme.com](https://wpreadme.com/).
6. Run the test suite (`composer test`) and let CI pass on the release branch.
7. Open a PR for the release branch against `master` and get it reviewed.

## Publishing

1. Merge the release branch into `master`.
2. Manually merge the release branch back into `develop` — this keeps `develop` in sync with the version bumps, compiled changelog, and replaced TBD tags from the release.
3. Draft a new GitHub release using the version as the tag and title, with the target branch set to `master`. Generate release notes, double-check everything, and publish.

## Hotfixes

For urgent fixes that can't wait for the normal release cycle, branch off `master` instead of `develop` (`hotfix/x.y.z`). The rest of the process is the same — run `composer run release:prep`, review, merge to `master`, and publish. Afterwards, merge `master` back into `develop` so the fix isn't lost in the next release.

## Post-release verification

* Verify the new version is available on WordPress.org and installs cleanly.
* Confirm automatic updates work from the previous version.
* Monitor the [support forums](https://wordpress.org/support/plugin/give) and error tracking for new issues over the next 24–48 hours.

## Notes

* The release tooling lives in `bin/` and is built on [stellarwp/pup](https://github.com/stellarwp/pup) and [@stellarwp/changelogger](https://github.com/stellarwp/changelogger). Its own tests can be run with `composer run release:test`.
* Patch releases follow the same process — the only difference is scope and testing focus (regressions around the fix).
