import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import './style.scss';

const DonationReceipt = ( { donation } ) => {
	if ( donation === undefined ) {
		return null;
	}

	const { receipt } = donation;

	return receipt.map( ( section, sectionIndex ) => {
		const lineItems = section.lineItems.map( ( item, itemIndex ) => {
			return (
				<div className={ `give-donor-profile-donation-receipt__row${ item.class.includes( 'total' ) ? ' give-donor-profile-donation-receipt__row--footer' : '' }` } key={ itemIndex }>
					<div className="give-donor-profile-donation-receipt__detail">
						{ item.icon && <FontAwesomeIcon icon={ item.icon } fixedWidth={ true } /> } { item.label }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ item.value }
					</div>
				</div>
			);
		} );
		return (
			<div className="give-donor-profile-donation-receipt__table" key={ sectionIndex }>
				{ lineItems }
			</div>
		);
	} );
};
export default DonationReceipt;
