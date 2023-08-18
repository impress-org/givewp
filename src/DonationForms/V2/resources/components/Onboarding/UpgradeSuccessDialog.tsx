import {MouseEventHandler} from 'react';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import ButtonGroup from '@givewp/components/AdminUI/ButtonGroup';
import Button from '@givewp/components/AdminUI/Button';
import styles from './style.module.scss';

interface OnboardingProps {
    handleClose: MouseEventHandler;
}

export default function UpgradeSuccessDialog({handleClose}: OnboardingProps) {
    const getImage = (name: string) => `${window.GiveDonationForms.pluginUrl}assets/dist/images/form-migration/${name}`;

    return (
        <ModalDialog
            showHeader={false}
            handleClose={handleClose}
            title={__('Your form has been upgraded', 'give')}
        >
            <>
                <div className={styles.imageContainer}>
                    <img src={getImage('step1.jpg')} alt={__('Upgraded form', 'give')} />
                </div>

                <div className={styles.title}>
                    {__('Your form has been upgraded', 'give')}
                </div>

                <div className={styles.info}>
                    {__('Make sure to check the settings for each section and block, and maybe even run some test donations to ensure your new form is good to go.', 'give')}
                </div>

                <ButtonGroup align="right">
                    <Button
                        size="large"
                        onClick={handleClose}
                    >
                        {__('Close', 'give')}
                    </Button>
                </ButtonGroup>
            </>
        </ModalDialog>
    );
}
