// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setUserType, setCauseType } from '../../store/actions';

// Import components
import CardInput from '../../../components/card-input';
import Card from '../../../components/card';
import SelectInput from '../../../components/select-input';
import ContinueButton from '../../../components/continue-button';

// Import styles
import './style.scss';

const YourCause = () => {
	const [ { configuration }, dispatch ] = useStoreValue();

	const userType = configuration.userType;
	const causeType = configuration.causeType;

	return (
		<div className="give-obw-your-cause">
			<h2>{ __( 'What does fundraising look for you?', 'give' ) }</h2>
			<CardInput values={ userType } onChange={ ( values ) => dispatch( setUserType( values ) ) } checkMultiple={ false } >
				<Card value="individual">
					<h2>{ __( 'Individual', 'give' ) }</h2>
				</Card>
				<Card value="another-test">
					<h1>Organization</h1>
				</Card>
				<Card value="does-this-work-too">
					<h1>Other</h1>
				</Card>
			</CardInput>
			<h2>{ __( 'What is your cause?', 'give' ) }</h2>
			<p>{ __( '(select all that apply)', 'give' ) }</p>
			<SelectInput value={ causeType } onChange={ ( value ) => dispatch( setCauseType( value ) ) } options={
				[
					{
						value: 'religous',
						label: 'Religous',
					},
					{
						value: 'school',
						label: 'School',
					},
				]
			} />
			<ContinueButton />
		</div>
	);
};

export default YourCause;
