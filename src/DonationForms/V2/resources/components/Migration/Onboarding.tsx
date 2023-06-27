import {MouseEventHandler, useState} from 'react';
import cx from 'classnames';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import Button from '@givewp/components/AdminUI/Button';
import {CompassIcon, MinusIcon, QuestionMarkIcon, StarsIcon} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

interface OnboardingProps {
    handleClose: MouseEventHandler;
}

export default function Onboarding({handleClose}: OnboardingProps) {
    const [step, setStep] = useState<number>(0);
    const [showHeader, setShowHeader] = useState<boolean>(true);
    const getImage = (name: string) => `${window.GiveDonationForms.pluginUrl}assets/dist/images/form-migration/${name}`;

    const nextStep = () => setStep((prev) => prev + 1);
    const previousStep = () => setStep((prev) => prev - 1);

    const StepIndicators = ({active}) => {
        const indicators: JSX.Element[] = [];

        for (let i = 1; i <= 3; i++) {
            const stepElement = (
                <li
                    key={i}
                    onClick={() => setStep(i)}
                    className={cx({[styles.active]: i === active, [styles.stepIndicator]: true})}
                />
            )
            indicators.push(stepElement);
        }

        return (
            <ul className={styles.indicator}>
                {indicators.map(indicator => indicator)}
            </ul>
        );
    }

    const Intro = () => {
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
                        nextStep();
                        setShowHeader(false);
                    }}>
                    {__('Get started', 'give')}
                </Button>
            </>
        );
    }

    const Step1 = () => {
        return (
            <>
                <div className={styles.image}>
                    <img key="step1" src={getImage('step1.jpg')} alt={__('Make a copy of your v2 form', 'give')} />
                </div>

                <StepIndicators active={1} />

                <div className={styles.title}>
                    {__('Make a copy of your v2 form', 'give')}
                </div>

                {__('Click "migrate" on the form you want to make a corresponding v3 form copy of', 'give')}

                <br />
                <br />

                <Button
                    onClick={nextStep}>
                    {__('Next', 'give')}
                </Button>
            </>
        );
    }

    const Step2 = () => {
        return (
            <>
                <div className={styles.image}>
                    <img key="step2" src={getImage('step2.jpg')} alt={__('Test out the new form', 'give')} />
                </div>

                <StepIndicators active={2} />

                <div className={styles.title}>
                    {__('Test out the new form', 'give')}
                </div>

                {__('Look at it in the form builder to make sure all your settings are as you like, check the form on the front end, and run some test donations', 'give')}

                <br />
                <br />

                <Button onClick={previousStep}>
                    {__('Previous', 'give')}
                </Button>

                <Button onClick={nextStep}>
                    {__('Next', 'give')}
                </Button>
            </>
        );
    }

    const Step3 = () => {
        return (
            <>
                <div className={styles.image}>
                    <img key="step3" src={getImage('step3.jpg')} alt={__('Transfer donation data', 'give')} />
                </div>

                <StepIndicators active={3} />

                <div className={styles.title}>
                    {__('Transfer donation data', 'give')}
                </div>

                {__('Click on “transfer data” to migrate all your donation data for the selected v2 form to the v3 form', 'give')}

                <br />
                <br />

                <Button onClick={previousStep}>
                    {__('Previous', 'give')}
                </Button>

                <Button onClick={handleClose}>
                    {__('Got it', 'give')}
                </Button>
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
            default:
                return <Intro />;
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
