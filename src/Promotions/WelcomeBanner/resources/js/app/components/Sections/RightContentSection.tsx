import {__} from '@wordpress/i18n';
import SpotLight from '../SpotLight';
import VideoPlayer from '../VideoPlayer';
import './styles.scss';

type RightContentSectionProps = {
    assets: string;
};

/**
 * @since 3.0.0
 */
export default function RightContentSection({assets}: RightContentSectionProps) {
    return (
        <section className={'givewp-welcome-banner-right-content'}>
            <h2>{__('Spotlight on awesome features', 'give')}</h2>
            <div className={'givewp-welcome-banner-right-content__media-container'}>
                <SpotLight
                    title={__('Design mode', 'give')}
                    description={__(
                        'See exactly what your form looks like for potential donors using the “Design” tab of the builder. Changes are visible immediately.',
                        'give'
                    )}
                >
                    <VideoPlayer src={`${assets}/design-mode.mp4`} fallbackImage={`${assets}/design-mode.min.png`} />
                </SpotLight>

                <SpotLight
                    title={
                        <>
                            {__('Event tickets', 'give')} <span className={'givewp-beta-icon'}>BETA</span>
                        </>
                    }
                    description={__('Easily create and manage your events, and connect to your donation form.', 'give')}
                >
                    <VideoPlayer
                        src={`${assets}/event-tickets.mp4`}
                        fallbackImage={`${assets}/event-tickets.min.png`}
                    />
                </SpotLight>
            </div>
        </section>
    );
}
