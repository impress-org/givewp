// Dependencies
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getWindowData } from '../../utils';

// Components
import Tab from '../tab';

const Tabs = () => {
	const url = getWindowData( 'legacyReportsUrl' );

	const renderItems = () => {
		const displayItems = [];
		const items = applyFilters( 'givewp-reports-page-menu-links', [] );

		if ( Array.isArray( items ) && items.length ) {
			items.forEach( ( item, i ) => {
				if ( item.hasOwnProperty( 'href' ) && item.hasOwnProperty( 'text' ) ) {
					displayItems.push( <a key={ i } className="nav-tab" href={ item.href }>{ item.text }</a> );
				} else {
					// eslint-disable-next-line no-console
					console.warn( 'Extending GiveWP Reports Menu requires both href and text property for each menu item' );
				}
			} );
		}

		return displayItems;
	};

	return (
		<div className="nav-tab-wrapper give-nav-tab-wrapper">
			<Tab to="/">
				{ __( 'Overview', 'give' ) }
			</Tab>
			{ renderItems() }
			<a className="nav-tab" href={ url }>{ __( 'Legacy Reports', 'give' ) }</a>
		</div>
	);
};
export default Tabs;
