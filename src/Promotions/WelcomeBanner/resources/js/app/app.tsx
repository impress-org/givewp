import {__} from '@wordpress/i18n';
import Badge from './components/Badge';
import './styles.scss';
import {useDismiss} from './hooks/useDismiss';
import getWindowData from '../index';
import LeftContentSection from './components/Sections/LeftContentSection';
import RightContentSection from './components/Sections/RightContentSection';

/**
 * @since 3.0.0
 */
export default function App() {
    const {showBanner, dismissBanner} = useDismiss();
    const {assets} = getWindowData();

    if (!showBanner) {
        return;
    }

    return (
        <div className={'givewp-welcome-banner'}>
            <div className={'givewp-welcome-banner__dismiss-container'}>
                <Badge
                    variant={'primary'}
                    caption={__('UPDATED', 'give')}
                    iconSrc={`${assets}/green-circle-check-icon.svg`}
                    alt={'check-mark'}
                />
                <button onClick={dismissBanner}>
                    <img src={`${assets}/close-icon.svg`} alt={'dismiss'} />
                </button>
            </div>

            <div className={'givewp-welcome-banner__content-container'}>
                <LeftContentSection assets={assets} />
                <RightContentSection assets={assets} />
            </div>
        </div>
    );
}
