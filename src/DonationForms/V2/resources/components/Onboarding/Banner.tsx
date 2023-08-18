import {useContext} from 'react';
import {__} from '@wordpress/i18n';
import {ExitIcon, StarsIcon} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';
import {OnboardingContext, updateOnboardingOption} from './index';

export default function Banner() {

    const [, setState] = useContext(OnboardingContext);

    const openFeatureNoticeModal = () => setState(prev => ({
        ...prev,
        showFeatureNoticeDialog: true
    }));

    const handleClose = () => {
        updateOnboardingOption('show_onboarding_banner')
            .then(() => setState(prev => ({
                ...prev,
                showBanner: false
            })))
    };


    return (
        <div className={styles.banner}>
            <div className={styles.icon}>
                <StarsIcon />
            </div>
            <div className={styles.text}>
                    <span>
                        {__('GiveWP 3.0 introduces customizable and flexible forms powered by the new Visual Donation Form Builder.', 'give')}
                    </span>
                <button
                    className={styles.button}
                    onClick={openFeatureNoticeModal}
                >
                    {__('Try the new form builder', 'give')}
                </button>
            </div>
            <div className={styles.closeIcon}>
                <ExitIcon onClick={handleClose} />
            </div>
        </div>
    );
}
