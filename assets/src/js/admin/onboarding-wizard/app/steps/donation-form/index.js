// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import store dependencies
import {useStoreValue} from "../../store";

// Import components
import ContinueButton from '../../../components/continue-button';
import DonationFormComponent from '../../../components/donation-form';
import GradientChevronIcon from '../../../components/icons/gradient-chevron';

// Import styles
import './style.scss';

const DonationForm = () => {
    const [{configuration}, dispatch] = useStoreValue();

	return (
		<div className="give-obw-donation-form">
			<div className="give-obw-donation-form__preview">
				<DonationFormComponent formId={configuration.formId} />
			</div>
			<div className="give-obw-donation-form__content">
				<div className="give-obw-donation-form__fixed">
					<h1>{ __( 'Check out your first donation form!', 'give' ) }</h1>
					<p>
						{ __( 'This form is customized based on your responses.', 'give' ) }
					</p>

					<h2>{ __( 'After setup you can:', 'give' ) }</h2>
					<ul>
						<li>
							<GradientChevronIcon index={ 1 } />
							{ __( 'Customize the text, color and image', 'give' ) }
						</li>
						<li>
							<GradientChevronIcon index={ 2 } />
							{ __( 'Modify donation amounts and add a fundraising goal', 'give' ) }
						</li>
						<li>
							<GradientChevronIcon index={ 3 } />
							{ __( 'Add or remove payment options', 'give' ) }
						</li>
						<li>
							<GradientChevronIcon index={ 4 } />
							{ __( 'Extend with add-ons and more!', 'give' ) }
						</li>
					</ul>
					<ContinueButton testId="preview-continue-button" />
				</div>
			</div>
		</div>
	);
};

export default DonationForm;
