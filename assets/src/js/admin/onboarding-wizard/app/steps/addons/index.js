// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import store dependencies
import { useStoreValue } from '../../store';
import { setAddons } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';
import RecurringDonationsIcon from '../../../components/icons/recurring-donations';
import DonorsCoverFeesIcon from '../../../components/icons/donors-cover-fees';
import PDFReceiptsIcon from '../../../components/icons/pdf-receipts';
import CustomFormFieldsIcon from '../../../components/icons/custom-form-fields';
import MultipleCurrenciesIcon from '../../../components/icons/multiple-currencies';
import DedicateDonationsIcon from '../../../components/icons/dedicate-donations';

// Import styles
import './style.scss';

const Addons = () => {
	const [ { configuration }, dispatch ] = useStoreValue();
	const addons = configuration.addons;

	return (
		<div className="give-obw-fundraising-needs">
			<h1>{ __( 'What else do you need to support your cause?', 'give' ) }</h1>
			<p>
				{ __( 'Take your fundraising to the next level with these premium add-ons.', 'give' ) }
			</p>
			<CardInput values={ addons } onChange={ ( value ) => dispatch( setAddons( value ) ) } >
				<Card value="recurring-donations">
					<RecurringDonationsIcon />
					<strong>{ __( 'Recurring Donations', 'give' ) }</strong>
				</Card>
				<Card value="donors-cover-fees">
					<DonorsCoverFeesIcon />
					<strong>{ __( 'Donors Cover Fees', 'give' ) }</strong>
				</Card>
				<Card value="pdf-receipts">
					<PDFReceiptsIcon />
					<strong>{ __( 'PDF Receipts', 'give' ) }</strong>
				</Card>
				<Card value="custom-form-fields">
					<CustomFormFieldsIcon />
					<strong>{ __( 'Custom Form Fields', 'give' ) }</strong>
				</Card>
				<Card value="multiple-currencies">
					<MultipleCurrenciesIcon />
					<strong>{ __( 'Multiple Currencies', 'give' ) }</strong>
				</Card>
				<Card value="dedicate-donations">
					<DedicateDonationsIcon />
					<strong>{ __( 'Dedicate Donations', 'give' ) }</strong>
				</Card>
			</CardInput>
			<ContinueButton testId="addons-continue-button" />
		</div>
	);
};

export default Addons;
