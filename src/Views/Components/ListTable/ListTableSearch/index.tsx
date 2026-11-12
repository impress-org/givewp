import {useCallback, useEffect, useId, useRef, useState} from 'react';
import {SearchControl} from '@wordpress/components';
import {useDebounce} from '@wordpress/compose';
import SearchHint from './SearchHint';
import {shortHint} from './shortHint';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableSearchProps {
    /** Visible label of the box. */
    label: string;
    /** Names the fields searched and how they match. Empty hides it (legacy screens). */
    hint: string;
    /** The search term in the view. */
    value: string;
    /** Called once, a moment after typing stops. */
    onChange: (search: string) => void;
}

const DEBOUNCE_MS = 300;

/**
 * The list search box. It keeps what the admin types, reports the term after a short pause, and
 * follows the view when the term changes from outside (for example a reset).
 *
 * @since TBD
 */
export default function ListTableSearch({label, hint, value, onChange}: ListTableSearchProps) {
    const [text, setText] = useState(value);
    const hintId = useId();
    const ruleId = useId();
    // The debounced call must keep a stable identity, or a parent re-render would cancel a pending search.
    const onChangeRef = useRef(onChange);
    const lastReported = useRef(value);

    // Synced after each commit, so a pending report never calls a callback from a render React discarded.
    useEffect(() => {
        onChangeRef.current = onChange;
    });

    const report = useCallback((term: string) => {
        lastReported.current = term;
        onChangeRef.current(term);
    }, []);
    const reportChange = useDebounce(report, DEBOUNCE_MS);

    // Follow the view only when it holds a term other than the one we last reported, so a late echo
    // of an older term does not overwrite newer typing. A new term from outside drops any pending
    // search, so it cannot undo a reset.
    useEffect(() => {
        if (value === lastReported.current) {
            return;
        }

        reportChange.cancel();
        lastReported.current = value;
        setText(value);
    }, [value]);

    const handleChange = (next: string) => {
        setText(next);
        reportChange(next);
    };

    // A long hint shows only its first sentence, so the input is described by the full rule instead.
    const hasMore = shortHint(hint) !== hint;
    const describedBy = hint ? (hasMore ? ruleId : hintId) : undefined;

    return (
        <div className="givewp-list-table-search">
            <SearchControl
                className="givewp-list-table-search__control"
                label={label}
                hideLabelFromVision={false}
                aria-describedby={describedBy}
                value={text}
                onChange={handleChange}
                __nextHasNoMarginBottom
            />
            {hint && <SearchHint id={hintId} ruleId={ruleId} hint={hint} />}
        </div>
    );
}
