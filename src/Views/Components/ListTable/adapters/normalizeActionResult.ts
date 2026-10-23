import type {ActionOutcome} from '../ListTablePage/types';

const count = (entries: unknown): number => (Array.isArray(entries) ? entries.length : Number(entries) || 0);

const countTruthy = (entries: unknown): number => (Array.isArray(entries) ? entries.filter(Boolean).length : count(entries));

const countFalsy = (entries: unknown): number => (Array.isArray(entries) ? entries.filter((entry) => !entry).length : 0);

const decodeQuotes = (text: string) => text.replace(/&#0?39;/g, "'").replace(/&quot;/g, '"');

/**
 * The readable message of a failed request. The old client threw the response body as the error
 * message (often a `WP_Error` JSON); `apiFetch` throws the parsed `WP_Error` object.
 */
function errorMessage(error: unknown): string | undefined {
    const raw = error instanceof Error ? error.message : (error as {message?: unknown})?.message;

    if (typeof raw !== 'string' || raw === '') {
        return undefined;
    }

    try {
        const parsed = JSON.parse(raw.replace(/^Error: /, ''));

        if (typeof parsed?.message === 'string') {
            return decodeQuotes(parsed.message);
        }
    } catch {
        // Not JSON: use the text as it is.
    }

    return decodeQuotes(raw);
}

/**
 * Counts what an action changed and what failed, from any reply the list action endpoints give:
 * `{errors, successes}` lists (Forms `duplicate` puts `false` in `successes` for a failure), a thrown
 * error, or nothing at all (a custom flow that reports by itself).
 *
 * @since TBD
 */
export function normalizeActionResult(result: unknown, ids: Array<string | number>): ActionOutcome {
    if (result === undefined || result === null) {
        return {changed: 0, failed: 0};
    }

    const isReply = typeof result === 'object' && ('successes' in result || 'errors' in result);

    if (!isReply) {
        return {changed: 0, failed: ids.length, message: errorMessage(result)};
    }

    const {successes, errors} = result as {successes?: unknown; errors?: unknown};

    return {
        changed: countTruthy(successes),
        failed: count(errors) + countFalsy(successes),
    };
}
