// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setUserType, setCauseType } from '../../store/actions';
import { getCauseTypes, subscribeToNewsletter } from '../../../utils';

// Import components
import CardInput from '../../../components/card-input';
import Card from '../../../components/card';
import SelectInput from '../../../components/select-input';
import ContinueButton from '../../../components/continue-button';
import IndividualIcon from '../../../components/icons/individual';
import OrganizationIcon from '../../../components/icons/organization';
import OtherIcon from '../../../components/icons/other';
import SkipLink from '../../../components/skip-optin-link';

// Import styles
import './style.scss';

const YourCause = () => {
	const [ { configuration }, dispatch ] = useStoreValue();

	const userType = configuration.userType;
	const causeType = configuration.causeType;

	return (
		<div className="give-obw-your-cause">
			<h1>{ __( '👋 Hi there! Tell us a little about your Organization.', 'give' ) }</h1>
			<p>{ __('This information will be used to customize your experience to your fundraising needs.', 'give')}</p>
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

			<div className="give-obw-optin-field">
				<h2>{ __( 'What are you fundraising for?', 'give' ) }</h2>
				<span className="screen-reader-text">{ __( 'What type of cause is yours?', 'give' ) }</span>
				<SelectInput testId="cause-select" value={ causeType } onChange={ ( value ) => dispatch( setCauseType( value ) ) } options={ getCauseTypes() } />
			</div>

			<div className="give-obw-community-field">
				<h2>{ __( '🌱 Would you like to join the GiveWP Community?', 'give' ) }</h2>
				<p>{ __('If you opt-in, some data will be shared with our teams (so they can work their butts off to improve). We will also share some helpful info on fundraising, WordPress and our products from time to time.  And if you skip this, that’s okay! GiveWP will still work just fine.', 'give')}</p>

			</div>

			<ContinueButton testId="cause-continue-button" label={__( 'Accept & Continue', 'give' )} clickCallback={ () => { subscribeToNewsletter() } } />

			<SkipLink />

		</div>
	);
};

export default YourCause;
