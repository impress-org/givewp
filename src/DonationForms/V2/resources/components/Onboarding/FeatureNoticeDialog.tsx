import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {MinusIcon, CheckVerified, StarsIcon} from '@givewp/components/AdminUI/Icons';
import Button from '@givewp/components/AdminUI/Button';
import styles from './style.module.scss';


export default function FeatureNoticeDialog({handleClose}) {
    return (
        <ModalDialog
            isOpen={true}
            title={__('Feature notice', 'give')}
            handleClose={handleClose}
        >
            <>
                <div className={styles.title}>
                    <StarsIcon /> {__("What's new", 'give')}
                </div>

                {__('GiveWP 3.0 introduces an enhanced forms experience powered by the new Visual Donations Form Builder. The team is still working on add-on and gateway compatibility. If you need to use an add-on or gateway that isn\'t listed, use the "Add form" option for now', 'give')}

                <div className={styles.title}>
                    {__('Supported add-ons', 'give')}
                </div>

                <div className={styles.addonsContainer}>
                    {window.GiveDonationForms.supportedAddons.map(addon => <div className={styles.addon} key={addon}><CheckVerified />{addon}</div>)}
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
    )
}
