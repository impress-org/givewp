// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import store dependencies
import { useStoreValue } from '../../store';
import {setCauseType, setUsageTracking, setUserType} from '../../store/actions';
import {getCauseTypes, saveSettingWithOnboardingAPI} from '../../../utils';

// Import components
import CardInput from '../../../components/card-input';
import Card from '../../../components/card';
import CheckboxInput from '../../../components/checkbox-input';
import SelectInput from '../../../components/select-input';
import ContinueButton from '../../../components/continue-button';
import IndividualIcon from '../../../components/icons/individual';
import OrganizationIcon from '../../../components/icons/organization';
import OtherIcon from '../../../components/icons/other';

// Import styles
import './style.scss';

const YourCause = () => {
	const [{ configuration }, dispatch] = useStoreValue();

	const userType = configuration.userType;
	const causeType = configuration.causeType;
    const usageTracking = configuration.usageTracking;

	return (
		<div className="give-obw-your-cause">
			<h1>{__( '👋 Hi there! Tell us about your cause.', 'give' )}</h1>
			<p>{__( 'This information will be used to customize your experience to your fundraising needs.', 'give' )}</p>
			<CardInput values={userType} onChange={( values ) => dispatch( setUserType( values ) )} checkMultiple={false}>
				<Card value="individual">
					<IndividualIcon />
					<p>{__( 'I\'m fundraising as an', 'give' )}</p>
					<strong>{__( 'Individual', 'give' )}</strong>
				</Card>
				<Card value="organization">
					<OrganizationIcon />
					<p>{__( 'I\'m fundraising within an', 'give' )}</p>
					<strong>{__( 'Organization', 'give' )}</strong>
				</Card>
				<Card value="other">
					<OtherIcon />
					<p>{__( 'My fundraising is', 'give' )}</p>
					<strong>{__( 'Other', 'give' )}</strong>
				</Card>
			</CardInput>

			<div className="give-obw-optin-field">
				<h2>{__( 'What are you fundraising for?', 'give' )}</h2>
				<span className="screen-reader-text">{__( 'What type of cause is yours?', 'give' )}</span>
				<SelectInput testId="cause-select" value={causeType} onChange={( value ) => dispatch( setCauseType( value ) )} options={getCauseTypes()} />
			</div>

            <div className="give-obw-usage-tracking-field">
                <CheckboxInput
                    testId="usage-tracking-checkbox"
                    label={__('Help us enhance your product experience', 'give')}
                    help={__(
                        "By opting-in, you'll enable us to gather anonymous data on how you use GiveWP. This information helps us make GiveWP better for you. No personal information about you or your donors is collected.",
                        'give'
                    )}
                    checked={usageTracking}
                    onChange={(e) => dispatch(setUsageTracking(e.target.checked))}
                />
            </div>

            <footer className="give-obw-footer">
                <ContinueButton
                    testId="cause-continue-button"
                    clickCallback={() => {
                        saveSettingWithOnboardingAPI('usage_tracking', usageTracking ? 'enabled' : 'disabled');
                    }}
                />
            </footer>
        </div>
    );
};

export default YourCause;
