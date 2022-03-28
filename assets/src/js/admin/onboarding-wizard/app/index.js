// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import store dependencies
import { StoreProvider } from './store';
import { reducer } from './store/reducer';

// Import styles
import './style.scss';

// Import components
import Wizard from '../components/wizard';
import Step from '../components/step';

// Import steps
import Introduction from './steps/introduction';
import YourCause from './steps/your-cause';
import Location from './steps/location';
import Features from './steps/features';
import DonationForm from './steps/donation-form';
import Addons from './steps/addons';

import {
	getCountryList,
	getDefaultStateList,
	getCurrencyList,
	getFeaturesEnabledDefault,
	getAddonsSelectedDefault,
	getDefaultCountry,
	getDefaultState,
	getDefaultCurrency,
} from '../utils';

/**
 * Onboarding Wizard app component
 *
 * @since 2.8.0
 * @returns {array} Array of React elements, comprising the Onboarding Wizard app
 */
const App = () => {
	// Initial app state (available in component through useStoreValue)
	const initialState = {
		currentStep: 0,
		lastStep: 5,
		configuration: {
			userType: 'individual',
			causeType: '',
			country: getDefaultCountry(),
			state: getDefaultState(),
			currency: getDefaultCurrency(),
			features: getFeaturesEnabledDefault(),
			addons: getAddonsSelectedDefault(),
		},
		countriesList: getCountryList(),
		currenciesList: getCurrencyList(),
		statesList: getDefaultStateList(),
		fetchingStatesList: false,
	};

	const steps = [
		{
			title: __( 'Introduction', 'give' ),
			component: <Introduction />,
			showInNavigation: false,
		},
		{
			title: __( 'Cause', 'give' ),
			component: <YourCause />,
			showInNavigation: true,
		},
		{
			title: __( 'Location', 'give' ),
			component: <Location />,
			showInNavigation: true,
		},
		{
			title: __( 'Features', 'give' ),
			component: <Features />,
			showInNavigation: true,
		},
		{
			title: __( 'Preview', 'give' ),
			component: <DonationForm />,
			showInNavigation: true,
		},
		{
			title: __( 'Add-ons', 'give' ),
			component: <Addons />,
			showInNavigation: true,
		},
	];

	return (
		<StoreProvider initialState={ initialState } reducer={ reducer }>
			<Wizard>
				{ steps.map( ( step, index ) => {
					return (
						<Step title={ step.title } showInNavigation={ step.showInNavigation } key={ index }>
							{ step.component }
						</Step>
					);
				} ) }
			</Wizard>
		</StoreProvider>
	);
};
export default App;
