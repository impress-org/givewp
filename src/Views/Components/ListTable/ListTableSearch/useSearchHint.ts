import {useId} from 'react';
import type {SearchHintProps} from './SearchHint';
import {shortHint} from './shortHint';

/**
 * @since TBD
 */
export interface SearchHintParts {
    /** The id that describes the search input. None when there is no hint. */
    describedBy?: string;
    /** The props of the hint line. None when the hint is empty (legacy screens). */
    hint?: SearchHintProps;
}

/**
 * The search hint, split so the list can place the box and the hint apart: the box in the toolbar
 * row, the hint under it. Both share the ids made here, so the input stays described by the hint.
 * A long hint shows only its first sentence, so the input is described by the full rule instead.
 *
 * @since TBD
 */
export default function useSearchHint(hint: string): SearchHintParts {
    const hintId = useId();
    const ruleId = useId();
    const rule = hint.trim();

    if (!rule) {
        return {};
    }

    const short = shortHint(rule);
    const fullRule = short !== rule ? rule : undefined;

    return {
        describedBy: fullRule ? ruleId : hintId,
        hint: {id: hintId, ruleId, text: short, fullRule},
    };
}
