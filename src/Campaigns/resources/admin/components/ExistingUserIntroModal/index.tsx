import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {useState} from 'react';
import {DismissIcon, StarIcon} from './icons';
import {getGiveCampaignsListTableWindowData} from '@givewp/campaigns/admin/components/CampaignsListTable';
import {__} from '@wordpress/i18n';
import {StepDetails} from '@givewp/campaigns/admin/components/ExistingUserIntroModal/StepDetails';
import styles from './ExistingUserIntroModal.module.scss';
import {updateUserNoticeOptions} from '@givewp/campaigns/utils';

/**
 * @since 4.0.0
 */

export type stepConfig = {
    title: string;
    description: string;
    buttonText: string;
    linkText?: string;
    badge?: () => JSX.Element;
};

const stepsConfig: stepConfig[] = [
    {
        badge: () => (
            <div className={styles.badge}>
                <StarIcon />
                {__('NEW', 'give')}
            </div>
        ),
        title: __('Introducing Campaigns', 'give'),
        description: __(
            'We’ve reimagined your online fundraising. Now, you can build a campaign page, track, and optimize entire fundraising campaigns with just a few steps.',
            'give'
        ),
        buttonText: __('Next', 'give'),
    },
    {
        title: __('Empowering your fundraising efforts', 'give'),
        description: __(
            'Add multiple donation forms, update your campaign on the go, and get tailored performance reports — all in one place.',
            'give'
        ),
        linkText: __('Read more on campaigns', 'give'),
        buttonText: __('Continue', 'give'),
    },
];

/**
 * @since 4.0.0
 */

type ExisingUserIntroModalProps = {
    isOpen: boolean;
    setOpen: (value: boolean) => void;
};

export default function ExistingUserIntroModal({isOpen, setOpen}: ExisingUserIntroModalProps) {
    const [step, setStep] = useState<number>(0);

    const stepConfig = stepsConfig[step];

    const handleClose = async () => {
        setStep(0);
        setOpen(false);
        updateUserNoticeOptions('givewp_campaign_existing_user_intro_notice');
    };

    const handleNextStep = () => {
        if (step >= stepsConfig.length - 1) {
            handleClose();
        } else {
            setStep((prevStep) => prevStep + 1);
        }
    };

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            wrapperClassName={`givewp-existing-user-intro-modal ${styles.introModal}`}
            showCloseIcon={false}
            title={''}
        >
            <div className={styles.preview}>
                <button type={'button'} className={styles.dismiss} onClick={handleClose}>
                    <DismissIcon />
                </button>
                <img
                    className={styles.previewImage}
                    src={`${
                        getGiveCampaignsListTableWindowData().pluginUrl
                    }/build/assets/dist/images/admin/campaigns/campaigns-cover.jpg`}
                    alt={'Campaign Preview Image'}
                />
            </div>

            {stepConfig && <StepDetails stepConfig={stepConfig} handleClick={handleNextStep} />}
        </ModalDialog>
    );
}
