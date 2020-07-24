// Import vendor dependencies
import { useState } from 'react';
const { __ } = wp.i18n;

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
	const [ userType, setUserType ] = useState( [ 'testing' ] );
	const [ causeType, setCauseType ] = useState( 'religous' );

	return (
		<div className="give-obw-your-cause">
			<h2>{ __( 'What does fundraising look for you?', 'give' ) }</h2>
			<CardInput values={ userType } onChange={ ( values ) => setUserType( values ) } checkMultiple={ false } >
				<Card value="individual" padding="60px 32px">
					<IndividualIcon />
					<p>{ __( 'I\'m funraising as an', 'give' ) }</p>
					<h2>{ __( 'Individual', 'give' ) }</h2>
				</Card>
				<Card value="organization" padding="60px 32px">
					<OrganizationIcon />
					<p>{ __( 'I\'m funraising within an', 'give' ) }</p>
					<h2>{ __( 'Organization', 'give' ) }</h2>
				</Card>
				<Card value="other" padding="60px 32px">
					<OtherIcon />
					<p>{ __( 'My fundraising is', 'give' ) }</p>
					<h2>{ __( 'Other', 'give' ) }</h2>
				</Card>
			</CardInput>
			<h3>{ __( 'What is your cause?', 'give' ) }</h3>
			<p>{ __( '(select all that apply)', 'give' ) }</p>
			<SelectInput value={ causeType } onChange={ ( value ) => setCauseType( value ) } options={
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
