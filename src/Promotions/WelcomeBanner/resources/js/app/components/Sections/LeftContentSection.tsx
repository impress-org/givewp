import Row from '../Row';
import {__} from '@wordpress/i18n';
import Badge from '../Badge';
import {ExternalLink, InternalLink} from '../Link';
import './styles.scss';

type LeftContentSectionProps = {
    assets: string;
};

/**
 * @since 3.0.0
 */
export default function LeftContentSection({assets}: LeftContentSectionProps) {
    return (
        <section className={'givewp-welcome-banner-left-content'}>
            <Row>
                <header className={'givewp-welcome-banner-row__header'}>
                    <h1>{__("What's new in GiveWP 3.0", 'give')}</h1>
                    <p>
                        {__(
                            'GiveWP 3.0 introduces an enhanced forms experience powered by the new Visual Donations Form Builder.',
                            'give'
                        )}
                    </p>
                </header>
            </Row>

            <Row>
                <span>
                    <Badge
                        variant={'secondary'}
                        caption={__('NEW', 'give')}
                        iconSrc={`${assets}/shades-white-star-icon.svg`}
                        alt={'star'}
                    />
                    <h2>{__('Create a donation form', 'give')}</h2>
                </span>
                <p>{__('This is powered by the new Visual Donation Form Builder', 'give')}</p>
                <InternalLink href={'/wp-admin/edit.php?post_type=give_forms&page=givewp-form-builder'}>
                    {__('Try the new form builder', 'give')}
                </InternalLink>
            </Row>

            <Row>
                <h2>{__('GiveWP 3.0 Updates', 'give')}</h2>
                <p>
                    {__(
                        'The team is still working on some new features, add-on and payment gateway compatibility to make your form experience better.',
                        'give'
                    )}
                </p>
                <ExternalLink href={'https://docs.givewp.com/welcome-docs'}>
                    {__('Read documentation', 'give')}
                </ExternalLink>
            </Row>
        </section>
    );
}
