// Dependencies
const { __ } = wp.i18n;
import { getWindowData } from '../../utils';

// Components
import Tab from '../tab';

const Tabs = () => {
	const url = getWindowData( 'legacyReportsUrl' );

	return (
		<div className="nav-tab-wrapper give-nav-tab-wrapper">
			<Tab to="/">
                Overview
			</Tab>
			<a className="nav-tab" href={ url }>{ __( 'Legacy Reports', 'give' ) }</a>
		</div>
	);
};
export default Tabs;
