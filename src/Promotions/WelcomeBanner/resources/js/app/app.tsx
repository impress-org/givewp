import {__} from '@wordpress/i18n';
import Badge from './components/Badge';
import './styles.scss';
import {ExternalLink, InternalLink} from './components/Link';
import SpotLight from './components/SpotLight';
import VideoPlayer from './components/VideoPlayer';
import ColumnRow from './components/ColumnRow';
import dismissWelcomeBanner from './utils/requests';
import getWindowData from '../index';

/**
 * @unreleased
 */
export default function App() {
    const {assets} = getWindowData();

    return (
        <div className={'givewp-welcome-banner'}>
            <div className={'givewp-welcome-banner__dismiss'}>
                <Badge
                    variant={'primary'}
                    caption={__('UPDATED', 'givewp')}
                    iconSrc={`${assets}/green-circle-check-icon.svg`}
                    alt={'check-mark'}
                />
                <button onClick={dismissWelcomeBanner}>
                    <img src={`${assets}/close-icon.svg`} alt={'dismiss welcome banner'} />
                </button>
            </div>

            <div className={'givewp-welcome-banner__content'}>
                <section className={'givewp-welcome-banner__col-left'}>
                    <ColumnRow>
                        <header className={'givewp-welcome-banner-col-header'}>
                            <h1>{__("What's new in Give 3.0", 'givewp')}</h1>
                            <p>
                                {__(
                                    'GiveWP 3.0 introduces an enhanced forms experience powered by the new Visual Donations Form Builder.',
                                    'givewp'
                                )}
                            </p>
                        </header>
                    </ColumnRow>

                    <ColumnRow>
                        <span>
                            <Badge
                                variant={'secondary'}
                                caption={__('NEW', 'givewp')}
                                iconSrc={`${assets}/shades-white-star-icon.svg`}
                                alt={'star'}
                            />
                            <h2>{__('Create a donation form', 'givewp')}</h2>
                        </span>
                        <p>{__('This is powered by the new Visual Donation Form Builder', 'givewp')}</p>
                        <InternalLink href={'/wp-admin/edit.php?post_type=give_forms&page=givewp-form-builder'}>
                            {__('Try the new form builder', 'givewp')}
                        </InternalLink>
                    </ColumnRow>

                    <ColumnRow>
                        <h2>{__('GiveWP 3.0 Updates', 'givewp')}</h2>
                        <p>
                            {__(
                                'The team is still working on some new features, add-on and payment gateway compatibility to make your form experience better.',
                                'givewp'
                            )}
                        </p>
                        <ExternalLink href={'https://docs.givewp.com/welcome-docs'}>
                            {__('Read documentation', 'givewp')}
                        </ExternalLink>
                    </ColumnRow>
                </section>

                <section className={'givewp-welcome-banner__col-right'}>
                    <h2>{__('Spotlight on awesome features', 'givewp')}</h2>
                    <div className={'givewp-welcome-banner__col-right-container'}>
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
            </div>
        </div>
    );
}
