import HelpTip from '../HelpTip';
import {__} from '@wordpress/i18n';

/**
 * @since TBD
 */
export interface SearchHintProps {
    /** Id of the visible short line. */
    id: string;
    /** Id of the screen reader copy of the full rule. */
    ruleId: string;
    /** The short line under the box. */
    text: string;
    /** The full rule, when the hint has more than the short line. */
    fullRule?: string;
}

/**
 * The line under the search box. When there is a full rule, a help button shows it in a popover, and
 * a screen reader copy of it describes the input.
 *
 * @since TBD
 */
export default function SearchHint({id, ruleId, text, fullRule}: SearchHintProps) {
    return (
        <div className="givewp-list-table-search__hint-row">
            <p id={id} className="givewp-list-table-search__hint">
                {text}
            </p>
            {fullRule && (
                <>
                    <HelpTip label={__('How search works', 'give')} text={fullRule} />
                    <span id={ruleId} className="screen-reader-text">
                        {fullRule}
                    </span>
                </>
            )}
        </div>
    );
}
