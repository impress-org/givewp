// Store dependencies
import {useState, Fragment} from 'react';
import {useSelector} from 'react-redux';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';

// Internal dependencies
import TabLink from '../tab-link';
import LogoutModal from '../logout-modal';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';

import './style.scss';

const TabMenu = () => {
    const [logoutModalOpen, setLogoutModalOpen] = useState(false);

    const tabsSelector = useSelector((state) => state.tabs);
    const tabLinks = Object.values(tabsSelector).map((tab, index) => {
        return <TabLink slug={tab.slug} label={tab.label} icon={tab.icon} key={index} />;
    });

    const toggleModal = () => {
        setLogoutModalOpen(!logoutModalOpen);
    };

    return (
        <Fragment>
            {logoutModalOpen && (
                <ModalDialog
                    wrapperClassName={'give-donor-dashboard-logout-modal'}
                    title={__('Are you sure you want to logout?', 'give')}
                    showHeader={true}
                    isOpen={logoutModalOpen}
                    handleClose={toggleModal}
                >
                    <LogoutModal onRequestClose={toggleModal} />
                </ModalDialog>
            )}
            <div className="give-donor-dashboard-tab-menu">
                {tabLinks}
                <div className="give-donor-dashboard-logout">
                    <div className="give-donor-dashboard-tab-link" onClick={toggleModal}>
                        <FontAwesomeIcon icon="sign-out-alt" /> {__('Logout', 'give')}
                    </div>
                </div>
            </div>
        </Fragment>
    );
};
export default TabMenu;
