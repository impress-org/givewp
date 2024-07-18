// Import vendor dependencies
import {__} from '@wordpress/i18n';

// Import store dependencies
import {useStoreValue} from '../../store';
import {setNewsletterSubscription} from '../../store/actions';
import {subscribeToNewsletter} from '../../../utils';

// Import components
import ContinueButton from '../../../components/continue-button';
import PreviousButton from '../../../components/previous-button';
import DonationFormComponent from '../../../components/donation-form';
import CheckboxInput from '../../../components/checkbox-input';
import Bullet from '../../../components/icons/bullet';

// Import styles
import './style.scss';

const DonationForm = () => {
    const [{configuration}, dispatch] = useStoreValue();
    const newsletterSubscription = configuration.newsletterSubscription;

    return (
        <div className="give-obw-donation-form">
            <div className="give-obw-donation-form__preview">
              <DonationFormComponent formId={configuration.formId} />
            </div>
            <div className="give-obw-donation-form__content">
                <div className="give-obw-donation-form__fixed">
                    <h1>{__('🎉 Congrats! Check out your first donation form.', 'give')}</h1>
                    <p>{__('This form is customized based on your responses.', 'give')}</p>

                    <h2>{__('After setup you can:', 'give')}</h2>
                    <ul>
                        <li>
                            <Bullet />
                            {__('Customize the text, color and image', 'give')}
                        </li>
                        <li>
                            <Bullet />
                            {__('Modify donation amounts and add a fundraising goal', 'give')}
                        </li>
                        <li>
                            <Bullet />
                            {__('Add or remove payment options', 'give')}
                        </li>
                        <li>
                            <Bullet />
                            {__('Extend functionality with add-ons and more', 'give')}
                        </li>
                    </ul>
                    <div className="give-obw-newsletter-subscription-field">
                        <CheckboxInput
                            testId="newsletter-subscription-checkbox"
                            label={__('Maximize your fundraising success', 'give')}
                            help={__(
                                'By opting in, you get access to tips for improving fundraising strategies and increasing donations, live events, product updates, and online courses. You can unsubscribe any time.',
                                'give'
                            )}
                            checked={newsletterSubscription}
                            onChange={(e) => dispatch(setNewsletterSubscription(e.target.checked))}
                        />
                    </div>
                    <footer className="give-obw-footer">
                        <ContinueButton
                            testId="preview-continue-button"
                            clickCallback={() => {
                                if (newsletterSubscription) {
                                    subscribeToNewsletter(configuration);
                                }
                            }}
                        />
                        <PreviousButton testId="preview-previous-button" />
                    </footer>
                </div>
            </div>
        </div>
    );
};

export default DonationForm;
