import {useId, useRef, useState} from 'react';
import {__} from '@wordpress/i18n';
import {Button} from '@wordpress/components';
import ConfirmationDialog from '../../AdminDetailsPage/ConfirmationDialog';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableViewResetProps {
    /** True while the table shows the screen's default layout. The button is hidden then. */
    isDefault: boolean;
    /** Puts the layout back to the defaults. Runs only after the admin confirms. */
    onConfirm: () => void;
}

/**
 * A "Reset view" button for the list table, shown while the layout differs from the defaults. It asks
 * before resetting. Cancel returns focus to the button; after a reset the button goes away, so focus
 * stays on this element and a status message says what happened.
 *
 * @since TBD
 */
export default function ListTableViewReset({isDefault, onConfirm}: ListTableViewResetProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [status, setStatus] = useState('');
    const rootRef = useRef<HTMLDivElement>(null);
    const buttonRef = useRef<HTMLButtonElement>(null);
    const descriptionId = useId();

    const open = () => {
        setStatus('');
        setIsOpen(true);
    };

    const cancel = () => {
        setIsOpen(false);
        buttonRef.current?.focus();
    };

    const confirm = () => {
        setIsOpen(false);
        onConfirm();
        setStatus(__('The table view is back to its defaults.', 'give'));
        rootRef.current?.focus();
    };

    return (
        <div ref={rootRef} tabIndex={-1} className="givewp-list-table-view-reset">
            {!isDefault && (
                <>
                    <Button
                        ref={buttonRef}
                        variant="tertiary"
                        className="givewp-list-table-view-reset__button"
                        aria-haspopup="dialog"
                        aria-describedby={descriptionId}
                        onClick={open}
                    >
                        {__('Reset view', 'give')}
                    </Button>
                    <span id={descriptionId} className="screen-reader-text">
                        {__('The columns, density or page size differ from the defaults.', 'give')}
                    </span>
                </>
            )}
            <span role="status" className="screen-reader-text">
                {status}
            </span>
            <ConfirmationDialog
                isOpen={isOpen}
                variant="regular"
                title={__('Reset view', 'give')}
                actionLabel={__('Reset view', 'give')}
                handleClose={cancel}
                handleConfirm={confirm}
            >
                <p className="givewp-list-table-view-reset__message">
                    {__(
                        'Show the default columns, column order, density and page size for this table? Your search, filters and sort stay as they are.',
                        'give'
                    )}
                </p>
            </ConfirmationDialog>
        </div>
    );
}
