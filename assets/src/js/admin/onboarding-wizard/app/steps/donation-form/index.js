const { __ } = wp.i18n;

import ContinueButton from '../../../components/continue-button';
import './style.scss';

const DonationForm = () => {
	return (
		<div className="give-obw-donation-form">
			<iframe src="https://google.com"></iframe>
			<div>
				<h1>{ __( 'Donation Form', 'give' ) }</h1>
				<ul>
					<li>{ __( 'List item', 'give' ) }</li>
					<li>{ __( 'List item', 'give' ) }</li>
					<li>{ __( 'List item', 'give' ) }</li>
					<li>{ __( 'List item', 'give' ) }</li>
					<li>{ __( 'List item', 'give' ) }</li>
				</ul>
				<ContinueButton />
			</div>
		</div>
	);
};

export default DonationForm;
