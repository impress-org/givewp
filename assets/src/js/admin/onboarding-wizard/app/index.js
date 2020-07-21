const { __ } = wp.i18n;

// Store related dependencies
import { StoreProvider } from './store';
import { reducer } from './store/reducer';

// Import styles
import './style.scss';

// Import components
import Wizard from '../components/wizard';
import Step from '../components/step';

// Import steps
import DonationForm from './steps/donation-form';
import FundraisingNeeds from './steps/fundraising-needs';
import Introduction from './steps/introduction';
import Location from './steps/location';
import YourCause from './steps/your-cause';

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
		lastStep: 4,
	};

	const steps = [
		{
			title: __( 'Introduction', 'give' ),
			component: <Introduction />,
			showInNavigation: false,
		},
		{
			title: __( 'Your Cause', 'give' ),
			component: <YourCause />,
		},
		{
			title: __( 'Location', 'give' ),
			component: <Location />,
		},
		{
			title: __( 'Fundraising Needs', 'give' ),
			component: <FundraisingNeeds />,
		},
		{
			title: __( 'Donation Form', 'give' ),
			component: <DonationForm />,
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
