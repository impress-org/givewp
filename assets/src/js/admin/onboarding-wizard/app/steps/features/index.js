// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setFeatures } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';
import OneTimeDonationIcon from '../../../components/icons/one-time-donation';
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
			<h1>{ __( 'What do you need to support your cause?', 'give' ) }</h1>
			<p>
				{ __( 'Take your fundraising to the next level with free and premium add-ons.', 'give' ) }
			</p>
			<CardInput values={ features } onChange={ ( value ) => dispatch( setFeatures( value ) ) } >
				<Card value="one-time-donations">
					<OneTimeDonationIcon />
					<strong>{ __( 'One-Time Donations', 'give' ) }</strong>
				</Card>
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
				<Card value="anonymous-donations">
					<AnonymousDonationsIcon />
					<strong>{ __( 'Anonymous Donations', 'give' ) }</strong>
				</Card>
				<Card value="company-donations">
					<CompanyDonationsIcon />
					<strong>{ __( 'Company Donations', 'give' ) }</strong>
				</Card>
			</CardInput>
			<ContinueButton />
		</div>
	);
};

export default Features;
