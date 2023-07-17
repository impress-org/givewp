import {useCallback, useState} from 'react';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';
import {MinusIcon, QuestionMarkIcon, StarsIcon} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

export default function Banner() {
    const [isOpen, setIsOpen] = useState<boolean>(false);

    const handleOpen = useCallback(() => setIsOpen(true), []);
    const handleClose = useCallback(() => setIsOpen(false), []);

    return (
        <>
            <div
                tabIndex={0}
                role="button"
                aria-label={__('GiveWP 3.0 update info', 'give')}
                className={styles.banner}
                onClick={handleOpen}
            >
                <span>{__('UPDATE', 'give')}</span>
                {__('GiveWP 3.0 introduces an enhanced forms but not all of the existing GiveWP add-ons and gateways are compatible with v3 forms.', 'give')}
                <strong>{__('See the unsupported addons', 'give')}</strong>
            </div>

            <ModalDialog
                isOpen={isOpen}
                title={__('Add-ons compatibility', 'give')}
                handleClose={handleClose}
                insertInto="#give-admin-donation-forms-root"
            >
                <>
                    <div className={styles.title}>
                        <StarsIcon /> {__("What's new", 'give')}
                    </div>

                    {__('The new forms introduced in GiveWP 3.0 which use the Visual Donation Form Builder are called v3 forms. At this time, not all currently installed GiveWP add-ons and gateways are compatible with v3 forms. While we work on making all add-ons compatible, understand that using a v3 form means these features cannot be used. In the meantime, we suggest using the v2 forms when the add-on features are required for that form.', 'give')}

                    <div className={styles.title}>
                        <QuestionMarkIcon /> {__('Add-ons compatibility', 'give')}
                    </div>

                    {__('This section highlights a list of add-ons that are not supported with v3 forms yet. Take note of these add-ons before making your migration to v3 forms.', 'give')}

                    <div className={styles.addonsContainer}>
                        {window.GiveDonationForms.unsupportedAddons.map(addon => <div className={styles.addon} key={addon}><MinusIcon />{addon}
                        </div>)}
                    </div>

                    <br />

                    <Button
                        size="large"
                        onClick={handleClose}
                        style={{width: '100%'}}
                    >
                        {__('Close', 'give')}
                    </Button>
                </>
            </ModalDialog>
        </>
    );
}
