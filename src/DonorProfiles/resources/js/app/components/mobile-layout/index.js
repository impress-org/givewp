import MobileMenu from '../mobile-menu';
import TabMenu from '../tab-menu';
import TabContent from '../tab-content';
import DonorInfo from '../donor-info';

import './style.scss';

const MobileLayout = () => {
	return (
		<div className="give-donor-dashboard-mobile-layout">
			<MobileMenu>
				<div className="give-donor-dashboard-mobile-layout__tab-menu">
					<TabMenu />
				</div>
			</MobileMenu>
			<div className="give-donor-dashboard-mobile-layout__donor-info">
				<DonorInfo />
			</div>
			<div className="give-donor-dashboard-mobile-layout__tab-content">
				<TabContent />
			</div>
		</div>
	);
};
export default MobileLayout;
