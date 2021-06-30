// Import vendor dependencies
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../store';
import { setUserType, setCauseType } from '../../store/actions';
import { getBlogName, getAdminEmail, getCauseTypes } from '../../../utils';

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
	const blogName = configuration.blogName;
	const adminEmail = configuration.adminEmail;
	const causeType = configuration.causeType;

	return (
		<div className="give-obw-your-cause">
			<h1>{ __( '👋 Hi there! Tell us a little about your Organization.', 'give' ) }</h1>
			<p>{ __('This information will be used to customize your experience to your fundraising needs.')}</p>
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
				<h2>{ __( 'What\'s the name of your cause?', 'give' ) }</h2>
				<span className="screen-reader-text">{ __( 'What\'s the name of your cause?', 'give' ) }</span>
				<input className="give-obw-text-field" type="text" value={ getBlogName() } />
			</div>

			<div className="give-obw-optin-field">
				<h2>{ __( 'What is your cause?', 'give' ) }</h2>
				<span className="screen-reader-text">{ __( 'What type of cause?', 'give' ) }</span>
				<SelectInput testId="cause-select" value={ causeType } onChange={ ( value ) => dispatch( setCauseType( value ) ) } options={ getCauseTypes() } />
			</div>

			<div className="give-obw-optin-field">
				<h2>{ __( 'What\'s your email address?', 'give' ) }</h2>
				<span className="screen-reader-text">{ __( 'What\'s your email address?', 'give' ) }</span>
				<input className="give-obw-text-field" type="text" value={ getAdminEmail() } />
				<p className="give-obw-email-notice">{__('I would like to receive articles and information how to get the most out of GiveWP.', 'give')}</p>
			</div>


			<ContinueButton testId="cause-continue-button" />
		</div>
	);
};

export default YourCause;
