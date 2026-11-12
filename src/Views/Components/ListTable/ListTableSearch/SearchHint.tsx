import {Button, Tooltip} from '@wordpress/components';
import {help} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import {shortHint} from './shortHint';

/**
 * @since TBD
 */
export interface SearchHintProps {
    /** Id of the visible short line. */
    id: string;
    /** Id of the screen reader copy of the full rule, when there is more than the short line. */
    ruleId: string;
    /** The full hint. */
    hint: string;
}

/**
 * The line under the search box: the first sentence of the hint. When the hint has more, a help
 * button shows the full rule in a tooltip, and a screen reader copy of it describes the input.
 *
 * @since TBD
 */
export default function SearchHint({id, ruleId, hint}: SearchHintProps) {
    const visible = shortHint(hint);
    const hasMore = visible !== hint;

    return (
        <div className="givewp-list-table-search__hint-row">
            <p id={id} className="givewp-list-table-search__hint">
                {visible}
            </p>
            {hasMore && (
                <>
                    <Tooltip text={hint}>
                        <Button
                            className="givewp-list-table-search__help"
                            icon={help}
                            aria-label={__('How search works', 'give')}
                        />
                    </Tooltip>
                    <span id={ruleId} className="screen-reader-text">
                        {hint}
                    </span>
                </>
            )}
        </div>
    );
}
