// Dependencies
const { __ } = wp.i18n;
const { applyFilters } = wp.hooks;
import { getWindowData } from '../../utils';

// Components
import Tab from '../tab';

const Tabs = () => {
	const url = getWindowData( 'legacyReportsUrl' );

	const renderItems = () => {
		const items = applyFilters( 'givewp-reports-page-menu-items', [] );

		if ( Array.isArray( items ) && items.length ) {
			return items
				.filter( ( item ) => item.hasOwnProperty( 'href' ) && item.hasOwnProperty( 'text' ) )
				.map( ( item, i ) => <a key={ i } className="nav-tab" href={ item.href }>{ item.text }</a> );
		}
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
