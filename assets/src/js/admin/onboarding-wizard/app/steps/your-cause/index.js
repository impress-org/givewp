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

// Import styles
import './style.scss';

const YourCause = () => {
	const [ { configuration }, dispatch ] = useStoreValue();

	const userType = configuration.userType;
	const causeType = configuration.causeType;
	const cardPadding = '60px 32px';

	return (
		<div className="give-obw-your-cause">
			<h2>{ __( 'What does fundraising look for you?', 'give' ) }</h2>
			<CardInput values={ userType } onChange={ ( values ) => dispatch( setUserType( values ) ) } checkMultiple={ false } >
				<Card value="individual" padding={ cardPadding }>
					<IndividualIcon />
					<p>{ __( 'I\'m fundraising as an', 'give' ) }</p>
					<h2>{ __( 'Individual', 'give' ) }</h2>
				</Card>
				<Card value="organization" padding={ cardPadding }>
					<OrganizationIcon />
					<p>{ __( 'I\'m fundraising within an', 'give' ) }</p>
					<h2>{ __( 'Organization', 'give' ) }</h2>
				</Card>
				<Card value="other" padding={ cardPadding }>
					<OtherIcon />
					<p>{ __( 'My fundraising is', 'give' ) }</p>
					<h2>{ __( 'Other', 'give' ) }</h2>
				</Card>
			</CardInput>
			<h3>{ __( 'What is your cause?', 'give' ) }</h3>
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
