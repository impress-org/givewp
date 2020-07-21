// Import vendor dependencies
import { useState } from 'react';
const { __ } = wp.i18n;

// Import components
import CardInput from '../../../components/card-input';
import Card from '../../../components/card';
import SelectInput from '../../../components/select-input';
import ContinueButton from '../../../components/continue-button';

// Import styles
import './style.scss';

const YourCause = () => {
	const [ userType, setUserType ] = useState( [ 'testing' ] );
	const [ causeType, setCauseType ] = useState( 'religous' );

	return (
		<div className="give-obw-your-cause">
			<h2>{ __( 'What does fundraising look for you?', 'give' ) }</h2>
			<CardInput values={ userType } onChange={ ( values ) => setUserType( values ) } checkMultiple={ false } >
				<Card value="testing">
					<h1>Individual</h1>
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
