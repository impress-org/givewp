import {__} from '@wordpress/i18n';
import type {LegacyRowActionArgs, LegacyRowActions as LegacyRowActionsRender} from '../ListTablePage/types';
import {keepInteractiveClicksInCell} from '../adapters/columnsToFields';
import './styles.scss';

/**
 * The id of the always-visible "Actions" column that holds a legacy row-actions function.
 *
 * @since TBD
 */
export const LEGACY_ROW_ACTIONS_FIELD = 'givewp-row-actions';

/**
 * @since TBD
 */
export interface LegacyRowActionsProps {
    render: LegacyRowActionsRender;
    args: LegacyRowActionArgs;
}

/**
 * Renders a row-actions function as it was written before the list engine change, in its own
 * column so the actions are visible without hovering. Typed row actions replace it on the core
 * screens; this keeps any other caller working.
 *
 * @since TBD
 */
export default function LegacyRowActions({render, args}: LegacyRowActionsProps) {
    return (
        // eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions
        <div
            role="group"
            aria-label={__('Actions', 'give')}
            className="givewp-legacy-row-actions"
            onClick={keepInteractiveClicksInCell}
        >
            {render(args)}
        </div>
    );
}
