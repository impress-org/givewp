#!/usr/bin/env bash
#
# Downloads the baseline.json that the Plugin Check workflow wrote for a branch: the
# plugin-check-report artifact of an earlier run. That run checked the branch as it really is, so
# the baseline always matches it. Nothing is committed, so two branches worked on at the same time
# can never leave a stale baseline behind. See docs/testing.md#plugin-check.
#
# Usage:
#   fetch-baseline.sh [--sha <commit>] [--skip-run <run-id>] <branch> [<output-file>]
#
# Runs are tried in this order, and the first one with the artifact wins:
#   1. runs for --sha (the pull request's base commit), when given;
#   2. the latest runs on <branch>, newest first.
# --skip-run leaves out one run, so a run never compares with itself.
#
# <output-file> defaults to baseline.json next to this script, which ratchet.sh reads by default
# and git ignores. Prints "<run-id> <run-url>" of the run it used. Exits 1 when no run has the
# artifact (none finished yet, or all expired).
#
# Needs the GitHub CLI, signed in (GH_TOKEN in CI) with read access to Actions.

set -euo pipefail

sha=""
skip_run=""
while [ $# -gt 0 ]; do
    case "$1" in
        --sha) sha=${2:?"--sha needs a commit"}; shift 2 ;;
        --skip-run) skip_run=${2:?"--skip-run needs a run id"}; shift 2 ;;
        *) break ;;
    esac
done

branch=${1:?"Usage: $0 [--sha <commit>] [--skip-run <run-id>] <branch> [<output-file>]"}
output=${2:-"$(cd "$(dirname "$0")" && pwd)/baseline.json"}
repo=${GITHUB_REPOSITORY:-$(gh repo view --json nameWithOwner --jq .nameWithOwner)}
runs="repos/$repo/actions/workflows/plugin-check.yml/runs?status=completed"

# Run ids to try, best first. A run can show up twice (once by commit, once by branch); the second
# try is skipped below.
candidates() {
    if [ -n "$sha" ]; then
        gh api "$runs&head_sha=$sha&per_page=10" --jq '.workflow_runs[] | "\(.id) \(.html_url)"'
    fi
    gh api "$runs&branch=$branch&per_page=20" --jq '.workflow_runs[] | "\(.id) \(.html_url)"'
}

tried=" "
tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT

while read -r id url; do
    [ -n "$id" ] || continue
    [ "$id" != "$skip_run" ] || continue
    case "$tried" in *" $id "*) continue ;; esac
    tried+="$id "

    # A run that broke before the report job, or whose artifact expired, has nothing to give.
    rm -rf "$tmp/report"
    gh run download "$id" --repo "$repo" --name plugin-check-report --dir "$tmp/report" > /dev/null 2>&1 || continue
    [ -s "$tmp/report/baseline.json" ] || continue

    mkdir -p "$(dirname "$output")"
    cp "$tmp/report/baseline.json" "$output"
    echo "$id $url"
    exit 0
done < <(candidates)

echo "No Plugin Check run on $branch${sha:+ or $sha} has a baseline." >&2
exit 1
