import {__} from '@wordpress/i18n';
import {DropdownMenu, MenuGroup, MenuItem} from '@wordpress/components';
import {moreVertical} from '@wordpress/icons';

/**
 * @since TBD
 */
export interface ListTableViewMenuProps {
    /** Offers "Switch to Legacy View". Without it, and with no other item, there is no menu. */
    legacyView?: {
        /** The accessible name of the item, when it should say more than "Switch to Legacy View". */
        ariaLabel?: string;
        onSelect: () => void;
    };
}

/**
 * Give's small "More view options" menu, placed right after the stock DataViews view options, which
 * cannot take extra items. Its last group holds "Switch to Legacy View" on the screens that have a
 * legacy screen.
 *
 * @since TBD
 */
export default function ListTableViewMenu({legacyView}: ListTableViewMenuProps) {
    if (!legacyView) {
        return null;
    }

    return (
        <DropdownMenu
            className="givewp-list-table-view-menu"
            icon={moreVertical}
            label={__('More view options', 'give')}
            toggleProps={{size: 'compact'} as Record<string, unknown>}
            popoverProps={{placement: 'bottom-end'}}
        >
            {({onClose}) => (
                <MenuGroup>
                    <MenuItem
                        aria-label={legacyView.ariaLabel}
                        onClick={() => {
                            onClose();
                            legacyView.onSelect();
                        }}
                    >
                        {__('Switch to Legacy View', 'give')}
                    </MenuItem>
                </MenuGroup>
            )}
        </DropdownMenu>
    );
}
