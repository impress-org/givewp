import {useEffect, useId, useState} from 'react';
import {SearchControl} from '@wordpress/components';
import {useDebounce} from '@wordpress/compose';
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
    const reportChange = useDebounce(onChange, DEBOUNCE_MS);

    useEffect(() => {
        setText(value);
    }, [value]);

    const handleChange = (next: string) => {
        setText(next);
        reportChange(next);
    };

    return (
        <div className="givewp-list-table-search">
            <SearchControl
                className="givewp-list-table-search__control"
                label={label}
                hideLabelFromVision={false}
                aria-describedby={hint ? hintId : undefined}
                value={text}
                onChange={handleChange}
                __nextHasNoMarginBottom
            />
            {hint && (
                <p id={hintId} className="givewp-list-table-search__hint">
                    {hint}
                </p>
            )}
        </div>
    );
}
