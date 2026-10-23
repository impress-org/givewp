#!/usr/bin/env bash
#
# Plugin Check ratchet. Counts ERROR and WARNING results per rule code and compares them with a
# baseline: the counts of an earlier run, fetched with fetch-baseline.sh. Only a rule with more
# ERRORS than the baseline fails. More warnings only add a notice. Fewer of either passes. With no
# baseline at all, nothing is compared and the check passes with a warning. See
# docs/testing.md#plugin-check.
#
# Usage:
#   ratchet.sh [--comment-file <path>] <results-file>   compare the results with the baseline
#   ratchet.sh --update <results-file>                  write the baseline from the results
#
# <results-file> is the output of `wp plugin check give --format=json`: for each file, a
# "FILE: <path>" line and then a line holding a JSON array of that file's results.
#
# --comment-file writes a short Markdown summary for the pull request comment. The same summary
# goes to the job summary when GITHUB_STEP_SUMMARY is set.
#
# Environment (all optional):
#   PLUGIN_CHECK_BASELINE  baseline path (default: baseline.json next to this script, where
#                          fetch-baseline.sh puts it)
#   BASELINE_LABEL         what the baseline is, for the comment's column (default: "Baseline")
#   BASELINE_URL           link to the run the baseline came from
#   COMMENT_BADGE          image URL shown under the status line
#   RUN_URL                link to the workflow run
#   ARTIFACT_URL           link to the uploaded results and fresh baseline

set -euo pipefail

# The pull request comment must stay short. Everything else is behind the links.
MAX_COMMENT_LINES=15
MAX_RULES_LISTED=5

# Hidden line that marks the sticky comment. The workflow searches for it, so no other comment
# is ever replaced. Keep it in sync with the workflow.
COMMENT_MARKER='<!-- givewp-plugin-check -->'

update=false
comment_file=""
while [ $# -gt 0 ]; do
    case "$1" in
        --update) update=true; shift ;;
        --comment-file) comment_file=${2:?"--comment-file needs a path"}; shift 2 ;;
        *) break ;;
    esac
done

results=${1:?"Usage: $0 [--update | --comment-file <path>] <results-file>"}
baseline=${PLUGIN_CHECK_BASELINE:-"$(cd "$(dirname "$0")" && pwd)/baseline.json"}

if [ ! -f "$results" ]; then
    echo "::error title=Plugin Check::Results file not found: $results"
    exit 1
fi

# A results file that holds anything else (an HTML database error, a PHP fatal) means the check
# itself broke. Without this guard that file would count as "no results" and pass.
unexpected=$({ grep -v -E '^(FILE: |\[|Success:)' "$results" || true; } | grep -v -E '^[[:space:]]*$' | head -n 1 | cut -c 1-200 || true)
if [ -n "$unexpected" ]; then
    echo "::error title=Plugin Check::The results file is not Plugin Check output, so the check did not run. First unexpected line: $unexpected"
    exit 1
fi

# A clean run prints "Success: ..." and a run with results prints "FILE: ..." lines. An empty
# file means the command failed with its error on stderr, which the results file does not hold.
if ! grep -q -E '^(FILE: |Success:)' "$results"; then
    echo "::error title=Plugin Check::The results file has no Plugin Check output, so the check did not run."
    exit 1
fi

