// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setCountry, setState, setCurrency } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import ContinueButton from '../../../components/continue-button';
import SelectInput from '../../../components/select-input';

// Import styles
import './style.scss';

const Location = () => {
	const [ { configuration, currenciesList, statesList, countriesList }, dispatch ] = useStoreValue();

	const country = configuration.country;
	const state = configuration.state;
	const currency = configuration.currency;

	return (
		<div className="give-obw-location">
			<h2>{ __( 'Where are you fundraising?', 'give' ) }</h2>
			<Card padding="20px 40px 60px 40px">
				<SelectInput label={ __( 'Country', 'give' ) } value={ country } onChange={ ( value ) => dispatch( setCountry( value ) ) } options={ countriesList } />
				<SelectInput label={ __( 'State / Province', 'give' ) } value={ state } onChange={ ( value ) => dispatch( setState( value ) ) } options={ statesList } />
				<SelectInput label={ __( 'Currency', 'give' ) } value={ currency } onChange={ ( value ) => dispatch( setCurrency( value ) ) } options={ currenciesList } />
			</Card>
			<ContinueButton />
		</div>
	);
};

export default Location;
