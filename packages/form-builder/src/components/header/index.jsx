import React from "react";
import PropTypes from 'prop-types';

const Header = ({contentLeft, contentMiddle, contentRight}) => {
    return <header style={{
        height: '60px',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingLeft: '1rem',
        paddingRight: '1rem'
    }}>
        <section style={{
            display: 'flex',
            gap: '1rem',
            alignItems: 'center',
            justifyContent: 'flex-start',
            flexBasis: '20%'
        }}>
            {contentLeft}
        </section>
        <section>
            {contentMiddle}
        </section>
        <section style={{
            display: 'flex',
            gap: '0.5rem',
            alignItems: 'center',
            flexBasis: '20%',
            justifyContent: 'flex-end',
        }}>
            {contentRight}
        </section>
    </header>
}

Header.propTypes = {
    contentLeft: PropTypes.node,
    contentMiddle: PropTypes.node,
    contentRight: PropTypes.node,
};

export default Header;
