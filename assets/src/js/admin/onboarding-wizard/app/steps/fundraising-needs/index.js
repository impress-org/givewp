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
					<strong>{ __( 'One-Time Donations', 'give' ) }</strong>
				</Card>
				<Card value="recurring-donations">
					<Badge label="Add-On" />
					<RecurringDonationsIcon />
					<strong>{ __( 'Recurring Donations', 'give' ) }</strong>
				</Card>
				<Card value="donors-cover-fees">
					<Badge label="Add-On" />
					<DonorsCoverFeesIcon />
					<strong>{ __( 'Donors Cover Fees', 'give' ) }</strong>
				</Card>
				<Card value="custom-form-fields">
					<Badge label="Add-On" />
					<CustomFormFieldsIcon />
					<strong>{ __( 'Custom Form Fields', 'give' ) }</strong>
				</Card>
				<Card value="multiple-currencies">
					<Badge label="Add-On" />
					<MultipleCurrenciesIcon />
					<strong>{ __( 'Multiple Currencies', 'give' ) }</strong>
				</Card>
				<Card value="dedicate-donations">
					<Badge label="Add-On" />
					<DedicateDonationsIcon />
					<strong>{ __( 'Dedicate Donations', 'give' ) }</strong>
				</Card>
			</CardInput>
			<ContinueButton />
		</div>
	);
};

export default FundraisingNeeds;
