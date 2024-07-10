// Import vendor dependencies
import {__} from '@wordpress/i18n';

// Import components
import ContinueButton from '../../../components/continue-button';
import PreviousButton from '../../../components/previous-button';
import DonationFormComponent from '../../../components/donation-form';
import Bullet from '../../../components/icons/bullet';

// Import styles
import './style.scss';

const DonationForm = () => {
    return (
        <div className="give-obw-donation-form">
            <div className="give-obw-donation-form__preview">
                <DonationFormComponent />
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
                    <footer className="give-obw-footer">
                        <ContinueButton testId="preview-continue-button" />
                        <PreviousButton testId="preview-previous-button" />
                    </footer>
                </div>
            </div>
        </div>
    );
};

export default DonationForm;
