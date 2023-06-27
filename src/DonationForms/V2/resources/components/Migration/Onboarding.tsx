import {MouseEventHandler, useState} from 'react';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';
import {CompassIcon, MinusIcon, QuestionMarkIcon, StarsIcon} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

interface OnboardingProps {
    handleClose: MouseEventHandler;
}

export default function Onboarding({handleClose}: OnboardingProps) {
    const [step, setStep] = useState<number>(1);
    const [showHeader, setShowHeader] = useState<boolean>(true);

    const Step1 = () => {
        return (
            <>
                <div className={styles.title}>
                    <StarsIcon /> {__("What's new", 'give')}
                </div>

                {__('GiveWP 3.0 introduces an enhanced forms experience using the new Visual Donation Form Builder. To unlock these features, your existing forms (v2) must be migrated to the new version (v3)', 'give')}

                <div className={styles.title}>
                    <QuestionMarkIcon /> {__('Add-ons compatibility', 'give')}
                </div>

                {__('This section highlights a list of add-ons that are not supported with v3 forms yet. Take note of these add-ons before making your migration to v3 forms.', 'give')}

                <div className={styles.addonsContainer}>
                    {window.GiveDonationForms.unsupportedAddons.map(addon => <div className={styles.addon} key={addon}>
                        <MinusIcon />{addon}
                    </div>)}
                </div>

                <div className={styles.title}>
                    <CompassIcon /> {__('Migration guide', 'give')}
                </div>

                {__('This section highlights a list of add-ons that are not supported with v3 forms yet. Take note of these add-ons before making your migration to v3 forms.', 'give')}

                <br />
                <br />

                <Button
                    onClick={() => {
                        setStep(2);
                        setShowHeader(false);
                    }}>
                    {__('Get started', 'give')}
                </Button>
            </>
        );
    }

    const Step2 = () => {
        return (
            <>
                Step 2
            </>
        );
    }

    const Step3 = () => {
        return (
            <>
                Step 3
            </>
        );
    }

    const Screen = () => {
        switch (step) {
            case 1:
                return <Step1 />;
            case 2:
                return <Step2 />;
            case 3:
                return <Step3 />;
        }
    }

    return (
        <ModalDialog
            showHeader={showHeader}
            handleClose={handleClose}
            title={__('Migration journey', 'give')}
            insertInto="#give-admin-donation-forms-root"
        >
            <Screen />
        </ModalDialog>
    );
}
