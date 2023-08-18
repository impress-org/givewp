import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {CheckVerified, StarsIcon} from '@givewp/components/AdminUI/Icons';
import Button from '@givewp/components/AdminUI/Button';
import styles from './style.module.scss';


export default function FeatureNoticeDialog({handleClose}) {
    const {supportedAddons, supportedGateways} = window.GiveDonationForms;

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

                {supportedAddons.length > 0 && (
                    <>
                        <div className={styles.title}>
                            {__('Supported add-ons', 'give')}
                        </div>

                        <div className={styles.itemsContainer}>
                            {supportedAddons.map(addon => (
                                <div className={styles.item} key={addon}><CheckVerified />{addon}</div>
                            ))}
                        </div>
                    </>
                )}

                {supportedGateways.length > 0 && (
                    <>
                        <div className={styles.title}>
                            {__('Supported gateways', 'give')}
                        </div>

                        <div className={styles.itemsContainer}>
                            {supportedGateways.map(gateway => (
                                <div className={styles.item} key={gateway}><CheckVerified />{gateway}</div>
                            ))}
                        </div>
                    </>
                )}

                <Button
                    size="large"
                    onClick={() => window.location.href = 'edit.php?post_type=give_forms&page=givewp-form-builder'}
                    style={{width: '100%'}}
                >
                    {__('Proceed with the new form builder', 'give')}
                </Button>

                <br />

                <a href="#">
                    {__('Read more on Add-ons and Gateways compatibility', 'give')}
                </a>
            </>
        </ModalDialog>
    )
}
