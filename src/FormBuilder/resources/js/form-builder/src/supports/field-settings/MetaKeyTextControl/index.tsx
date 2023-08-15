import {Button, Popover, TextControl} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {useState} from "@wordpress/element";

import styles from './styles.module.scss'

/**
 * The text control for meta keys. The key can be optionally locked so the user has to explicitly decide to change it
 * after being warned.
 *
 * @since 3.0.0
 */
export default function MetaKeyTextControl({value, lockValue, onChange, onBlur}) {
    const [isPopoverOpen, setIsPopoverOpen] = useState<boolean>(false);
    const [valueIsLocked, setValueIsLocked] = useState<boolean>(lockValue);

    const togglePopover = () => setIsPopoverOpen((state) => !state);

    const handlePopoverConfirmation = () => {
        setIsPopoverOpen(false);
        setValueIsLocked(false);
    };

    return (
        <>
            {/* This extra div wrapper prevents a margin change when the popup is opened. It's weird. */}
            <div>
                <TextControl
                    className={styles.textControl}
                    label={[__('Meta Key', 'give'), lockValue ? <EditButton onClick={togglePopover} /> : null]}
                    value={value}
                    help={__(
                        'The name of the meta key this field will to in the database. Can only be letters, numbers, and underscores.',
                        'give'
                    )}
                    readOnly={valueIsLocked}
                    onChange={onChange}
                    onBlur={onBlur}
                />
            </div>
            <EditPopover
                visible={isPopoverOpen}
                onCancel={() => setIsPopoverOpen(false)}
                onConfirm={handlePopoverConfirmation}
            />
        </>
    );
}

/**
 * Takes a string and returns a slugified version of it. This is not intended to be a general purpose slugify function
 * and is specific to meta keys.
 *
 * @since 3.0.0
 */
export function slugifyMeta(value) {
    return value
        .trim()
        .slice(0, 255) // Limit to 255 characters
        .toLowerCase()
        .replace(/^_/g, '') // Removes leading underscore
        .replace(/\s/g, '_') // Replace spaces and underscores with underscores
        .replace(/[^a-zA-Z\d\s_]/g, '') // Replace non-alphanumeric characters (other underscores) with nothing
        .replace(/-$/g, ''); // Remove trailing dash
}

function EditButton({onClick}) {
    return (
        <button onClick={onClick} type="button" className={styles.button}>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path
                    d="M6.02157 10.1075L12.666 3.39785L11.2216 2L4.57713 8.70968L3.99935 10.6667L6.02157 10.1075Z"
                    fill="#0E0E0E"
                />
                <path
                    d="M2.66602 13.3333H7.99935M12.666 3.39785L6.02157 10.1075L3.99935 10.6667L4.57713 8.70968L11.2216 2L12.666 3.39785Z"
                    stroke="#0E0E0E"
                />
            </svg>
        </button>
    );
}

/**
 * The popover that allows the user to override the meta key.
 *
 * @since 3.0.0
 */
function EditPopover({visible, onCancel, onConfirm}) {
    if ( ! visible ) {
        return null;
    }

    return (
        <Popover placement="left" offset={35}>
            <div className={styles.popover}>
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path fillRule="evenodd" clipRule="evenodd" d="M10.0007 0.833984C4.93804 0.833984 0.833984 4.93804 0.833984 10.0007C0.833984 15.0633 4.93804 19.1673 10.0007 19.1673C15.0633 19.1673 19.1673 15.0633 19.1673 10.0007C19.1673 4.93804 15.0633 0.833984 10.0007 0.833984ZM10.834 6.66732C10.834 6.20708 10.4609 5.83398 10.0007 5.83398C9.54041 5.83398 9.16732 6.20708 9.16732 6.66732V10.0007C9.16732 10.4609 9.54041 10.834 10.0007 10.834C10.4609 10.834 10.834 10.4609 10.834 10.0007V6.66732ZM10.0007 12.5007C9.54041 12.5007 9.16732 12.8737 9.16732 13.334C9.16732 13.7942 9.54041 14.1673 10.0007 14.1673H10.009C10.4692 14.1673 10.8423 13.7942 10.8423 13.334C10.8423 12.8737 10.4692 12.5007 10.009 12.5007H10.0007Z" fill="#F29718"/>
                    </svg>
                </div>
                <div className={styles.popoverContents}>
                    <h3>{__('Edit field meta key', 'give')}</h3>
                    <p>{__('Changing the meta key value will affect the visibility of existing donation data. Would you like to proceed?', 'give')}</p>
                    <div className={styles.popoverButtons}>
                        <Button onClick={() => onCancel()} variant="secondary">{__('No, cancel', 'give')}</Button>
                        <Button onClick={() => onConfirm()} variant="primary">{__('Yes, proceed', 'give')}</Button>
                    </div>
                </div>
            </div>
        </Popover>
    );
}
