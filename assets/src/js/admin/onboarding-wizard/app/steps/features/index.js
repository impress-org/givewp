// Import vendor dependencies
import {__} from '@wordpress/i18n';

// Import store dependencies
import {useStoreValue} from '../../store';
import {setFeatures} from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';
import PreviousButton from '../../../components/previous-button';
import OfflineDonationsIcon from '../../../components/icons/offline-donations';
import DonationGoalIcon from '../../../components/icons/donation-goal';
import DonationCommentsIcon from '../../../components/icons/donation-comments';
import TermsConditionsIcon from '../../../components/icons/terms-conditions';
import AnonymousDonationsIcon from '../../../components/icons/anonymous-donations';
import CompanyDonationsIcon from '../../../components/icons/company-donations'; // Import styles
import './style.scss';

const Features = () => {
    const [{configuration}, dispatch] = useStoreValue();
    const features = configuration.features;

    return (
        <div className="give-obw-fundraising-needs">
            <h1>{__('What do you need in your first donation form?', 'give')}</h1>
            <p>{__('Select the features you need. These can always be changed later.', 'give')}</p>
            <CardInput values={features} onChange={(value) => dispatch(setFeatures(value))}>
                <Card value="donation-goal">
                    <DonationGoalIcon />
                    <h2>{__('Donation Goal', 'give')}</h2>
                    <p>{__('Show the donation goal progress on the form.', 'give')}</p>
                </Card>
                <Card value="donation-comments">
                    <DonationCommentsIcon />
                    <h2>{__('Donation Comments', 'give')}</h2>
                    <p>{__('Allow donors to add comments to their donations.', 'give')}</p>
                </Card>
                <Card value="terms-conditions">
                    <TermsConditionsIcon />
                    <h2>{__('Terms & Conditions', 'give')}</h2>
                    <p>{__('Require donors to accept terms and conditions.', 'give')}</p>
                </Card>
                <Card value="offline-donations">
                    <OfflineDonationsIcon />
                    <h2>{__('Offline Donations', 'give')}</h2>
                    <p>{__('Donors can choose to donate offline, via mail or in person.', 'give')}</p>
                </Card>
                <Card value="anonymous-donations">
                    <AnonymousDonationsIcon />
                    <h2>{__('Anonymous Donations', 'give')}</h2>
                    <p>{__('Enable donors to give anonymously.', 'give')}</p>
                </Card>
                <Card value="company-donations">
                    <CompanyDonationsIcon />
                    <h2>{__('Company Donations', 'give')}</h2>
                    <p>{__('Donors can donate via their company.', 'give')}</p>
                </Card>
            </CardInput>
            <footer className="give-obw-footer">
                <ContinueButton testId="features-continue-button" />
                <PreviousButton testId="features-previous-button" />
            </footer>
        </div>
    );
};

export default Features;
