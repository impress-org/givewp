import {useState, useEffect, useRef} from 'react';
import {useLocation} from 'react-router-dom';
import {useSelector} from 'react-redux';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';

import './style.scss';

const MobileMenu = ({children}) => {
    const [isOpen, setIsOpen] = useState(false);

    const contentRef = useRef(null);

    useEffect(() => {
        const handleClick = (evt) => {
            if (contentRef.current && !contentRef.current.contains(evt.target)) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener('click', handleClick);
        }

        return function cleanup() {
            if (isOpen) {
                document.removeEventListener('click', handleClick);
            }
        };
    }, [isOpen, contentRef]);

    const location = useLocation();
    const tabsSelector = useSelector((state) => state.tabs);

    const slug = location.pathname.length > 2 ? location.pathname.split('/')[1] : 'dashboard';
    const label = tabsSelector[slug] ? tabsSelector[slug].label : null;

    return (
        <div className="give-donor-dashboard-mobile-menu">
            <div className="give-donor-dashboard-mobile-menu__header">
                <div className="give-donor-dashboard-mobile-menu__label">{label}</div>
                <div
                    className={`give-donor-dashboard-mobile-menu__toggle ${
                        isOpen ? 'give-donor-dashboard-mobile-menu__toggle--toggled' : ''
                    }`}
                    onClick={() => setIsOpen(!isOpen)}
                >
                    <FontAwesomeIcon icon="bars" />
                </div>
            </div>
            {isOpen && (
                <div className="give-donor-dashboard-mobile-menu__content" ref={contentRef}>
                    {children}
                </div>
            )}
        </div>
    );
};

export default MobileMenu;
