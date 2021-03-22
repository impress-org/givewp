import TabMenu from '../tab-menu';
import TabContent from '../tab-content';
import DonorInfo from '../donor-info';

import './style.scss';

const DesktopLayout = () => {
	return (
		<div className="give-donor-dashboard-desktop-layout">
			<div className="give-donor-dashboard-desktop-layout__donor-info">
				<DonorInfo />
			</div>
			<div className="give-donor-dashboard-desktop-layout__tab-menu">
				<TabMenu />
			</div>
			<div className="give-donor-dashboard-desktop-layout__tab-content">
				<TabContent />
			</div>
		</div>
	);
};
export default DesktopLayout;
