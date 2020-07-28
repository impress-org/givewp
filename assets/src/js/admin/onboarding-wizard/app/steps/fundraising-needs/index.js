// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setFundraisingNeeds } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';
import OneTimeDonationIcon from '../../../components/icons/one-time-donation';
import RecurringDonationsIcon from '../../../components/icons/recurring-donations';
import DonorsCoverFeesIcon from '../../../components/icons/donors-cover-fees';
import CustomFormFieldsIcon from '../../../components/icons/custom-form-fields';
import MultipleCurrenciesIcon from '../../../components/icons/multiple-currencies';
import DedicateDonationsIcon from '../../../components/icons/dedicate-donations';
import Badge from '../../../components/badge';

// Import styles
import './style.scss';

const FundraisingNeeds = () => {
	const [ { configuration }, dispatch ] = useStoreValue();
	const needs = configuration.fundraisingNeeds;

	return (
		<div className="give-obw-fundraising-needs">
			<h1>{ __( 'What do you need to support your cause?', 'give' ) }</h1>
			<p>
				{ __( 'Take your fundraising to the next level with free and premium add-ons.', 'give' ) }
			</p>
			<CardInput values={ needs } onChange={ ( value ) => dispatch( setFundraisingNeeds( value ) ) } >
				<Card value="one-time-donations">
					<OneTimeDonationIcon />
					<h2>{ __( 'One-Time Donations', 'give' ) }</h2>
				</Card>
				<Card value="recurring-donations">
					<Badge label="Add-On" />
					<RecurringDonationsIcon />
					<h2>{ __( 'Recurring Donations', 'give' ) }</h2>
				</Card>
				<Card value="donors-cover-fees">
					<Badge label="Add-On" />
					<DonorsCoverFeesIcon />
					<h2>{ __( 'Donors Cover Fees', 'give' ) }</h2>
				</Card>
				<Card value="custom-form-fields">
					<Badge label="Add-On" />
					<CustomFormFieldsIcon />
					<h2>{ __( 'Custom Form Fields', 'give' ) }</h2>
				</Card>
				<Card value="multiple-currencies">
					<Badge label="Add-On" />
					<MultipleCurrenciesIcon />
					<h2>{ __( 'Multiple Currencies', 'give' ) }</h2>
				</Card>
				<Card value="dedicate-donations">
					<Badge label="Add-On" />
					<DedicateDonationsIcon />
					<h2>{ __( 'Dedicate Donations', 'give' ) }</h2>
				</Card>
			</CardInput>
			<ContinueButton />
		</div>
	);
};

export default FundraisingNeeds;
