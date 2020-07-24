// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setFundraisingNeeds } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';

// Import styles
import './style.scss';

const FundraisingNeeds = () => {
	const [ { configuration }, dispatch ] = useStoreValue();
	const needs = configuration.fundraisingNeeds;

	return (
		<div className="give-obw-fundraising-needs">
			<h2>{ __( 'What do you need to support your cause', 'give' ) }</h2>
			<CardInput values={ needs } onChange={ ( value ) => dispatch( setFundraisingNeeds( value ) ) } >
				<Card value="testing">
					<h1>One-Time Donations</h1>
				</Card>
				<Card value="another-test">
					<h1>Recurring Donations</h1>
				</Card>
				<Card value="does-this-work-too">
					<h1>Donors Cover Fees</h1>
				</Card>
				<Card value="custom-form-fields">
					<h1>Custom Form Fields</h1>
				</Card>
				<Card value="multiple-currencies">
					<h1>Multiple Currencies</h1>
				</Card>
				<Card value="dedicate-donations">
					<h1>Dedicate Donations</h1>
				</Card>
			</CardInput>
			<ContinueButton />
		</div>
	);
};

export default FundraisingNeeds;
