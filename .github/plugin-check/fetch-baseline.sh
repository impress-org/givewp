#!/usr/bin/env bash
#
# Downloads the baseline.json that the Plugin Check workflow wrote for a branch: the
# plugin-check-report artifact of an earlier run. That run checked the branch as it really is, so
# the baseline always matches it. Nothing is committed, so two branches worked on at the same time
# can never leave a stale baseline behind. See docs/testing.md#plugin-check.
#
# Usage:
#   fetch-baseline.sh [--sha <commit>] [--wait <seconds>] [--skip-run <run-id>] <branch> [<output-file>]
#
# Runs are tried in this order, and the first one with the artifact wins:
#   1. runs for --sha (a pull request's base commit), when given. With --wait, a run for that
#      commit that is still going is waited for, up to that many seconds;
#   2. the latest runs on <branch>, newest first.
# --skip-run leaves out one run, so a run never compares with itself.
#
# <output-file> defaults to baseline.json next to this script, which ratchet.sh reads by default
# and git ignores.
#
# Prints "<run-id> <run-url> <match>". <match> is "exact" when the run is for --sha (or no --sha
# was given), and "older" when --sha was given but only an older run on the branch had a baseline.
#
# Exit status:
#   0  baseline written
#   1  no run has the artifact (none finished yet, or all expired)
#   2  the lookup itself failed (GitHub API or download error), so "no baseline" is not known
#
# Needs the GitHub CLI, signed in (GH_TOKEN in CI) with read access to Actions.

set -euo pipefail

sha=""
wait_for=0
skip_run=""
while [ $# -gt 0 ]; do
    case "$1" in
        --sha) sha=${2:?"--sha needs a commit"}; shift 2 ;;
        --wait) wait_for=${2:?"--wait needs seconds"}; shift 2 ;;
        --skip-run) skip_run=${2:?"--skip-run needs a run id"}; shift 2 ;;
        *) break ;;
    esac
done

branch=${1:?"Usage: $0 [--sha <commit>] [--wait <seconds>] [--skip-run <run-id>] <branch> [<output-file>]"}
output=${2:-"$(cd "$(dirname "$0")" && pwd)/baseline.json"}

lookup_failed() {
    echo "Could not look up Plugin Check runs: $1" >&2
    exit 2
}

repo=${GITHUB_REPOSITORY:-$(gh repo view --json nameWithOwner --jq .nameWithOwner)} || lookup_failed "no repository"
runs="repos/$repo/actions/workflows/plugin-check.yml/runs"

# The run for the base commit may still be going: a pull request opened right after a merge starts
# at the same time as the push run on the target branch. Waiting for it beats comparing with an
# older run.
if [ -n "$sha" ] && [ "$wait_for" -gt 0 ]; then
    deadline=$(($(date +%s) + wait_for))
    while :; do
        going=$(gh api "$runs?head_sha=$sha&per_page=20" \
            --jq "[.workflow_runs[] | select(.status != \"completed\" and (.id | tostring) != \"$skip_run\")] | length") \
            || lookup_failed "runs for $sha"
        [ "$going" -gt 0 ] || break
        if [ "$(date +%s)" -ge "$deadline" ]; then
            echo "The run for $sha is still going after $wait_for seconds; trying older runs." >&2
            break
        fi
        sleep 30
    done
fi

# "<run-id> <run-url> <head-sha>" lines, best first. Captured, not streamed, so a failed API call
# stops here instead of looking like an empty list.
candidates=""
if [ -n "$sha" ]; then
    candidates=$(gh api "$runs?status=completed&head_sha=$sha&per_page=10" \
        --jq '.workflow_runs[] | "\(.id) \(.html_url) \(.head_sha)"') || lookup_failed "runs for $sha"
fi
on_branch=$(gh api "$runs?status=completed&branch=$branch&per_page=20" \
    --jq '.workflow_runs[] | "\(.id) \(.html_url) \(.head_sha)"') || lookup_failed "runs on $branch"
candidates+=${candidates:+$'\n'}$on_branch

tried=" "
tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT

while read -r id url head; do
    [ -n "$id" ] || continue
    [ "$id" != "$skip_run" ] || continue
    case "$tried" in *" $id "*) continue ;; esac
    tried+="$id "

    # A run that broke before the report job, or whose artifact expired, has nothing to give.
    found=$(gh api "repos/$repo/actions/runs/$id/artifacts?name=plugin-check-report" \
        --jq '[.artifacts[] | select(.expired | not)] | length') || lookup_failed "artifacts of run $id"
    [ "$found" -gt 0 ] || continue

    rm -rf "$tmp/report"
    gh run download "$id" --repo "$repo" --name plugin-check-report --dir "$tmp/report" > /dev/null \
        || lookup_failed "download of the artifact of run $id"
    [ -s "$tmp/report/baseline.json" ] || continue

    match=exact
    if [ -n "$sha" ] && [ "$head" != "$sha" ]; then
        match=older
    fi

    mkdir -p "$(dirname "$output")"
    cp "$tmp/report/baseline.json" "$output"
    echo "$id $url $match"
    exit 0
done <<< "$candidates"

echo "No Plugin Check run on $branch${sha:+ or $sha} has a baseline." >&2
exit 1
