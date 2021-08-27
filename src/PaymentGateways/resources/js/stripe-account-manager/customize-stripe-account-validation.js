/**
 * Disconnect stripe account
 *
 * This will be used to disconnect any Stripe account from the list
 *
 * @since 2.13.0
 */
const {__, sprintf} = wp.i18n;

window.addEventListener('DOMContentLoaded', function () {
	const bodyClass = document.body.classList.contains('post-php') ? '.post-php' : '.post-new-php'
	const form = document.querySelector(`${bodyClass}.post-type-give_forms form[name="post"]`);

	if (!form) {
		return;
	}

	form.addEventListener('submit', event => {
		const stripeSetting = document.querySelector('#stripe_form_account_options [name="give_stripe_per_form_accounts"]:checked');

		// Do nothing if Stripe gateway is not active
		if( ! stripeSetting ) {
			return;
		}

		// Do nothing if Stripe account is not connected
		const isCustomizeStripeAccountOptionSelected = stripeSetting.value === 'enabled';
		if (!isCustomizeStripeAccountOptionSelected) {
			return;
		}

		// Do nothing if default Stripe account is used
		const hasDefaultStripeAccount = !!document.querySelector('#stripe_form_account_options .give-stripe-account-manager-list-item.give-stripe-boxshadow-option-wrap__selected');
		if ( hasDefaultStripeAccount ) {
			return;
		}

		event.preventDefault();
		new Give.modal.GiveNoticeAlert({
			 type: 'warning',
			 modalContent: {
				 title: __('Select Default Stripe Account', 'give'),
				 desc: __('We notice that you want to accept donation with other then global default Stripe Account but you did not selected Stripe account.', 'give'),
			 },
		 }).render();

		 // Open Stripe settings.
		 if ('stripe_form_account_options' !== Give.fn.getParameterByName('give_tab')) {
			 document.querySelector('a[href="#stripe_form_account_options"]').click();
		 }
	});
});
