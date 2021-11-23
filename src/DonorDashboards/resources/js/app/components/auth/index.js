import {isLoggedIn} from '../../utils';

import AuthModal from '../auth-modal';

import './style.scss';

const Auth = ({children}) => {
    const loggedIn = isLoggedIn();

    return !loggedIn ? (
        <div className="give-donor-dashboard__auth">
            <AuthModal />
            <div className="give-donor-dashboard__auth-wrapper">{children}</div>
        </div>
    ) : (
        children
    );
};

export default Auth;
