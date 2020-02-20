// Dependencies
const { __ } = wp.i18n;

// Components
import Tab from '../tab';

const Tabs = () => {
	return (
		<div className="nav-tab-wrapper give-nav-tab-wrapper">
			<Tab to="/">
                Overview
			</Tab>
			<a className="nav-tab" href={ window.giveReportsData.legacyReportsUrl }>{ __( 'Legacy Reports', 'give' ) }</a>
		</div>
	);
};
export default Tabs;
