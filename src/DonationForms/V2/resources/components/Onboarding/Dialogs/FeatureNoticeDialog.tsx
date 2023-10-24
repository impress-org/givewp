import {__, sprintf} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {CheckVerified, StarsIcon} from '@givewp/components/AdminUI/Icons';
import Button from '@givewp/components/AdminUI/Button';
import styles from '../style.module.scss';
import {createInterpolateElement} from "@wordpress/element";


export default function FeatureNoticeDialog({isUpgrading, isEditing, handleClose}) {
    const {supportedAddons, supportedGateways, migrationApiRoot, apiNonce} = window.GiveDonationForms;
    const handleUpgrade = async () => {

        // @ts-ignore
        const response = await fetch(migrationApiRoot + '/' + window.give_vars.post_id, {
            method: 'post',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': apiNonce,
            },
        });

        const data = await response.json();

        if (response.ok) {
            window.location = data.redirect;
        } else {
            alert('Error migrating form');
        }
    }

    // @note the <Button/> component does not support the `className` prop.
    const upgradeButtonStyles = {
        width: '100%',
        marginTop: 'var(--givewp-spacing-6)',
        marginBottom: 'var(--givewp-spacing-4)',
        backgroundColor: 'var(--wp-blue-blue-50)'
    }

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

                <p className={styles.message}>
                    {createInterpolateElement(
                        sprintf(__('GiveWP 3.0 introduces an enhanced forms experience powered by the new Visual Donation Form Builder. The team is still working on add-on and gateway compatibility. If you need to use an add-on or gateway that isn\'t listed, use the "%sAdd form%s" option for now.', 'give'), '<b>','</b>'),
                        {
                            b: <strong />,
                        }
                    )}
                </p>

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

                {isUpgrading ? (
                    <Button
                        size="large"
                        onClick={handleUpgrade}
                        className={styles.proceedButton}
                    >
                        {__('Proceed with upgrade', 'give')}
                    </Button>
                ) : (
                    <Button
                        size="large"
                        onClick={() => {
                            if(isEditing) {
                                sessionStorage.setItem('givewp-show-return-btn', 'true');
                            }
                            window.location.href = 'edit.php?post_type=give_forms&page=givewp-form-builder'
                        }}
                        className={styles.proceedButton}
                    >
                        {__('Proceed with the new form builder', 'give')}
                    </Button>
                )}

                <div className={styles.link}>
                    <a href="https://docs.givewp.com/compat-guide" rel="noopener noreferrer" target="_blank">
                        {__('Read more on Add-ons and Gateways compatibility', 'give')}
                    </a>
                </div>
            </>
        </ModalDialog>
    )
}
