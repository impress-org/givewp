import { Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { __ } from '@wordpress/i18n';
;

const SubscriptionReceipt = ( { subscription } ) => {

	if ( subscription === undefined ) {
		return null;
	}

	const { receipt } = subscription;

	let donationSection;

	const sections = receipt.map( ( section, sectionIndex ) => {
		const lineItems = section.lineItems.map( ( item, itemIndex ) => {

			const value = typeof item.value === 'object' && item.value.color ? <Fragment><div className="give-donor-dashboard-donation-receipt__status-indicator" style={{background: item.value.color }}/>{item.value.label}</Fragment> : item.value;

			return (
				<div className={ `give-donor-dashboard-donation-receipt__row${ item.class.includes( 'total' ) ? ' give-donor-dashboard-donation-receipt__row--footer' : '' }` } key={ itemIndex }>
					<div className="give-donor-dashboard-donation-receipt__detail">
						{ item.icon && <FontAwesomeIcon icon={ item.icon } fixedWidth={ true } /> } { item.label }
					</div>
					<div className="give-donor-dashboard-donation-receipt__value">
						{ value }
					</div>
				</div>
			);
		} );

		if ( section.id === 'Donation' ) {
			donationSection = <div className="give-donor-dashboard-donation-receipt__table" key={ sectionIndex }>
				{ lineItems }
			</div>;
			return null;
		} else {
			return (
				<div className="give-donor-dashboard-donation-receipt__table" key={ sectionIndex }>
					{ lineItems }
				</div>
			);
		}
	} );

	// Ensure that "Donation" section is last
	sections.push(donationSection);

	return sections;
};
export default SubscriptionReceipt;
