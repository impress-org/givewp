// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import store dependencies
import { useStoreValue } from '../../store';
import { setFeatures } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';
import OfflineDonationsIcon from '../../../components/icons/offline-donations';
import DonationGoalIcon from '../../../components/icons/donation-goal';
import DonationCommentsIcon from '../../../components/icons/donation-comments';
import TermsConditionsIcon from '../../../components/icons/terms-conditions';
import AnonymousDonationsIcon from '../../../components/icons/anonymous-donations';
import CompanyDonationsIcon from '../../../components/icons/company-donations';

// Import styles
import './style.scss';

const Features = () => {
	const [ { configuration }, dispatch ] = useStoreValue();
	const features = configuration.features;

	return (
		<div className="give-obw-fundraising-needs">
			<h1>{ __( 'What do you need in your first donation form?', 'give' ) }</h1>
			<p>
				{ __( 'Don\'t worry, these settings can always be changed later.', 'give' ) }
			</p>
			<CardInput values={ features } onChange={ ( value ) => dispatch( setFeatures( value ) ) } >
				<Card value="donation-goal">
					<DonationGoalIcon />
					<strong>{ __( 'Donation Goal', 'give' ) }</strong>
				</Card>
				<Card value="donation-comments">
					<DonationCommentsIcon />
					<strong>{ __( 'Donation Comments', 'give' ) }</strong>
				</Card>
				<Card value="terms-conditions">
					<TermsConditionsIcon />
					<strong>{ __( 'Terms & Conditions', 'give' ) }</strong>
				</Card>
				<Card value="offline-donations">
					<OfflineDonationsIcon />
					<strong>{ __( 'Offline Donations', 'give' ) }</strong>
				</Card>
				<Card value="anonymous-donations">
					<AnonymousDonationsIcon />
					<strong>{ __( 'Anonymous Donations', 'give' ) }</strong>
				</Card>
				<Card value="company-donations">
					<CompanyDonationsIcon />
					<strong>{ __( 'Company Donations', 'give' ) }</strong>
				</Card>
			</CardInput>
			<ContinueButton testId="features-continue-button" />
		</div>
	);
};

export default Features;