# Only the JSON lines matter. grep exits 1 when there are none (a clean run), which is fine.
counts=$({ grep '^\[' "$results" || true; } | jq -s -S '
    def per_code($type):
        map(select(.type == $type))
        | group_by(.code)
        | map({ key: .[0].code, value: length })
        | from_entries;
    (add // []) as $all
    | { errors: ($all | per_code("ERROR")), warnings: ($all | per_code("WARNING")) }
')

if [ "$update" = true ]; then
    printf '%s\n' "$counts" > "$baseline"
    echo "Wrote $baseline: $(jq '.errors | length' <<< "$counts") error codes, $(jq '.warnings | length' <<< "$counts") warning codes"
    exit 0
fi

# No earlier run to compare with: report the counts and pass. Failing here would block every pull
# request whenever the target branch has no finished run (its first run, or expired artifacts).
if [ ! -f "$baseline" ]; then
    echo "::warning title=Plugin Check::No baseline to compare with ($baseline), so nothing was compared. Run the workflow on the target branch to make one."
    baseline=$(mktemp)
    jq -n '{errors: {}, warnings: {}}' > "$baseline"
    no_baseline=true
else
    no_baseline=false
fi

# One tab-separated row per kind and rule code found in either file:
# kind, code, baseline count, current count. Rules that went up come first, errors before
# warnings, biggest increase first, so the comment can show the top few.
rows=$(jq -r -n --argjson now "$counts" --slurpfile base "$baseline" '
    $base[0] as $base
    | [
        ("errors", "warnings") as $kind
        | ((($now[$kind] // {}) + ($base[$kind] // {})) | keys[]) as $code
        | { kind: $kind, code: $code, base: ($base[$kind][$code] // 0), now: ($now[$kind][$code] // 0) }
      ]
    | sort_by((if .now > .base then 0 else 1 end), (if .kind == "errors" then 0 else 1 end), -(.now - .base))
    | .[]
    | [.kind, .code, .base, .now]
    | @tsv
')

failed=false
errors_base=0
errors_now=0
warnings_base=0
warnings_now=0
went_down=0
went_up=0
errors_up=0
up_list=""

while IFS=$'\t' read -r kind code allowed found; do
    [ -n "$code" ] || continue

    if [ "$kind" = errors ]; then
        errors_base=$((errors_base + allowed))
        errors_now=$((errors_now + found))
        label=""
    else
        warnings_base=$((warnings_base + allowed))
        warnings_now=$((warnings_now + found))
        label=" warning"
    fi

    # With no baseline, only the totals mean anything.
    [ "$no_baseline" = false ] || continue

    if [ "$found" -gt "$allowed" ]; then
        went_up=$((went_up + 1))
        if [ "$went_up" -le "$MAX_RULES_LISTED" ]; then
            up_list+="${up_list:+, }\`$code\`$label (+$((found - allowed)))"
        fi
        if [ "$kind" = errors ]; then
            failed=true
            errors_up=$((errors_up + 1))
            echo "::error title=Plugin Check::$code has $found errors, the baseline has $allowed. The full list is in the plugin-check-results artifact."
        else
            echo "::notice title=Plugin Check::$code has $found warnings, the baseline has $allowed. Warnings do not fail the check."
        fi
    elif [ "$found" -lt "$allowed" ]; then
        went_down=$((went_down + 1))
        echo "::notice title=Plugin Check::$code ($kind) went down from $allowed to $found."
    fi
done <<< "$rows"

# "+3", "-2" or "±0".
change() {
    local diff=$(($1 - $2))
    if [ "$diff" -gt 0 ]; then echo "+$diff"; elif [ "$diff" -lt 0 ]; then echo "$diff"; else echo "±0"; fi
}

# The totals can go down while a rule goes up, so the status names the rules, not the totals.
if [ "$no_baseline" = true ]; then
    status="⚠️ Plugin Check ran, but there was no baseline to compare with"
elif [ "$failed" = true ]; then
    if [ "$errors_up" -eq 1 ]; then
        status="❌ Plugin Check failed: 1 rule has more errors than the baseline allows"
    else
        status="❌ Plugin Check failed: $errors_up rules have more errors than the baseline allows"
    fi
else
    status="✅ Plugin Check passed"
fi

run_url=${RUN_URL:-}
if [ -z "$run_url" ] && [ -n "${GITHUB_RUN_ID:-}" ]; then
    run_url="${GITHUB_SERVER_URL}/${GITHUB_REPOSITORY}/actions/runs/${GITHUB_RUN_ID}"
fi
if [ -n "${ARTIFACT_URL:-}" ]; then
    links="📎 [Full results and new baseline](${ARTIFACT_URL})"
    [ -z "$run_url" ] || links+=" · [Run]($run_url)"
elif [ -n "$run_url" ]; then
    links="📎 [Run]($run_url)"
else
    links="📎 Local run, see $results"
fi

body="$COMMENT_MARKER"$'\n'"$status"$'\n\n'
if [ -n "${COMMENT_BADGE:-}" ]; then
    body+="![Plugin Check finished](${COMMENT_BADGE})"$'\n\n'
fi
# The column is named after where the baseline came from ("Target branch", "Previous run"), and
# links to that run when known.
column=${BASELINE_LABEL:-Baseline}
if [ -n "${BASELINE_URL:-}" ]; then
    column="[$column](${BASELINE_URL})"
fi
if [ "$no_baseline" = true ]; then
    body+="|          | Now |"$'\n'
    body+="|:--|--:|"$'\n'
    body+="| Errors   | $errors_now |"$'\n'
    body+="| Warnings | $warnings_now |"$'\n\n'
else
    body+="|          | Now | $column | Change |"$'\n'
    body+="|:--|--:|--:|:--|"$'\n'
    body+="| Errors   | $errors_now | $errors_base | $(change "$errors_now" "$errors_base") |"$'\n'
    body+="| Warnings | $warnings_now | $warnings_base | $(change "$warnings_now" "$warnings_base") |"$'\n\n'
fi
if [ "$went_up" -gt "$MAX_RULES_LISTED" ]; then
    body+="Went up (top $MAX_RULES_LISTED of $went_up): $up_list"$'\n'
elif [ "$went_up" -gt 0 ]; then
    body+="Went up: $up_list"$'\n'
fi
if [ "$went_down" -gt 0 ]; then
    body+="Rules that went down: $went_down."$'\n'
fi
# A blank line separates the notes above from the links, when there are notes.
if [ "$went_up" -gt 0 ] || [ "$went_down" -gt 0 ]; then
    body+=$'\n'
fi
body+="$links"

# Hard cap: whatever happens above, the comment never grows past MAX_COMMENT_LINES.
body=$(head -n "$MAX_COMMENT_LINES" <<< "$body")

if [ -n "$comment_file" ]; then
    printf '%s\n' "$body" > "$comment_file"
fi
if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
    printf '%s\n' "$body" >> "$GITHUB_STEP_SUMMARY"
fi

if [ "$failed" = true ]; then
    echo "Plugin Check found more errors than the baseline allows."
    exit 1
fi

if [ "$no_baseline" = true ]; then
    echo "Plugin Check ran with no baseline, so nothing was compared."
else
    echo "Plugin Check error counts are within the baseline."
fi
