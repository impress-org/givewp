import {__} from '@wordpress/i18n';
import SpotLight from '../SpotLight';
import VideoPlayer from '../VideoPlayer';
import './styles.scss';

type RightContentSectionProps = {
    assets: string;
};

/**
 * @unreleased
 */
export default function RightContentSection({assets}: RightContentSectionProps) {
    return (
        <section className={'givewp-welcome-banner-right-content'}>
            <h2>{__('Spotlight on awesome features', 'givewp')}</h2>
            <div className={'givewp-welcome-banner-right-content__media-container'}>
                <SpotLight
                    title={__('Design mode', 'givewp')}
                    description={__(
                        'See exactly what your form looks like for potential donors using the “Design” tab of the builder. Changes are visible immediately.',
                        'givewp'
                    )}
                >
                    <VideoPlayer src={`${assets}/`} fallbackImage={`${assets}/design-mode.min.png`} />
                </SpotLight>

                <SpotLight
                    title={__('Custom Paragraph and Sections', 'givewp')}
                    description={__(
                        'Add custom paragraphs or add whole sections anywhere in your form, no code required.',
                        'givewp'
                    )}
                >
                    <VideoPlayer
                        src={`${assets}/custom-fields.mp4`}
                        fallbackImage={`${assets}/custom-fields.min.png`}
                    />
                </SpotLight>
            </div>
        </section>
    );
}
