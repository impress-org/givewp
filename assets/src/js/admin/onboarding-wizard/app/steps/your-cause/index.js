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
import IndividualIcon from '../../../components/icons/individual';
import OrganizationIcon from '../../../components/icons/organization';
import OtherIcon from '../../../components/icons/other';
import DismissLink from '../../../components/dismiss-link';

// Import styles
import './style.scss';

const YourCause = () => {
	const [ { configuration }, dispatch ] = useStoreValue();

	const userType = configuration.userType;
	const causeType = configuration.causeType;

	return (
		<div className="give-obw-your-cause">
			<h1>{ __( 'What does fundraising look like for you?', 'give' ) }</h1>
			<CardInput values={ userType } onChange={ ( values ) => dispatch( setUserType( values ) ) } checkMultiple={ false } >
				<Card value="individual">
					<IndividualIcon />
					<p>{ __( 'I\'m fundraising as an', 'give' ) }</p>
					<strong>{ __( 'Individual', 'give' ) }</strong>
				</Card>
				<Card value="organization">
					<OrganizationIcon />
					<p>{ __( 'I\'m fundraising within an', 'give' ) }</p>
					<strong>{ __( 'Organization', 'give' ) }</strong>
				</Card>
				<Card value="other">
					<OtherIcon />
					<p>{ __( 'My fundraising is', 'give' ) }</p>
					<strong>{ __( 'Other', 'give' ) }</strong>
				</Card>
			</CardInput>
			<h2>{ __( 'What is your cause?', 'give' ) }</h2>
			<span className="screen-reader-text">{ __( 'What is your cause?', 'give' ) }</span>
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
			<DismissLink />
		</div>
	);
};

export default YourCause;
