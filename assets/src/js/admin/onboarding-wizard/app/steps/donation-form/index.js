// Import vendor dependencies
const { __ } = wp.i18n;

// Import components
import ContinueButton from '../../../components/continue-button';
import DonationFormComponent from '../../../components/donation-form';
import GradientChevronIcon from '../../../components/icons/gradient-chevron';

// Import styles
import './style.scss';

const DonationForm = () => {
	return (
		<div className="give-obw-donation-form">
			<DonationFormComponent />
			<div className="give-obw-donation-form__content">
				<h2>{ __( 'Check out your first donation form!', 'give' ) }</h2>
				<p>
					{ __( 'We\'ve created a donation form for you based on your responses.', 'give' ) }
				</p>

				<h3>{ __( 'After setup you can:', 'give' ) }</h3>
				<ul>
					<li>
						<GradientChevronIcon />
						{ __( 'Customize the text, color and image', 'give' ) }
					</li>
					<li>
						<GradientChevronIcon />
						{ __( 'Modify donation amounts and add a fundraising goal', 'give' ) }
					</li>
					<li>
						<GradientChevronIcon />
						{ __( 'Add or remove payment options', 'give' ) }
					</li>
					<li>
						<GradientChevronIcon />
						{ __( 'Extend with add-ons and more!', 'give' ) }
					</li>
				</ul>
				<ContinueButton />
			</div>
		</div>
	);
};

export default DonationForm;
