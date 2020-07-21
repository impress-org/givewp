// Import vendor dependencies
import { useState } from 'react';
const { __ } = wp.i18n;

// Import components
import Card from '../../../components/card';
import ContinueButton from '../../../components/continue-button';
import SelectInput from '../../../components/select-input';

// Import styles
import './style.scss';

const Location = () => {
	const [ country, setCountry ] = useState( 'usa' );
	const [ state, setState ] = useState( 'WA' );
	const [ currency, setCurrency ] = useState( 'USD' );

	return (
		<div className="give-obw-location">
			<h2>{ __( 'Where are you fundraising?', 'give' ) }</h2>
			<Card>
				<SelectInput label={ __( 'Country', 'give' ) } value={ country } onChange={ ( value ) => setCountry( value ) } options={
					[
						{
							value: 'usa',
							label: 'United States',
						},
						{
							value: 'germany',
							label: 'Germany',
						},
					]
				} />
				<SelectInput label={ __( 'State / Province', 'give' ) } value={ state } onChange={ ( value ) => setState( value ) } options={
					[
						{
							value: 'WA',
							label: 'Washington',
						},
						{
							value: 'MA',
							label: 'Maine',
						},
					]
				} />
				<SelectInput label={ __( 'Currency', 'give' ) } value={ currency } onChange={ ( value ) => setCurrency( value ) } options={
					[
						{
							value: 'USD',
							label: 'US Dollars',
						},
						{
							value: 'EUR',
							label: 'Euros',
						},
					]
				} />
			</Card>
			<ContinueButton />
		</div>
	);
};

export default Location;
