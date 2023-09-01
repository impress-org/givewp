import {__} from '@wordpress/i18n';
import Badge from './components/Badge';
import './styles.scss';
import dismissWelcomeBanner from './utils/requests';
import getWindowData from '../index';
import LeftContentSection from './components/Sections/LeftContentSection';
import RightContentSection from './components/Sections/RightContentSection';

/**
 * @unreleased
 */
export default function App() {
    const {assets} = getWindowData();

    return (
        <div className={'givewp-welcome-banner'}>
            <div className={'givewp-welcome-banner__dismiss-container'}>
                <Badge
                    variant={'primary'}
                    caption={__('UPDATED', 'givewp')}
                    iconSrc={`${assets}/green-circle-check-icon.svg`}
                    alt={'check-mark'}
                />
                <button onClick={dismissWelcomeBanner}>
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
