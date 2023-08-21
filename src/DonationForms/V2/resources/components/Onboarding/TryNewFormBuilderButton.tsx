import {useContext} from 'react';
import {__} from '@wordpress/i18n';
import {OnboardingContext} from './index';
import {CubeIcon} from '@givewp/components/AdminUI/Icons';
import styles from './style.module.scss';

export default function TryNewFormBuilderButton() {
    const [, setState] = useContext(OnboardingContext);

    return (
        <button
            className={styles.tryNewFormBuilderButton}
            onClick={() => setState(prev => ({
                ...prev,
                showFeatureNoticeDialog: true
            }))}
        >
            <CubeIcon /> {__('Try the new form builder', 'give')}
        </button>
    )
}
